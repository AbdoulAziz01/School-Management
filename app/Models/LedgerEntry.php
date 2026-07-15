<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Écriture comptable immuable — voir docblock de la migration. Ne jamais
 * créer d'instance directement dans un contrôleur : passer par les
 * Observers (app/Observers) qui écoutent Payment/Expense/SalaryPayment.
 */
class LedgerEntry extends Model
{
    use BelongsToSchool, HasFactory;

    public const TYPE_RECETTE = 'recette';
    public const TYPE_DEPENSE = 'depense';

    protected $fillable = [
        'school_id',
        'entry_type',
        'source_type',
        'source_id',
        'amount',
        'description',
        'recorded_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
