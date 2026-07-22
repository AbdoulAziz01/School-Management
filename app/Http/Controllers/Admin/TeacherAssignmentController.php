<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherAssignmentController extends Controller
{
    /**
     * Affiche la liste des affectations d'un enseignant
     * 
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\View\View
     */
    public function teacherAssignments(User $teacher)
    {
        $assignments = $teacher->teacherAssignments()
            ->with(['schoolClass', 'subject', 'academicYear'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('class_id')
            ->get()
            ->groupBy('academic_year_id');
            
        return view('admin.teachers.assignments', compact('teacher', 'assignments'));
    }
    
    /**
     * Affiche le formulaire d'ajout d'une affectation
     * 
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\View\View
     */
    public function createAssignment(User $teacher)
    {
        $academicYears = \App\Models\AcademicYear::orderBy('start_date', 'desc')->get();

        return view('admin.teachers.create-assignment', compact('teacher', 'academicYears'));
    }

    /**
     * Matières que cet enseignant peut se voir affecter pour cette classe
     * (ses matières générales — teacher_subjects — filtrées par les
     * matières actives du niveau, voir TeacherSubjectResolver). Alimente
     * le select "Matière" du formulaire d'affectation en AJAX, pour éviter
     * de présenter la liste complète des matières de l'établissement.
     */
    public function getSubjectsForTeacherClass(User $teacher, SchoolClass $class)
    {
        $subjects = \App\Support\TeacherSubjectResolver::forClass($teacher, $class);

        return response()->json($subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values());
    }
    
    /**
     * Enregistre une nouvelle affectation
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $teacher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeAssignment(Request $request, User $teacher)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Vérifier si l'affectation existe déjà
            $existingAssignment = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('class_id', $validated['class_id'])
                ->where('subject_id', $validated['subject_id'])
                ->exists();
                
            if ($existingAssignment) {
                return back()
                    ->withInput()
                    ->with('error', 'Cette affectation existe déjà.');
            }
            
            // Créer l'affectation. school_id est fixé explicitement à partir
            // de l'enseignant (plutôt que de dépendre du contexte tenant en
            // session) : c'est la valeur correcte dans tous les cas et ça
            // évite une violation NOT NULL si le contexte session n'est pas
            // celui attendu au moment de l'appel.
            $assignment = new TeacherAssignment($validated);
            $assignment->teacher_id = $teacher->id;
            $assignment->school_id = $teacher->school_id;
            $assignment->save();
            
            DB::commit();
            
            return redirect()
                ->route('admin.teachers.assignments', $teacher)
                ->with('success', 'Affectation enregistrée avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'enregistrement de l\'affectation : ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement de l\'affectation.');
        }
    }
    
    /**
     * Supprime une affectation
     * 
     * @param  \App\Models\TeacherAssignment  $assignment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAssignment(TeacherAssignment $assignment)
    {
        try {
            $assignment->delete();

            return back()->with('success', 'Affectation supprimée avec succès.');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'affectation : ' . $e->getMessage());
            
            return back()
                ->with('error', 'Une erreur est survenue lors de la suppression de l\'affectation.');
        }
    }
    
    /**
     * Récupère les classes d'une année scolaire pour le formulaire d'affectation
     * 
     * @param  int  $academicYearId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClasses($academicYearId)
    {
        $classes = SchoolClass::where(SchoolClass::column('academic_year_id'), $academicYearId)
            ->orderedByLevel()
            ->get(['id', 'name']);
            
        return response()->json($classes);
    }
}
