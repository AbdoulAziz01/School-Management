<?php

namespace App\Services;

use App\Models\AccountingCounter;
use Illuminate\Support\Facades\DB;

/**
 * Numérotation séquentielle des reçus par établissement, sous
 * lockForUpdate() pour empêcher deux caissiers d'obtenir le même numéro en
 * encaissant au même instant — même classe de bug que celle déjà corrigée
 * sur StudentController::autoAssignToClass (Phase 5.5).
 *
 * DOIT être appelé à l'intérieur d'une transaction ouverte par l'appelant
 * (voir PaymentService::recordPayment()) : le verrou n'a d'effet que
 * jusqu'au COMMIT de cette transaction.
 */
class ReceiptNumberGenerator
{
    private const KEY = 'receipt_number';

    public function next(int $schoolId): string
    {
        $counter = AccountingCounter::where('school_id', $schoolId)
            ->where('key', self::KEY)
            ->lockForUpdate()
            ->first();

        if (! $counter) {
            try {
                $counter = AccountingCounter::create([
                    'school_id' => $schoolId,
                    'key' => self::KEY,
                    'next_value' => 1,
                ]);
            } catch (\Illuminate\Database\QueryException) {
                // Créé entre-temps par une transaction concurrente (course
                // possible uniquement sur le tout premier reçu d'une école,
                // la contrainte unique (school_id, key) l'empêche en double).
            }

            $counter = AccountingCounter::where('school_id', $schoolId)
                ->where('key', self::KEY)
                ->lockForUpdate()
                ->first();
        }

        $value = $counter->next_value;
        $counter->update(['next_value' => $value + 1]);

        return sprintf('REC-%d-%s-%05d', $schoolId, date('Y'), $value);
    }
}
