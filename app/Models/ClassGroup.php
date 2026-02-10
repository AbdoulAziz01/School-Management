<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassGroup extends Model
{
    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'school_class_id',
        'academic_year_id',
        'is_active',
        'max_students',
        'created_by',
        'updated_by'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'max_students' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * La classe à laquelle appartient ce groupe.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Les étudiants appartenant à ce groupe.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_group_student', 'class_group_id', 'student_id')
            ->withTimestamps();
    }

    /**
     * Les emplois du temps associés à ce groupe.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_group_id');
    }

    /**
     * L'année académique à laquelle ce groupe appartient.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Vérifie si le groupe est complet.
     */
    public function isFull(): bool
    {
        if (is_null($this->max_students)) {
            return false;
        }
        return $this->students()->count() >= $this->max_students;
    }

    /**
     * Vérifie si le groupe est actif.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Récupère le nombre d'étudiants dans le groupe.
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Récupère le taux de remplissage du groupe.
     */
    public function getFillRateAttribute(): float
    {
        if (is_null($this->max_students) || $this->max_students === 0) {
            return 0;
        }
        return round(($this->students_count / $this->max_students) * 100, 2);
    }

    /**
     * Récupère les groupes actifs pour une classe donnée.
     */
    public static function getActiveForClass($classId)
    {
        return static::where('school_class_id', $classId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Récupère les groupes avec le nombre d'étudiants.
     */
    public static function withStudentCount($classId = null)
    {
        $query = static::withCount('students');
        
        if ($classId) {
            $query->where('school_class_id', $classId);
        }
        
        return $query->orderBy('name')->get();
    }
}
