<?php

namespace App\Support;

use App\Models\Level;

/**
 * Niveau technique interne (modules) — invisible pour l'admin formation.
 */
class FormationLevelResolver
{
    public static function resolve(int $schoolId, string $formationYear, ?string $filiere = null, ?string $diplomaType = null): Level
    {
        $parts = array_filter([
            $diplomaType ? SenegalFormationDiplomas::label($diplomaType) : null,
            $formationYear,
            $filiere,
        ]);
        $label = implode(' — ', $parts);

        $existing = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('cycle', 'formation')
            ->where('name', $label)
            ->first();

        if ($existing) {
            return $existing;
        }

        $nextOrder = (int) Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('cycle', 'formation')
            ->max('order') + 1;

        return Level::withoutGlobalScopes()->create([
            'school_id' => $schoolId,
            'name' => $label,
            'cycle' => 'formation',
            'serie' => $filiere,
            'order' => max(1, $nextOrder),
        ]);
    }
}
