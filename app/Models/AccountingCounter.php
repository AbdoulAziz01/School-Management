<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Compteur séquentiel par école (ex. numéro de reçu) — volontairement SANS
 * BelongsToSchool : toujours interrogé avec un school_id explicite sous
 * lockForUpdate() (voir ReceiptNumberGenerator), y compris depuis des
 * commandes planifiées sans contexte tenant ambiant.
 */
class AccountingCounter extends Model
{
    protected $fillable = [
        'school_id',
        'key',
        'next_value',
    ];
}
