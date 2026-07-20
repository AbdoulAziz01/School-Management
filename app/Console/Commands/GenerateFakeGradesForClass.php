<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Support\Grading\PrimaryGradeSequence;
use App\Support\Grading\PrimaryGradingSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Génère des notes fictives mais réalistes pour compléter les matières
 * manquantes d'une classe de primaire, sans jamais toucher aux notes déjà
 * saisies — utile pour tester bulletins/classements/moyennes sans devoir
 * remplir manuellement chaque matière pour chaque élève.
 *
 * Chaque élève reçoit un "profil" de performance dérivé de ses notes déjà
 * saisies (s'il en a) — pour rester cohérent avec ce qui existe déjà —
 * réparti sur 4 bandes (excellent/bon/moyen/faible), avec une petite
 * variation par matière et une tendance (progression/régression/stable)
 * entre les 3 compositions.
 */
class GenerateFakeGradesForClass extends Command
{
    protected $signature = 'grades:generate-fake
                            {class : ID ou nom exact de la classe (ex. 1 ou "CI")}
                            {--dry-run : Affiche ce qui serait créé sans rien enregistrer}';

    protected $description = 'Complète les notes manquantes d\'une classe primaire avec des données fictives réalistes (ne modifie jamais les notes existantes)';

    public function handle(): int
    {
        $class = $this->resolveClass($this->argument('class'));
        if (! $class) {
            $this->error('Classe introuvable.');

            return self::FAILURE;
        }

        $class->loadMissing('level');
        if (! $class->level?->isPrimaireCycle()) {
            $this->error("La classe {$class->name} n'est pas une classe du primaire — cette commande ne génère que des compositions primaire.");

            return self::FAILURE;
        }

        $academicYear = AcademicYear::where('is_current', true)->first();
        if (! $academicYear) {
            $this->error('Aucune année scolaire courante.');

            return self::FAILURE;
        }

        $students = User::where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get();

        if ($students->isEmpty()) {
            $this->error('Aucun élève approuvé dans cette classe.');

            return self::FAILURE;
        }

        $subjects = $class->level->subjects()->wherePivot('is_active', true)->get();
        if ($subjects->isEmpty()) {
            $this->error("Aucune matière active configurée pour le niveau {$class->level->name}.");

            return self::FAILURE;
        }

        $this->info("Classe : {$class->name} ({$class->level->name}) — {$students->count()} élève(s), {$subjects->count()} matière(s) active(s).");

        $profiles = $this->assignProfiles($students);
        $dryRun = (bool) $this->option('dry-run');

        $toCreate = [];
        $skippedExisting = 0;

        foreach ($subjects as $subject) {
            $settings = PrimaryGradingSettings::fromLevelSubject($class->level, $subject);
            $types = PrimaryGradeSequence::orderFor($class, $subject->id);

            foreach ($students as $student) {
                $existingTypes = Grade::where('user_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->pluck('type')
                    ->all();

                if (count($existingTypes) >= count($types)) {
                    // Déjà complet pour cette matière — on ne touche à rien.
                    $skippedExisting += count($existingTypes);

                    continue;
                }

                $rows = $this->generateCompositions(
                    $student,
                    $subject,
                    $types,
                    $existingTypes,
                    $settings,
                    $profiles[$student->id],
                    $academicYear
                );

                foreach ($rows as $row) {
                    $toCreate[] = $row;
                }
            }
        }

        if ($toCreate === []) {
            $this->info('Rien à générer — toutes les matières actives sont déjà complètes pour cette classe.');

            return self::SUCCESS;
        }

        $this->table(
            ['Élève', 'Matière', 'Composition', 'Note', 'Barème'],
            collect($toCreate)->take(15)->map(fn ($r) => [
                $r['_student_name'], $r['_subject_name'], $r['type'],
                number_format($r['grade'], 2), $r['_max_grade'],
            ])->all()
        );

        if (count($toCreate) > 15) {
            $this->line('... et '.(count($toCreate) - 15).' autre(s) note(s).');
        }

        $this->info(count($toCreate)." note(s) à créer (matières déjà complètes ignorées : {$skippedExisting} note(s) existante(s) conservées).");

        if ($dryRun) {
            $this->comment('Mode --dry-run : rien n\'a été enregistré.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Confirmer la génération de ces notes ?', true)) {
            $this->comment('Annulé.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($toCreate) {
            foreach ($toCreate as $row) {
                Grade::create([
                    'user_id'          => $row['user_id'],
                    'subject_id'       => $row['subject_id'],
                    'grade'            => $row['grade'],
                    'type'             => $row['type'],
                    'date'             => $row['date'],
                    'coefficient'      => $row['coefficient'],
                    'semester'         => 1,
                    'academic_year_id' => $row['academic_year_id'],
                    'school_id'        => $row['school_id'],
                ]);
            }
        });

        $this->info('Terminé : '.count($toCreate).' note(s) créée(s).');

        return self::SUCCESS;
    }

    private function resolveClass(string $identifier): ?SchoolClass
    {
        if (ctype_digit($identifier)) {
            return SchoolClass::find((int) $identifier);
        }

        return SchoolClass::where('name', $identifier)->first();
    }

    /**
     * Un profil par élève (bande de performance + valeur centrale sur une
     * échelle /10), dérivé de la moyenne de ses notes déjà saisies quand il
     * en a. Le classement observé (min..max des moyennes existantes) est
     * étiré linéairement sur toute la fourchette demandée (2 à 10) : cela
     * préserve l'ordre relatif exact des élèves — donc la cohérence avec
     * les notes déjà saisies — tout en garantissant que les 4 bandes
     * (excellent/bon/moyen/faible) soient représentées, même si les notes
     * déjà saisies sont resserrées dans une plage étroite.
     *
     * @return array<int, array{band: string, center: float}>
     */
    private function assignProfiles($students): array
    {
        $existingAverages = $students->mapWithKeys(function (User $student) {
            $avg = Grade::where('user_id', $student->id)->avg('grade');

            return [$student->id => $avg !== null ? (float) $avg : null];
        });

        $withGrades = $existingAverages->filter(fn ($v) => $v !== null);
        $min = $withGrades->isNotEmpty() ? (float) $withGrades->min() : null;
        $max = $withGrades->isNotEmpty() ? (float) $withGrades->max() : null;

        $profiles = [];
        foreach ($students as $student) {
            $existing = $existingAverages[$student->id];

            if ($existing !== null && $max !== null && $max > $min) {
                // Fourchette légèrement resserrée (2.5 à 9.3 plutôt que
                // 2 à 10) : laisse de la place à la variation par matière
                // / composition ci-dessous sans buter systématiquement
                // sur le plafond ou le plancher (sinon les meilleurs
                // élèves finissent avec des 10 partout, peu réaliste).
                $ratio = ($existing - $min) / ($max - $min);
                $center = 2.5 + $ratio * 6.8;
            } elseif ($existing !== null) {
                // Un seul niveau observé (toutes moyennes égales) : on
                // garde ce niveau tel quel plutôt que d'inventer un écart.
                $center = max(2.5, min(9.3, $existing));
            } else {
                // Aucune note existante pour cet élève : profil aléatoire.
                $center = $this->randomFloat(2.5, 9.3);
            }

            $band = match (true) {
                $center >= 8.5 => 'excellent',
                $center >= 7.0 => 'bon',
                $center >= 5.0 => 'moyen',
                default => 'faible',
            };

            $profiles[$student->id] = ['band' => $band, 'center' => $center];
        }

        return $profiles;
    }

    /**
     * @param  array<int, string>  $types  Types de composition attendus, dans l'ordre (voir PrimaryGradeSequence::orderFor())
     * @param  array<int, string>  $existingTypes
     * @return list<array<string, mixed>>
     */
    private function generateCompositions(
        User $student,
        Subject $subject,
        array $types,
        array $existingTypes,
        PrimaryGradingSettings $settings,
        array $profile,
        AcademicYear $academicYear
    ): array {
        $maxGrade = $settings->maxGrade;
        // Le centre du profil est calibré sur une échelle /10 (bandes de
        // l'énoncé) — on le ramène au barème réel de la matière.
        $subjectCenter = min($maxGrade, max(0, ($profile['center'] / 10) * $maxGrade));

        // Petite variation propre à cette matière (un élève n'est jamais
        // parfaitement homogène partout), et une tendance entre les 3
        // compositions (progression, régression ou stable).
        $subjectJitter = $this->randomFloat(-0.9, 0.9) * ($maxGrade / 10);
        $trend = self::pick([-0.5, -0.25, 0.0, 0.25, 0.5]) * ($maxGrade / 10);

        $dates = $this->compositionDates(count($types));

        $rows = [];
        foreach ($types as $i => $type) {
            if (in_array($type, $existingTypes, true)) {
                continue;
            }

            $noise = $this->randomFloat(-0.4, 0.4) * ($maxGrade / 10);
            $value = $subjectCenter + $subjectJitter + ($trend * ($i - 1)) + $noise;
            $value = max(0, min($maxGrade, $value));
            $value = round($value * 4) / 4; // arrondi au quart de point

            $rows[] = [
                'user_id' => $student->id,
                'subject_id' => $subject->id,
                'grade' => $value,
                'type' => $type,
                'date' => $dates[$i],
                'coefficient' => $settings->coefficient,
                'academic_year_id' => $academicYear->id,
                'school_id' => $student->school_id,
                '_student_name' => $student->name,
                '_subject_name' => $subject->name,
                '_max_grade' => rtrim(rtrim(number_format($maxGrade, 2), '0'), '.'),
            ];
        }

        return $rows;
    }

    /** @return array<int, string> */
    private function compositionDates(int $count): array
    {
        $today = now();
        $dates = [];
        for ($i = 0; $i < $count; $i++) {
            $daysAgo = ($count - 1 - $i) * 25;
            $dates[] = $today->copy()->subDays($daysAgo)->toDateString();
        }

        return $dates;
    }

    private function randomFloat(float $min, float $max): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    private static function pick(array $values)
    {
        return $values[array_rand($values)];
    }
}
