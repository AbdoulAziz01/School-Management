<?php

namespace App\Services\Import;

use Carbon\Carbon;

/**
 * Parsing tolérant de dates issues d'un fichier Excel/CSV importé —
 * accepte plusieurs formats texte courants ainsi que les numéros de série
 * Excel (une date stockée comme nombre quand la cellule n'est pas
 * formatée "Date" dans le tableur d'origine).
 */
class FlexibleDateParser
{
    public static function parse(string $value): ?Carbon
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
            } catch (\Throwable) {
                return null;
            }
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) {
                    return $date->startOfDay();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
