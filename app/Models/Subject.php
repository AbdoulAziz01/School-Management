<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subject extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'coefficient', 'is_active', 'school_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Matière {$eventName}");
    }

    protected $fillable = [
        'name',
        'code',
        'school_id',
        'coefficient',
        'lmd_settings',
        'description',
        'is_active',
        'department',
        'hours_per_week',
        'is_core_subject',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core_subject' => 'boolean',
        'hours_per_week' => 'float',
        'coefficient' => 'float',
        'lmd_settings' => 'array',
    ];

    /**
     * Niveaux associés à cette matière
     */
    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'level_subject')
                    ->withPivot('coefficient', 'is_compulsory')
                    ->withTimestamps();
    }

    /**
     * Assignations des enseignants à cette matière
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'subject_id');
    }

    /**
     * Notes associées à cette matière
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Présences associées à cette matière
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Enseignants qui enseignent cette matière
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_subjects', 'subject_id', 'teacher_id')
                    ->withTimestamps();
    }

    /**
     * Vérifie si la matière est obligatoire pour un niveau donné
     */
    public function isCompulsoryForLevel($levelId): bool
    {
        return $this->levels()->where('level_id', $levelId)
                      ->wherePivot('is_compulsory', true)
                      ->exists();
    }

    /**
     * Récupère le coefficient pour un niveau donné
     */
    public function getCoefficientForLevel($levelId): float
    {
        $level = $this->levels()->where('level_id', $levelId)->first();
        return $level ? (float) $level->pivot->coefficient : 1.0;
    }

    /**
     * Vérifie si la matière est active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Récupère les matières obligatoires pour un niveau
     */
    public static function getCompulsoryForLevel($levelId)
    {
        return static::whereHas('levels', function($query) use ($levelId) {
            $query->where('level_id', $levelId)
                  ->where('is_compulsory', true);
        })->get();
    }
}
