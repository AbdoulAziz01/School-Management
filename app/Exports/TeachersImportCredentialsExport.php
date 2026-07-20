<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export ponctuel des identifiants/mots de passe générés lors d'un import
 * en masse d'enseignants (miroir de StudentsImportCredentialsExport).
 */
class TeachersImportCredentialsExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    /** @param  list<array{identifier: string, name: string, email: string, password: string, subjects: string}>  $rows */
    public function __construct(private array $rows) {}

    public function array(): array
    {
        return array_map(fn (array $row) => [
            $row['identifier'],
            $row['name'],
            $row['email'],
            $row['password'],
            $row['subjects'],
        ], $this->rows);
    }

    public function headings(): array
    {
        return ['Identifiant', 'Nom complet', 'Email', 'Mot de passe', 'Matières'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
