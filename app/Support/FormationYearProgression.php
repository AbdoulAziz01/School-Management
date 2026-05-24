<?php

namespace App\Support;

class FormationYearProgression
{
    /** @return array<string, string> */
    private static function yearsMap(string $diplomaType): array
    {
        return SenegalFormationDiplomas::formationYearsByDiploma()[$diplomaType] ?? [];
    }

    public static function yearLabel(string $diplomaType, string $key): ?string
    {
        return self::yearsMap($diplomaType)[$key] ?? null;
    }

    /** Libellé → clé ('Licence 2' → '2') */
    public static function labelToKey(string $diplomaType, string $label): ?string
    {
        $map = self::yearsMap($diplomaType);
        $key = array_search($label, $map, true);

        return $key === false ? null : (string) $key;
    }

    /** Clé suivante : '1' → '2', '3' → null */
    public static function nextYearKey(string $diplomaType, string $currentYearLabel): ?string
    {
        $map = self::yearsMap($diplomaType);
        $keys = array_keys($map);
        $currentKey = array_search($currentYearLabel, $map, true);

        if ($currentKey === false) {
            return null;
        }

        $pos = array_search($currentKey, $keys, true);
        if ($pos === false || $pos >= count($keys) - 1) {
            return null;
        }

        return (string) $keys[$pos + 1];
    }

    public static function nextYearLabel(string $diplomaType, string $currentYearLabel): ?string
    {
        $nextKey = self::nextYearKey($diplomaType, $currentYearLabel);

        return $nextKey === null ? null : self::yearLabel($diplomaType, $nextKey);
    }

    public static function isFinalYear(string $diplomaType, string $currentYearLabel): bool
    {
        $map = self::yearsMap($diplomaType);
        if ($map === []) {
            return false;
        }

        return end($map) === $currentYearLabel;
    }
}