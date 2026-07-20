<?php

namespace App\Support\Grading;

use Illuminate\Support\Collection;

/**
 * Formule de calcul de la moyenne matière pour un schéma d'évaluation
 * donné (secondaire, primaire standard, primaire CM2...). Voir
 * SubjectAverageFormulaResolver pour la sélection selon le niveau.
 */
interface SubjectAverageFormula
{
    /**
     * @param  Collection<int, \App\Models\Grade>  $subjectGrades  toutes les notes d'un élève pour une matière
     * @return array<string, mixed> fragment de ligne de bulletin (valeurs brutes par type + 'moyenne_matiere')
     */
    public function compute(Collection $subjectGrades): array;
}
