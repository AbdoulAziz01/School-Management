<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Enregistrement des dépenses par le comptable — jamais de suppression,
 * une erreur passe par cancel() (traçabilité complète), jamais un DELETE.
 */
class ExpenseService
{
    /** @param array{category: string, beneficiary: string, amount: float, motif: string, expense_date: string, payment_method: string} $data */
    public function record(array $data, User $recordedBy, ?CashSession $session = null, ?UploadedFile $justificatif = null): Expense
    {
        $justificatifPath = $justificatif
            ? $justificatif->store('expense-justificatifs', 'local')
            : null;

        return Expense::create([
            'school_id' => $recordedBy->school_id,
            'category' => $data['category'],
            'beneficiary' => $data['beneficiary'],
            'amount' => $data['amount'],
            'motif' => $data['motif'],
            'expense_date' => $data['expense_date'],
            'payment_method' => $data['payment_method'],
            'recorded_by' => $recordedBy->id,
            'justificatif_path' => $justificatifPath,
            'cash_session_id' => $session?->id,
            'status' => Expense::STATUS_ACTIVE,
        ]);
    }

    public function cancel(Expense $expense, User $cancelledBy, string $reason): Expense
    {
        if ($expense->isCancelled()) {
            throw new \RuntimeException('Cette dépense est déjà annulée.');
        }

        $expense->update([
            'status' => Expense::STATUS_CANCELLED,
            'cancelled_by' => $cancelledBy->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $expense;
    }

    public function justificatifUrl(Expense $expense): ?string
    {
        if (! $expense->justificatif_path) {
            return null;
        }

        return Storage::disk('local')->exists($expense->justificatif_path)
            ? $expense->justificatif_path
            : null;
    }
}
