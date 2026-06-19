<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TeacherAssignment;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherClassesController extends Controller
{
    /**
     * Afficher la liste des classes de l'enseignant
     */
    public function index()
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Récupérer les classes affectées via la table class_teacher
        $assignedClasses = $teacher->assignedClasses()->with('level')->get();
        
        // Construire la liste des classes avec les informations
        $classes = collect();
        foreach ($assignedClasses as $class) {
            // Compter les élèves de cette classe
            $studentsCount = User::where('class_id', $class->id)
                ->whereIn('role', ['student', 'eleve'])
                ->where('status', 'approved')
                ->count();
            
            // Récupérer les matières enseignées par ce prof dans cette classe (si disponible)
            $subjects = TeacherAssignment::with('subject')
                ->where('teacher_id', $teacher->id)
                ->where('class_id', $class->id)
                ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
                ->get()
                ->pluck('subject')
                ->filter();
            
            // Si pas d'affectation de matières, récupérer les matières du prof
            if ($subjects->isEmpty()) {
                $subjects = $teacher->subjects()->get();
            }
            
            $classes->push([
                'class' => $class,
                'subjects' => $subjects,
                'students_count' => $studentsCount
            ]);
        }
        
        return view('teacher.classes.index', compact('classes', 'currentYear'));
    }

    /**
     * Afficher le détail d'une classe
     */
    public function show($id)
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Vérifier que l'enseignant a bien accès à cette classe via class_teacher
        $hasAccess = $teacher->assignedClasses()->where('classes.id', $id)->exists();
        
        if (!$hasAccess) {
            return redirect()->route('teacher.classes.index')
                ->with('error', 'Vous n\'avez pas accès à cette classe.');
        }
        
        $class = SchoolClass::with('level')->findOrFail($id);
        
        // Récupérer les matières via teacher_assignments ou les matières du prof
        $assignments = TeacherAssignment::with(['subject'])
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->get();
        
        $subjects = $assignments->pluck('subject')->filter();
        if ($subjects->isEmpty()) {
            $subjects = $teacher->subjects()->get();
        }
        
        // Récupérer les élèves de la classe
        $students = User::where('class_id', $id)
            ->whereIn('role', ['student', 'eleve'])
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();
        
        return view('teacher.classes.show', compact('class', 'subjects', 'students', 'assignments'));
    }
}
