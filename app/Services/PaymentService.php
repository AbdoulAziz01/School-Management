<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\StudentInvoice;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Encaissement d'un paiement élève, réparti sur une ou plusieurs factures
 * (payment_allocations). Chaque paiement met automatiquement à jour la
 * caisse (cash_session_id), le solde des factures et génère un reçu —
 * "aucune double saisie" (cahier des charges).
 *
 * L'annulation (avec traçabilité, jamais de suppression) est traitée côté
 * comptable en Phase 6.4, hors périmètre du guichet caissier.
 */
class PaymentService
{
    public function __construct(
        private ReceiptNumberGenerator $receipts
    ) {}

    /** @return Collection<int, StudentInvoice> factures impayées ou partiellement payées, les plus anciennes d'abord */
    public function outstandingInvoices(User $student): Collection
    {
        return StudentInvoice::where('student_id', $student->id)
            ->whereIn('status', [StudentInvoice::STATUS_PENDING, StudentInvoice::STATUS_PARTIAL])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * @param  array<int, float>  $allocations  student_invoice_id => montant à encaisser sur cette facture
     */
    public function recordPayment(
        User $student,
        array $allocations,
        string $method,
        User $cashier,
        ?CashSession $session,
        ?string $notes = null,
        ?string $reference = null,
        ?string $bank = null
    ): Payment {
        $allocations = array_filter(
            $allocations,
            fn ($amount) => $amount !== null && (float) $amount > 0
        );

        if (empty($allocations)) {
            throw new \InvalidArgumentException('Aucun montant à encaisser.');
        }

        if (in_array($method, Payment::METHODS_REQUIRING_REFERENCE, true) && ! $reference) {
            throw new \InvalidArgumentException('Une référence est obligatoire pour ce mode de paiement.');
        }

        if (in_array($method, Payment::METHODS_REQUIRING_BANK, true) && ! $bank) {
            throw new \InvalidArgumentException('La banque est obligatoire pour ce mode de paiement.');
        }

        return DB::transaction(function () use ($student, $allocations, $method, $cashier, $session, $notes, $reference, $bank) {
            $invoices = StudentInvoice::whereIn('id', array_keys($allocations))
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalAmount = 0.0;
            foreach ($allocations as $invoiceId => $amount) {
                $invoice = $invoices->get((int) $invoiceId);

                if (! $invoice) {
                    throw new \RuntimeException("Facture #{$invoiceId} introuvable pour cet élève.");
                }

                if ((float) $amount > $invoice->balanceDue() + 0.01) {
                    throw new \RuntimeException("Le montant dépasse le solde dû pour « {$invoice->label} ».");
                }

                $totalAmount += (float) $amount;
            }

            $payment = Payment::create([
                'school_id' => $student->school_id,
                'receipt_number' => $this->receipts->next($student->school_id),
                'student_id' => $student->id,
                'amount' => round($totalAmount, 2),
                'payment_method' => $method,
                'payment_reference' => $reference,
                'payment_bank' => $bank,
                'paid_at' => now(),
                'cash_session_id' => $session?->id,
                'recorded_by' => $cashier->id,
                'status' => Payment::STATUS_COMPLETED,
                'notes' => $notes,
            ]);

            foreach ($allocations as $invoiceId => $amount) {
                $invoice = $invoices->get((int) $invoiceId);

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'student_invoice_id' => $invoice->id,
                    'amount' => $amount,
                ]);

                $newPaid = round((float) $invoice->amount_paid + (float) $amount, 2);
                $invoice->update([
                    'amount_paid' => $newPaid,
                    'status' => $newPaid >= $invoice->amount_due - 0.01
                        ? StudentInvoice::STATUS_PAID
                        : StudentInvoice::STATUS_PARTIAL,
                ]);
            }

            return $payment;
        });
    }

    /**
     * Annule un paiement (erreur de saisie ou remboursement à l'élève) :
     * jamais de suppression, chaque facture retrouve le solde dû qu'elle
     * avait avant ce paiement.
     */
    public function cancel(Payment $payment, User $cancelledBy, string $reason): Payment
    {
        if ($payment->isCancelled()) {
            throw new \RuntimeException('Ce paiement est déjà annulé.');
        }

        return DB::transaction(function () use ($payment, $cancelledBy, $reason) {
            $allocations = $payment->allocations()->with('studentInvoice')->lockForUpdate()->get();

            foreach ($allocations as $allocation) {
                $invoice = $allocation->studentInvoice;
                $newPaid = round((float) $invoice->amount_paid - (float) $allocation->amount, 2);

                $invoice->update([
                    'amount_paid' => max(0, $newPaid),
                    'status' => $newPaid <= 0.01
                        ? StudentInvoice::STATUS_PENDING
                        : ($newPaid >= $invoice->amount_due - 0.01
                            ? StudentInvoice::STATUS_PAID
                            : StudentInvoice::STATUS_PARTIAL),
                ]);
            }

            $payment->update([
                'status' => Payment::STATUS_CANCELLED,
                'cancelled_by' => $cancelledBy->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            return $payment;
        });
    }
}
