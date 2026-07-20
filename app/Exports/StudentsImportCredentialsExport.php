<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export ponctuel des identifiants/mots de passe générés lors d'un import
 * en masse — seule façon de les communiquer puisqu'aucune session ne peut
 * porter un flash "à révéler une fois" par élève créé (voir
 * StudentCredentialService, pensé pour une création à la fois).
 */
class StudentsImportCredentialsExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    /** @param  list<array{identifier: string, name: string, email: ?string, password: string, class: string}>  $rows */
    public function __construct(private array $rows) {}

    public function array(): array
    {
        return array_map(fn (array $row) => [
            $row['identifier'],
            $row['name'],
            $row['email'] ?? '—',
            $row['password'],
            $row['class'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return ['Identifiant', 'Nom complet', 'Email', 'Mot de passe', 'Classe'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
