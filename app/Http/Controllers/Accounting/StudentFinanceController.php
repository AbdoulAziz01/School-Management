<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\School;
use App\Models\StudentInvoice;
use App\Models\User;
use App\Services\AccountingDashboardService;
use Illuminate\Http\Request;

/**
 * Consultation de la situation financière des élèves (comptable/directeur,
 * lecture seule — l'encaissement reste réservé au caissier). Même
 * contrôleur pour les deux portails, comme LedgerController.
 */
class StudentFinanceController extends Controller
{
    public function __construct(
        private AccountingDashboardService $dashboard
    ) {}

    public function debtors(Request $request)
    {
        $school = School::find($request->user()->school_id);

        return view('accounting.shared.students.debtors', [
            'debtors' => $this->dashboard->debtorsList($school),
        ]);
    }

    public function show(User $student)
    {
        abort_unless($student->isStudent(), 404, 'Élève introuvable.');

        $student->load(['schoolClass.level']);

        $invoices = StudentInvoice::where('student_id', $student->id)
            ->orderByDesc('due_date')
            ->get();

        $paymentHistory = Payment::where('student_id', $student->id)
            ->orderByDesc('paid_at')
            ->get();

        return view('accounting.shared.students.show', [
            'student' => $student,
            'invoices' => $invoices,
            'paymentHistory' => $paymentHistory,
        ]);
    }
}
