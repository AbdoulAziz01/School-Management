<?php

namespace App\Support\Grading;

use App\Models\SchoolClass;

/**
 * Point de bascule unique entre les deux schémas d'évaluation existants :
 * secondaire (SenegalGradeSequence — Devoir 1/2 + Composition, par
 * semestre) et primaire (PrimaryGradeSequence — 3 compositions annuelles,
 * ou 2 compositions + examen final pour la CM2). Basé sur Level::cycle.
 */
class GradeSequenceResolver
{
    public static function isPrimaire(SchoolClass $class): bool
    {
        return $class->level?->isPrimaireCycle() ?? false;
    }

    public static function isCm2(SchoolClass $class): bool
    {
        return $class->level?->isCm2() ?? false;
    }
}
