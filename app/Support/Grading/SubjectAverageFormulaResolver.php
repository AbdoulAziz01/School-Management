<?php

namespace App\Support\Grading;

use App\Models\Level;
use App\Models\Subject;

class SubjectAverageFormulaResolver
{
    /**
     * $subject est requis pour une résolution primaire précise (le nombre
     * de compositions et la note max sont configurés par niveau+matière) ;
     * sans lui, on retombe sur les valeurs par défaut du niveau.
     */
    public static function for(?Level $level, ?Subject $subject = null): SubjectAverageFormula
    {
        if ($level?->isPrimaireCycle()) {
            $settings = $subject
                ? PrimaryGradingSettings::fromLevelSubject($level, $subject)
                : PrimaryGradingSettings::defaults($level);

            return new PrimaryCompositionsAverageFormula($settings);
        }

        return new SecondaireSubjectAverageFormula();
    }
}
