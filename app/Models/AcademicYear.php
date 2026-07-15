<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AcademicYear extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'is_current', 'is_closed', 'school_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Année académique {$eventName}");
    }

    protected $fillable = ['name', 'start_date', 'end_date', 'is_current', 'is_closed', 'school_id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_closed' => 'boolean',
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

    public function isClosed(): bool
    {
        return (bool) $this->is_closed;
    }

    /** Année archivée : plus aucune modification (consultation seule). */
    public function isReadOnly(): bool
    {
        return $this->isClosed() || ! $this->is_current;
    }

    /** Passage en classe supérieure autorisé uniquement sur une année terminée. */
    public function allowsPromotion(): bool
    {
        return $this->isClosed();
    }

    /** Affectation d'élèves autorisée uniquement sur l'année courante non terminée. */
    public function allowsStudentAssignment(): bool
    {
        return $this->is_current && ! $this->isClosed();
    }

    public function statusLabel(): string
    {
        if ($this->is_current && ! $this->isClosed()) {
            return 'Courante';
        }

        if ($this->isClosed()) {
            return 'Terminée';
        }

        return 'Passée';
    }

    public function statusBadgeClass(): string
    {
        if ($this->is_current && ! $this->isClosed()) {
            return 'success';
        }

        if ($this->isClosed()) {
            return 'secondary';
        }

        return 'warning';
    }
}
