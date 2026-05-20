<?php

namespace App\Support;

use App\Models\Level;

class SchoolLevelProvisioner
{
    /** Niveaux type collège → lycée (programme sénégalais courant). */
    public static function defaultDefinitions(): array
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

    /**
     * Crée les niveaux par défaut pour l'établissement s'ils n'existent pas encore.
     */
    public static function ensureForSchool(?int $schoolId): void
    {
        if (! $schoolId) {
            return;
        }

        $exists = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->exists();

        if ($exists) {
            return;
        }

        foreach (self::defaultDefinitions() as $definition) {
            Level::withoutGlobalScopes()->create([
                ...$definition,
                'school_id' => $schoolId,
            ]);
        }
    }
}
