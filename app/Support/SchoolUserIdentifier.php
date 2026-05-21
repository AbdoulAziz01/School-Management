<?php

namespace App\Support;

use App\Models\User;

class SchoolUserIdentifier
{
    /**
     * Prochain identifiant séquentiel pour un établissement (ex. E2026001, P2026002).
     * La numérotation repart à 001 pour chaque école et chaque année.
     */
    public static function next(int $schoolId, string $prefix): string
    {
        $year = date('Y');
        $fullPrefix = $prefix.$year;

        $last = User::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('identifier', 'like', $fullPrefix.'%')
            ->orderByDesc('identifier')
            ->value('identifier');

        $num = $last ? ((int) substr($last, strlen($fullPrefix)) + 1) : 1;

        return $fullPrefix.str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }
}
