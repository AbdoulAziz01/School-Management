<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\EmployeeSalaryProfile;
use App\Models\SalaryPayment;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Génération et paiement des salaires — une ligne SalaryPayment par employé
 * par période (mois), jamais le montant dû saisi à la main : il vient
 * toujours du salaire actif dans EmployeeSalaryProfile (Phase 6.2).
 * Idempotent (unique(user_id, period)) : la génération mensuelle peut être
 * rejouée sans risque de doublon.
 */
class SalaryPaymentService
{
    /** @return Collection<int, SalaryPayment> */
    public function generateForPeriod(School $school, Carbon $period): Collection
    {
        $period = $period->copy()->startOfMonth();

        $activeProfiles = EmployeeSalaryProfile::where('school_id', $school->id)
            ->whereNull('effective_to')
            ->where('effective_from', '<=', $period->copy()->endOfMonth())
            ->get();

        $created = collect();

        foreach ($activeProfiles as $profile) {
            $created->push(SalaryPayment::firstOrCreate(
                [
                    'user_id' => $profile->user_id,
                    'period' => $period,
                ],
                [
                    'school_id' => $school->id,
                    'employee_salary_profile_id' => $profile->id,
                    'amount_due' => $profile->monthly_amount,
                    'status' => SalaryPayment::STATUS_PENDING,
                ]
            ));
        }

        return $created;
    }

    public function pay(SalaryPayment $payment, float $amount, string $method, User $paidBy, ?CashSession $session = null): SalaryPayment
    {
        if ($amount > $payment->balanceDue() + 0.01) {
            throw new \RuntimeException('Le montant dépasse le solde dû.');
        }

        return DB::transaction(function () use ($payment, $amount, $method, $paidBy, $session) {
            $newPaid = round($payment->amount_paid + $amount, 2);

            $payment->update([
                'amount_paid' => $newPaid,
                'status' => $newPaid >= $payment->amount_due - 0.01
                    ? SalaryPayment::STATUS_PAID
                    : SalaryPayment::STATUS_PARTIAL,
                'paid_at' => now(),
                'payment_method' => $method,
                'paid_by' => $paidBy->id,
                'cash_session_id' => $session?->id,
            ]);

            return $payment;
        });
    }

    public function cancel(SalaryPayment $payment, User $cancelledBy, string $reason): SalaryPayment
    {
        if ($payment->status === SalaryPayment::STATUS_CANCELLED) {
            throw new \RuntimeException('Ce paiement est déjà annulé.');
        }

        $payment->update([
            'status' => SalaryPayment::STATUS_CANCELLED,
            'cancelled_by' => $cancelledBy->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return $payment;
    }
}
