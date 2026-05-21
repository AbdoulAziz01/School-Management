<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use BelongsToSchool;
    use HasFactory;

    protected $fillable = ['name', 'order', 'cycle', 'serie', 'school_id', 'description'];

    public function cycleLabel(): string
    {
        return match ($this->cycle) {
            'college'   => 'Collège',
            'lycee'     => 'Lycée',
            'formation' => 'Cycle formation',
            default     => (string) $this->cycle,
        };
    }

    public function isFormationCycle(): bool
    {
        return $this->cycle === 'formation';
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'level_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'level_subject')
                    ->withPivot('coefficient', 'is_compulsory')
                    ->withTimestamps();
    }
}
