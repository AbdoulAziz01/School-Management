<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use App\Models\School;
use App\Services\AccountingDashboardService;
use Illuminate\Http\Request;

class DirecteurDashboardController extends Controller
{
    public function __construct(
        private AccountingDashboardService $dashboard
    ) {}

    public function index(Request $request)
    {
        $school = School::find($request->user()->school_id);

        return view('accounting.directeur.dashboard', [
            'summary' => $this->dashboard->summary($school),
            'evolution' => $this->dashboard->evolution($school),
            'recentOperations' => $this->dashboard->recentOperations($school),
            'expenseBreakdown' => $this->dashboard->expenseBreakdown($school),
            'paymentRate' => $this->dashboard->paymentRate($school),
            'pendingPayroll' => $this->dashboard->pendingPayroll($school),
            'topDebtors' => $this->dashboard->debtorsList($school)->sortByDesc('total_due')->take(5),
        ]);
    }

    public function exportLedger(Request $request)
    {
        $school = School::find($request->user()->school_id);

        $entries = LedgerEntry::where('school_id', $school->id)
            ->orderBy('recorded_at')
            ->get();

        $filename = 'grand-livre-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Description', 'Montant']);

            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->recorded_at->format('d/m/Y H:i'),
                    $entry->entry_type === LedgerEntry::TYPE_RECETTE ? 'Recette' : 'Dépense',
                    $entry->description,
                    number_format($entry->amount, 2, ',', ''),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
