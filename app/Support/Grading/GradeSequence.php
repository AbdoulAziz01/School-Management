<?php

namespace App\Support\Grading;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Support\SenegalGradeSequence;
use Illuminate\Support\Collection;

/**
 * Façade de dispatch entre SenegalGradeSequence (secondaire, inchangée) et
 * PrimaryGradeSequence (primaire) — point d'entrée unique pour les
 * contrôleurs/vues, basé sur Level::cycle via GradeSequenceResolver.
 */
class GradeSequence
{
    /** Borne haute pour les validations 'in:...' : le nombre de compositions est configurable (max 6, voir PrimaryGradingSettings::validationRules()). */
    private const MAX_POSSIBLE_COMPOSITIONS = 6;

    /** @return array{semester: int, type: string, label: string}|null */
    public static function nextAllowed(SchoolClass $class, int $subjectId, ?int $academicYearId): ?array
    {
        return GradeSequenceResolver::isPrimaire($class)
            ? PrimaryGradeSequence::nextAllowed($class, $subjectId, $academicYearId)
            : SenegalGradeSequence::nextAllowed($class->id, $subjectId, $academicYearId);
    }

    public static function validateEntry(SchoolClass $class, int $subjectId, int $semester, string $type, ?int $academicYearId): ?string
    {
        return GradeSequenceResolver::isPrimaire($class)
            ? PrimaryGradeSequence::validateEntry($class, $subjectId, $type, $academicYearId)
            : SenegalGradeSequence::validateEntry($class->id, $subjectId, $semester, $type, $academicYearId);
    }

    /** @return array<int, array<string, bool>> */
    public static function progress(SchoolClass $class, int $subjectId, ?int $academicYearId): array
    {
        return GradeSequenceResolver::isPrimaire($class)
            ? PrimaryGradeSequence::progress($class, $subjectId, $academicYearId)
            : SenegalGradeSequence::progress($class->id, $subjectId, $academicYearId);
    }

    /** @return Collection<int, Grade> keyed by user_id */
    public static function gradesForEvaluation(SchoolClass $class, int $subjectId, int $semester, string $type, ?int $academicYearId): Collection
    {
        return GradeSequenceResolver::isPrimaire($class)
            ? PrimaryGradeSequence::gradesForEvaluation($class, $subjectId, $type, $academicYearId)
            : SenegalGradeSequence::gradesForEvaluation($class->id, $subjectId, $semester, $type, $academicYearId);
    }

    public static function findStudentGrade(SchoolClass $class, int $userId, int $subjectId, int $semester, string $type, ?int $academicYearId): ?Grade
    {
        return GradeSequenceResolver::isPrimaire($class)
            ? PrimaryGradeSequence::findStudentGrade($userId, $subjectId, $type, $academicYearId)
            : SenegalGradeSequence::findStudentGrade($userId, $subjectId, $semester, $type, $academicYearId);
    }

    /**
     * Note maximale pour une classe+matière — configurable pour le
     * primaire (PrimaryGradingSettings), toujours 20 pour le secondaire
     * (comportement inchangé, aucune configuration n'existe pour ce cycle).
     */
    public static function maxGradeFor(SchoolClass $class, int $subjectId): float
    {
        return GradeSequenceResolver::isPrimaire($class)
            ? PrimaryGradeSequence::settingsFor($class, $subjectId)->maxGrade
            : 20.0;
    }

    /**
     * Colonnes d'évaluation à afficher pour une classe+matière donnée (à
     * plat, N colonnes configurables pour le primaire ; S1/S2×3 pour le
     * secondaire). Sans matière sélectionnée, un primaire retombe sur les
     * valeurs par défaut du niveau (voir PrimaryGradingSettings::defaults()).
     */
    public static function evaluationColumnsFor(?SchoolClass $class, ?int $subjectId = null): array
    {
        if ($class && GradeSequenceResolver::isPrimaire($class)) {
            $order = $subjectId
                ? PrimaryGradeSequence::orderFor($class, $subjectId)
                : array_map(fn ($i) => 'composition'.$i, range(1, PrimaryGradingSettings::defaults($class->level)->compositionsCount));
            $labels = $subjectId
                ? PrimaryGradeSequence::labelsFor($class, $subjectId)
                : collect($order)->mapWithKeys(fn ($t, $i) => [$t => 'Composition '.($i + 1)])->all();

            $columns = [];
            foreach ($order as $type) {
                $columns[] = [
                    'semester' => 1,
                    'type' => $type,
                    'header' => self::shortLabel($type),
                    'label' => $labels[$type],
                ];
            }

            return $columns;
        }

        return self::secondaireEvaluationColumns();
    }

    /** Colonnes S1/S2 × Devoir/Composition — comportement par défaut quand aucune classe n'est sélectionnée. */
    public static function secondaireEvaluationColumns(): array
    {
        $columns = [];
        foreach ([1, 2] as $semester) {
            foreach (SenegalGradeSequence::ORDER as $type) {
                $columns[] = [
                    'semester' => $semester,
                    'type' => $type,
                    'header' => 'S'.$semester.' · '.self::shortLabel($type),
                    'label' => 'Semestre '.$semester.' — '.SenegalGradeSequence::LABELS[$type],
                ];
            }
        }

        return $columns;
    }

    /** Union de tous les types d'évaluation possibles (secondaire + primaire), pour les validations 'in:...'. */
    public static function allTypes(): array
    {
        $compositions = array_map(fn ($i) => 'composition'.$i, range(1, self::MAX_POSSIBLE_COMPOSITIONS));

        return array_values(array_unique(array_merge(SenegalGradeSequence::ORDER, $compositions)));
    }

    /** Union de tous les libellés connus (secondaire + primaire générique, sans contexte classe/matière). */
    public static function labels(): array
    {
        $compositions = [];
        for ($i = 1; $i <= self::MAX_POSSIBLE_COMPOSITIONS; $i++) {
            $compositions['composition'.$i] = 'Composition '.$i;
        }

        return array_merge(SenegalGradeSequence::LABELS, $compositions);
    }

    public static function shortLabel(string $type): string
    {
        if (preg_match('/^composition(\d+)$/', $type, $m)) {
            return 'C'.$m[1];
        }

        return match ($type) {
            'devoir1' => 'D1',
            'devoir2' => 'D2',
            'composition' => 'Compo',
            default => $type,
        };
    }

    /**
     * Couleur d'une note en fonction de son propre barème (jamais un seuil
     * absolu type "note >= 10", qui n'a de sens que sur /20). Le secondaire
     * garde son code couleur réussite/échec habituel (vert/rouge à 50 %,
     * comportement visuel inchangé) ; le primaire utilise 4 bandes sur le
     * pourcentage de réussite, conformément au barème /10 par défaut :
     * < 50 % rouge, 50-69 % orange, 70-89 % vert clair, 90-100 % vert foncé.
     */
    public static function gradeBadgeColor(float $grade, float $maxGrade, bool $isPrimaire): string
    {
        $ratio = $maxGrade > 0 ? $grade / $maxGrade : 0;

        if (! $isPrimaire) {
            return $ratio >= 0.5 ? '#198754' : '#dc3545';
        }

        return match (true) {
            $ratio >= 0.9 => '#15803d',
            $ratio >= 0.7 => '#22c55e',
            $ratio >= 0.5 => '#f59e0b',
            default => '#dc2626',
        };
    }
}
