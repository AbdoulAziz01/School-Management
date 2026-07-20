<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Support\ScheduleHelper;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ScheduleController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['academicYear', 'level'])
            ->withCount('schedules')
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.schedules.index', compact('classes'));
    }

    public function edit(SchoolClass $class, Request $request)
    {
        $class->load(['academicYear', 'level']);

        $schedules = Schedule::with(['subject', 'teacher', 'schoolClass'])
            ->where('class_id', $class->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $scheduleGrid = ScheduleHelper::buildGrid($schedules);
        $days         = ScheduleHelper::DAYS;
        $timeSlots    = ScheduleHelper::timeSlots();

        $subjects = $this->subjectsForClass($class);
        $teachersBySubject = $this->teachersBySubjectForClass($class);

        $teachersBySubjectJson = $teachersBySubject->map(
            fn ($teachers) => $teachers->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values()
        );

        $defaultSubjectId = $subjects->first(
            fn ($s) => ($teachersBySubject[(int) $s->id] ?? collect())->isNotEmpty()
        )?->id;

        // Prochain créneau libre pour cette classe : pré-rempli comme
        // valeur par défaut du formulaire. On reste sur le jour où
        // l'utilisateur vient de saisir (transmis par store() via ?day=)
        // tant qu'il reste des créneaux libres ce jour-là — on ne passe au
        // jour suivant que lorsqu'il est complet, jamais de retour en
        // arrière vers un jour antérieur déjà entamé.
        $startDay = $request->integer('day') ?: 1;
        if (! array_key_exists($startDay, ScheduleHelper::DAYS)) {
            $startDay = 1;
        }
        $nextAvailableSlot = $this->nextAvailableSlot($schedules, $startDay);

        // Dernière salle utilisée par matière (dans cette classe) :
        // suggestion pré-remplie mais toujours modifiable.
        $roomSuggestions = $this->roomSuggestionsBySubject($schedules);

        return view('admin.schedules.edit', compact(
            'class',
            'schedules',
            'scheduleGrid',
            'days',
            'timeSlots',
            'subjects',
            'teachersBySubject',
            'teachersBySubjectJson',
            'defaultSubjectId',
            'nextAvailableSlot',
            'roomSuggestions'
        ));
    }

    public function store(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', Rule::in(array_keys(ScheduleHelper::DAYS))],
            'time_slot'   => ['required', 'string', Rule::in(ScheduleHelper::slotLabels())],
            'subject_id'  => ['required', 'exists:subjects,id'],
            'teacher_id'  => ['required', 'exists:users,id'],
            'room'        => ['nullable', 'string', 'max:50'],
        ]);

        $slot = collect(ScheduleHelper::timeSlots())->firstWhere('label', $validated['time_slot']);
        if (! $slot) {
            return back()->with('error', 'Créneau horaire invalide.')->withInput();
        }

        $subjectIds = $this->subjectsForClass($class)->pluck('id');
        if (! $subjectIds->contains((int) $validated['subject_id'])) {
            return back()->with('error', 'Cette matière n\'est pas disponible pour cette classe.')->withInput();
        }

        if (! $this->teacherTeachesSubjectInClass($class, (int) $validated['teacher_id'], (int) $validated['subject_id'])) {
            return back()->with('error', 'Ce professeur n\'est pas affecté à cette classe et matière.')->withInput();
        }

        // Conflit n°1 : un autre cours existe déjà pour CETTE classe à ce
        // jour/heure.
        $duplicate = Schedule::where('class_id', $class->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->whereTime('start_time', $slot['start'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'Un cours existe déjà à ce créneau pour cette classe.')->withInput();
        }

        // Conflit n°2 : le professeur est déjà occupé dans une AUTRE classe
        // au même jour/heure.
        $teacherBusy = Schedule::with('schoolClass')
            ->where('teacher_id', $validated['teacher_id'])
            ->where('day_of_week', $validated['day_of_week'])
            ->whereTime('start_time', $slot['start'])
            ->where('class_id', '!=', $class->id)
            ->first();

        if ($teacherBusy) {
            $teacherName = User::find($validated['teacher_id'])?->name ?? 'Ce professeur';
            $busyClassName = $teacherBusy->schoolClass?->name ?? 'une autre classe';

            return back()
                ->with('error', "{$teacherName} est déjà occupé(e) avec {$busyClassName} à ce créneau ({$slot['label']}).")
                ->withInput();
        }

        // Conflit n°3 : la salle (si renseignée) est déjà occupée à ce
        // jour/heure par une autre classe.
        if (! empty($validated['room'])) {
            $roomBusy = Schedule::with('schoolClass')
                ->where('room', $validated['room'])
                ->where('day_of_week', $validated['day_of_week'])
                ->whereTime('start_time', $slot['start'])
                ->where('class_id', '!=', $class->id)
                ->first();

            if ($roomBusy) {
                $busyClassName = $roomBusy->schoolClass?->name ?? 'une autre classe';

                return back()
                    ->with('error', "La salle « {$validated['room']} » est déjà occupée par {$busyClassName} à ce créneau ({$slot['label']}).")
                    ->withInput();
            }
        }

        Schedule::create([
            'class_id'    => $class->id,
            'subject_id'  => $validated['subject_id'],
            'teacher_id'  => $validated['teacher_id'],
            'day_of_week' => $validated['day_of_week'],
            'start_time'  => $slot['start'],
            'end_time'    => $slot['end'],
            'room'        => $validated['room'] ?? null,
            'school_id'   => $class->school_id ?? TenantSchool::id(),
        ]);

        return redirect()
            ->route('admin.schedules.edit', ['class' => $class, 'day' => $validated['day_of_week']])
            ->with('success', 'Créneau ajouté à l\'emploi du temps.');
    }

    public function destroy(Schedule $schedule)
    {
        $class = $schedule->schoolClass;
        $schedule->delete();

        return redirect()
            ->route('admin.schedules.edit', $class)
            ->with('success', 'Créneau supprimé.');
    }

    private function subjectsForClass(SchoolClass $class)
    {
        $fromPivot = $class->subjects()->orderBy('name')->get();
        if ($fromPivot->isNotEmpty()) {
            return $fromPivot;
        }

        if ($class->level_id) {
            return Subject::whereHas('levels', fn ($q) => $q->where('levels.id', $class->level_id))
                ->orderBy('name')
                ->get();
        }

        return collect();
    }

    /**
     * Matières → enseignants affectés, pour l'auto-remplissage du champ
     * Professeur. Deux sources : TeacherAssignment (affectation explicite
     * classe+matière) et, à défaut, class_teacher + teacher_subjects
     * (professeur rattaché à la classe qui enseigne cette matière). Les
     * deux requêtes de la seconde source sont chargées en une seule fois
     * (pas de N+1 par professeur).
     *
     * @return Collection<int, Collection<int, User>>
     */
    private function teachersBySubjectForClass(SchoolClass $class): Collection
    {
        $map = collect();

        TeacherAssignment::with('teacher')
            ->where('class_id', $class->id)
            ->get()
            ->each(function (TeacherAssignment $row) use ($map) {
                if (! $row->teacher) {
                    return;
                }
                $this->addTeacherForSubject($map, (int) $row->subject_id, $row->teacher);
            });

        $classTeacherIds = DB::table('class_teacher')
            ->where('class_id', $class->id)
            ->pluck('teacher_id');

        if ($classTeacherIds->isNotEmpty()) {
            $teachers = User::whereIn('id', $classTeacherIds)
                ->whereIn('role', User::ROLE_TEACHER_ALIASES)
                ->get()
                ->keyBy('id');

            // Une seule requête pour tous les professeurs de la classe,
            // plutôt qu'une requête teacher_subjects par professeur dans
            // la boucle.
            $subjectIdsByTeacher = DB::table('teacher_subjects')
                ->whereIn('teacher_id', $teachers->keys())
                ->get(['teacher_id', 'subject_id'])
                ->groupBy('teacher_id');

            foreach ($subjectIdsByTeacher as $teacherId => $rows) {
                $teacher = $teachers->get($teacherId);
                if (! $teacher) {
                    continue;
                }
                foreach ($rows as $row) {
                    $this->addTeacherForSubject($map, (int) $row->subject_id, $teacher);
                }
            }
        }

        return $map;
    }

    private function addTeacherForSubject(Collection $map, int $subjectId, User $teacher): void
    {
        $teachers = $map->get($subjectId, collect());
        if ($teachers->contains('id', $teacher->id)) {
            return;
        }
        $map->put($subjectId, $teachers->push($teacher)->values());
    }

    private function teacherTeachesSubjectInClass(SchoolClass $class, int $teacherId, int $subjectId): bool
    {
        return ($this->teachersBySubjectForClass($class)[$subjectId] ?? collect())
            ->contains('id', $teacherId);
    }

    /**
     * Premier créneau (jour, horaire) encore libre pour cette classe, dans
     * l'ordre lundi→samedi puis 08h→17h — passe au jour suivant dès que le
     * jour courant est complet. Retourne null si la semaine est pleine.
     *
     * @param  Collection<int, Schedule>  $schedules
     * @return array{day: int, time_slot: string}|null
     */
    /**
     * Cherche à partir de $startDay (le jour où l'utilisateur vient de
     * saisir un créneau, transmis par store() via ?day=) et avance
     * uniquement vers l'avant — jamais de retour à un jour antérieur déjà
     * entamé. Ne passe au jour suivant que lorsque $startDay est complet.
     */
    private function nextAvailableSlot(Collection $schedules, int $startDay = 1): ?array
    {
        $occupied = $schedules
            ->map(fn (Schedule $s) => (int) $s->day_of_week.'|'.ScheduleHelper::formatTime($s->start_time))
            ->flip();

        foreach (array_keys(ScheduleHelper::DAYS) as $day) {
            if ($day < $startDay) {
                continue;
            }
            foreach (ScheduleHelper::timeSlots() as $slot) {
                $key = $day.'|'.$slot['start'];
                if (! isset($occupied[$key])) {
                    return ['day' => $day, 'time_slot' => $slot['label']];
                }
            }
        }

        return null;
    }

    /**
     * Dernière salle utilisée pour chaque matière dans cette classe — sert
     * de suggestion pré-remplie (mais toujours modifiable) côté formulaire.
     *
     * @param  Collection<int, Schedule>  $schedules
     * @return array<int, string>
     */
    private function roomSuggestionsBySubject(Collection $schedules): array
    {
        return $schedules
            ->filter(fn (Schedule $s) => filled($s->room) && $s->subject_id)
            ->sortByDesc('id')
            ->unique('subject_id')
            ->pluck('room', 'subject_id')
            ->all();
    }
}
