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

    public function edit(SchoolClass $class)
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

        return view('admin.schedules.edit', compact(
            'class',
            'schedules',
            'scheduleGrid',
            'days',
            'timeSlots',
            'subjects',
            'teachersBySubject',
            'teachersBySubjectJson',
            'defaultSubjectId'
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

        $duplicate = Schedule::where('class_id', $class->id)
            ->where('day_of_week', $validated['day_of_week'])
            ->whereTime('start_time', $slot['start'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'Un cours existe déjà à ce créneau pour cette classe.')->withInput();
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
            ->route('admin.schedules.edit', $class)
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

    /** @return Collection<int, Collection<int, User>> */
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
            User::whereIn('id', $classTeacherIds)
                ->whereIn('role', User::ROLE_TEACHER_ALIASES)
                ->get()
                ->each(function (User $teacher) use ($map) {
                    $subjectIds = DB::table('teacher_subjects')
                        ->where('teacher_id', $teacher->id)
                        ->pluck('subject_id');

                    foreach ($subjectIds as $subjectId) {
                        $this->addTeacherForSubject($map, (int) $subjectId, $teacher);
                    }
                });
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
}
