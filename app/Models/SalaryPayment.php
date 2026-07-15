<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Une ligne par employé par période — générée par
 * SalaryPaymentService::generateForPeriod(), jamais saisie à la main.
 */
class SalaryPayment extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'En attente',
        self::STATUS_PARTIAL => 'Partiellement payé',
        self::STATUS_PAID => 'Payé',
        self::STATUS_CANCELLED => 'Annulé',
    ];

    protected $fillable = [
        'school_id',
        'user_id',
        'employee_salary_profile_id',
        'period',
        'amount_due',
        'amount_paid',
        'status',
        'paid_at',
        'payment_method',
        'paid_by',
        'cash_session_id',
        'notes',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'period' => 'date',
        'amount_due' => 'float',
        'amount_paid' => 'float',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount_paid', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Salaire {$eventName}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function balanceDue(): float
    {
        return round($this->amount_due - $this->amount_paid, 2);
    }
}
