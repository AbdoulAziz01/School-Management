<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Afficher la liste des présences par classe
     */
    public function index(Request $request)
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Récupérer les classes de l'enseignant via class_teacher
        $classes = $teacher->assignedClasses()->with('level')->get();
        
        $selectedClassId = $request->get('class_id');
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $students = collect();
        $attendances = collect();
        
        if ($selectedClassId) {
            // Récupérer les élèves de la classe
            $students = User::where('class_id', $selectedClassId)
                ->whereIn('role', ['student', 'eleve'])
                ->where('status', 'approved')
                ->orderBy('name')
                ->get();
            
            // Récupérer les présences du jour
            $attendances = Attendance::where('date', $selectedDate)
                ->whereIn('user_id', $students->pluck('id'))
                ->get()
                ->keyBy('user_id');
        }
        
        return view('teacher.attendance.index', compact(
            'classes',
            'students',
            'attendances',
            'selectedClassId',
            'selectedDate'
        ));
    }

    /**
     * Enregistrer les présences
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.user_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused'
        ]);
        
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Vérifier l'accès à la classe via class_teacher
        $hasAccess = $teacher->assignedClasses()->where('classes.id', $request->class_id)->exists();
        
        if (!$hasAccess) {
            return back()->with('error', 'Vous n\'avez pas accès à cette classe.');
        }
        
        foreach ($request->attendances as $attendanceData) {
            Attendance::updateOrCreate(
                [
                    'user_id' => $attendanceData['user_id'],
                    'date' => $request->date,
                ],
                [
                    'status' => $attendanceData['status'],
                    'recorded_by' => $teacher->id,
                    'notes' => $attendanceData['notes'] ?? null,
                ]
            );
        }
        
        return redirect()->route('teacher.attendance.index', [
            'class_id' => $request->class_id,
            'date' => $request->date
        ])->with('success', 'Présences enregistrées avec succès.');
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
