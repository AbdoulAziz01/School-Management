<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormationDepartment extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'name',
        'school_id',
    ];

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'formation_department_id');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(FormationPromotion::class, 'formation_department_id');
    }
}
