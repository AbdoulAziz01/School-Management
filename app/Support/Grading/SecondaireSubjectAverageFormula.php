<?php

namespace App\Support\Grading;

use Illuminate\Support\Collection;

/**
 * Formule secondaire (collège/lycée), extraite verbatim de l'ancienne
 * logique inline de BulletinComputation::calculateBulletinData() — mêmes
 * clés de sortie, mêmes poids, pour zéro changement de comportement.
 */
class SecondaireSubjectAverageFormula implements SubjectAverageFormula
{
    private const DEVOIRS_WEIGHT = 0.5;

    private const COMPOSITION_WEIGHT = 0.5;

    public function compute(Collection $subjectGrades): array
    {
        $devoir1 = $subjectGrades->where('type', 'devoir1')->first();
        $devoir2 = $subjectGrades->where('type', 'devoir2')->first();
        $composition = $subjectGrades->where('type', 'composition')->first();

        $moyenneDevoirs = null;
        $moyenneMatiere = null;

        if ($devoir1 && $devoir2) {
            $moyenneDevoirs = ($devoir1->grade + $devoir2->grade) / 2;
        }

        if ($moyenneDevoirs !== null && $composition) {
            $moyenneMatiere = ($moyenneDevoirs * self::DEVOIRS_WEIGHT) + ($composition->grade * self::COMPOSITION_WEIGHT);
        } elseif ($composition) {
            $moyenneMatiere = $composition->grade;
        } elseif ($moyenneDevoirs !== null) {
            $moyenneMatiere = $moyenneDevoirs;
        }

        return [
            'devoir1' => $devoir1 ? round($devoir1->grade, 2) : null,
            'devoir2' => $devoir2 ? round($devoir2->grade, 2) : null,
            'composition' => $composition ? round($composition->grade, 2) : null,
            'moyenne_devoirs' => $moyenneDevoirs !== null ? round($moyenneDevoirs, 2) : null,
            'moyenne_matiere' => $moyenneMatiere !== null ? round($moyenneMatiere, 2) : null,
        ];
    }
}
