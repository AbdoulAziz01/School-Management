<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\ThermalTicketPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Reçu de paiement — même pattern que les bulletins (URL signée + QR code
 * de vérification publique), voir Admin\ReportController::semesterBulletinsPdf().
 */
class PaymentReceiptController extends Controller
{
    /** Recherche un reçu par numéro ou par élève — même écran pour caissier et comptable. */
    public function search(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $payments = collect();

        if ($search !== '') {
            $payments = Payment::with('student')
                ->where(function ($q) use ($search) {
                    $q->where('receipt_number', 'ilike', "%{$search}%")
                        ->orWhereHas('student', fn ($sq) => $sq->where('name', 'ilike', "%{$search}%"));
                })
                ->orderByDesc('paid_at')
                ->limit(30)
                ->get();
        }

        return view('accounting.shared.receipts.search', [
            'payments' => $payments,
            'search' => $search,
        ]);
    }

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
        $payment->load(['student', 'recordedBy', 'allocations.studentInvoice', 'school']);

        $verifyUrl = $this->verifyUrl($payment);
        // SVG plutôt que PNG : le rendu PNG de simple-qrcode nécessite
        // l'extension Imagick, absente de certains environnements — le
        // format SVG ne dépend que de GD/aucune lib d'image système.
        $qrCode = base64_encode(
            QrCode::format('svg')->size(120)->errorCorrection('H')->generate($verifyUrl)
        );

        $html = view('accounting.caisse.receipt-pdf', [
            'payment' => $payment,
            'verifyUrl' => $verifyUrl,
            'qrCode' => $qrCode,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->render();

        // Ticket 80mm (imprimante thermique de caisse) : largeur fixe,
        // hauteur calculée dynamiquement pour épouser le contenu réel — voir
        // ThermalTicketPdf.
        $pdf = ThermalTicketPdf::render($html);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="recu-'.$payment->receipt_number.'.pdf"',
        ]);
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
