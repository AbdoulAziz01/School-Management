<?php

namespace App\Services\SchoolBot;

use App\Models\Grade;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Support\TeacherSubjectResolver;
use App\Support\FormationLmdSettings;
use App\Support\FormationModuleGradeCalculator;
use App\Support\Grading\PrimaryGradingSettings;
use App\Support\Grading\SubjectAverageFormulaResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Source unique de calcul du bulletin sénégalais, utilisée par
 * StudentBulletinController (vue en ligne élève), BulletinReportService
 * (PDF/rapports admin) et SchoolBotStatsService (statistiques bot) —
 * pour éviter que ces trois consommateurs dérivent vers des formules
 * différentes (cf. audit sécurité/qualité).
 *
 * Collège/lycée : moyenne matière = moyenne devoirs (50 %) + composition
 * (50 %). Primaire : voir App\Support\Grading (3 compositions annuelles,
 * schéma différent pour la CM2). Les établissements en filière
 * "formation" (LMD) suivent une pondération CC/examen distincte, voir
 * FormationLmdSettings. La formule matière elle-même est déléguée à
 * App\Support\Grading\SubjectAverageFormulaResolver (sauf branche LMD,
 * qui reste inline ci-dessous).
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
     * @return list<array<string, mixed>>
     */
    public function calculateBulletinData(Collection $grades, ?Level $level, ?School $school = null, ?array $coefficients = null, ?SchoolClass $class = null): array
    {
        if ($school?->usesLmdGrading()) {
            return $this->calculateFormationBulletinData($grades, $school);
        }

        $coefficients ??= $this->fetchLevelCoefficients($level);
        $referenceMaxGrade = $this->referenceMaxGradeForLevel($level);

        $bulletinData = [];
        $gradesBySubject = $grades->groupBy('subject_id');

        foreach ($gradesBySubject as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->subject ?? null;
            if (! $subject) {
                continue;
            }

            // Matière explicitement désactivée pour CETTE classe (voir
            // admin/classes/show — "Matières de la classe") : ne doit
            // apparaître ni côté enseignant (déjà géré par
            // TeacherSubjectResolver) ni côté élève, même si des notes ont
            // été saisies avant la désactivation.
            if ($class && TeacherSubjectResolver::isDisabledForClass((int) $subjectId, $class)) {
                continue;
            }

            $coefficient = $coefficients[$subjectId] ?? 1;
            $formula = SubjectAverageFormulaResolver::for($level, $subject);
            $formulaResult = $formula->compute($subjectGrades);
            $moyenneMatiere = $formulaResult['moyenne_matiere'] ?? null;
            $maxGrade = $formulaResult['max_grade'] ?? 20.0;

            // Chaque matière primaire peut avoir son propre barème
            // (configurable) ; on normalise sur le barème de référence du
            // niveau (10 par défaut en primaire, 20 en secondaire — voir
            // referenceMaxGradeForLevel()) avant pondération, pour que la
            // moyenne générale reste comparable entre matières tout en ne
            // réintroduisant jamais de /20 pour le primaire. La colonne
            // 'moyenne_matiere' reste, elle, sur le barème propre de la
            // matière pour l'affichage ligne par ligne.
            $moyenneNormalisee = $moyenneMatiere !== null
                ? ($moyenneMatiere / max($maxGrade, 0.01)) * $referenceMaxGrade
                : null;

            $bulletinData[] = array_merge($formulaResult, [
                'subject' => $subject->name,
                'subject_code' => $subject->code,
                'coefficient' => $coefficient,
                'max_grade' => $maxGrade,
                'points' => $moyenneNormalisee !== null ? round($moyenneNormalisee * $coefficient, 2) : null,
                'appreciation' => $this->getAppreciation($moyenneMatiere, $maxGrade),
            ]);
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

    /**
     * Barème sur lequel afficher/calculer une moyenne générale (qui
     * combine plusieurs matières pouvant avoir des barèmes différents) :
     * 20 en secondaire (barème unique, inchangé), ou le barème le plus
     * représenté parmi les matières actives du niveau en primaire (10 par
     * défaut) — jamais 20 pour ce cycle, conformément au barème /10 du
     * primaire sénégalais.
     */
    public function referenceMaxGradeForLevel(?Level $level): float
    {
        if (! $level || ! $level->isPrimaireCycle()) {
            return 20.0;
        }

        $subjects = $level->subjects()->wherePivot('is_active', true)->get();
        if ($subjects->isEmpty()) {
            return 10.0;
        }

        $maxGrades = $subjects->map(
            fn ($subject) => PrimaryGradingSettings::fromLevelSubject($level, $subject)->maxGrade
        );

        return (float) $maxGrades->countBy()->sortDesc()->keys()->first();
    }

    public function getCurrentSemester(): int
    {
        $month = now()->month;
        if ($month >= 10 || $month <= 1) {
            return 1;
        }

        return 2;
    }

    /**
     * $maxGrade permet d'apprécier une note dont le barème n'est pas /20
     * (matières primaire configurées différemment) en comparant un ratio
     * plutôt qu'une valeur absolue — pour maxGrade=20 (valeur par défaut,
     * utilisée par tous les appels secondaire existants), le comportement
     * est mathématiquement identique à avant (average/20 vs les mêmes seuils).
     */
    public function getAppreciation(?float $average, float $maxGrade = 20.0): string
    {
        if ($average === null) {
            return '-';
        }

        $ratio = $maxGrade > 0 ? $average / $maxGrade : 0;

        if ($ratio >= 16 / 20) {
            return 'Très Bien';
        }
        if ($ratio >= 14 / 20) {
            return 'Bien';
        }
        if ($ratio >= 12 / 20) {
            return 'Assez Bien';
        }
        if ($ratio >= 10 / 20) {
            return 'Passable';
        }
        if ($ratio >= 8 / 20) {
            return 'Insuffisant';
        }

        return 'Très Insuffisant';
    }

    /**
     * Phrase d'appréciation générale (bloc "APPRÉCIATION GÉNÉRALE" du
     * bulletin imprimé) — dérivée du pourcentage de réussite, jamais d'un
     * seuil absolu, pour rester valable quel que soit le barème.
     */
    public function generalAppreciationText(?float $average, float $maxGrade = 20.0): string
    {
        if ($average === null || $maxGrade <= 0) {
            return 'Aucune note suffisante pour établir une appréciation ce trimestre.';
        }

        $ratio = $average / $maxGrade;

        return match (true) {
            $ratio >= 0.8 => 'Élève excellent(e) et très appliqué(e). Résultats remarquables dans l\'ensemble des matières. Continuez ainsi.',
            $ratio >= 0.7 => 'Élève sérieux(se) et travailleur(se). Résultats très satisfaisants. Doit poursuivre ses efforts pour maintenir ce niveau.',
            $ratio >= 0.6 => 'Élève appliqué(e). Résultats satisfaisants dans l\'ensemble. Peut mieux faire avec plus de régularité.',
            $ratio >= 0.5 => 'Résultats moyens. Des efforts supplémentaires sont attendus pour consolider les acquis.',
            $ratio >= 0.4 => 'Résultats insuffisants. Un travail plus soutenu est indispensable pour progresser.',
            default => 'Résultats très insuffisants. Une remise à niveau et un accompagnement rapproché sont nécessaires.',
        };
    }

    /**
     * $maxGrade permet d'évaluer une moyenne annuelle qui n'est pas sur
     * /20 (primaire, voir referenceMaxGradeForLevel()) en comparant un
     * ratio plutôt qu'une valeur absolue — pour maxGrade=20 (défaut,
     * comportement des appels secondaire existants), inchangé.
     */
    public function getDecisionText(float $moyenneAnnuelle, float $maxGrade = 20.0): string
    {
        $ratio = $maxGrade > 0 ? $moyenneAnnuelle / $maxGrade : 0;

        if ($ratio >= 10 / 20) {
            return 'Admis(e) en classe supérieure';
        }
        if ($ratio >= 8 / 20) {
            return 'Passage conditionnel / Redoublement';
        }

        return 'Redoublement';
    }

    public function getMention(float $moyenne, float $maxGrade = 20.0): ?string
    {
        $ratio = $maxGrade > 0 ? $moyenne / $maxGrade : 0;

        if ($ratio >= 16 / 20) {
            return 'Mention Très Bien';
        }
        if ($ratio >= 14 / 20) {
            return 'Mention Bien';
        }
        if ($ratio >= 12 / 20) {
            return 'Mention Assez Bien';
        }

        return null;
    }
}
