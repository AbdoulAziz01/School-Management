<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';

    public const CATEGORY_FOURNITURES = 'fournitures';
    public const CATEGORY_MATERIEL = 'materiel';
    public const CATEGORY_INTERNET = 'internet';
    public const CATEGORY_ELECTRICITE = 'electricite';
    public const CATEGORY_EAU = 'eau';
    public const CATEGORY_ENTRETIEN = 'entretien';
    public const CATEGORY_PRIME = 'prime';
    public const CATEGORY_AUTRE = 'autre';

    /** @var array<string, string> */
    public const CATEGORIES = [
        self::CATEGORY_FOURNITURES => 'Fournitures',
        self::CATEGORY_MATERIEL => 'Matériel',
        self::CATEGORY_INTERNET => 'Internet',
        self::CATEGORY_ELECTRICITE => 'Électricité',
        self::CATEGORY_EAU => 'Eau',
        self::CATEGORY_ENTRETIEN => 'Entretien',
        self::CATEGORY_PRIME => 'Prime',
        self::CATEGORY_AUTRE => 'Autre',
    ];

    protected $fillable = [
        'school_id',
        'category',
        'beneficiary',
        'amount',
        'motif',
        'expense_date',
        'payment_method',
        'recorded_by',
        'justificatif_path',
        'cash_session_id',
        'status',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'amount' => 'float',
        'expense_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'amount', 'motif', 'status', 'cancellation_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Dépense {$eventName}");
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
