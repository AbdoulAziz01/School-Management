<?php

namespace App\Support\Grading;

use Illuminate\Support\Collection;

/**
 * Formule unique du primaire : moyenne simple des N compositions
 * présentes, N venant de PrimaryGradingSettings (configurable par niveau
 * + matière, jamais codé en dur). S'applique aussi bien à CI-CM1 (3 par
 * défaut) qu'à la CM2 (2 par défaut) — c'est la configuration qui les
 * distingue, plus une classe PHP par cas comme avant.
 */
class PrimaryCompositionsAverageFormula implements SubjectAverageFormula
{
    public function __construct(private PrimaryGradingSettings $settings) {}

    public function compute(Collection $subjectGrades): array
    {
        $result = [];
        $values = [];

        for ($i = 1; $i <= $this->settings->compositionsCount; $i++) {
            $type = 'composition'.$i;
            $grade = $subjectGrades->where('type', $type)->first();
            $result[$type] = $grade ? round((float) $grade->grade, 2) : null;

            if ($grade) {
                $values[] = (float) $grade->grade;
            }
        }

        $result['moyenne_matiere'] = $values !== [] ? round(array_sum($values) / count($values), 2) : null;
        $result['max_grade'] = $this->settings->maxGrade;

        return $result;
    }
}
