<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\CashSessionDiscrepancyNotification;
use Illuminate\Support\Facades\DB;

/**
 * Ouverture/clôture de la caisse d'un caissier — une caisse par caissier
 * (CashRegister::user_id unique), une session = une période
 * ouverture→clôture. Une seule session ouverte à la fois par caisse
 * (contrainte SQL, voir migration cash_sessions).
 */
class CashSessionService
{
    public function registerFor(User $cashier): CashRegister
    {
        return CashRegister::firstOrCreate(
            ['user_id' => $cashier->id],
            ['school_id' => $cashier->school_id, 'name' => 'Caisse de '.$cashier->name]
        );
    }

    public function currentOpenSession(User $cashier): ?CashSession
    {
        return CashSession::where('cash_register_id', $this->registerFor($cashier)->id)
            ->where('status', CashSession::STATUS_OPEN)
            ->first();
    }

    public function open(User $cashier, float $openingBalance): CashSession
    {
        if ($this->currentOpenSession($cashier)) {
            throw new \RuntimeException('Une session de caisse est déjà ouverte.');
        }

        $register = $this->registerFor($cashier);

        return CashSession::create([
            'school_id' => $cashier->school_id,
            'cash_register_id' => $register->id,
            'opened_by' => $cashier->id,
            'opened_at' => now(),
            'opening_balance' => $openingBalance,
            'status' => CashSession::STATUS_OPEN,
        ]);
    }

    public function close(CashSession $session, float $actualClosingBalance, User $closedBy, ?string $notes = null): CashSession
    {
        return DB::transaction(function () use ($session, $actualClosingBalance, $closedBy, $notes) {
            $encaissements = Payment::where('cash_session_id', $session->id)
                ->where('status', Payment::STATUS_COMPLETED)
                ->sum('amount');

            $expected = round((float) $session->opening_balance + (float) $encaissements, 2);
            $difference = round($actualClosingBalance - $expected, 2);

            $session->update([
                'closed_by' => $closedBy->id,
                'closed_at' => now(),
                'expected_closing_balance' => $expected,
                'actual_closing_balance' => $actualClosingBalance,
                'difference' => $difference,
                'status' => CashSession::STATUS_CLOSED,
                'notes' => $notes,
            ]);

            if (abs($difference) > 0.01) {
                $this->notifyDirecteursOfDiscrepancy($session, $closedBy);
            }

            return $session;
        });
    }

    private function notifyDirecteursOfDiscrepancy(CashSession $session, User $closedBy): void
    {
        $directeurs = User::where('school_id', $session->school_id)
            ->where('role', User::ROLE_DIRECTEUR)
            ->get();

        foreach ($directeurs as $directeur) {
            $directeur->notify(new CashSessionDiscrepancyNotification($session, $closedBy->name));
        }
    }

    /** @return array{opening_balance: float, encaissements: float, expected_balance: float, payments_count: int} */
    public function liveSummary(CashSession $session): array
    {
        $payments = Payment::where('cash_session_id', $session->id)
            ->where('status', Payment::STATUS_COMPLETED)
            ->get(['amount']);

        $encaissements = round((float) $payments->sum('amount'), 2);

        return [
            'opening_balance' => (float) $session->opening_balance,
            'encaissements' => $encaissements,
            'expected_balance' => round((float) $session->opening_balance + $encaissements, 2),
            'payments_count' => $payments->count(),
        ];
    }
}
