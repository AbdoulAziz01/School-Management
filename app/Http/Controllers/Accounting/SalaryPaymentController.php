<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SalaryPayment;
use App\Models\School;
use App\Services\SalaryPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Paiement des salaires (comptable) — les montants dus viennent toujours de
 * EmployeeSalaryProfile (Phase 6.2) via SalaryPaymentService, jamais saisis
 * à la main.
 */
class SalaryPaymentController extends Controller
{
    public function __construct(
        private SalaryPaymentService $salaries
    ) {}

    public function index(Request $request)
    {
        $period = $this->resolvePeriod($request);

        $payments = SalaryPayment::where('period', $period)
            ->with('user')
            ->orderBy('status')
            ->get();

        return view('accounting.comptable.salaries.index', [
            'payments' => $payments,
            'period' => $period,
        ]);
    }

    public function generate(Request $request)
    {
        $period = $this->resolvePeriod($request);
        $school = School::find($request->user()->school_id);

        $created = $this->salaries->generateForPeriod($school, $period);

        return redirect()
            ->route('comptable.salaries.index', ['period' => $period->format('Y-m')])
            ->with('success', $created->count().' paiement(s) de salaire généré(s) pour '.$period->translatedFormat('F Y').'.');
    }

    public function pay(Request $request, SalaryPayment $salaryPayment)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(array_keys(Payment::METHOD_LABELS))],
        ]);

        try {
            $this->salaries->pay(
                $salaryPayment,
                (float) $validated['amount'],
                $validated['payment_method'],
                $request->user()
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Salaire payé pour '.$salaryPayment->user->name.'.');
    }

    public function cancel(Request $request, SalaryPayment $salaryPayment)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->salaries->cancel($salaryPayment, $request->user(), $validated['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Paiement de salaire annulé.');
    }

    private function resolvePeriod(Request $request): Carbon
    {
        if ($request->filled('period')) {
            try {
                return Carbon::createFromFormat('Y-m', $request->input('period'))->startOfMonth();
            } catch (\Exception) {
                // format invalide, on retombe sur le mois courant
            }
        }

        return now()->startOfMonth();
    }
}
