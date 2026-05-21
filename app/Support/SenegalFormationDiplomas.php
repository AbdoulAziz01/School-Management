<?php

namespace App\Support;

class SenegalFormationDiplomas
{
    /** @return array<string, string> code => libellé */
    public static function types(): array
    {
        return [
            'bt'        => 'BT — Brevet de Technicien',
            'bts'       => 'BTS — Brevet de Technicien Supérieur',
            'licence'   => 'Licence',
            'licence_pro' => 'Licence Professionnelle',
            'master'    => 'Master',
            'cap'       => 'CAP — Certificat d\'Aptitude Professionnelle',
            'bep'       => 'BEP — Brevet d\'Études Professionnelles',
            'dut'       => 'DUT',
            'du'        => 'DU — Diplôme Universitaire',
            'certifiant'=> 'Formation certifiante',
            'autre'     => 'Autre diplôme',
        ];
    }

    public static function label(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        return self::types()[$code] ?? strtoupper($code);
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(self::types());
    }

    /**
     * Années de formation possibles par diplôme (système LMD / formations sénégalaises).
     *
     * @return array<string, array<string, string>> diplôme => [valeur stockée => libellé affiché]
     */
    public static function formationYearsByDiploma(): array
    {
        return [
            'bt' => [
                '1' => 'Année 1',
                '2' => 'Année 2',
            ],
            'bts' => [
                '1' => 'Année 1',
                '2' => 'Année 2',
            ],
            'licence' => [
                '1' => 'Licence 1',
                '2' => 'Licence 2',
                '3' => 'Licence 3',
            ],
            'licence_pro' => [
                '1' => 'Licence Pro 1',
                '2' => 'Licence Pro 2',
                '3' => 'Licence Pro 3',
            ],
            'master' => [
                '4' => 'Master 1',
                '5' => 'Master 2',
            ],
            'cap' => [
                '1' => 'Année 1',
                '2' => 'Année 2',
            ],
            'bep' => [
                '1' => 'Année 1',
                '2' => 'Année 2',
            ],
            'dut' => [
                '1' => 'Année 1',
                '2' => 'Année 2',
            ],
            'du' => [
                '1' => 'Année 1',
            ],
            'certifiant' => [
                '1' => 'Session unique',
            ],
            'autre' => [
                '1' => 'Année 1',
                '2' => 'Année 2',
                '3' => 'Année 3',
            ],
        ];
    }

    /** @return list<string> */
    public static function allowedFormationYearLabels(?string $diplomaType): array
    {
        if (! $diplomaType) {
            return [];
        }

        return array_values(self::formationYearsByDiploma()[$diplomaType] ?? []);
    }

    public static function isValidFormationYear(?string $diplomaType, ?string $yearLabel): bool
    {
        if ($yearLabel === null || $yearLabel === '') {
            return false;
        }

        return in_array($yearLabel, self::allowedFormationYearLabels($diplomaType), true);
    }
}
