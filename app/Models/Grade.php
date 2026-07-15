<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Grade extends Model
{
    use BelongsToSchool, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'subject_id', 'grade', 'type', 'semester', 'coefficient', 'teacher_edited_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Note {$eventName}");
    }

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'subject_id',
        'grade',
        'comments',
        'appreciation',
        'date',
        'type',
        'coefficient',
        'semester',
        'academic_year_id',
        'school_id',
        'teacher_edited_at',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'               => 'date',
        'grade'              => 'decimal:2',
        'semester'           => 'integer',
        'academic_year_id'   => 'integer',
        'teacher_edited_at'  => 'datetime',
    ];

    /**
     * Un enseignant ne peut corriger une note qu'une seule fois après sa
     * saisie initiale (voir migration add_teacher_edited_at_to_grades_table).
     */
    public function canStillBeEditedByTeacher(): bool
    {
        return $this->teacher_edited_at === null;
    }

    /**
     * L'année académique à laquelle cette note est rattachée.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Obtenir l'utilisateur associé à cette note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir la matière associée à cette note.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
