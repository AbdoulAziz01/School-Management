<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Une ligne = un salaire en vigueur pour une période. Jamais mise à jour une
 * fois créée : un changement de salaire ferme la ligne active
 * (effective_to) et en ouvre une nouvelle — voir EmployeeSalaryProfileService.
 */
class EmployeeSalaryProfile extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    protected $fillable = [
        'school_id',
        'user_id',
        'monthly_amount',
        'effective_from',
        'effective_to',
        'created_by',
    ];

    protected $casts = [
        'monthly_amount' => 'float',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['monthly_amount', 'effective_from', 'effective_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Salaire {$eventName}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->effective_to === null;
    }
}
