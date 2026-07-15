<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\LedgerEntry;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        LedgerEntry::create([
            'school_id' => $expense->school_id,
            'entry_type' => LedgerEntry::TYPE_DEPENSE,
            'source_type' => Expense::class,
            'source_id' => $expense->id,
            'amount' => $expense->amount,
            'description' => "Dépense : {$expense->motif} ({$expense->beneficiary})",
            'recorded_at' => $expense->expense_date,
            'created_by' => $expense->recorded_by,
        ]);
    }

    public function updated(Expense $expense): void
    {
        if (! $expense->wasChanged('status') || $expense->status !== Expense::STATUS_CANCELLED) {
            return;
        }

        LedgerEntry::create([
            'school_id' => $expense->school_id,
            'entry_type' => LedgerEntry::TYPE_DEPENSE,
            'source_type' => Expense::class,
            'source_id' => $expense->id,
            'amount' => -$expense->amount,
            'description' => "Annulation dépense : {$expense->motif}",
            'recorded_at' => $expense->cancelled_at ?? now(),
            'created_by' => $expense->cancelled_by,
        ]);
    }
}
