<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TeacherAssignment;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Support\TeacherSubjectResolver;
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
                ->active()
                ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
                ->get()
                ->pluck('subject')
                ->filter();
            
            // Si pas d'affectation de matières, récupérer les matières du prof
            if ($subjects->isEmpty()) {
                $subjects = $teacher->subjects()->get();
            }

            // Une matière désactivée par l'admin pour ce niveau (grille
            // "Notation primaire") ne doit plus apparaître ici, même si
            // elle reste dans teacher_subjects ou une ancienne affectation.
            $subjects = TeacherSubjectResolver::filterActiveForLevel($subjects, $class);

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
        // (non filtré sur is_active : la vue affiche aussi les matières
        // désactivées par le prof, avec un bouton pour les réactiver).
        $assignments = TeacherAssignment::with(['subject'])
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->get();

        $subjects = $assignments->where('is_active', true)->pluck('subject')->filter();
        if ($assignments->isEmpty()) {
            $subjects = $teacher->subjects()->get();
        }

        // Une matière désactivée par l'admin pour ce niveau (grille
        // "Notation primaire") ne doit plus apparaître ici, même si elle
        // reste dans teacher_subjects ou une ancienne affectation.
        $subjects = TeacherSubjectResolver::filterActiveForLevel($subjects, $class);

        // Récupérer les élèves de la classe
        $students = User::where('class_id', $id)
            ->whereIn('role', ['student', 'eleve'])
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();
        
        return view('teacher.classes.show', compact('class', 'subjects', 'students', 'assignments'));
    }

    /**
     * Active/désactive une matière pour une classe donnée — un enseignant
     * (notamment au primaire, où toutes les matières lui sont assignées
     * d'office) peut ainsi indiquer qu'il n'assure pas réellement telle
     * matière, sans intervention de l'administrateur.
     */
    public function toggleAssignment(TeacherAssignment $assignment)
    {
        abort_unless($assignment->teacher_id === Auth::id(), 403);

        $assignment->update(['is_active' => ! $assignment->is_active]);

        return back()->with(
            'success',
            $assignment->is_active
                ? 'Matière réactivée.'
                : 'Matière désactivée — elle n\'apparaîtra plus pour la saisie de notes ou l\'appel.'
        );
    }
}
