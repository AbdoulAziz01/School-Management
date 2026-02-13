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
        $classes = SchoolClass::with(['academicYear', 'level', 'students', 'teachers.subjects'])
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
                ->route('admin.classes.show', $class)
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
        
        // Récupérer les étudiants déjà affectés à cette classe
        $assignedStudents = $class->students()->paginate(10);
        
        // ========= STATISTIQUES DE LA CLASSE =========
        $studentIds = $class->students()->pluck('id')->toArray();
        
        // Moyenne générale de la classe
        $classAverage = 0;
        $totalGrades = 0;
        $passCount = 0; // >= 10/20
        $failCount = 0; // < 10/20
        $studentAverages = [];
        $gradeDistribution = [
            'excellent' => 0, // >= 16
            'good' => 0,      // >= 14
            'average' => 0,   // >= 12
            'passing' => 0,   // >= 10
            'failing' => 0    // < 10
        ];
        
        // Évolution par mois (pour le graphique)
        $monthlyAverages = [];
        
        if (count($studentIds) > 0) {
            // Calcul des moyennes par élève
            foreach ($studentIds as $studentId) {
                $grades = \App\Models\Grade::where('user_id', $studentId)
                    ->whereHas('subject')
                    ->get();
                
                if ($grades->count() > 0) {
                    $studentAvg = $grades->avg('grade');
                    $studentAverages[$studentId] = $studentAvg;
                    
                    // Distribution des notes
                    if ($studentAvg >= 16) {
                        $gradeDistribution['excellent']++;
                    } elseif ($studentAvg >= 14) {
                        $gradeDistribution['good']++;
                    } elseif ($studentAvg >= 12) {
                        $gradeDistribution['average']++;
                    } elseif ($studentAvg >= 10) {
                        $gradeDistribution['passing']++;
                    } else {
                        $gradeDistribution['failing']++;
                    }
                    
                    // Réussite / Échec
                    if ($studentAvg >= 10) {
                        $passCount++;
                    } else {
                        $failCount++;
                    }
                }
            }
            
            // Moyenne de la classe
            if (count($studentAverages) > 0) {
                $classAverage = array_sum($studentAverages) / count($studentAverages);
            }
            
            // Évolution mensuelle des notes (6 derniers mois)
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();
                
                $monthGrades = \App\Models\Grade::whereIn('user_id', $studentIds)
                    ->whereBetween('date', [$monthStart, $monthEnd])
                    ->get();
                
                $monthlyAverages[] = [
                    'month' => $month->translatedFormat('M Y'),
                    'average' => $monthGrades->count() > 0 ? round($monthGrades->avg('grade'), 2) : null,
                    'count' => $monthGrades->count()
                ];
            }
            
            $totalGrades = \App\Models\Grade::whereIn('user_id', $studentIds)->count();
        }
        
        // Meilleure et plus basse moyenne
        $bestAverage = count($studentAverages) > 0 ? max($studentAverages) : 0;
        $lowestAverage = count($studentAverages) > 0 ? min($studentAverages) : 0;
        $studentsWithGrades = count($studentAverages);
        
        // Statistiques compilées
        $classStats = [
            'average' => round($classAverage, 2),
            'best_average' => round($bestAverage, 2),
            'lowest_average' => round($lowestAverage, 2),
            'total_students' => count($studentIds),
            'students_with_grades' => $studentsWithGrades,
            'total_grades' => $totalGrades,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_rate' => $studentsWithGrades > 0 ? round(($passCount / $studentsWithGrades) * 100, 1) : 0,
            'grade_distribution' => $gradeDistribution,
            'monthly_averages' => $monthlyAverages
        ];
        
        return view('admin.classes.show', compact('class', 'availableTeachers', 'subjects', 'assignedStudents', 'classStats'));
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
                ->route('admin.classes.show', $class)
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
                ->route('admin.academic-years.show', $class->academic_year_id)
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
