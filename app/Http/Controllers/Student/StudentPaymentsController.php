<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StudentInvoice;
use Illuminate\Support\Facades\Auth;

/**
 * L'élève voit sa propre situation financière (factures dues/payées,
 * historique des paiements) — lecture seule, module Comptabilité
 * (établissements privés uniquement, voir la garde de route module:accounting).
 */
class StudentPaymentsController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        $invoices = StudentInvoice::where('student_id', $student->id)
            ->orderByDesc('due_date')
            ->get();

        $paymentHistory = Payment::where('student_id', $student->id)
            ->orderByDesc('paid_at')
            ->get();

        $totalDue = round((float) $invoices->sum(fn (StudentInvoice $invoice) => $invoice->balanceDue()), 2);

        return view('student.payments', [
            'invoices' => $invoices,
            'paymentHistory' => $paymentHistory,
            'totalDue' => $totalDue,
        ]);
    }
}
