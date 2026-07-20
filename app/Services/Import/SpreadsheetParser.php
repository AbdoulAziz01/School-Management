<?php

namespace App\Services\Import;

use Maatwebsite\Excel\Facades\Excel;

/**
 * Lecture générique d'un fichier Excel/CSV en tableau brut (en-têtes +
 * lignes) — partagée par tous les imports en masse (élèves, enseignants...)
 * pour ne pas dupliquer la logique de parsing.
 */
class SpreadsheetParser
{
    /** @return array{headers: list<string>, rows: list<list<mixed>>} */
    public static function parse(string $absolutePath): array
    {
        $sheets = Excel::toArray(new \stdClass, $absolutePath);
        $rows = $sheets[0] ?? [];

        $headers = array_map(
            fn ($h) => trim((string) $h),
            array_shift($rows) ?? []
        );

        // Ignore les lignes totalement vides (fin de fichier, lignes blanches).
        $rows = array_values(array_filter($rows, function (array $row) {
            return collect($row)->contains(fn ($cell) => trim((string) $cell) !== '');
        }));

        return ['headers' => $headers, 'rows' => $rows];
    }
}
