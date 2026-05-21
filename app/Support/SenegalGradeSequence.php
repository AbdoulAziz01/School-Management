<?php

namespace App\Support;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Support\Collection;

class SenegalGradeSequence
{
    public const ORDER = ['devoir1', 'devoir2', 'composition'];

    public const LABELS = [
        'devoir1' => 'Premier devoir',
        'devoir2' => 'Deuxième devoir',
        'composition' => 'Composition',
    ];

    public static function evaluationTypes(): array
    {
        return self::LABELS;
    }

    /**
     * @return Collection<int, Grade>
     */
    public static function existingEvaluations(int $classId, int $subjectId, ?int $academicYearId): Collection
    {
        $studentIds = User::query()
            ->where('class_id', $classId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return collect();
        }

        return Grade::query()
            ->where('subject_id', $subjectId)
            ->whereIn('user_id', $studentIds)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->whereIn('type', self::ORDER)
            ->whereIn('semester', [1, 2])
            ->get()
            ->unique(fn (Grade $g) => $g->semester.'-'.$g->type);
    }

    public static function hasEvaluation(int $classId, int $subjectId, int $semester, string $type, ?int $academicYearId): bool
    {
        $studentIds = User::query()
            ->where('class_id', $classId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return false;
        }

        $gradedCount = Grade::query()
            ->where('subject_id', $subjectId)
            ->where('semester', $semester)
            ->where('type', $type)
            ->whereIn('user_id', $studentIds)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->distinct()
            ->count('user_id');

        return $gradedCount >= $studentIds->count();
    }

    /**
     * Prochaine saisie autorisée, ou null si tout est complet.
     *
     * @return array{semester: int, type: string, label: string}|null
     */
    public static function nextAllowed(int $classId, int $subjectId, ?int $academicYearId): ?array
    {
        foreach ([1, 2] as $semester) {
            if ($semester === 2 && ! self::semesterComplete($classId, $subjectId, 1, $academicYearId)) {
                return null;
            }

            foreach (self::ORDER as $type) {
                if (! self::hasEvaluation($classId, $subjectId, $semester, $type, $academicYearId)) {
                    if (self::validateEntry($classId, $subjectId, $semester, $type, $academicYearId) === null) {
                        return [
                            'semester' => $semester,
                            'type' => $type,
                            'label' => self::LABELS[$type],
                        ];
                    }
                }
            }
        }

        return null;
    }

    public static function semesterComplete(int $classId, int $subjectId, int $semester, ?int $academicYearId): bool
    {
        foreach (self::ORDER as $type) {
            if (! self::hasEvaluation($classId, $subjectId, $semester, $type, $academicYearId)) {
                return false;
            }
        }

        return true;
    }

    public static function validateEntry(
        int $classId,
        int $subjectId,
        int $semester,
        string $type,
        ?int $academicYearId
    ): ?string {
        if (! in_array($type, self::ORDER, true)) {
            return 'Type d\'évaluation invalide. Utilisez : premier devoir, deuxième devoir ou composition.';
        }

        if (! in_array($semester, [1, 2], true)) {
            return 'Semestre invalide.';
        }

        $typeIndex = array_search($type, self::ORDER, true);

        if ($typeIndex > 0) {
            $previous = self::ORDER[$typeIndex - 1];
            if (! self::hasEvaluation($classId, $subjectId, $semester, $previous, $academicYearId)) {
                return 'Saisissez d\'abord « '.self::LABELS[$previous].' » (semestre '.$semester.').';
            }
        }

        if ($semester === 2 && ! self::semesterComplete($classId, $subjectId, 1, $academicYearId)) {
            return 'Terminez le semestre 1 (premier devoir, deuxième devoir et composition) avant le semestre 2.';
        }

        if (self::hasEvaluation($classId, $subjectId, $semester, $type, $academicYearId)) {
            return '« '.self::LABELS[$type].' » (semestre '.$semester.') est déjà entièrement saisi pour cette classe et matière.';
        }

        return null;
    }

    public static function validateDeletion(Grade $grade): ?string
    {
        $classId = $grade->user?->class_id;
        if (! $classId) {
            return null;
        }

        $subjectId = (int) $grade->subject_id;
        $semester = (int) $grade->semester;
        $academicYearId = $grade->academic_year_id;
        $typeIndex = array_search($grade->type, self::ORDER, true);

        if ($typeIndex !== false) {
            for ($i = $typeIndex + 1; $i < count(self::ORDER); $i++) {
                $later = self::ORDER[$i];
                if (self::hasEvaluation($classId, $subjectId, $semester, $later, $academicYearId)) {
                    return 'Supprimez d\'abord « '.self::LABELS[$later].' » (semestre '.$semester.') avant celle-ci.';
                }
            }
        }

        if ($semester === 1 && self::existingEvaluations($classId, $subjectId, $academicYearId)->contains(fn (Grade $g) => (int) $g->semester === 2)) {
            return 'Supprimez d\'abord toutes les notes du semestre 2.';
        }

        return null;
    }

    /**
     * @return array<int, array<string, bool>>
     */
    public static function progress(int $classId, int $subjectId, ?int $academicYearId): array
    {
        $progress = [];

        foreach ([1, 2] as $semester) {
            foreach (self::ORDER as $type) {
                $progress[$semester][$type] = self::hasEvaluation($classId, $subjectId, $semester, $type, $academicYearId);
            }
        }

        return $progress;
    }

    /**
     * Note déjà enregistrée pour un élève (évaluation verrouillée).
     */
    public static function findStudentGrade(
        int $userId,
        int $subjectId,
        int $semester,
        string $type,
        ?int $academicYearId
    ): ?Grade {
        return Grade::query()
            ->where('user_id', $userId)
            ->where('subject_id', $subjectId)
            ->where('semester', $semester)
            ->where('type', $type)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->first();
    }

    /** @return Collection<int, Grade> keyed by user_id */
    public static function gradesForEvaluation(
        int $classId,
        int $subjectId,
        int $semester,
        string $type,
        ?int $academicYearId
    ): Collection {
        $studentIds = User::query()
            ->where('class_id', $classId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return collect();
        }

        return Grade::query()
            ->where('subject_id', $subjectId)
            ->where('semester', $semester)
            ->where('type', $type)
            ->whereIn('user_id', $studentIds)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->get()
            ->keyBy('user_id');
    }
}
