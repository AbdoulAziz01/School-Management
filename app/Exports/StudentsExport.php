<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(private ?int $classId = null) {}

    public function query()
    {
        $query = User::whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->with(['class', 'class.level', 'class.academicYear'])
            ->orderBy('name');

        if ($this->classId) {
            $query->where('class_id', $this->classId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Identifiant',
            'Prénom',
            'Nom',
            'Email',
            'Date de naissance',
            'Classe',
            'Niveau',
            'Année scolaire',
            'Statut',
        ];
    }

    public function map($student): array
    {
        return [
            $student->identifier,
            $student->first_name,
            $student->last_name,
            $student->email ?? '—',
            $student->date_of_birth?->format('d/m/Y') ?? '—',
            $student->class?->name ?? '—',
            $student->class?->level?->name ?? '—',
            $student->class?->academicYear?->name ?? '—',
            match ($student->status) {
                'approved' => 'Approuvé',
                'pending'  => 'En attente',
                'rejected' => 'Rejeté',
                default    => $student->status,
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
