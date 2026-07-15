<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FeeType extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    public const CATEGORY_INSCRIPTION = 'inscription';
    public const CATEGORY_REINSCRIPTION = 'reinscription';
    public const CATEGORY_MENSUALITE = 'mensualite';
    public const CATEGORY_EXAMEN = 'examen';
    public const CATEGORY_AUTRE = 'autre';

    /** @var array<string, string> */
    public const CATEGORIES = [
        self::CATEGORY_INSCRIPTION => 'Frais d\'inscription',
        self::CATEGORY_REINSCRIPTION => 'Frais de réinscription',
        self::CATEGORY_MENSUALITE => 'Mensualité',
        self::CATEGORY_EXAMEN => 'Frais d\'examen',
        self::CATEGORY_AUTRE => 'Autre frais',
    ];

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'category',
        'is_recurring',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'category', 'is_recurring'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Type de frais {$eventName}");
    }

    public function amounts(): HasMany
    {
        return $this->hasMany(FeeAmount::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
