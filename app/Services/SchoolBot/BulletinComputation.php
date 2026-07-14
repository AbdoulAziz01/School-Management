<?php

namespace App\Services\SchoolBot;

use App\Models\Grade;
use App\Models\Level;
use App\Models\School;
use App\Support\FormationLmdSettings;
use App\Support\FormationModuleGradeCalculator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Source unique de calcul du bulletin sénégalais, utilisée par
 * StudentBulletinController (vue en ligne élève), BulletinReportService
 * (PDF/rapports admin) et SchoolBotStatsService (statistiques bot) —
 * pour éviter que ces trois consommateurs dérivent vers des formules
 * différentes (cf. audit sécurité/qualité).
 *
 * Primaire / collège / lycée : moyenne matière = moyenne devoirs (50 %)
 * + composition (50 %). Les établissements en filière "formation"
 * (LMD) suivent une pondération CC/examen distincte, voir
 * FormationLmdSettings.
 */
class BulletinComputation
{
    private const DEVOIRS_WEIGHT = 0.5;
    private const COMPOSITION_WEIGHT = 0.5;

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
     * @return list<array<string, mixed>>
     */
    public function calculateBulletinData(Collection $grades, ?Level $level, ?School $school = null, ?array $coefficients = null): array
    {
        if ($school?->usesLmdGrading()) {
            return $this->calculateFormationBulletinData($grades, $school);
        }

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
                $moyenneMatiere = ($moyenneDevoirs * self::DEVOIRS_WEIGHT) + ($composition->grade * self::COMPOSITION_WEIGHT);
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
                'appreciation' => $this->getAppreciation($moyenneMatiere),
            ];
        }

        usort($bulletinData, fn ($a, $b) => $b['coefficient'] <=> $a['coefficient']);

        return $bulletinData;
    }

    /**
     * Bulletin formation : moyenne module LMD (pondération CC/examen définie
     * par module, ou 100 % si une seule famille de notes présente).
     *
     * @param  Collection<int, Grade>  $grades
     * @return list<array<string, mixed>>
     */
    private function calculateFormationBulletinData(Collection $grades, School $school): array
    {
        $bulletinData = [];

        foreach ($grades->groupBy('subject_id') as $subjectGrades) {
            $subject = $subjectGrades->first()->subject ?? null;
            if (! $subject) {
                continue;
            }

            $settings = FormationLmdSettings::fromSubject($subject);
            $coefficient = (float) ($subject->coefficient ?? 1);
            $devoir1 = $subjectGrades->where('type', 'devoir1')->first();
            $devoir2 = $subjectGrades->where('type', 'devoir2')->first();
            $composition = $subjectGrades->where('type', 'composition')->first();

            $ccGrades = $subjectGrades->filter(
                fn ($g) => in_array((string) $g->type, $settings->ccGradeTypes, true)
            );
            $moyenneDevoirs = $ccGrades->isNotEmpty()
                ? round((float) $ccGrades->avg('grade'), 2)
                : null;

            $summary = FormationModuleGradeCalculator::summarize($subjectGrades, $settings);
            $moyenneMatiere = $summary['average'];

            $bulletinData[] = [
                'subject' => $subject->name,
                'subject_code' => $subject->code,
                'coefficient' => $coefficient,
                'devoir1' => $devoir1 ? round((float) $devoir1->grade, 2) : null,
                'devoir2' => $devoir2 ? round((float) $devoir2->grade, 2) : null,
                'composition' => $composition ? round((float) $composition->grade, 2) : null,
                'moyenne_devoirs' => $moyenneDevoirs,
                'moyenne_exam' => $summary['exam_average'],
                'moyenne_matiere' => $moyenneMatiere,
                'lmd_mode' => $summary['mode'],
                'points' => $moyenneMatiere !== null ? round($moyenneMatiere * $coefficient, 2) : null,
                'appreciation' => $this->getAppreciation($moyenneMatiere),
                'validated' => $summary['validated'],
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

    public function getAppreciation(?float $average): string
    {
        if ($average === null) {
            return '-';
        }
        if ($average >= 16) {
            return 'Très Bien';
        }
        if ($average >= 14) {
            return 'Bien';
        }
        if ($average >= 12) {
            return 'Assez Bien';
        }
        if ($average >= 10) {
            return 'Passable';
        }
        if ($average >= 8) {
            return 'Insuffisant';
        }

        return 'Très Insuffisant';
    }

    public function getDecisionText(float $moyenneAnnuelle): string
    {
        if ($moyenneAnnuelle >= 12) {
            return 'Admis(e) en classe supérieure';
        }
        if ($moyenneAnnuelle >= 10) {
            return 'Admis(e) en classe supérieure';
        }
        if ($moyenneAnnuelle >= 8) {
            return 'Passage conditionnel / Redoublement';
        }

        return 'Redoublement';
    }

    public function getMention(float $moyenne): ?string
    {
        if ($moyenne >= 16) {
            return 'Mention Très Bien';
        }
        if ($moyenne >= 14) {
            return 'Mention Bien';
        }
        if ($moyenne >= 12) {
            return 'Mention Assez Bien';
        }

        return null;
    }
}
