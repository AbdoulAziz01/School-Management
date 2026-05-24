<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
            'primaire'  => 'Primaire',
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

    /** Ordre pédagogique : primaire → collège → lycée → formation. */
    public function scopeOrderedPedagogically(Builder $query): Builder
    {
        return $query
            ->orderByRaw(self::cycleOrderSql('cycle'))
            ->orderBy('order')
            ->orderBy('name');
    }

    public static function cycleOrderSql(string $column = 'cycle'): string
    {
        return "CASE COALESCE({$column}, 'zzz') "
            ."WHEN 'primaire' THEN 1 WHEN 'college' THEN 2 WHEN 'lycee' THEN 3 WHEN 'formation' THEN 4 ELSE 99 END";
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
