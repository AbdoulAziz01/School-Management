<?php

namespace App\Services;

use App\Models\EmployeeSalaryProfile;
use App\Models\Expense;
use App\Models\LedgerEntry;
use App\Models\SalaryPayment;
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

    /**
     * Élèves ayant au moins une facture en attente/partielle, avec le
     * montant total dû — pour l'écran "Élèves débiteurs".
     *
     * @return Collection<int, array{student: User, total_due: float}>
     */
    public function debtorsList(School $school): Collection
    {
        $cycleRank = ['primaire' => 1, 'college' => 2, 'lycee' => 3, 'formation' => 4];

        return User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->whereHas('studentInvoices', function ($q) {
                $q->whereIn('status', [StudentInvoice::STATUS_PENDING, StudentInvoice::STATUS_PARTIAL]);
            })
            ->with(['studentInvoices' => function ($q) {
                $q->whereIn('status', [StudentInvoice::STATUS_PENDING, StudentInvoice::STATUS_PARTIAL]);
            }, 'schoolClass.level'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $student) => [
                'student' => $student,
                'total_due' => round((float) $student->studentInvoices->sum(
                    fn (StudentInvoice $invoice) => $invoice->balanceDue()
                ), 2),
            ])
            // Tri pédagogique (CI → ... → Terminale) plutôt qu'alphabétique
            // sur le nom de l'élève, pour permettre un regroupement par
            // classe lisible côté vue.
            ->sortBy(function (array $row) use ($cycleRank) {
                $class = $row['student']->schoolClass;
                $rank = $cycleRank[$class?->level?->cycle] ?? 99;
                $order = $class?->level?->order ?? 999;

                return sprintf('%02d-%03d-%s-%s', $rank, $order, $class?->name ?? 'zzz', $row['student']->name);
            })
            ->values();
    }

    /**
     * Répartition des dépenses par catégorie sur le mois courant, pour le
     * camembert du dashboard directeur. Lue depuis Expense (pas
     * ledger_entries, qui ne porte pas la catégorie) — actives uniquement.
     *
     * @return array{labels: list<string>, amounts: list<float>}
     */
    public function expenseBreakdown(School $school): array
    {
        $rows = Expense::where('school_id', $school->id)
            ->where('status', Expense::STATUS_ACTIVE)
            ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => Expense::CATEGORIES[$row->category] ?? $row->category)->all(),
            'amounts' => $rows->map(fn ($row) => round((float) $row->total, 2))->all(),
        ];
    }

    /** Pourcentage d'élèves à jour parmi ceux ayant au moins une facture. */
    public function paymentRate(School $school): float
    {
        $paid = $this->studentsFullyPaidCount($school);
        $debtors = $this->studentsWithDebtCount($school);
        $total = $paid + $debtors;

        return $total > 0 ? round(($paid / $total) * 100, 1) : 0.0;
    }

    /** @return array{count: int, amount: float} salaires du mois en cours non encore soldés */
    public function pendingPayroll(School $school): array
    {
        $query = SalaryPayment::where('school_id', $school->id)
            ->where('period', now()->startOfMonth()->format('Y-m-d'))
            ->whereIn('status', [SalaryPayment::STATUS_PENDING, SalaryPayment::STATUS_PARTIAL]);

        return [
            'count' => (clone $query)->count(),
            'amount' => round((float) (clone $query)->get()->sum(fn (SalaryPayment $p) => $p->balanceDue()), 2),
        ];
    }
}
