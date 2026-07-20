<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Modèle Excel téléchargeable pour l'import en masse d'enseignants.
 * Matières : optionnel, utile au collège/lycée (au primaire un enseignant
 * couvre toutes les matières de sa classe, rien à préciser). Les classes ne
 * sont volontairement pas dans le modèle : l'affectation se fait
 * manuellement par l'admin après l'import, une fois les emplois du temps
 * connus.
 */
class TeachersImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            ['Kane', 'Moussa', 'moussa.kane@example.com', '221771234567', 'Dakar', '10/06/1985', 'Mathématiques; Physique-Chimie'],
        ];
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Adresse',
            'Date de naissance (jj/mm/aaaa)',
            'Matières (séparées par ;, optionnel — collège/lycée)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
