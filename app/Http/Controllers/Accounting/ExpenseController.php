<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Payment;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Dépenses (comptable) — fournitures, matériel, internet, électricité,
 * eau, entretien, primes, autres. Jamais de suppression : voir
 * ExpenseService::cancel().
 */
class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenses
    ) {}

    public function index(Request $request)
    {
        $query = Expense::with('recordedBy')->orderByDesc('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $expenses = $query->paginate(20)->withQueryString();

        return view('accounting.comptable.expenses.index', [
            'expenses' => $expenses,
            'categories' => Expense::CATEGORIES,
        ]);
    }

    public function create()
    {
        return view('accounting.comptable.expenses.create', [
            'categories' => Expense::CATEGORIES,
            'methods' => Payment::METHOD_LABELS,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('depense.creer'), 403);

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'beneficiary' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'motif' => ['required', 'string', 'max:255'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(array_keys(Payment::METHOD_LABELS))],
            'justificatif' => ['nullable', 'file', 'max:5120'],
        ]);

        $this->expenses->record($validated, $request->user(), null, $request->file('justificatif'));

        return redirect()
            ->route('comptable.expenses.index')
            ->with('success', 'Dépense enregistrée.');
    }

    public function cancel(Request $request, Expense $expense)
    {
        abort_unless($request->user()->can('depense.annuler'), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->expenses->cancel($expense, $request->user(), $validated['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Dépense annulée.');
    }

    public function downloadJustificatif(Expense $expense)
    {
        $path = $this->expenses->justificatifUrl($expense);

        abort_unless($path, 404, 'Aucun justificatif pour cette dépense.');

        return Storage::disk('local')->download($path);
    }
}
