<?php

namespace App\Observers;

use App\Models\LedgerEntry;
use App\Models\SalaryPayment;
use App\Models\User;

class SalaryPaymentObserver
{
    /**
     * Une écriture par versement réel (pas à la création de la ligne
     * "à payer", qui ne représente encore aucun mouvement d'argent) — le
     * montant enregistré est le delta entre l'ancien et le nouveau
     * amount_paid, pour couvrir les paiements partiels successifs.
     */
    public function updated(SalaryPayment $payment): void
    {
        // Lecture directe (pas $payment->user, lazy loading interdit hors
        // contexte eager-loaded) : simple lookup par id.
        $employeeName = User::withoutGlobalScopes()->find($payment->user_id)?->name ?? 'employé';

        if ($payment->wasChanged('amount_paid')) {
            $delta = round($payment->amount_paid - (float) $payment->getOriginal('amount_paid'), 2);

            if ($delta > 0) {
                LedgerEntry::create([
                    'school_id' => $payment->school_id,
                    'entry_type' => LedgerEntry::TYPE_DEPENSE,
                    'source_type' => SalaryPayment::class,
                    'source_id' => $payment->id,
                    'amount' => $delta,
                    'description' => "Salaire {$employeeName} — {$payment->period->locale('fr')->translatedFormat('F Y')}",
                    'recorded_at' => $payment->paid_at ?? now(),
                    'created_by' => $payment->paid_by,
                ]);
            }
        }

        if ($payment->wasChanged('status') && $payment->status === SalaryPayment::STATUS_CANCELLED && $payment->amount_paid > 0) {
            LedgerEntry::create([
                'school_id' => $payment->school_id,
                'entry_type' => LedgerEntry::TYPE_DEPENSE,
                'source_type' => SalaryPayment::class,
                'source_id' => $payment->id,
                'amount' => -$payment->amount_paid,
                'description' => "Annulation salaire {$employeeName} — {$payment->period->locale('fr')->translatedFormat('F Y')}",
                'recorded_at' => $payment->cancelled_at ?? now(),
                'created_by' => $payment->cancelled_by,
            ]);
        }
    }
}
