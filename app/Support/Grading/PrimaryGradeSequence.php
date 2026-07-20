<?php

namespace App\Support\Grading;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Séquence d'évaluation du primaire (CI à CM2) — N compositions annuelles,
 * N étant configurable par (niveau, matière) via PrimaryGradingSettings
 * (3 par défaut, 2 pour la CM2, ajustable depuis l'admin). Aucun nombre
 * de compositions n'est codé en dur ici : voir orderFor().
 *
 * Le bulletin primaire ne mélange plus d'examen (CFEE) — celui-ci vit
 * dans un module totalement séparé (Phase 2, hors de ce fichier).
 *
 * Miroir volontairement autonome de SenegalGradeSequence (pas de classe
 * abstraite partagée) : le secondaire ne doit jamais être affecté par
 * une évolution de cette classe, et inversement.
 *
 * `semester` reste toujours à 1 pour les notes du primaire — l'identité
 * de l'évaluation vit entièrement dans `type` (composition1..N).
 */
class PrimaryGradeSequence
{
    private const PRIMARY_SEMESTER = 1;

    /** @return list<string> ['composition1', ..., 'compositionN'] */
    public static function orderFor(SchoolClass $class, int $subjectId): array
    {
        $count = self::settingsFor($class, $subjectId)->compositionsCount;

        return array_map(fn ($i) => 'composition'.$i, range(1, max(1, $count)));
    }

    /** @return array<string, string> */
    public static function labelsFor(SchoolClass $class, int $subjectId): array
    {
        $labels = [];
        foreach (self::orderFor($class, $subjectId) as $i => $type) {
            $labels[$type] = 'Composition '.($i + 1);
        }

        return $labels;
    }

    public static function settingsFor(SchoolClass $class, int $subjectId): PrimaryGradingSettings
    {
        $level = $class->level;
        $subject = Subject::withoutGlobalScopes()->find($subjectId);

        if (! $level || ! $subject) {
            return new PrimaryGradingSettings();
        }

        return PrimaryGradingSettings::fromLevelSubject($level, $subject);
    }

    public static function hasEvaluation(SchoolClass $class, int $subjectId, string $type, ?int $academicYearId): bool
    {
        $studentIds = self::studentIds($class);

        if ($studentIds->isEmpty()) {
            return false;
        }

        $gradedCount = Grade::query()
            ->where('subject_id', $subjectId)
            ->where('semester', self::PRIMARY_SEMESTER)
            ->where('type', $type)
            ->whereIn('user_id', $studentIds)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->distinct()
            ->count('user_id');

        return $gradedCount >= $studentIds->count();
    }

    /**
     * @return array{semester: int, type: string, label: string}|null
     */
    public static function nextAllowed(SchoolClass $class, int $subjectId, ?int $academicYearId): ?array
    {
        $order = self::orderFor($class, $subjectId);
        $labels = self::labelsFor($class, $subjectId);

        foreach ($order as $type) {
            if (! self::hasEvaluation($class, $subjectId, $type, $academicYearId)) {
                if (self::validateEntry($class, $subjectId, $type, $academicYearId) === null) {
                    return [
                        'semester' => self::PRIMARY_SEMESTER,
                        'type' => $type,
                        'label' => $labels[$type],
                    ];
                }
            }
        }

        return null;
    }

    public static function validateEntry(SchoolClass $class, int $subjectId, string $type, ?int $academicYearId): ?string
    {
        $order = self::orderFor($class, $subjectId);
        $labels = self::labelsFor($class, $subjectId);

        if (! in_array($type, $order, true)) {
            return 'Type d\'évaluation invalide pour cette classe du primaire.';
        }

        $typeIndex = array_search($type, $order, true);

        if ($typeIndex > 0) {
            $previous = $order[$typeIndex - 1];
            if (! self::hasEvaluation($class, $subjectId, $previous, $academicYearId)) {
                return 'Saisissez d\'abord « '.$labels[$previous].' ».';
            }
        }

        if (self::hasEvaluation($class, $subjectId, $type, $academicYearId)) {
            return '« '.$labels[$type].' » est déjà entièrement saisi pour cette classe et matière.';
        }

        return null;
    }

    /** @return array<string, bool> */
    public static function progress(SchoolClass $class, int $subjectId, ?int $academicYearId): array
    {
        $progress = [];

        foreach (self::orderFor($class, $subjectId) as $type) {
            $progress[$type] = self::hasEvaluation($class, $subjectId, $type, $academicYearId);
        }

        return [self::PRIMARY_SEMESTER => $progress];
    }

    public static function findStudentGrade(int $userId, int $subjectId, string $type, ?int $academicYearId): ?Grade
    {
        return Grade::query()
            ->where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->where('semester', self::PRIMARY_SEMESTER)
            ->where('type', $type)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->first();
    }

    /** @return Collection<int, Grade> keyed by user_id */
    public static function gradesForEvaluation(SchoolClass $class, int $subjectId, string $type, ?int $academicYearId): Collection
    {
        $studentIds = self::studentIds($class);

        if ($studentIds->isEmpty()) {
            return collect();
        }

        return Grade::query()
            ->where('subject_id', $subjectId)
            ->where('semester', self::PRIMARY_SEMESTER)
            ->where('type', $type)
            ->whereIn('user_id', $studentIds)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->get()
            ->keyBy('user_id');
    }

    /** @return Collection<int, int> */
    private static function studentIds(SchoolClass $class): Collection
    {
        return User::query()
            ->where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->pluck('id');
    }
}
