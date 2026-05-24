<?php

namespace App\Services;

use App\Support\ClassHistoricalContext;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Collection;

class StudentClassPromotionService
{
    public const TERMINALE_LEVEL_ORDER = 7;

    private float $passingAverage;

    public function __construct()
    {
        $this->passingAverage = (float) config('school.passing_grade_min', 10);
    }

    public function appliesToSchool(?School $school): bool
    {
        return $school !== null && $school->supportsAutomaticClassPromotion();
    }

    /** @var list<string> */
    private const PROMOTION_CYCLES = ['primaire', 'college', 'lycee'];

    /**
     * @return array<string, mixed>
     */
    public function evaluate(User $student, AcademicYear $academicYear, bool $forPreview = false, ?SchoolClass $contextClass = null): array
    {
        $base = [
            'student_id' => $student->id,
            'student_name' => $student->name,
            'eligible' => false,
            'promoted' => false,
            'status' => 'ineligible',
            'message' => '',
            'annual_average' => null,
            'semester_averages' => ['1' => null, '2' => null],
            'missing_subjects' => [],
            'processed' => false,
        ];

        if (! $student->isStudent()) {
            return array_merge($base, ['message' => 'Compte non élève.']);
        }

        $wasProcessed = (int) $student->last_promotion_academic_year_id === (int) $academicYear->id;

        if ($wasProcessed && ! $forPreview) {
            return array_merge($base, [
                'status' => 'already_processed',
                'message' => 'Passage déjà traité pour cette année scolaire.',
            ]);
        }

        if ($wasProcessed && $forPreview) {
            $base['processed'] = true;
        }

        $school = $student->school ?? School::find($student->school_id);
        if (! $this->appliesToSchool($school)) {
            return array_merge($base, [
                'status' => 'skipped',
                'message' => 'Passage automatique non disponible pour cet établissement (formation LMD).',
            ]);
        }

        $class = $contextClass ?? $student->schoolClass;
        if (! $class) {
            return array_merge($base, ['message' => 'Élève sans classe assignée.']);
        }

        $level = $class->level;
        if (! $level || ! in_array($level->cycle, self::PROMOTION_CYCLES, true)) {
            return array_merge($base, ['message' => 'Niveau scolaire non pris en charge pour le passage automatique.']);
        }

        $allGrades = $this->gradesForStudent($student, $academicYear);
        $semesterAverages = [
            '1' => $this->weightedAverageFromGrades($allGrades->where('semester', 1)),
            '2' => $this->weightedAverageFromGrades($allGrades->where('semester', 2)),
        ];

        $annualAverage = $this->weightedAverageFromGrades($allGrades);

        $base['semester_averages'] = $semesterAverages;
        $base['annual_average'] = $annualAverage;

        if ($annualAverage === null) {
            return array_merge($base, [
                'status' => 'incomplete',
                'message' => 'Aucune moyenne calculable — notes insuffisantes pour cette année.',
            ]);
        }

        if ($annualAverage < $this->passingAverage) {
            return array_merge($base, [
                'status' => 'failed',
                'message' => "Moyenne annuelle insuffisante ({$annualAverage}/20, seuil {$this->passingAverage}/20).",
            ]);
        }

        if ($this->isGraduationLevel($school, $level)) {
            $graduateMessage = $level->cycle === 'primaire'
                ? 'Fin du cycle primaire (CM2).'
                : 'Diplômé(e) — fin de scolarité (Terminale).';

            return array_merge($base, [
                'eligible' => true,
                'status' => 'graduate',
                'message' => $graduateMessage,
                'target_class_name' => 'Diplômé — fin de scolarité',
            ]);
        }

        $targetClass = $this->findTargetClass($student, $class, $level, $academicYear, $school);
        if (! $targetClass) {
            return array_merge($base, [
                'eligible' => true,
                'status' => 'no_target_class',
                'message' => 'Admis(e) mais aucune classe de niveau supérieur n\'est disponible. Créez la classe de l\'année suivante.',
            ]);
        }

        return array_merge($base, [
            'eligible' => true,
            'status' => 'ready',
            'message' => "Admis(e) en {$targetClass->name}.",
            'target_class_id' => $targetClass->id,
            'target_class_name' => $targetClass->name,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function tryPromote(User $student, ?AcademicYear $academicYear = null): array
    {
        $academicYear ??= AcademicYear::where('is_current', true)->first();

        if (! $academicYear) {
            return [
                'student_id' => $student->id,
                'eligible' => false,
                'promoted' => false,
                'status' => 'no_year',
                'message' => 'Aucune année scolaire en cours.',
            ];
        }

        if (! $academicYear->isClosed()) {
            return [
                'student_id' => $student->id,
                'eligible' => false,
                'promoted' => false,
                'status' => 'year_not_closed',
                'message' => 'L\'année scolaire n\'est pas encore terminée.',
            ];
        }

        $evaluation = $this->evaluate($student, $academicYear);

        if (! ($evaluation['eligible'] ?? false)) {
            return $evaluation;
        }

        if (in_array($evaluation['status'], ['already_processed', 'skipped', 'failed', 'no_target_class'], true)) {
            return $evaluation;
        }

        if ($evaluation['status'] === 'graduate') {
            $student->update([
                'last_promotion_academic_year_id' => $academicYear->id,
                'promotion_source_class_id' => $student->class_id,
            ]);

            return array_merge($evaluation, [
                'promoted' => true,
                'status' => 'graduated',
            ]);
        }

        if ($evaluation['status'] !== 'ready' || empty($evaluation['target_class_id'])) {
            return $evaluation;
        }

        $sourceClassId = $student->class_id;

        $student->update([
            'class_id' => $evaluation['target_class_id'],
            'last_promotion_academic_year_id' => $academicYear->id,
            'promotion_source_class_id' => $sourceClassId,
        ]);

        return array_merge($evaluation, [
            'promoted' => true,
            'status' => 'promoted',
            'message' => "Passage effectué en {$evaluation['target_class_name']}.",
        ]);
    }

    /**
     * Élèves de la cohorte d'une classe pour une année (y compris après passage en classe sup.).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function resolveClassCohort(SchoolClass $class, AcademicYear $academicYear): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->where('school_id', $class->school_id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->where(function ($query) use ($class, $academicYear) {
                $query->where('class_id', $class->id)
                    ->orWhere(function ($sub) use ($class, $academicYear) {
                        $sub->where('last_promotion_academic_year_id', $academicYear->id)
                            ->where('promotion_source_class_id', $class->id);
                    });
            })
            ->orderBy('name')
            ->get();
    }

    public function countClassCohort(SchoolClass $class, ?AcademicYear $academicYear = null): int
    {
        $academicYear ??= $class->academicYear;

        if (! $academicYear) {
            return 0;
        }

        return $this->resolveClassCohort($class, $academicYear)->count();
    }

    /**
     * @param  iterable<SchoolClass>  $classes
     * @return array<int, int>
     */
    public function cohortCountsForClasses(iterable $classes): array
    {
        $counts = [];

        foreach ($classes as $class) {
            $class->loadMissing('academicYear');

            if ($class->academicYear?->isReadOnly()) {
                $counts[$class->id] = $this->countClassCohort($class, $class->academicYear);
            }
        }

        return $counts;
    }

    /**
     * Nombre d'élèves admis/diplômés encore passibles de promotion pour une année terminée.
     */
    public function countPendingBulkPromotions(AcademicYear $academicYear): int
    {
        if (! $academicYear->allowsPromotion()) {
            return 0;
        }

        $school = School::find($academicYear->school_id);
        if (! $this->appliesToSchool($school)) {
            return 0;
        }

        $pending = 0;

        $classes = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $academicYear->school_id)
            ->where('academic_year_id', $academicYear->id)
            ->with('level')
            ->get();

        foreach ($classes as $class) {
            if ($class->level && ! in_array($class->level->cycle, self::PROMOTION_CYCLES, true)) {
                continue;
            }

            foreach ($this->resolveClassCohort($class, $academicYear) as $student) {
                if ((int) $student->last_promotion_academic_year_id === (int) $academicYear->id) {
                    continue;
                }

                $evaluation = $this->evaluate($student, $academicYear, forPreview: true, contextClass: $class);

                if (($evaluation['eligible'] ?? false)
                    && in_array($evaluation['status'] ?? '', ['ready', 'graduate'], true)) {
                    $pending++;
                }
            }
        }

        return $pending;
    }

    public function countPendingClassPromotions(SchoolClass $class, ?AcademicYear $academicYear = null): int
    {
        $academicYear ??= $class->academicYear;

        if (! $academicYear?->allowsPromotion()) {
            return 0;
        }

        $pending = 0;

        foreach ($this->resolveClassCohort($class, $academicYear) as $student) {
            if ((int) $student->last_promotion_academic_year_id === (int) $academicYear->id) {
                continue;
            }

            $evaluation = $this->evaluate($student, $academicYear, forPreview: true, contextClass: $class);

            if (($evaluation['eligible'] ?? false)
                && in_array($evaluation['status'] ?? '', ['ready', 'graduate'], true)) {
                $pending++;
            }
        }

        return $pending;
    }

    /**
     * Aperçu des passages / redoublements pour une classe (sans modification).
     *
     * @return array<string, mixed>
     */
    public function previewClass(SchoolClass $class, ?AcademicYear $academicYear = null): array
    {
        $academicYear ??= $class->academicYear;

        $preview = [
            'enabled' => false,
            'passing_grade_min' => $this->passingAverage,
            'academic_year_name' => $academicYear?->name,
            'counts' => [
                'will_pass' => 0,
                'will_stay' => 0,
                'incomplete' => 0,
                'no_target' => 0,
                'processed' => 0,
            ],
            'will_pass' => [],
            'will_stay' => [],
            'incomplete' => [],
            'no_target' => [],
        ];

        if (! $academicYear) {
            return $preview;
        }

        $school = School::find($class->school_id);
        if (! $this->appliesToSchool($school)) {
            return $preview;
        }

        $level = $class->level;
        if (! $level || ! in_array($level->cycle, self::PROMOTION_CYCLES, true)) {
            return $preview;
        }

        $preview['enabled'] = true;

        $students = $this->resolveClassCohort($class, $academicYear);

        foreach ($students as $student) {
            $evaluation = $this->evaluate($student, $academicYear, forPreview: true, contextClass: $class);
            $entry = $this->formatPreviewEntry($student, $evaluation, $class, $level, $academicYear);

            match ($evaluation['status'] ?? 'ineligible') {
                'ready', 'graduate' => $preview['will_pass'][] = $entry,
                'no_target_class' => $preview['no_target'][] = $entry,
                'failed' => $preview['will_stay'][] = $entry,
                'incomplete' => $preview['incomplete'][] = $entry,
                default => $preview['incomplete'][] = $entry,
            };
        }

        foreach (['will_pass', 'will_stay', 'incomplete', 'no_target'] as $key) {
            usort($preview[$key], fn ($a, $b) => strcmp($a['name'], $b['name']));
            $preview['counts'][$key] = count($preview[$key]);
        }

        $preview['counts']['processed'] = collect(array_merge(
            $preview['will_pass'],
            $preview['will_stay'],
            $preview['incomplete'],
            $preview['no_target']
        ))->where('is_processed', true)->count();

        return $preview;
    }

    /**
     * @param  array<string, mixed>  $evaluation
     * @return array<string, mixed>
     */
    private function formatPreviewEntry(
        User $student,
        array $evaluation,
        SchoolClass $class,
        Level $level,
        AcademicYear $academicYear
    ): array {
        $status = $evaluation['status'] ?? 'ineligible';
        $targetClassName = $evaluation['target_class_name'] ?? null;

        if ($status === 'graduate') {
            $targetClassName = 'Diplômé — fin de scolarité';
        } elseif (in_array($status, ['failed'], true) && $targetClassName === null) {
            $repeatClass = $this->findRepeatClassForPreview($class, $level, $academicYear);
            $targetClassName = $repeatClass?->name ?? $class->name.' (année suivante)';
        } elseif ($status === 'ready' && $targetClassName === null) {
            $targetClassName = '—';
        }

        $outcomeLabel = match ($status) {
            'ready' => 'Passe en classe supérieure',
            'graduate' => 'Diplômé(e) — fin de scolarité',
            'failed' => 'Redouble — même niveau',
            'no_target_class' => 'Admis(e) — classe supérieure à créer',
            'already_processed' => 'Déjà traité',
            'incomplete' => 'Non évaluable — pas assez de notes',
            default => 'Non évaluable',
        };

        return [
            'id' => $student->id,
            'name' => $student->name,
            'identifier' => $student->identifier ?? '—',
            'annual_average' => $evaluation['annual_average'],
            'semester_averages' => $evaluation['semester_averages'] ?? ['1' => null, '2' => null],
            'status' => $status,
            'outcome_label' => $outcomeLabel,
            'target_class_name' => $targetClassName,
            'message' => $evaluation['message'] ?? '',
            'missing_subjects' => $evaluation['missing_subjects'] ?? [],
            'is_processed' => (bool) ($evaluation['processed'] ?? false),
            'url' => route('admin.students.show', $student),
        ];
    }

    private function findRepeatClassForPreview(
        SchoolClass $class,
        Level $level,
        AcademicYear $academicYear
    ): ?SchoolClass {
        $nextYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $class->school_id)
            ->where('start_date', '>', $academicYear->start_date)
            ->orderBy('start_date')
            ->first();

        if (! $nextYear) {
            return $class;
        }

        return $this->findClassAtLevel($class, $level, $nextYear);
    }

    /**
     * @return array{promoted: int, graduated: int, repeated: int, unchanged: int, messages: list<string>}
     */
    public function processYearTransition(AcademicYear $endingYear, AcademicYear $newYear): array
    {
        $summary = [
            'promoted' => 0,
            'graduated' => 0,
            'repeated' => 0,
            'unchanged' => 0,
            'messages' => [],
        ];

        $school = School::find($endingYear->school_id);
        if (! $this->appliesToSchool($school)) {
            return $summary;
        }

        $classes = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $endingYear->school_id)
            ->where('academic_year_id', $endingYear->id)
            ->with('level')
            ->get();

        foreach ($classes as $class) {
            if ($class->level && ! in_array($class->level->cycle, self::PROMOTION_CYCLES, true)) {
                continue;
            }

            $students = User::where('class_id', $class->id)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->get();

            foreach ($students as $student) {
                $result = $this->tryPromote($student, $endingYear);

                if (($result['promoted'] ?? false) && ($result['status'] ?? '') === 'promoted') {
                    $summary['promoted']++;

                    continue;
                }

                if (($result['status'] ?? '') === 'graduated') {
                    $summary['graduated']++;

                    continue;
                }

                if (in_array($result['status'] ?? '', ['already_processed', 'skipped'], true)) {
                    continue;
                }

                if (in_array($result['status'] ?? '', ['ready', 'no_target_class'], true)) {
                    $summary['unchanged']++;
                    if (($result['status'] ?? '') === 'no_target_class') {
                        $summary['messages'][] = "{$student->name} : admis(e) mais classe supérieure manquante pour {$newYear->name}.";
                    }

                    continue;
                }

                $repeat = $this->assignRepeater($student, $class, $endingYear, $newYear);
                if (($repeat['status'] ?? '') === 'repeated') {
                    $summary['repeated']++;
                } else {
                    $summary['unchanged']++;
                    if (! empty($repeat['message'])) {
                        $summary['messages'][] = "{$student->name} : {$repeat['message']}";
                    }
                }
            }
        }

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    public function assignRepeater(
        User $student,
        SchoolClass $currentClass,
        AcademicYear $endingYear,
        AcademicYear $newYear
    ): array {
        if ((int) $student->last_promotion_academic_year_id === (int) $endingYear->id) {
            return ['status' => 'already_processed', 'message' => 'Déjà traité pour cette année.'];
        }

        $level = $currentClass->level;
        if (! $level) {
            return ['status' => 'no_level', 'message' => 'Classe sans niveau.'];
        }

        $target = $this->findClassAtLevel($currentClass, $level, $newYear);
        if (! $target) {
            return [
                'status' => 'no_repeat_class',
                'message' => "Aucune classe {$level->name} trouvée pour {$newYear->name}.",
            ];
        }

        $student->update([
            'class_id' => $target->id,
            'last_promotion_academic_year_id' => $endingYear->id,
            'promotion_source_class_id' => $currentClass->id,
        ]);

        return [
            'status' => 'repeated',
            'message' => "Redoublement — affecté(e) en {$target->name}.",
            'target_class_id' => $target->id,
            'target_class_name' => $target->name,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function processClass(SchoolClass $class, ?AcademicYear $academicYear = null): array
    {
        $academicYear ??= AcademicYear::where('is_current', true)->first();
        $results = [];

        $students = User::where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->get();

        foreach ($students as $student) {
            $results[] = $this->tryPromote($student, $academicYear);
        }

        return $results;
    }

    /**
     * Traite le passage en classe supérieure pour toutes les classes d'une année terminée.
     *
     * @return array<string, mixed>
     */
    public function processAllClasses(AcademicYear $academicYear): array
    {
        $summary = [
            'promoted' => 0,
            'graduated' => 0,
            'already_processed' => 0,
            'no_target' => 0,
            'failed' => 0,
            'unchanged' => 0,
            'classes_processed' => 0,
            'messages' => [],
        ];

        if (! $academicYear->isClosed()) {
            return array_merge($summary, ['error' => 'year_not_closed']);
        }

        $school = School::find($academicYear->school_id);
        if (! $this->appliesToSchool($school)) {
            return array_merge($summary, ['error' => 'not_classic_school']);
        }

        $classes = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $academicYear->school_id)
            ->where('academic_year_id', $academicYear->id)
            ->with('level')
            ->orderBy('name')
            ->get();

        foreach ($classes as $class) {
            if ($class->level && ! in_array($class->level->cycle, self::PROMOTION_CYCLES, true)) {
                continue;
            }

            $results = $this->processClass($class, $academicYear);
            if ($results === []) {
                continue;
            }

            $summary['classes_processed']++;

            foreach ($results as $result) {
                $status = $result['status'] ?? '';

                if (($result['promoted'] ?? false) && $status === 'promoted') {
                    $summary['promoted']++;
                } elseif ($status === 'graduated') {
                    $summary['graduated']++;
                } elseif ($status === 'already_processed') {
                    $summary['already_processed']++;
                } elseif ($status === 'no_target_class') {
                    $summary['no_target']++;
                    $name = $result['student_name'] ?? 'Élève';
                    $summary['messages'][] = "{$name} : admis(e) sans classe de destination.";
                } elseif ($status === 'failed') {
                    $summary['failed']++;
                } else {
                    $summary['unchanged']++;
                }
            }
        }

        return $summary;
    }

    /** @return Collection<int, Grade> */
    private function gradesForStudent(User $student, AcademicYear $academicYear): Collection
    {
        $query = Grade::where('user_id', $student->id)->whereHas('subject');

        ClassHistoricalContext::applyGradeYearFilter($query, $academicYear, $student->school_id);

        return $query->with('subject')->get();
    }

    /**
     * Même logique que les statistiques de la fiche classe (moyenne pondérée par matière).
     */
    private function weightedAverageFromGrades(Collection $grades): ?float
    {
        if ($grades->isEmpty()) {
            return null;
        }

        $weightedSum = 0.0;
        $totalCoef = 0.0;

        foreach ($grades->groupBy('subject_id') as $subjectGrades) {
            $subjectAvg = $subjectGrades->avg('grade');
            if ($subjectAvg === null) {
                continue;
            }

            $coefficient = (float) ($subjectGrades->first()->subject->coefficient ?? 1);
            $weightedSum += $subjectAvg * $coefficient;
            $totalCoef += $coefficient;
        }

        return $totalCoef > 0 ? round($weightedSum / $totalCoef, 2) : null;
    }

    private function findTargetClass(
        User $student,
        SchoolClass $currentClass,
        Level $currentLevel,
        AcademicYear $academicYear,
        School $school
    ): ?SchoolClass {
        $nextLevel = $this->findNextLevel($school, $currentLevel);

        if (! $nextLevel) {
            return null;
        }

        $nextYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $currentClass->school_id)
            ->where('start_date', '>', $academicYear->start_date)
            ->orderBy('start_date')
            ->first();

        $targetYearId = $nextYear?->id ?? $academicYear->id;

        return $this->findClassAtLevel($currentClass, $nextLevel, AcademicYear::withoutGlobalScopes()->find($targetYearId) ?? $academicYear);
    }

    private function findNextLevel(School $school, Level $currentLevel): ?Level
    {
        $next = Level::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('cycle', $currentLevel->cycle)
            ->where('order', (int) $currentLevel->order + 1)
            ->first();

        if ($next) {
            return $next;
        }

        if ($school->isMixte() && $currentLevel->cycle === 'primaire' && $this->levelIsCm2($currentLevel)) {
            return Level::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('cycle', 'college')
                ->where(function ($q) {
                    $q->where('name', '6ème')->orWhere('name', '6eme');
                })
                ->orderBy('order')
                ->first();
        }

        if ($currentLevel->cycle === 'college' && $this->levelIs3eme($currentLevel)) {
            return Level::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('cycle', 'lycee')
                ->where(function ($q) {
                    $q->where('name', 'like', 'Seconde%')
                        ->orWhere('order', 1);
                })
                ->orderBy('order')
                ->first();
        }

        return null;
    }

    private function isGraduationLevel(School $school, Level $level): bool
    {
        if ($level->cycle === 'lycee' && preg_match('/terminale/i', $level->name)) {
            return true;
        }

        if ($school->isPrimaireEstablishment()
            && $level->cycle === 'primaire'
            && $this->levelIsCm2($level)) {
            return true;
        }

        if (in_array($level->cycle, ['college', 'lycee'], true)
            && (int) $level->order >= self::TERMINALE_LEVEL_ORDER) {
            return true;
        }

        return false;
    }

    private function levelIsCm2(Level $level): bool
    {
        return preg_match('/^CM\s*2$/iu', trim($level->name)) === 1;
    }

    private function levelIs3eme(Level $level): bool
    {
        return preg_match('/^3\s*(?:ème|eme)$/iu', trim($level->name)) === 1;
    }

    private function findClassAtLevel(
        SchoolClass $currentClass,
        Level $level,
        AcademicYear $targetYear
    ): ?SchoolClass {
        $query = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $currentClass->school_id)
            ->where('level_id', $level->id)
            ->where('academic_year_id', $targetYear->id);

        $suffix = $this->extractClassGroupSuffix($currentClass->getRawOriginal('name') ?? $currentClass->name);
        if ($suffix !== null) {
            $matched = (clone $query)
                ->where(function ($q) use ($suffix) {
                    $q->where('name', 'like', "% {$suffix}")
                        ->orWhere('name', 'like', "%-{$suffix}")
                        ->orWhere('name', 'like', "% {$suffix}%");
                })
                ->first();

            if ($matched) {
                return $matched;
            }
        }

        return $query->orderBy('name')->first();
    }

    private function extractClassGroupSuffix(string $name): ?string
    {
        if (preg_match('/\b(\d+)\s*$/', trim($name), $matches)) {
            $number = (int) $matches[1];
            if ($number >= 1 && $number <= 26) {
                return chr(64 + $number);
            }
        }

        if (preg_match('/\b([A-Z])\s*$/u', trim($name), $matches)) {
            return $matches[1];
        }

        return null;
    }
}
