<?php

namespace App\Services\SchoolBot;

use App\Models\Grade;
use App\Models\Level;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reproduit la logique de calcul du bulletin sénégalais utilisée par StudentBulletinController
 * (moyennes matières pondérées par les coefficients du niveau).
 */
class BulletinComputation
{
    /**
     * @return array<int, int|float> map subject_id => coefficient
     */
    public function fetchLevelCoefficients(?Level $level): array
    {
        if (! $level) {
            return [];
        }

        return DB::table('level_subject')
            ->where('level_id', $level->id)
            ->pluck('coefficient', 'subject_id')
            ->toArray();
    }

    /**
     * @param  Collection<int, Grade>  $grades
     * @return array<int, array<string, mixed>>
     */
    public function calculateBulletinData(Collection $grades, ?Level $level, ?array $coefficients = null): array
    {
        $coefficients ??= $this->fetchLevelCoefficients($level);

        $bulletinData = [];
        $gradesBySubject = $grades->groupBy('subject_id');

        foreach ($gradesBySubject as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject ?? null;
            if (! $subject) {
                continue;
            }

            $coefficient = $coefficients[$subjectId] ?? 1;

            $devoir1 = $subjectGrades->where('type', 'devoir1')->first();
            $devoir2 = $subjectGrades->where('type', 'devoir2')->first();
            $composition = $subjectGrades->where('type', 'composition')->first();

            $moyenneDevoirs = null;
            $moyenneMatiere = null;

            if ($devoir1 && $devoir2) {
                $moyenneDevoirs = ($devoir1->grade + $devoir2->grade) / 2;
            }

            if ($moyenneDevoirs !== null && $composition) {
                $moyenneMatiere = ($moyenneDevoirs * 0.4) + ($composition->grade * 0.6);
            } elseif ($composition) {
                $moyenneMatiere = $composition->grade;
            } elseif ($moyenneDevoirs !== null) {
                $moyenneMatiere = $moyenneDevoirs;
            }

            $bulletinData[] = [
                'subject' => $subject->name,
                'subject_code' => $subject->code,
                'coefficient' => $coefficient,
                'devoir1' => $devoir1 ? round($devoir1->grade, 2) : null,
                'devoir2' => $devoir2 ? round($devoir2->grade, 2) : null,
                'composition' => $composition ? round($composition->grade, 2) : null,
                'moyenne_devoirs' => $moyenneDevoirs !== null ? round($moyenneDevoirs, 2) : null,
                'moyenne_matiere' => $moyenneMatiere !== null ? round($moyenneMatiere, 2) : null,
                'points' => $moyenneMatiere !== null ? round($moyenneMatiere * $coefficient, 2) : null,
            ];
        }

        usort($bulletinData, fn ($a, $b) => $b['coefficient'] <=> $a['coefficient']);

        return $bulletinData;
    }

    /**
     * @param  array<int, array<string, mixed>>  $bulletinData
     */
    public function calculateWeightedAverage(array $bulletinData): float
    {
        $totalPoints = 0;
        $totalCoef = 0;

        foreach ($bulletinData as $data) {
            if ($data['moyenne_matiere'] !== null) {
                $totalPoints += $data['points'];
                $totalCoef += $data['coefficient'];
            }
        }

        return $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : 0.0;
    }

    public function getCurrentSemester(): int
    {
        $month = now()->month;
        if ($month >= 10 || $month <= 1) {
            return 1;
        }

        return 2;
    }
}
