<?php

namespace App\Support;

class FormationGroupNaming
{
    /** @var list<string> */
    private static function stopWords(): array
    {
        return ['de', 'du', 'des', 'd', 'et', 'la', 'le', 'les', 'en', 'au', 'aux', 'à', 'a', 'l'];
    }

    /** Diplômes courts : le code diplôme sert de préfixe (BT → BT1-1, BTS → BTS1-1). */
    public static function usesDiplomaCodePrefix(?string $diplomaType): bool
    {
        return in_array($diplomaType, ['bt', 'bts', 'cap', 'bep', 'dut', 'du', 'certifiant'], true);
    }

    public static function diplomaCodePrefix(?string $diplomaType): ?string
    {
        if (! $diplomaType || ! self::usesDiplomaCodePrefix($diplomaType)) {
            return null;
        }

        return match ($diplomaType) {
            'certifiant' => 'CF',
            default => strtoupper($diplomaType),
        };
    }

    /** Diminutif filière : « Licence Informatique de Gestion » → LIG, « Master Comptabilité » → MC */
    public static function filiereAbbreviation(string $filiere): string
    {
        $words = preg_split('/\s+/u', trim($filiere)) ?: [];
        $abbr = '';

        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '') {
                continue;
            }

            $lower = mb_strtolower($word);
            if (in_array($lower, self::stopWords(), true)) {
                continue;
            }

            $abbr .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $abbr !== '' ? $abbr : 'CLS';
    }

    /** Préfixe du nom de groupe : code diplôme (BT, BTS…) ou diminutif filière (LIG, MC…). */
    public static function prefixAbbreviation(?string $filiere, ?string $diplomaType): string
    {
        $fromDiploma = self::diplomaCodePrefix($diplomaType);
        if ($fromDiploma !== null) {
            return $fromDiploma;
        }

        $filiere = trim($filiere ?? '');
        if ($filiere !== '') {
            return self::filiereAbbreviation($filiere);
        }

        if ($diplomaType) {
            return strtoupper(str_replace('_', '', $diplomaType));
        }

        return 'CLS';
    }

    public static function canSuggest(?string $filiere, ?string $diplomaType, string $formationYearLabel): bool
    {
        if ($formationYearLabel === '') {
            return false;
        }

        if (! $diplomaType) {
            return false;
        }

        if (self::usesDiplomaCodePrefix($diplomaType)) {
            return true;
        }

        return trim($filiere ?? '') !== '';
    }

    /** Chiffre d'année dans le code (Licence Pro 1 → 1, Master 1 → 1). */
    public static function yearDigit(?string $diplomaType, string $formationYearLabel): string
    {
        if (preg_match('/(\d+)/u', $formationYearLabel, $matches)) {
            return $matches[1];
        }

        if ($diplomaType) {
            $map = SenegalFormationDiplomas::formationYearsByDiploma()[$diplomaType] ?? [];
            $key = array_search($formationYearLabel, $map, true);
            if ($key !== false) {
                return (string) $key;
            }
        }

        return '1';
    }

    /** Ex. : LIG1-1, BT1-1, MC1-1 */
    public static function groupName(string $filiere, string $formationYearLabel, int $classIndex, ?string $diplomaType = null): string
    {
        $abbr = self::prefixAbbreviation($filiere, $diplomaType);
        $year = self::yearDigit($diplomaType, $formationYearLabel);

        return $abbr.$year.'-'.$classIndex;
    }

    /** @return list<string> */
    public static function suggestGroupNames(string $filiere, string $formationYearLabel, ?string $diplomaType = null, int $count = 1): array
    {
        $names = [];
        for ($i = 1; $i <= max(1, $count); $i++) {
            $names[] = self::groupName($filiere, $formationYearLabel, $i, $diplomaType);
        }

        return $names;
    }

    /** @return array<string, string> */
    public static function diplomaCodePrefixes(): array
    {
        $prefixes = [];
        foreach (SenegalFormationDiplomas::codes() as $code) {
            $prefix = self::diplomaCodePrefix($code);
            if ($prefix !== null) {
                $prefixes[$code] = $prefix;
            }
        }

        return $prefixes;
    }
}
