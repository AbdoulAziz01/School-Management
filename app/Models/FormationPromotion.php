<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormationPromotion extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'formation_department_id',
        'academic_year_id',
        'name',
        'filiere',
        'diploma_type',
        'formation_year',
        'description',
    ];

    public function formationDepartment(): BelongsTo
    {
        return $this->belongsTo(FormationDepartment::class, 'formation_department_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** Groupes / classes de la promotion (BEP1-1, MC2-1…). */
    public function groups(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'formation_promotion_id');
    }

    /** Nombre d'élèves inscrits dans tous les groupes (calculé, pas stocké). */
    public function studentsCount(): int
    {
        return (int) $this->groups()->withCount('students')->get()->sum('students_count');
    }

    /** Capacité totale des groupes. */
    public function totalCapacity(): int
    {
        return (int) $this->groups()->sum('capacity');
    }
}
