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

        return view('teacher.attendance.index', compact(
            'classes',
            'students',
            'attendances',
            'selectedClassId',
            'selectedDate',
            'attendanceLocked'
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

        DB::transaction(function () use ($request, $schoolId, $date) {
            foreach ($request->attendances as $attendanceData) {
                Attendance::withoutGlobalScopes()->create([
                    'user_id'   => (int) $attendanceData['user_id'],
                    'date'      => $date,
                    'status'    => $attendanceData['status'],
                    'reason'    => $attendanceData['notes'] ?? null,
                    'school_id' => $schoolId,
                ]);
            }
        });

        return redirect()->route('teacher.attendance.index', [
            'class_id' => $request->class_id,
            'date'     => $date,
        ])->with('success', 'Appel enregistré. Les présences de cette date sont définitives.');
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
