<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CashSession;
use App\Models\Payment;
use App\Models\StudentInvoice;
use App\Models\User;
use App\Services\CashSessionService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Guichet caissier : ouvrir la caisse → chercher un élève → encaisser →
 * reçu, avec une navigation complète (sidebar) depuis la Phase 7.1.
 */
class CaisseController extends Controller
{
    public function __construct(
        private CashSessionService $sessions,
        private PaymentService $payments
    ) {}

    public function index(Request $request)
    {
        $cashier = $request->user();
        $session = $this->sessions->currentOpenSession($cashier);

        if (! $session) {
            return view('accounting.caisse.open-session');
        }

        return view('accounting.caisse.guichet', [
            'session' => $session,
            'summary' => $this->sessions->liveSummary($session),
        ]);
    }

    public function openSession(Request $request)
    {
        abort_unless($request->user()->can('caisse.ouvrir'), 403);

        $validated = $request->validate([
            'opening_balance' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $this->sessions->open($request->user(), (float) $validated['opening_balance']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('caisse.dashboard')->with('success', 'Caisse ouverte.');
    }

    public function closeSessionForm(Request $request)
    {
        $session = $this->sessions->currentOpenSession($request->user());

        if (! $session) {
            return redirect()->route('caisse.dashboard');
        }

        return view('accounting.caisse.close-session', [
            'session' => $session,
            'summary' => $this->sessions->liveSummary($session),
        ]);
    }

    public function closeSession(Request $request)
    {
        abort_unless($request->user()->can('caisse.cloturer'), 403);

        $session = $this->sessions->currentOpenSession($request->user());

        if (! $session) {
            return redirect()->route('caisse.dashboard');
        }

        $validated = $request->validate([
            'actual_closing_balance' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->sessions->close(
            $session,
            (float) $validated['actual_closing_balance'],
            $request->user(),
            $validated['notes'] ?? null
        );

        return redirect()->route('caisse.dashboard')->with('success', 'Caisse clôturée.');
    }

    public function search(Request $request)
    {
        $cashier = $request->user();
        $session = $this->sessions->currentOpenSession($cashier);

        if (! $session) {
            return redirect()->route('caisse.dashboard');
        }

        $students = collect();
        $search = $request->query('q', '');

        if (trim($search) !== '') {
            $students = User::whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('identifier', 'like', "%{$search}%");
                })
                ->with('schoolClass')
                ->orderBy('name')
                ->limit(30)
                ->get();
        }

        return view('accounting.caisse.search', [
            'students' => $students,
            'search' => $search,
        ]);
    }

    public function showStudent(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404, 'Élève introuvable.');

        $session = $this->sessions->currentOpenSession($request->user());

        if (! $session) {
            return redirect()->route('caisse.dashboard');
        }

        $invoices = $this->payments->outstandingInvoices($student);

        return view('accounting.caisse.pay', [
            'student' => $student,
            'invoices' => $invoices,
        ]);
    }

    public function pay(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404, 'Élève introuvable.');
        abort_unless($request->user()->can('caisse.encaisser'), 403);

        $session = $this->sessions->currentOpenSession($request->user());

        if (! $session) {
            return redirect()->route('caisse.dashboard')->with('error', 'Ouvrez la caisse avant d\'encaisser.');
        }

        $validated = $request->validate([
            'amounts' => ['required', 'array'],
            'amounts.*' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(array_keys(Payment::METHOD_LABELS))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payment = $this->payments->recordPayment(
                $student,
                array_map('floatval', $validated['amounts']),
                $validated['payment_method'],
                $request->user(),
                $session,
                $validated['notes'] ?? null
            );
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('caisse.receipts.show', $payment)->with('success', 'Paiement enregistré.');
    }

    public function history(Request $request)
    {
        $cashier = $request->user();

        $query = Payment::where('recorded_by', $cashier->id)
            ->with('student')
            ->orderByDesc('paid_at');

        if ($request->query('scope') === 'today') {
            $query->whereDate('paid_at', now()->toDateString());
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('accounting.caisse.history', [
            'payments' => $payments,
            'scope' => $request->query('scope'),
        ]);
    }

    /** Historique des sessions de caisse (ouvertures/clôtures) du caissier connecté. */
    public function sessionHistory(Request $request)
    {
        $register = $this->sessions->registerFor($request->user());

        $sessions = CashSession::where('cash_register_id', $register->id)
            ->orderByDesc('opened_at')
            ->paginate(20);

        return view('accounting.caisse.session-history', ['sessions' => $sessions]);
    }

    /** Situation financière d'un élève (lecture seule — pas besoin de caisse ouverte). */
    public function studentSituation(User $student)
    {
        abort_unless($student->isStudent(), 404, 'Élève introuvable.');

        $invoices = StudentInvoice::where('student_id', $student->id)
            ->orderByDesc('due_date')
            ->get();

        $paymentHistory = Payment::where('student_id', $student->id)
            ->orderByDesc('paid_at')
            ->get();

        return view('accounting.caisse.student-situation', [
            'student' => $student,
            'invoices' => $invoices,
            'paymentHistory' => $paymentHistory,
        ]);
    }
}
