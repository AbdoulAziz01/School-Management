<?php

namespace App\Services;

use App\Models\EmployeeSalaryProfile;
use App\Models\LedgerEntry;
use App\Models\School;
use App\Models\StudentInvoice;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Indicateurs financiers du dashboard directeur — lecture seule, calculée
 * uniquement à partir de ledger_entries (jamais recalculée depuis
 * payments/expenses/salary_payments directement, pour que le dashboard
 * reflète toujours exactement ce que le grand livre contient).
 */
class AccountingDashboardService
{
    /** @return array<string, mixed> */
    public function summary(School $school): array
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        return [
            'solde_actuel' => $this->balance($school),
            'recettes_jour' => $this->sumByType($school, LedgerEntry::TYPE_RECETTE, $today, now()),
            'depenses_jour' => $this->sumByType($school, LedgerEntry::TYPE_DEPENSE, $today, now()),
            'recettes_mois' => $this->sumByType($school, LedgerEntry::TYPE_RECETTE, $monthStart, now()),
            'depenses_mois' => $this->sumByType($school, LedgerEntry::TYPE_DEPENSE, $monthStart, now()),
            'masse_salariale' => $this->monthlyPayroll($school),
            'eleves_payes' => $this->studentsFullyPaidCount($school),
            'eleves_debiteurs' => $this->studentsWithDebtCount($school),
            'paiements_en_attente' => StudentInvoice::where('school_id', $school->id)
                ->whereIn('status', [StudentInvoice::STATUS_PENDING, StudentInvoice::STATUS_PARTIAL])
                ->count(),
        ];
    }

    private function balance(School $school): float
    {
        $recettes = LedgerEntry::where('school_id', $school->id)
            ->where('entry_type', LedgerEntry::TYPE_RECETTE)
            ->sum('amount');

        $depenses = LedgerEntry::where('school_id', $school->id)
            ->where('entry_type', LedgerEntry::TYPE_DEPENSE)
            ->sum('amount');

        return round((float) $recettes - (float) $depenses, 2);
    }

    private function sumByType(School $school, string $type, \DateTimeInterface $from, \DateTimeInterface $to): float
    {
        return round((float) LedgerEntry::where('school_id', $school->id)
            ->where('entry_type', $type)
            ->whereBetween('recorded_at', [$from, $to])
            ->sum('amount'), 2);
    }

    private function monthlyPayroll(School $school): float
    {
        return round((float) EmployeeSalaryProfile::where('school_id', $school->id)
            ->whereNull('effective_to')
            ->sum('monthly_amount'), 2);
    }

    private function studentsFullyPaidCount(School $school): int
    {
        return User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->whereDoesntHave('studentInvoices', function ($q) {
                $q->whereIn('status', [StudentInvoice::STATUS_PENDING, StudentInvoice::STATUS_PARTIAL]);
            })
            ->whereHas('studentInvoices')
            ->count();
    }

    private function studentsWithDebtCount(School $school): int
    {
        return User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->whereHas('studentInvoices', function ($q) {
                $q->whereIn('status', [StudentInvoice::STATUS_PENDING, StudentInvoice::STATUS_PARTIAL]);
            })
            ->count();
    }

    /**
     * Évolution recettes/dépenses des 6 derniers mois, pour le graphique.
     *
     * @return array{labels: list<string>, recettes: list<float>, depenses: list<float>}
     */
    public function evolution(School $school, int $months = 6): array
    {
        $labels = [];
        $recettes = [];
        $depenses = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $labels[] = ucfirst($monthStart->locale('fr')->translatedFormat('M Y'));
            $recettes[] = $this->sumByType($school, LedgerEntry::TYPE_RECETTE, $monthStart, $monthEnd);
            $depenses[] = $this->sumByType($school, LedgerEntry::TYPE_DEPENSE, $monthStart, $monthEnd);
        }

        return ['labels' => $labels, 'recettes' => $recettes, 'depenses' => $depenses];
    }

    /** @return Collection<int, LedgerEntry> */
    public function recentOperations(School $school, int $limit = 10): Collection
    {
        return LedgerEntry::where('school_id', $school->id)
            ->orderByDesc('recorded_at')
            ->limit($limit)
            ->get();
    }
}
