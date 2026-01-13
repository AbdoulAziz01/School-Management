<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassController extends Controller
{
    /**
     * Affiche la liste des classes
     */
    public function index()
    {
        $classes = SchoolClass::with(['academicYear', 'level', 'students'])
            ->withCount('students')
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('name')
            ->paginate(15);
            
        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Affiche le formulaire de création d'une classe
     */
    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $levels = Level::orderBy('name')->get();
        
        // Si un academic_year_id est passé en paramètre
        $selectedAcademicYear = request('academic_year_id') 
            ? AcademicYear::find(request('academic_year_id'))
            : AcademicYear::where('is_current', true)->first();
            
        return view('admin.classes.create', compact('academicYears', 'levels', 'selectedAcademicYear'));
    }

    /**
     * Enregistre une nouvelle classe
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'capacity' => 'nullable|integer|min:1|max:50',
            'room_number' => 'nullable|string|max:20',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Vérifier si la classe existe déjà pour cette année
            $existingClass = SchoolClass::where('name', $validated['name'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->exists();
                
            if ($existingClass) {
                return back()
                    ->withInput()
                    ->with('error', 'Une classe avec ce nom existe déjà pour cette année scolaire.');
            }
            
            // Créer la classe
            $class = SchoolClass::create($validated);
            
            DB::commit();
            
            return redirect()
                ->route('classes.show', $class)
                ->with('success', 'La classe a été créée avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de la classe.');
        }
    }

    /**
     * Affiche les détails d'une classe
     */
    public function show(SchoolClass $class)
    {
        $class->load([
            'academicYear', 
            'level', 
            'students' => function($query) {
                $query->orderBy('name');
            },
            'teacherAssignments.teacher',
            'teacherAssignments.subject'
        ]);
        
        // Récupérer les enseignants disponibles pour l'ajout
        $availableTeachers = User::where('role', 'teacher')
            ->whereDoesntHave('teacherAssignments', function($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->orderBy('name')
            ->get();
            
        // Récupérer les matières disponibles
        $subjects = \App\Models\Subject::orderBy('name')->get();
        
        return view('admin.classes.show', compact('class', 'availableTeachers', 'subjects'));
    }

    /**
     * Affiche le formulaire d'édition d'une classe
     */
    public function edit(SchoolClass $class)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $levels = Level::orderBy('name')->get();
        
        return view('admin.classes.edit', compact('class', 'academicYears', 'levels'));
    }

    /**
     * Met à jour une classe existante
     */
    public function update(Request $request, SchoolClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'capacity' => 'nullable|integer|min:1|max:50',
            'room_number' => 'nullable|string|max:20',
        ]);
        
        try {
            // Vérifier si une autre classe avec le même nom existe déjà pour cette année
            $existingClass = SchoolClass::where('name', $validated['name'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('id', '!=', $class->id)
                ->exists();
                
            if ($existingClass) {
                return back()
                    ->withInput()
                    ->with('error', 'Une classe avec ce nom existe déjà pour cette année scolaire.');
            }
            
            // Mettre à jour la classe
            $class->update($validated);
            
            return redirect()
                ->route('classes.show', $class)
                ->with('success', 'La classe a été mise à jour avec succès.');
                
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour de la classe.');
        }
    }

    /**
     * Supprime une classe
     */
    public function destroy(SchoolClass $class)
    {
        try {
            DB::beginTransaction();
            
            // Vérifier s'il y a des étudiants dans la classe
            if ($class->students()->exists()) {
                return back()
                    ->with('error', 'Impossible de supprimer cette car elle contient des étudiants.');
            }
            
            // Supprimer les affectations d'enseignants liées à cette classe
            $class->teacherAssignments()->delete();
            
            // Supprimer la classe
            $class->delete();
            
            DB::commit();
            
            return redirect()
                ->route('academic-years.show', $class->academic_year_id)
                ->with('success', 'La classe a été supprimée avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Une erreur est survenue lors de la suppression de la classe.');
        }
    }
    
    /**
     * Affiche le formulaire d'ajout d'élèves à une classe
     */
    public function showAddStudents(SchoolClass $class)
    {
        $class->load('academicYear');
        
        // Récupérer les étudiants non affectés à une classe pour l'année en cours
        $availableStudents = User::where('role', 'student')
            ->where('status', 'approved')
            ->where(function($query) use ($class) {
                $query->whereNull('class_id')
                      ->orWhere('class_id', $class->id);
            })
            ->orderBy('name')
            ->get();
            
        return view('admin.classes.add-students', compact('class', 'availableStudents'));
    }
    
    /**
     * Ajoute des élèves à une classe
     */
    public function addStudents(Request $request, SchoolClass $class)
    {
        $request->validate([
            'students' => 'required|array',
            'students.*' => 'exists:users,id,role,student'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Mettre à jour les étudiants sélectionnés
            User::whereIn('id', $request->students)
                ->update(['class_id' => $class->id]);
                
            // Retirer les étudiants non sélectionnés de cette classe
            User::where('class_id', $class->id)
                ->whereNotIn('id', $request->students)
                ->update(['class_id' => null]);
            
            DB::commit();
            
            return redirect()
                ->route('classes.show', $class)
                ->with('success', 'La liste des élèves a été mise à jour avec succès.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour des élèves.');
        }
    }
}
