<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormationDepartment;
use App\Models\FormationPromotion;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\User;
use App\Support\ClassHistoricalContext;
use App\Support\ClosedAcademicYearGuard;
use App\Support\FormationLevelResolver;
use App\Support\SchoolSubjectProvisioner;
use App\Support\SenegalFormationDiplomas;
use App\Support\TenantSchool;
use App\Services\StudentClassPromotionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    /**
     * Nombre de classes déjà créées pour un niveau et une année (suggestion CE1, CE1 A…).
     */
    public function countByLevel(Request $request)
    {
        $validated = $request->validate([
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
        ]);

        $count = SchoolClass::query()
            ->where(SchoolClass::column('level_id'), $validated['level_id'])
            ->where(SchoolClass::column('academic_year_id'), $validated['academic_year_id'])
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Affiche la liste des classes
     */
    public function index()
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;
        $isFormationSchool = $school?->isFormation() ?? false;

        $classes = SchoolClass::with([
            'academicYear',
            'level',
            'formationDepartment',
            'formationPromotion.formationDepartment',
            'students',
            'teachers.subjects',
        ])
            ->withCount('students')
            ->orderByDesc(SchoolClass::column('academic_year_id'))
            ->orderedByLevel()
            ->paginate(15);

        $promotionService = app(StudentClassPromotionService::class);

        $promotionYear = AcademicYear::where('is_closed', true)
            ->orderByDesc('start_date')
            ->get()
            ->first(fn (AcademicYear $year) => $promotionService->countPendingBulkPromotions($year) > 0);

        $canProcessPromotions = ($school?->supportsAutomaticClassPromotion() ?? false) && $promotionYear !== null;

        $cohortCounts = $promotionService->cohortCountsForClasses($classes);

        return view('admin.classes.index', compact(
            'classes',
            'isFormationSchool',
            'canProcessPromotions',
            'promotionYear',
            'cohortCounts',
        ));
    }

    /**
     * Affiche le formulaire de création d'une classe
     */
    public function create()
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;

        if ($school?->isClassic()) {
            SchoolSubjectProvisioner::ensureForSchool($schoolId);
        }

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $levels = Level::orderedPedagogically()->get();
        $isFormationSchool = $school?->isFormation() ?? false;

        if ($academicYears->isEmpty()) {
            return redirect()
                ->route('admin.academic-years.index')
                ->with('error', 'Créez d\'abord une année scolaire avant d\'ajouter une classe.');
        }

        $selectedAcademicYear = request('academic_year_id')
            ? AcademicYear::find(request('academic_year_id'))
            : AcademicYear::where('is_current', true)->first();

        if (! $selectedAcademicYear) {
            $selectedAcademicYear = $academicYears->first();
        }

        $formationDiplomas = SenegalFormationDiplomas::types();
        $formationDepartments = FormationDepartment::orderBy('name')->get();

        return view('admin.classes.create', compact('academicYears', 'levels', 'selectedAcademicYear', 'isFormationSchool', 'formationDiplomas', 'formationDepartments'));
    }

    /**
     * Enregistre une nouvelle classe
     */
    public function store(Request $request)
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;

        if ($school?->isFormation()) {
            return $this->storeFormationPromotion($request, $school);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'capacity' => 'nullable|integer|min:1|max:100',
            'room_number' => 'nullable|string|max:120',
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
            'formationDepartment',
            'formationPromotion.formationDepartment',
            'students' => function($query) {
                $query->orderBy('name');
            },
            'teacherAssignments.teacher',
            'teacherAssignments.subject',
            'teachers.subjects',
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
        $academicYear = $class->academicYear;
        $yearClosed = $academicYear?->isClosed() ?? false;
        $isReadOnly = $academicYear?->isReadOnly() ?? false;
        $promotionService = app(StudentClassPromotionService::class);

        if ($isReadOnly && $academicYear) {
            $cohortIds = $promotionService->resolveClassCohort($class, $academicYear)->pluck('id');
            $assignedStudents = User::query()
                ->whereIn('id', $cohortIds)
                ->with('schoolClass')
                ->orderBy('name')
                ->paginate(10);
        } else {
            $assignedStudents = $class->students()->with('schoolClass')->paginate(10);
        }

        // ========= STATISTIQUES DE LA CLASSE =========
        if ($isReadOnly && $academicYear) {
            $studentIds = $promotionService->resolveClassCohort($class, $academicYear)->pluck('id')->toArray();
        } else {
            $studentIds = $class->students()->pluck('id')->toArray();
        }
        
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
        
        $applyAcademicYearFilter = function ($query) use ($academicYear, $class) {
            if (! $academicYear) {
                return $query;
            }

            return ClassHistoricalContext::applyGradeYearFilter($query, $academicYear, $class->school_id);
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

        $promotionPreview = null;
        $school = \App\Models\School::find(TenantSchool::id() ?? auth()->user()?->school_id);
        $canProcessClassPromotions = $yearClosed
            && $school
            && $promotionService->appliesToSchool($school)
            && $academicYear
            && $promotionService->countPendingClassPromotions($class, $academicYear) > 0;

        if ($yearClosed && $school && $promotionService->appliesToSchool($school) && $academicYear) {
            $promotionPreview = $promotionService->previewClass($class, $academicYear);
        }
        
        $archivedHistoryClass = ClassHistoricalContext::findArchivedMirrorClass($class);

        return view('admin.classes.show', compact(
            'class',
            'availableTeachers',
            'subjects',
            'assignedStudents',
            'classStats',
            'promotionPreview',
            'yearClosed',
            'isReadOnly',
            'canProcessClassPromotions',
            'archivedHistoryClass',
        ));
    }

    /**
     * Traite le passage en classe supérieure pour les élèves admis (6ème → Terminale).
     */
    public function processPromotions(SchoolClass $class, StudentClassPromotionService $promotionService)
    {
        $school = \App\Models\School::find(TenantSchool::id() ?? auth()->user()?->school_id);
        if (! $promotionService->appliesToSchool($school)) {
            return back()->with('error', 'Le passage automatique concerne uniquement les établissements scolaires (6ème → Terminale).');
        }

        $academicYear = $class->academicYear ?? AcademicYear::where('is_current', true)->first();
        if (! $academicYear?->isClosed()) {
            return back()->with('error', 'Les passages en classe supérieure sont disponibles uniquement lorsque l\'année scolaire est marquée comme terminée.');
        }

        if ($promotionService->countPendingClassPromotions($class, $academicYear) === 0) {
            return back()->with('info', 'Les passages pour cette classe sont déjà terminés.');
        }

        $results = $promotionService->processClass($class, $academicYear);

        $promoted = collect($results)->where('promoted', true)->count();
        $ready = collect($results)->where('status', 'no_target_class')->count();
        $failed = collect($results)->where('status', 'failed')->count();

        if ($promoted === 0 && $ready === 0) {
            return back()->with('warning', 'Aucun passage effectué. Vérifiez que les élèves ont une moyenne calculable et ≥ '.config('school.passing_grade_min', 10).'/20.');
        }

        $message = "{$promoted} élève(s) promu(s) en classe supérieure.";
        if ($ready > 0) {
            $message .= " {$ready} admis(s) sans classe de destination — créez les classes du niveau supérieur.";
        }
        if ($failed > 0) {
            $message .= " {$failed} élève(s) non admis(s) (moyenne insuffisante).";
        }

        return back()->with('success', $message);
    }

    /**
     * Traite le passage en classe supérieure pour toutes les classes d'une année terminée.
     */
    public function processAllPromotions(Request $request, StudentClassPromotionService $promotionService)
    {
        $school = \App\Models\School::find(TenantSchool::id() ?? auth()->user()?->school_id);
        if (! $promotionService->appliesToSchool($school)) {
            return back()->with('error', 'Le passage automatique concerne uniquement les établissements scolaires (6ème → Terminale).');
        }

        $academicYear = $request->filled('academic_year_id')
            ? AcademicYear::findOrFail($request->integer('academic_year_id'))
            : AcademicYear::where('is_closed', true)->orderByDesc('start_date')->first();

        if (! $academicYear?->isClosed()) {
            return back()->with('error', 'Les passages en masse sont disponibles uniquement pour une année scolaire marquée comme terminée.');
        }

        if ($promotionService->countPendingBulkPromotions($academicYear) === 0) {
            return back()->with('info', 'Les passages en classe supérieure pour '.$academicYear->name.' sont déjà terminés.');
        }

        $summary = $promotionService->processAllClasses($academicYear);

        if (($summary['error'] ?? null) === 'year_not_closed') {
            return back()->with('error', 'Cette année scolaire n\'est pas terminée.');
        }

        $promoted = ($summary['promoted'] ?? 0) + ($summary['graduated'] ?? 0);
        $ready = $summary['no_target'] ?? 0;

        if ($promoted === 0 && $ready === 0 && ($summary['already_processed'] ?? 0) === 0) {
            return back()->with('warning', 'Aucun passage effectué pour '.$academicYear->name.'. Vérifiez les moyennes (≥ '.config('school.passing_grade_min', 10).'/20).');
        }

        $parts = [];
        if ($summary['promoted'] > 0) {
            $parts[] = "{$summary['promoted']} promu(s) en classe supérieure";
        }
        if ($summary['graduated'] > 0) {
            $parts[] = "{$summary['graduated']} diplômé(s)";
        }
        if ($summary['already_processed'] > 0) {
            $parts[] = "{$summary['already_processed']} déjà traité(s)";
        }
        if ($ready > 0) {
            $parts[] = "{$ready} admis(s) sans classe de destination";
        }
        if ($promotionService->countPendingBulkPromotions($academicYear) === 0) {
            $parts[] = 'passages terminés pour cette année';
        }

        $message = 'Passage en masse — '.$academicYear->name.' : '.implode(', ', $parts).'.';
        $message .= " ({$summary['classes_processed']} classe(s) traitée(s))";

        if ($ready > 0) {
            $message .= ' Créez les classes du niveau supérieur pour l\'année suivante.';
        }

        return back()->with('success', $message);
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
        if ($response = ClosedAcademicYearGuard::denyClassMutation($class)) {
            return $response;
        }

        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;

        if ($school?->isClassic()) {
            SchoolSubjectProvisioner::ensureForSchool($schoolId);
        }

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $levels = Level::orderedPedagogically()->get();
        $isFormationSchool = $school?->isFormation() ?? false;
        $selectedAcademicYear = $class->academicYear;
        $formationDiplomas = SenegalFormationDiplomas::types();
        $formationDepartments = FormationDepartment::orderBy('name')->get();

        return view('admin.classes.edit', compact('class', 'academicYears', 'levels', 'isFormationSchool', 'selectedAcademicYear', 'formationDiplomas', 'formationDepartments'));
    }

    /**
     * Met à jour une classe existante
     */
    public function update(Request $request, SchoolClass $class)
    {
        if ($response = ClosedAcademicYearGuard::denyClassMutation($class)) {
            return $response;
        }

        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;

        if ($school?->isFormation()) {
            return $this->updateFormationPromotion($request, $class, $school);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'capacity' => 'nullable|integer|min:1|max:100',
            'room_number' => 'nullable|string|max:120',
        ]);

        if ($response = ClosedAcademicYearGuard::denyYearIdMutation((int) $validated['academic_year_id'])) {
            return $response;
        }
        
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
        if ($response = ClosedAcademicYearGuard::denyClassMutation($class)) {
            return $response;
        }

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

    private function storeFormationPromotion(Request $request, \App\Models\School $school): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'promotion_name' => ['required', 'string', 'max:120'],
            'diploma_type' => ['required', Rule::in(SenegalFormationDiplomas::codes())],
            'filiere' => ['required', 'string', 'max:120'],
            'formation_year' => [
                'required',
                'string',
                'max:80',
                function ($attribute, $value, $fail) use ($request) {
                    if (! SenegalFormationDiplomas::isValidFormationYear($request->input('diploma_type'), $value)) {
                        $fail('Choisissez une année de formation valide pour ce diplôme.');
                    }
                },
            ],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'groups' => ['required', 'string', 'max:2000'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'room_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], $this->formationDepartmentRules($school)), array_merge([
            'groups.required' => 'Indiquez au moins un groupe ou une classe (un nom par ligne).',
            'diploma_type.required' => 'Sélectionnez le type de diplôme visé.',
        ], $this->formationDepartmentMessages()));

        if (! $request->input('formation_department_id') && trim((string) $request->input('department_name', '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'department_name' => 'Sélectionnez un département existant ou saisissez le nom d\'un nouveau département.',
            ]);
        }

        $groupNames = collect(preg_split('/\r\n|\r|\n/', $validated['groups']))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->unique()
            ->values();

        if ($groupNames->isEmpty()) {
            return back()->withInput()->with('error', 'Indiquez au moins un groupe ou une classe.');
        }

        try {
            DB::beginTransaction();

            $department = $this->resolveFormationDepartment($request, $school);

            $level = FormationLevelResolver::resolve(
                $school->id,
                $validated['formation_year'],
                $validated['filiere'],
                $validated['diploma_type']
            );

            $promotion = FormationPromotion::create([
                'school_id' => $school->id,
                'formation_department_id' => $department->id,
                'academic_year_id' => $validated['academic_year_id'],
                'name' => $validated['promotion_name'],
                'filiere' => $validated['filiere'],
                'diploma_type' => $validated['diploma_type'],
                'formation_year' => $validated['formation_year'],
                'description' => $validated['description'] ?? null,
            ]);

            $created = 0;

            foreach ($groupNames as $groupName) {
                $exists = SchoolClass::where('name', $groupName)
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->exists();

                if ($exists) {
                    DB::rollBack();

                    return back()
                        ->withInput()
                        ->with('error', "Le groupe « {$groupName} » existe déjà pour cette année scolaire.");
                }

                $class = SchoolClass::create([
                    'name' => $groupName,
                    'formation_promotion_id' => $promotion->id,
                    'promotion_name' => $validated['promotion_name'],
                    'filiere' => $validated['filiere'],
                    'diploma_type' => $validated['diploma_type'],
                    'formation_year' => $validated['formation_year'],
                    'formation_department_id' => $department->id,
                    'academic_year_id' => $validated['academic_year_id'],
                    'level_id' => $level->id,
                    'capacity' => $validated['capacity'] ?? 40,
                    'room_number' => $validated['room_number'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'school_id' => $school->id,
                ]);

                $created++;
            }

            DB::commit();

            $message = $created > 1
                ? "Promotion « {$validated['promotion_name']} » créée avec {$created} groupes."
                : 'La promotion a été créée avec succès.';

            return redirect()
                ->route('admin.classes.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Erreur création promotion formation', [
                'school_id' => $school->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Une erreur est survenue lors de la création de la promotion.');
        }
    }

    private function updateFormationPromotion(Request $request, SchoolClass $class, \App\Models\School $school): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'promotion_name' => ['required', 'string', 'max:120'],
            'diploma_type' => ['required', Rule::in(SenegalFormationDiplomas::codes())],
            'filiere' => ['required', 'string', 'max:120'],
            'formation_year' => [
                'required',
                'string',
                'max:80',
                function ($attribute, $value, $fail) use ($request) {
                    if (! SenegalFormationDiplomas::isValidFormationYear($request->input('diploma_type'), $value)) {
                        $fail('Choisissez une année de formation valide pour ce diplôme.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:50'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'room_number' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], $this->formationDepartmentRules($school)), array_merge([
            'diploma_type.required' => 'Sélectionnez le type de diplôme visé.',
        ], $this->formationDepartmentMessages()));

        if ($response = ClosedAcademicYearGuard::denyYearIdMutation((int) $validated['academic_year_id'])) {
            return $response;
        }

        if (! $request->input('formation_department_id') && trim((string) $request->input('department_name', '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'department_name' => 'Sélectionnez un département existant ou saisissez le nom d\'un nouveau département.',
            ]);
        }

        $existingClass = SchoolClass::where('name', $validated['name'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('id', '!=', $class->id)
            ->exists();

        if ($existingClass) {
            return back()->withInput()->with('error', 'Un groupe avec ce nom existe déjà pour cette année scolaire.');
        }

        try {
            $department = $this->resolveFormationDepartment($request, $school);

            $level = FormationLevelResolver::resolve(
                $school->id,
                $validated['formation_year'],
                $validated['filiere'],
                $validated['diploma_type']
            );

            $promotion = $class->formationPromotion ?? FormationPromotion::create([
                'school_id' => $school->id,
                'formation_department_id' => $department->id,
                'academic_year_id' => $validated['academic_year_id'],
                'name' => $validated['promotion_name'],
                'filiere' => $validated['filiere'],
                'diploma_type' => $validated['diploma_type'],
                'formation_year' => $validated['formation_year'],
                'description' => $validated['description'] ?? null,
            ]);

            $promotion->update([
                'formation_department_id' => $department->id,
                'academic_year_id' => $validated['academic_year_id'],
                'name' => $validated['promotion_name'],
                'filiere' => $validated['filiere'],
                'diploma_type' => $validated['diploma_type'],
                'formation_year' => $validated['formation_year'],
                'description' => $validated['description'] ?? null,
            ]);

            $sharedAttributes = [
                'promotion_name' => $validated['promotion_name'],
                'filiere' => $validated['filiere'],
                'diploma_type' => $validated['diploma_type'],
                'formation_year' => $validated['formation_year'],
                'formation_department_id' => $department->id,
                'formation_promotion_id' => $promotion->id,
                'academic_year_id' => $validated['academic_year_id'],
                'level_id' => $level->id,
                'description' => $validated['description'] ?? null,
            ];

            SchoolClass::where('formation_promotion_id', $promotion->id)
                ->where('id', '!=', $class->id)
                ->update($sharedAttributes);

            $class->update([
                ...$sharedAttributes,
                'name' => $validated['name'],
                'capacity' => $validated['capacity'] ?? $class->capacity,
                'room_number' => $validated['room_number'] ?? null,
            ]);

            return redirect()
                ->route('admin.classes.show', $class)
                ->with('success', 'La promotion a été mise à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    private function formationDepartmentRules(\App\Models\School $school): array
    {
        return [
            'formation_department_id' => [
                'nullable',
                'integer',
                Rule::exists('formation_departments', 'id')->where('school_id', $school->id),
            ],
            'department_name' => ['nullable', 'string', 'max:120'],
        ];
    }

    private function formationDepartmentMessages(): array
    {
        return [
            'department_name.required' => 'Sélectionnez un département existant ou saisissez le nom d\'un nouveau département.',
        ];
    }

    private function resolveFormationDepartment(Request $request, \App\Models\School $school): FormationDepartment
    {
        $departmentId = $request->input('formation_department_id');
        if ($departmentId) {
            return FormationDepartment::where('school_id', $school->id)->findOrFail($departmentId);
        }

        $name = trim((string) $request->input('department_name', ''));
        if ($name === '') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'department_name' => 'Sélectionnez un département existant ou saisissez le nom d\'un nouveau département.',
            ]);
        }

        return FormationDepartment::firstOrCreate(
            ['school_id' => $school->id, 'name' => $name],
            ['name' => $name]
        );
    }
}
