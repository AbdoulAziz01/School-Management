<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

/**
 * Consultation de tous les paiements élèves — comptable (avec correction/
 * annulation tracée, voir PaymentService::cancel()) et directeur (lecture
 * seule : la vue masque l'action d'annulation faute de la permission
 * paiement.annuler, jamais accordée au rôle directeur).
 */
class PaymentCorrectionController extends Controller
{
    public function __construct(
        private PaymentService $payments
    ) {}

    public function index(Request $request)
    {
        $query = Payment::with(['student', 'recordedBy'])->orderByDesc('paid_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('student', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('accounting.shared.payments.index', ['payments' => $payments]);
    }

    public function cancel(Request $request, Payment $payment)
    {
        abort_unless($request->user()->can('paiement.annuler'), 403);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->payments->cancel($payment, $request->user(), $validated['reason']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Paiement annulé — les factures concernées redeviennent dues.');
    }
}
