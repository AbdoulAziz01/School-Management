<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Reçu de paiement — même pattern que les bulletins (URL signée + QR code
 * de vérification publique), voir Admin\ReportController::semesterBulletinsPdf().
 */
class PaymentReceiptController extends Controller
{
    public function show(Payment $payment)
    {
        $payment->load(['student', 'recordedBy', 'allocations.studentInvoice']);

        return view('accounting.caisse.receipt', [
            'payment' => $payment,
            'verifyUrl' => $this->verifyUrl($payment),
        ]);
    }

    public function pdf(Payment $payment)
    {
        $payment->load(['student', 'recordedBy', 'allocations.studentInvoice']);

        $verifyUrl = $this->verifyUrl($payment);
        // SVG plutôt que PNG : le rendu PNG de simple-qrcode nécessite
        // l'extension Imagick, absente de certains environnements — le
        // format SVG ne dépend que de GD/aucune lib d'image système.
        $qrCode = base64_encode(
            QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
        );

        $pdf = Pdf::loadView('accounting.caisse.receipt-pdf', [
            'payment' => $payment,
            'verifyUrl' => $verifyUrl,
            'qrCode' => $qrCode,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->stream("recu-{$payment->receipt_number}.pdf");
    }

    public function verify(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Ce lien de vérification est invalide ou a expiré.');
        }

        $payment = Payment::withoutGlobalScopes()
            ->with(['student', 'allocations.studentInvoice'])
            ->findOrFail($request->integer('payment_id'));

        return view('accounting.caisse.receipt-verify', ['payment' => $payment]);
    }

    private function verifyUrl(Payment $payment): string
    {
        return URL::signedRoute('payment.receipt.verify', ['payment_id' => $payment->id]);
    }
}
