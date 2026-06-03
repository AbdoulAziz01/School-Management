<?php

namespace App\Support;

use App\Models\Level;
use App\Models\School;

class SchoolLevelProvisioner
{
  /** @return list<array{name: string, order: int, cycle: string, serie: null}> */
    public static function primaireDefinitions(): array
    {
        return [
            ['name' => 'CI', 'order' => 1, 'cycle' => 'primaire', 'serie' => null],
            ['name' => 'CP', 'order' => 2, 'cycle' => 'primaire', 'serie' => null],
            ['name' => 'CE1', 'order' => 3, 'cycle' => 'primaire', 'serie' => null],
            ['name' => 'CE2', 'order' => 4, 'cycle' => 'primaire', 'serie' => null],
            ['name' => 'CM1', 'order' => 5, 'cycle' => 'primaire', 'serie' => null],
            ['name' => 'CM2', 'order' => 6, 'cycle' => 'primaire', 'serie' => null],
        ];
    }

    /** Niveaux collège → lycée (programme sénégalais courant). */
    public static function collegeLyceeDefinitions(): array
    {
        return [
            ['name' => '6ème', 'order' => 1, 'cycle' => 'college', 'serie' => null],
            ['name' => '5ème', 'order' => 2, 'cycle' => 'college', 'serie' => null],
            ['name' => '4ème', 'order' => 3, 'cycle' => 'college', 'serie' => null],
            ['name' => '3ème', 'order' => 4, 'cycle' => 'college', 'serie' => null],
            ['name' => 'Seconde', 'order' => 5, 'cycle' => 'lycee', 'serie' => null],
            ['name' => 'Première', 'order' => 6, 'cycle' => 'lycee', 'serie' => null],
            ['name' => 'Terminale', 'order' => 7, 'cycle' => 'lycee', 'serie' => null],
        ];
    }

    /** @return list<array{name: string, order: int, cycle: string, serie: null}> */
    public static function definitionsForSchool(School $school): array
    {
        return self::definitionsForType($school->establishment_type);
    }

    /** @return list<array{name: string, order: int, cycle: string, serie: null}> */
    public static function definitionsForType(?string $establishmentType): array
    {
        return match ($establishmentType) {
            School::TYPE_PRIMAIRE => self::primaireDefinitions(),
            School::TYPE_COLLEGE => array_values(array_filter(
                self::collegeLyceeDefinitions(),
                fn (array $d) => $d['cycle'] === 'college'
            )),
            School::TYPE_LYCEE => array_values(array_filter(
                self::collegeLyceeDefinitions(),
                fn (array $d) => $d['cycle'] === 'lycee'
            )),
            School::TYPE_MIXTE => array_merge(
                self::primaireDefinitions(),
                self::collegeLyceeDefinitions()
            ),
            School::TYPE_FORMATION => [],
            default => self::collegeLyceeDefinitions(),
        };
    }

    /** Texte d'aide formulaire : niveaux créés à l'enregistrement. */
    public static function defaultLevelsHintForType(?string $establishmentType): string
    {
        if (! $establishmentType) {
            return 'Choisissez un type pour afficher les niveaux par défaut.';
        }

        if ($establishmentType === School::TYPE_FORMATION) {
            return 'Formation professionnelle : promotions et modules personnalisés (pas de niveaux CI–Terminale automatiques).';
        }

        $names = array_column(self::definitionsForType($establishmentType), 'name');

        return 'Niveaux créés automatiquement : '.implode(', ', $names).'.';
    }

    /** @return array<string, string> type d'établissement => aide */
    public static function defaultLevelsHintsByType(): array
    {
        $hints = [];
        foreach (array_keys(School::ESTABLISHMENT_TYPES) as $type) {
            $hints[$type] = self::defaultLevelsHintForType($type);
        }

        return $hints;
    }

    /**
     * Crée les niveaux par défaut pour l'établissement s'ils n'existent pas encore.
     */
    public static function ensureForSchool(?int $schoolId): void
    {
        if (! $schoolId) {
            return;
        }

        $school = School::find($schoolId);
        if (! $school || $school->isFormation()) {
            return;
        }

        $exists = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->exists();

        if ($exists) {
            self::syncLevelsForSchool($school);

            return;
        }

        foreach (self::definitionsForSchool($school) as $definition) {
            Level::withoutGlobalScopes()->create([
                ...$definition,
                'school_id' => $schoolId,
            ]);
        }
    }

    /**
     * Ajoute les niveaux manquants (ex. primaire pour une école mixte déjà créée).
     */
    public static function syncLevelsForSchool(School $school): void
    {
        if ($school->isFormation()) {
            return;
        }

        foreach (self::definitionsForSchool($school) as $definition) {
            Level::withoutGlobalScopes()->firstOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => $definition['name'],
                    'cycle' => $definition['cycle'],
                ],
                [
                    'order' => $definition['order'],
                    'serie' => $definition['serie'],
                ]
            );
        }
    }

    /** @deprecated Use collegeLyceeDefinitions() */
    public static function defaultDefinitions(): array
    {
        return self::collegeLyceeDefinitions();
    }
}
