<?php

namespace App\Observers;

use App\Models\LedgerEntry;
use App\Models\Payment;

/**
 * Alimente automatiquement le grand livre (ledger_entries) à chaque
 * paiement élève créé ou annulé — voir docblock de la migration
 * ledger_entries. Jamais l'inverse : un contrôleur ne doit jamais créer de
 * LedgerEntry lui-même.
 */
class PaymentObserver
{
    public function created(Payment $payment): void
    {
        LedgerEntry::create([
            'school_id' => $payment->school_id,
            'entry_type' => LedgerEntry::TYPE_RECETTE,
            'source_type' => Payment::class,
            'source_id' => $payment->id,
            'amount' => $payment->amount,
            'description' => "Paiement {$payment->receipt_number}",
            'recorded_at' => $payment->paid_at,
            'created_by' => $payment->recorded_by,
        ]);
    }

    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status') || $payment->status !== Payment::STATUS_CANCELLED) {
            return;
        }

        LedgerEntry::create([
            'school_id' => $payment->school_id,
            'entry_type' => LedgerEntry::TYPE_RECETTE,
            'source_type' => Payment::class,
            'source_id' => $payment->id,
            'amount' => -$payment->amount,
            'description' => "Annulation paiement {$payment->receipt_number}",
            'recorded_at' => $payment->cancelled_at ?? now(),
            'created_by' => $payment->cancelled_by,
        ]);
    }
}
