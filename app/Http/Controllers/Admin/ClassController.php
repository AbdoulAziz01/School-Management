<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\User;
use Carbon\Carbon;
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
        $academicYear = $class->academicYear;
        
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
        
        $applyAcademicYearFilter = function ($query) use ($academicYear) {
            if (!$academicYear) {
                return $query;
            }

            return $query->where(function ($subQuery) use ($academicYear) {
                $subQuery->where('academic_year_id', $academicYear->id);

                if ($academicYear->start_date && $academicYear->end_date) {
                    $subQuery->orWhere(function ($dateQuery) use ($academicYear) {
                        $dateQuery->whereNull('academic_year_id')
                            ->whereBetween('date', [$academicYear->start_date, $academicYear->end_date]);
                    });
                }
            });
        };

        $calculateWeightedAverage = function ($grades) {
            if ($grades->isEmpty()) {
                return null;
            }

            $weightedSum = 0;
            $totalCoef = 0;

            foreach ($grades->groupBy('subject_id') as $subjectGrades) {
                $subjectAvg = $subjectGrades->avg('grade');
                if ($subjectAvg === null) {
                    continue;
                }

                $coefficient = $subjectGrades->first()->subject->coefficient ?? 1;
                $weightedSum += $subjectAvg * $coefficient;
                $totalCoef += $coefficient;
            }

            return $totalCoef > 0 ? $weightedSum / $totalCoef : null;
        };

        if (count($studentIds) > 0) {
            $allGradesQuery = \App\Models\Grade::whereIn('user_id', $studentIds)
                ->whereHas('subject')
                ->with('subject');
            $allGradesQuery = $applyAcademicYearFilter($allGradesQuery);
            $allGrades = $allGradesQuery->get();

            // Calcul des moyennes par élève (pondérées par coefficient)
            foreach ($studentIds as $studentId) {
                $studentGrades = $allGrades->where('user_id', $studentId);
                $studentAvg = $calculateWeightedAverage($studentGrades);

                if ($studentAvg !== null) {
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
            
            $monthlyAverages = $this->buildMonthlyPeriodAverages(
                $studentIds,
                $allGrades,
                $calculateWeightedAverage,
                $academicYear
            );

            $hasChartData = collect($monthlyAverages)->contains(
                fn ($row) => $row['average'] !== null
            );

            if (! $hasChartData && $allGrades->isNotEmpty()) {
                $gradeStart = Carbon::parse($allGrades->min('date'))->startOfMonth();
                $gradeEnd = Carbon::parse($allGrades->max('date'))->endOfMonth();
                if ($gradeEnd->gt(now())) {
                    $gradeEnd = now()->copy()->endOfMonth();
                }

                $monthlyAverages = $this->buildMonthlyPeriodAverages(
                    $studentIds,
                    $allGrades,
                    $calculateWeightedAverage,
                    $academicYear,
                    $gradeStart,
                    $gradeEnd
                );
            }

            $totalGrades = $allGrades->count();
        }

        $evolutionPeriod = null;
        if (count($monthlyAverages) > 0) {
            $firstLabel = $monthlyAverages[0]['month'];
            $lastLabel = $monthlyAverages[count($monthlyAverages) - 1]['month'];
            $evolutionPeriod = $firstLabel . ' — ' . $lastLabel;
        }

        $studentsWithGrades = count($studentAverages);
        $bestAverage = $studentsWithGrades > 0 ? max($studentAverages) : 0;
        $lowestAverage = $studentsWithGrades > 0 ? min($studentAverages) : 0;

        $studentInsights = $this->buildClassStudentInsights($studentAverages);

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
            'monthly_averages' => $monthlyAverages,
            'evolution_period' => $evolutionPeriod,
            'best_student' => $studentInsights['best_student'],
            'lowest_student' => $studentInsights['lowest_student'],
            'ranking' => $studentInsights['ranking'],
            'passing_students' => $studentInsights['passing_students'],
            'failing_students' => $studentInsights['failing_students'],
            'students_by_bucket' => $studentInsights['students_by_bucket'],
        ];
        
        return view('admin.classes.show', compact('class', 'availableTeachers', 'subjects', 'assignedStudents', 'classStats'));
    }

    /**
     * Période mensuelle de l'année scolaire (début → fin, plafonnée à aujourd'hui).
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function getAcademicYearMonthRange(?AcademicYear $academicYear): array
    {
        $now = Carbon::now();

        if ($academicYear?->start_date && $academicYear?->end_date) {
            $start = Carbon::parse($academicYear->start_date)->startOfMonth();
            $end = Carbon::parse($academicYear->end_date)->endOfMonth();

            // Ex. : nous sommes en mai 2026 mais l'année en BDD commence en oct. 2026
            if ($now->lt($start)) {
                $start->subYear();
                $end->subYear();
            }
        } else {
            $start = $now->copy()->month(9)->day(1)->startOfMonth();
            if ($now->month < 9) {
                $start->subYear();
            }
            $end = $start->copy()->addMonths(9)->endOfMonth();
        }

        if ($end->gt($now)) {
            $end = $now->copy()->endOfMonth();
        }

        if ($start->gt($end)) {
            $start = $end->copy()->startOfMonth();
        }

        return [$start, $end];
    }

    /**
     * Moyenne de classe par mois (notes saisies uniquement dans le mois concerné).
     */
    private function buildMonthlyPeriodAverages(
        array $studentIds,
        $allGrades,
        callable $calculateWeightedAverage,
        ?AcademicYear $academicYear,
        ?Carbon $periodStartOverride = null,
        ?Carbon $periodEndOverride = null
    ): array {
        if ($allGrades->isEmpty() || count($studentIds) === 0) {
            return [];
        }

        if ($periodStartOverride && $periodEndOverride) {
            $periodStart = $periodStartOverride->copy()->startOfMonth();
            $periodEnd = $periodEndOverride->copy()->endOfMonth();
        } else {
            [$periodStart, $periodEnd] = $this->getAcademicYearMonthRange($academicYear);
        }

        $monthlyAverages = [];
        $currentMonth = $periodStart->copy();

        while ($currentMonth <= $periodEnd) {
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();

            $monthGrades = $allGrades->filter(function ($grade) use ($monthStart, $monthEnd) {
                $date = $grade->date;

                return $date && $date->between($monthStart, $monthEnd);
            });

            $monthlyStudentAverages = collect($studentIds)
                ->map(function ($studentId) use ($monthGrades, $calculateWeightedAverage) {
                    return $calculateWeightedAverage(
                        $monthGrades->where('user_id', $studentId)
                    );
                })
                ->filter(fn ($average) => $average !== null);

            $monthAverage = $monthlyStudentAverages->count() > 0
                ? $monthlyStudentAverages->avg()
                : null;

            $monthlyAverages[] = [
                'month' => $currentMonth->translatedFormat('M Y'),
                'average' => $monthAverage !== null ? round($monthAverage, 2) : null,
                'count' => $monthGrades->count(),
            ];

            $currentMonth->addMonth();
        }

        return $monthlyAverages;
    }

    /**
     * @param  array<int, float>  $studentAverages
     * @return array<string, mixed>
     */
    private function buildClassStudentInsights(array $studentAverages): array
    {
        if ($studentAverages === []) {
            return [
                'best_student' => null,
                'lowest_student' => null,
                'ranking' => [],
                'passing_students' => [],
                'failing_students' => [],
                'students_by_bucket' => [
                    'excellent' => [],
                    'good' => [],
                    'average' => [],
                    'passing' => [],
                    'failing' => [],
                ],
            ];
        }

        $users = User::query()
            ->whereIn('id', array_keys($studentAverages))
            ->get(['id', 'name', 'identifier', 'email'])
            ->keyBy('id');

        $ranking = [];
        $passing = [];
        $failing = [];
        $buckets = [
            'excellent' => [],
            'good' => [],
            'average' => [],
            'passing' => [],
            'failing' => [],
        ];

        foreach ($studentAverages as $studentId => $average) {
            $user = $users->get($studentId);
            if (! $user) {
                continue;
            }

            $rounded = round($average, 2);
            $entry = [
                'id' => $user->id,
                'name' => $user->name,
                'identifier' => $user->identifier,
                'email' => $user->email,
                'average' => $rounded,
                'url' => route('admin.students.show', $user),
            ];

            $ranking[] = $entry;

            if ($rounded >= 10) {
                $passing[] = $entry;
            } else {
                $failing[] = $entry;
            }

            if ($rounded >= 16) {
                $buckets['excellent'][] = $entry;
            } elseif ($rounded >= 14) {
                $buckets['good'][] = $entry;
            } elseif ($rounded >= 12) {
                $buckets['average'][] = $entry;
            } elseif ($rounded >= 10) {
                $buckets['passing'][] = $entry;
            } else {
                $buckets['failing'][] = $entry;
            }
        }

        usort($ranking, fn ($a, $b) => $b['average'] <=> $a['average']);
        usort($passing, fn ($a, $b) => $b['average'] <=> $a['average']);
        usort($failing, fn ($a, $b) => $b['average'] <=> $a['average']);

        foreach ($buckets as $key => $list) {
            usort($buckets[$key], fn ($a, $b) => $b['average'] <=> $a['average']);
        }

        $best = $ranking[0] ?? null;
        $lowest = $ranking[count($ranking) - 1] ?? null;

        return [
            'best_student' => $best,
            'lowest_student' => $lowest,
            'ranking' => $ranking,
            'passing_students' => $passing,
            'failing_students' => $failing,
            'students_by_bucket' => $buckets,
        ];
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
