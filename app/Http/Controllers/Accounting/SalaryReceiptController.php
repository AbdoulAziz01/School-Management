<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SalaryPayment;
use App\Support\ThermalTicketPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Reçu de paiement de salaire — même pattern que PaymentReceiptController
 * (reçu élève) : URL signée + QR code de vérification publique, PDF au
 * format ticket 80mm pour impression caisse.
 */
class SalaryReceiptController extends Controller
{
    /** Écran affiché juste après le paiement (comptable/caissier ou directeur). */
    public function show(SalaryPayment $salaryPayment)
    {
        abort_unless($salaryPayment->status !== 'pending', 404);

        $salaryPayment->load(['user', 'paidBy']);

        return view('accounting.shared.receipts.salary-receipt', [
            'salaryPayment' => $salaryPayment,
            'verifyUrl' => $this->verifyUrl($salaryPayment),
        ]);
    }

    public function pdf(SalaryPayment $salaryPayment)
    {
        $salaryPayment->load(['user', 'paidBy', 'school']);

        $verifyUrl = $this->verifyUrl($salaryPayment);
        $qrCode = base64_encode(
            QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
        );

        $html = view('accounting.shared.receipts.salary-receipt-pdf', [
            'salaryPayment' => $salaryPayment,
            'verifyUrl' => $verifyUrl,
            'qrCode' => $qrCode,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->render();

        // Ticket 80mm (imprimante thermique de caisse), hauteur dynamique —
        // voir ThermalTicketPdf.
        $pdf = ThermalTicketPdf::render($html);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="recu-salaire-'.$salaryPayment->id.'.pdf"',
        ]);
    }

    public function verify(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Ce lien de vérification est invalide ou a expiré.');
        }

        $salaryPayment = SalaryPayment::withoutGlobalScopes()
            ->with(['user', 'paidBy'])
            ->findOrFail($request->integer('salary_payment_id'));

        return view('accounting.shared.receipts.salary-receipt-verify', ['salaryPayment' => $salaryPayment]);
    }

    private function verifyUrl(SalaryPayment $salaryPayment): string
    {
        return URL::signedRoute('salary.receipt.verify', ['salary_payment_id' => $salaryPayment->id]);
    }
}
