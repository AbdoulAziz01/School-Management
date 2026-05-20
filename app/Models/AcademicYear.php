<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use BelongsToSchool, HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date', 'is_current', 'school_id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'academic_year_id');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'academic_year_id');
    }

    /** Libellé de la date de fin (optionnelle — courant au Sénégal). */
    public function endDateLabel(): string
    {
        return $this->end_date?->format('d/m/Y') ?? 'À définir';
    }

    /** Période affichée : début connu, fin éventuelle. */
    public function periodLabel(): string
    {
        $start = $this->start_date?->format('d/m/Y') ?? '—';

        if ($this->end_date) {
            return "Du {$start} au {$this->end_date->format('d/m/Y')}";
        }

        return "Depuis le {$start} (fin non fixée)";
    }
}
