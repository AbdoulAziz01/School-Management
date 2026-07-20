<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Modèle Excel téléchargeable pour l'import en masse d'élèves — en-têtes
 * alignés sur App\Services\StudentImportService::CANONICAL_FIELDS, avec une
 * ligne d'exemple pour guider le remplissage.
 */
class StudentsImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            ['Kane', 'Fatou', 'fatou.kane@example.com', '15/03/2015', '6ème A', 'Moussa Kane', '221771234567', 'fr_text'],
        ];
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Prénom',
            'Email',
            'Date de naissance (jj/mm/aaaa)',
            'Classe',
            'Nom du parent',
            'WhatsApp du parent',
            'Langue du parent (fr_text, wo_audio ou pu_audio)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
