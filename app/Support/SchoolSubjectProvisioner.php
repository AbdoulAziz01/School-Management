<?php

namespace App\Support;

use App\Models\Level;
use App\Models\School;
use App\Models\Subject;
use App\Support\Grading\PrimaryGradingSettings;
use Illuminate\Support\Collection;

/**
 * Catalogue matières type programme sénégalais, par cycle (primaire, collège,
 * lycée...). Les coefficients par niveau sont dans level_subject ; subjects
 * garde des valeurs par défaut.
 *
 * Le catalogue à provisionner est déterminé à partir des `Level::cycle`
 * réellement présents pour l'établissement (eux-mêmes créés par
 * SchoolLevelProvisioner selon establishment_type) plutôt que d'un
 * établissement_type unique — ceci couvre nativement les écoles "Mixte"
 * (primaire + collège + lycée à la fois) et permet d'ajouter un nouveau
 * cycle plus tard en ajoutant simplement un cas à catalogForCycle() /
 * coefficientsForCycle(), sans toucher aux points d'appel existants.
 */
class SchoolSubjectProvisioner
{
    /** @return array<string, array{0: string, 1: string, 2: float}> nom => [code, département, heures/sem] */
    public static function catalog(): array
    {
        return [
            'Français' => ['FR', 'Lettres', 5],
            'Anglais' => ['ANG', 'Langues vivantes', 3],
            'Mathématiques' => ['MATH', 'Sciences', 5],
            'Histoire-Géographie' => ['HG', 'SHS', 4],
            'PC' => ['PC', 'Sciences', 4],
            'SVT' => ['SVT', 'Sciences', 3],
            'ICE' => ['ICE', 'Technologie', 2],
            'Philosophie' => ['PHI', 'Lettres', 2],
            'EPS' => ['EPS', 'EPS', 2],
            'Arabe' => ['AR', 'Langues vivantes', 2],
            'Espagnol' => ['ES', 'Langues vivantes', 2],
        ];
    }

    /**
     * Catalogue officiel du primaire — ces matières ne sont jamais saisies
     * manuellement : elles servent de référentiel commun, chaque classe
     * n'en enseignant qu'un sous-ensemble (choisi via les cases à cocher
     * "Matières enseignées" de la fiche enseignant).
     *
     * @return array<string, array{0: string, 1: string, 2: float}> nom => [code, département, heures/sem]
     */
    public static function primaireCatalog(): array
    {
        return [
            'Communication orale' => ['PRI-CO', 'Primaire', 2],
            'Lecture' => ['PRI-LEC', 'Primaire', 3],
            'Vocabulaire' => ['PRI-VOC', 'Primaire', 2],
            'Grammaire' => ['PRI-GRA', 'Primaire', 2],
            'Orthographe' => ['PRI-ORT', 'Primaire', 2],
            'Conjugaison' => ['PRI-CONJ', 'Primaire', 2],
            'Production d\'écrits (Rédaction)' => ['PRI-PROD', 'Primaire', 2],
            'Mathématiques' => ['PRI-MATH', 'Primaire', 5],
            'Histoire' => ['PRI-HIST', 'Primaire', 1],
            'Géographie' => ['PRI-GEO', 'Primaire', 1],
            'Initiation aux Sciences et à la Technologie (IST)' => ['PRI-IST', 'Primaire', 2],
            'Vivre dans son milieu (Hygiène / Environnement)' => ['PRI-VIVRE', 'Primaire', 1],
            'Éducation morale et civique' => ['PRI-EMC', 'Primaire', 1],
            'Éducation Physique et Sportive (EPS)' => ['PRI-EPS', 'Primaire', 2],
            'Éducation Artistique' => ['PRI-ART', 'Primaire', 1],
            'Éducation Religieuse' => ['PRI-REL', 'Primaire', 1],
        ];
    }

    /** @return array<string, int> */
    public static function coefCollege(): array
    {
        return [
            'Français' => 5,
            'Anglais' => 2,
            'Mathématiques' => 5,
            'Histoire-Géographie' => 4,
            'PC' => 4,
            'SVT' => 3,
            'ICE' => 2,
            'Philosophie' => 1,
            'EPS' => 1,
            'Arabe' => 2,
            'Espagnol' => 2,
        ];
    }

