<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendWhatsAppNotification;
use App\Services\NotificationService;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Afficher la liste des présences par classe
     */
    public function index(Request $request)
    {
        $teacher     = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $classes = $teacher->assignedClasses()->with('level')->get();

        $selectedClassId = $request->get('class_id');
        $selectedDate    = $request->get('date', Carbon::today()->format('Y-m-d'));
        $students        = collect();
        $attendances     = collect();

        if ($selectedClassId) {
            // Garde-fou IDOR : l'enseignant doit être affecté à cette classe
            $hasAccess = $teacher->assignedClasses()
                ->where('classes.id', $selectedClassId)
                ->exists();

            if (! $hasAccess) {
                return redirect()->route('teacher.attendance.index')
                    ->with('error', 'Vous n\'avez pas accès à cette classe.');
            }

            $students = User::where('class_id', $selectedClassId)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->orderBy('name')
                ->get();

            $attendances = $this->attendancesForClassDate(
                $students->pluck('id'),
                $selectedDate
            );
        }

        $attendanceLocked = $students->isNotEmpty() && $attendances->isNotEmpty();

        $subjects = $teacher->subjects()->get()
            ->merge(
                \App\Models\TeacherAssignment::with('subject')
                    ->where('teacher_id', $teacher->id)
                    ->active()
                    ->when($selectedClassId, fn ($q) => $q->where('class_id', $selectedClassId))
                    ->get()->pluck('subject')->filter()
            )->unique('id');

        return view('teacher.attendance.index', compact(
            'classes',
            'students',
            'attendances',
            'selectedClassId',
            'selectedDate',
            'attendanceLocked',
            'subjects'
        ));
    }

    /**
     * Enregistrer les présences
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id'              => 'required|exists:classes,id',
            'date'                  => 'required|date',
            'subject_id'            => 'nullable|exists:subjects,id',
            'start_time'            => 'nullable|date_format:H:i',
            'attendances'           => 'required|array',
            'attendances.*.user_id' => 'required|integer|exists:users,id',
            'attendances.*.status'  => 'required|in:present,absent,late,excused',
        ]);

        $teacher = Auth::user();

        // Garde-fou : prof affecté à la classe ?
        $hasAccess = $teacher->assignedClasses()
            ->where('classes.id', $request->class_id)
            ->exists();

        if (! $hasAccess) {
            return back()->with('error', 'Vous n\'avez pas accès à cette classe.');
        }

        // Garde-fou : tous les user_id soumis doivent être des élèves de cette classe
        $submittedUserIds = collect($request->attendances)->pluck('user_id')->unique();
        $validStudentIds  = User::where('class_id', $request->class_id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->whereIn('id', $submittedUserIds)
            ->pluck('id')
            ->all();

        if (count($validStudentIds) !== $submittedUserIds->count()) {
            return back()->with('error', 'Certains élèves ne font pas partie de cette classe.');
        }

        $schoolId = $teacher->school_id ?? TenantSchool::id();
        $date     = Carbon::parse($request->date)->toDateString();

        $alreadySaved = Attendance::withoutGlobalScopes()
            ->whereDate('date', $date)
            ->whereIn('user_id', $validStudentIds)
            ->exists();

        if ($alreadySaved) {
            return redirect()->route('teacher.attendance.index', [
                'class_id' => $request->class_id,
                'date'     => $date,
            ])->with('error', 'L\'appel de cette date est déjà enregistré et ne peut plus être modifié.');
        }

        $subjectId  = $request->input('subject_id');
        $startTime  = $request->input('start_time');
        $teacherId  = $teacher->id;

        // Indexer les remarques par user_id pour les retrouver dans la notification
        $remarksByUserId = collect($request->attendances)
            ->keyBy('user_id')
            ->map(fn ($a) => $a['notes'] ?? null);

        DB::transaction(function () use ($request, $schoolId, $date, $subjectId, $startTime, $teacherId) {
            foreach ($request->attendances as $attendanceData) {
                Attendance::withoutGlobalScopes()->create([
                    'user_id'    => (int) $attendanceData['user_id'],
                    'subject_id' => $subjectId ?: null,
                    'teacher_id' => $teacherId,
                    'class_id'   => (int) $request->class_id,
                    'date'       => $date,
                    'status'     => $attendanceData['status'],
                    'reason'     => $attendanceData['notes'] ?? null,
                    'start_time' => $startTime ?: null,
                    'school_id'  => $schoolId,
                ]);
            }
        });

        // ── Notifications WhatsApp aux parents des élèves absents ────────────
        $subjectName = $subjectId
            ? \App\Models\Subject::find($subjectId)?->name
            : null;

        $absentIds = collect($request->attendances)
            ->filter(fn (array $a) => ($a['status'] ?? '') === 'absent')
            ->pluck('user_id');

        if ($absentIds->isNotEmpty() && config('services.ultramsg.notify_parents_enabled')) {
            User::whereIn('id', $absentIds)
                ->whereNotNull('parent_whatsapp')
                ->get()
                ->each(function (User $student) use ($subjectName, $startTime, $teacher, $remarksByUserId) {
                    SendWhatsAppNotification::dispatchAfterResponse(
                        $student,
                        NotificationService::EVENT_ABSENCE,
                        [
                            'date'         => now()->format('d/m/Y'),
                            'subject'      => $subjectName,
                            'time'         => $startTime,
                            'teacher_name' => $teacher->name,
                            'remark'       => $remarksByUserId->get($student->id),
                        ]
                    );
                });
        }

        return redirect()->route('teacher.attendance.index', [
            'class_id' => $request->class_id,
            'date'     => $date,
        ])->with('success', 'Appel enregistré. Les présences de cette date sont définitives.');
    }

    /**
     * Historique des appels d'une classe : un jour par ligne, avec le
     * décompte présents/absents/retards/excusés, pour que l'enseignant
     * retrouve facilement ce qui a été saisi les jours précédents.
     */
    public function history(Request $request)
    {
        $teacher = Auth::user();
        $classes = $teacher->assignedClasses()->with('level')->get();

        $selectedClassId = $request->get('class_id');
        $days = collect();
        $selectedClass = null;

        if ($selectedClassId) {
            $selectedClass = $classes->firstWhere('id', (int) $selectedClassId);
            if (! $selectedClass) {
                return redirect()->route('teacher.attendance.history')
                    ->with('error', 'Vous n\'avez pas accès à cette classe.');
            }

            $days = Attendance::withoutGlobalScopes()
                ->where('class_id', $selectedClassId)
                ->get()
                ->groupBy(fn (Attendance $a) => $a->date->toDateString())
                ->map(function ($rows, $date) {
                    return [
                        'date' => $date,
                        'total' => $rows->count(),
                        'present' => $rows->where('status', 'present')->count(),
                        'absent' => $rows->where('status', 'absent')->count(),
                        'late' => $rows->where('status', 'late')->count(),
                        'excused' => $rows->where('status', 'excused')->count(),
                    ];
                })
                ->sortByDesc('date')
                ->values();
        }

        return view('teacher.attendance.history', compact(
            'classes',
            'selectedClassId',
            'selectedClass',
            'days',
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int, int>  $studentIds
     */
    private function attendancesForClassDate($studentIds, string $date): \Illuminate\Support\Collection
    {
        if ($studentIds instanceof \Illuminate\Support\Collection) {
            $studentIds = $studentIds->all();
        }

        if ($studentIds === []) {
            return collect();
        }

        $date = Carbon::parse($date)->toDateString();

        return Attendance::withoutGlobalScopes()
            ->whereDate('date', $date)
            ->whereIn('user_id', $studentIds)
            ->orderByDesc('updated_at')
            ->get()
            ->unique('user_id')
            ->keyBy(fn (Attendance $row) => (int) $row->user_id);
    }

    /**
     * Afficher l'historique des présences d'un élève
     */
    public function studentHistory($studentId)
    {
        $student = User::findOrFail($studentId);
        
        // Vérifier que l'enseignant a accès à cet élève via class_teacher
        $teacher = Auth::user();
        
        $hasAccess = $teacher->assignedClasses()->where('classes.id', $student->class_id)->exists();
        
        if (!$hasAccess) {
            return back()->with('error', 'Vous n\'avez pas accès à cet élève.');
        }
        
        $attendances = Attendance::where('user_id', $studentId)
            ->orderBy('date', 'desc')
            ->paginate(30);
        
        // Statistiques
        $stats = [
            'present' => Attendance::where('user_id', $studentId)->where('status', 'present')->count(),
            'absent' => Attendance::where('user_id', $studentId)->where('status', 'absent')->count(),
            'late' => Attendance::where('user_id', $studentId)->where('status', 'late')->count(),
            'excused' => Attendance::where('user_id', $studentId)->where('status', 'excused')->count(),
        ];
        
        return view('teacher.attendance.student-history', compact('student', 'attendances', 'stats'));
    }
}
