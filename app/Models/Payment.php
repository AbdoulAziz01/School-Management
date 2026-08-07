<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Un encaissement élève. Jamais supprimé — une erreur passe par
 * PaymentService::cancel() (status=cancelled, traçabilité complète),
 * jamais un DELETE.
 */
class Payment extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const METHOD_ESPECES = 'especes';
    public const METHOD_WAVE = 'wave';
    public const METHOD_ORANGE_MONEY = 'orange_money';
    public const METHOD_VIREMENT = 'virement';
    public const METHOD_CHEQUE = 'cheque';

    /** @var array<string, string> */
    public const METHOD_LABELS = [
        self::METHOD_ESPECES => 'Espèces',
        self::METHOD_WAVE => 'Wave',
        self::METHOD_ORANGE_MONEY => 'Orange Money',
        self::METHOD_VIREMENT => 'Virement',
        self::METHOD_CHEQUE => 'Chèque',
    ];

    /** Modes de paiement exigeant une référence (numéro de chèque, réf. virement, ID transaction mobile money). */
    public const METHODS_REQUIRING_REFERENCE = [
        self::METHOD_CHEQUE,
        self::METHOD_VIREMENT,
        self::METHOD_WAVE,
        self::METHOD_ORANGE_MONEY,
    ];

    /** Modes de paiement exigeant le nom de la banque. */
    public const METHODS_REQUIRING_BANK = [
        self::METHOD_CHEQUE,
        self::METHOD_VIREMENT,
    ];

    protected $fillable = [
        'school_id',
        'receipt_number',
        'student_id',
        'amount',
        'payment_method',
        'payment_reference',
        'payment_bank',
        'paid_at',
        'cash_session_id',
        'recorded_by',
        'status',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'cancellation_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Paiement {$eventName}");
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function methodLabel(): string
    {
        return self::METHOD_LABELS[$this->payment_method] ?? $this->payment_method;
    }

    /** Libellé du champ "référence" adapté au mode de paiement (numéro de chèque, réf. virement, ID transaction). */
    public function referenceLabel(): string
    {
        return match ($this->payment_method) {
            self::METHOD_CHEQUE => 'Numéro de chèque',
            self::METHOD_VIREMENT => 'Référence de virement',
            self::METHOD_WAVE, self::METHOD_ORANGE_MONEY => 'ID de transaction',
            default => 'Référence',
        };
    }

    public function requiresReference(): bool
    {
        return in_array($this->payment_method, self::METHODS_REQUIRING_REFERENCE, true);
    }

    public function requiresBank(): bool
    {
        return in_array($this->payment_method, self::METHODS_REQUIRING_BANK, true);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
