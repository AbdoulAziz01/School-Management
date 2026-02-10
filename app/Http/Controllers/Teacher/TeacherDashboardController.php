<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TeacherAssignment;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Récupérer les classes affectées via class_teacher
        $assignedClasses = $teacher->assignedClasses()->with('level')->get();
        $classIds = $assignedClasses->pluck('id')->toArray();
        
        // Nombre de classes
        $classesCount = $assignedClasses->count();
        
        // Nombre total d'élèves dans ces classes
        $studentsCount = User::whereIn('class_id', $classIds)
            ->whereIn('role', ['student', 'eleve'])
            ->where('status', 'approved')
            ->count();
        
        // Matières enseignées (via teacher_subjects ou teacher_assignments)
        $subjects = $teacher->subjects;
        $subjectsCount = $subjects->count();
        
        // Récupérer aussi les affectations TeacherAssignment si disponibles
        $assignments = TeacherAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->get();
        
        // Notes récentes saisies par cet enseignant
        $subjectIds = $subjects->pluck('id')->merge($assignments->pluck('subject_id'))->unique()->toArray();
        
        $recentGrades = Grade::with(['user', 'subject'])
            ->whereIn('subject_id', $subjectIds)
            ->whereIn('user_id', function($query) use ($classIds) {
                $query->select('id')
                    ->from('users')
                    ->whereIn('class_id', $classIds);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Calculer les moyennes par classe
        $classAverages = [];
        foreach ($assignedClasses as $class) {
            $studentIds = User::where('class_id', $class->id)
                ->whereIn('role', ['student', 'eleve'])
                ->pluck('id');
            
            $average = Grade::whereIn('user_id', $studentIds)
                ->whereIn('subject_id', $subjectIds)
                ->avg('grade');
            
            $classAverages[] = [
                'class' => $class,
                'average' => round($average ?? 0, 2)
            ];
        }
        
        return view('teacher.dashboard', compact(
            'teacher',
            'assignments',
            'classesCount',
            'studentsCount',
            'subjectsCount',
            'subjects',
            'recentGrades',
            'classAverages',
            'currentYear'
        ));
    }
}
