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
 * Ce qu'un élève doit pour un fee_type donné — généré automatiquement
 * (StudentInvoiceService), jamais saisi à la main par le caissier.
 */
class StudentInvoice extends Model
{
    use BelongsToSchool, HasFactory, LogsActivity;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'À payer',
        self::STATUS_PARTIAL => 'Partiellement payé',
        self::STATUS_PAID => 'Payé',
        self::STATUS_CANCELLED => 'Annulé',
    ];

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'fee_type_id',
        'label',
        'amount_due',
        'amount_paid',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount_due' => 'float',
        'amount_paid' => 'float',
        'due_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount_paid', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Facture élève {$eventName}");
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function balanceDue(): float
    {
        return round($this->amount_due - $this->amount_paid, 2);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