    /** @return array<string, int> */
    public static function coefLycee(): array
    {
        return [
            'Français' => 4,
            'Anglais' => 2,
            'Mathématiques' => 5,
            'Histoire-Géographie' => 3,
            'PC' => 4,
            'SVT' => 4,
            'ICE' => 2,
            'Philosophie' => 3,
            'EPS' => 1,
            'Arabe' => 2,
            'Espagnol' => 2,
        ];
    }

    /** @return array<string, int> toutes les matières du primaire pèsent le même coefficient */
    public static function coefPrimaire(): array
    {
        return array_fill_keys(array_keys(self::primaireCatalog()), 1);
    }

    /**
     * Crée le catalogue matières (par cycle réellement présent) et les lie
     * aux niveaux de l'établissement si besoin. Idempotent : ne recrée
     * jamais une matière ou un lien déjà existant.
     */
    public static function ensureForSchool(?int $schoolId): void
    {
        if (! $schoolId) {
            return;
        }

        $school = School::find($schoolId);
        if ($school?->isFormation()) {
            return;
        }

        SchoolLevelProvisioner::ensureForSchool($schoolId);

        $cycles = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->pluck('cycle')
            ->unique();

        foreach ($cycles as $cycle) {
            $catalog = self::catalogForCycle($cycle);
            if ($catalog !== []) {
                self::ensureSubjectsExist($schoolId, $catalog);
            }
        }

        self::syncLevelSubjectLinks($schoolId);
    }

    /** @return array<string, array{0: string, 1: string, 2: float}> */
    private static function catalogForCycle(string $cycle): array
    {
        return match ($cycle) {
            'primaire' => self::primaireCatalog(),
            'college', 'lycee' => self::catalog(),
            default => [],
        };
    }

    /** @return array<string, int> */
    private static function coefficientsForCycle(string $cycle): array
    {
        return match ($cycle) {
            'primaire' => self::coefPrimaire(),
            'college' => self::coefCollege(),
            'lycee' => self::coefLycee(),
            default => [],
        };
    }

    /** @param array<string, array{0: string, 1: string, 2: float}> $catalog */
    private static function ensureSubjectsExist(int $schoolId, array $catalog): void
    {
        foreach ($catalog as $name => [$code, $department, $hours]) {
            Subject::withoutGlobalScopes()->firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'code' => $code,
                ],
                [
                    'name' => $name,
                    'coefficient' => 1,
                    'description' => $name.' — programme standard',
                    'department' => $department,
                    'is_active' => true,
                    'hours_per_week' => $hours,
                    'is_core_subject' => true,
                ]
            );
        }
    }

    private static function syncLevelSubjectLinks(int $schoolId): void
    {
        $levels = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->orderBy('order')
            ->get();

        if ($levels->isEmpty()) {
            return;
        }

        /** @var Collection<string, Collection<int, Level>> $levelsByCycle */
        $levelsByCycle = $levels->groupBy('cycle');

        foreach ($levelsByCycle as $cycle => $levelsInCycle) {
            $catalog = self::catalogForCycle($cycle);
            if ($catalog === []) {
                continue;
            }

            $codes = collect($catalog)->map(fn ($meta) => $meta[0]);

            $subjectsByCode = Subject::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->whereIn('code', $codes)
                ->get()
                ->keyBy('code');

            $nameByCode = collect($catalog)
                ->mapWithKeys(fn ($meta, $name) => [$meta[0] => $name]);

            $coefs = self::coefficientsForCycle($cycle);

            foreach ($levelsInCycle as $level) {
                foreach ($subjectsByCode as $code => $subject) {
                    $subjectName = $nameByCode[$code] ?? null;
                    if (! $subjectName) {
                        continue;
                    }

                    $alreadyLinked = $level->subjects()
                        ->where('subjects.id', $subject->id)
                        ->exists();

                    if ($alreadyLinked) {
                        continue;
                    }

                    $coefficient = $coefs[$subjectName] ?? 1;

                    // Primaire : seed explicite des réglages de notation
                    // par défaut (note max, nb de compositions selon
                    // CI-CM1/CM2) — jamais laissés null à la création,
                    // l'admin peut ensuite les ajuster depuis la grille
                    // de notation sans que le comportement change entre
                    // "pas encore configuré" et "configuré aux défauts".
                    $pivotData = $cycle === 'primaire'
                        ? [...PrimaryGradingSettings::defaults($level)->toArray(), 'coefficient' => $coefficient, 'is_compulsory' => true]
                        : ['coefficient' => $coefficient, 'is_compulsory' => true];

                    $level->subjects()->attach($subject->id, $pivotData);
                }
            }
        }
    }
}
