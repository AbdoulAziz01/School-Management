<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $payment->receipt_number }}</title>
    <style>
        /* Ticket 80mm — voir PaymentReceiptController::pdf() pour la taille
           de page (largeur fixe, hauteur généreuse pour éviter la pagination). */
        @page { margin: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 8px 10px;
            width: 100%;
        }
        .center { text-align: center; }
        .school-name { font-size: 13px; font-weight: bold; margin: 0 0 2px; }
        .school-meta { font-size: 9px; color: #333; margin: 0 0 6px; }
        .title { font-size: 11px; font-weight: bold; margin: 6px 0 2px; text-transform: uppercase; }
        .receipt-number { font-size: 10px; margin: 0 0 6px; }
        .divider { border: none; border-top: 1px dashed #000; margin: 6px 0; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.info td { padding: 1px 0; font-size: 9.5px; vertical-align: top; }
        table.info td.label { width: 42%; color: #333; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.lines td { padding: 2px 0; font-size: 9.5px; vertical-align: top; }
        table.lines td.amount { text-align: right; white-space: nowrap; }
        .total-row td { font-weight: bold; font-size: 11px; padding-top: 4px; }
        .qr-wrap { margin-top: 10px; text-align: center; }
        .qr-wrap img { width: 70px; height: 70px; }
        .footer-note { font-size: 7.5px; color: #555; text-align: center; margin-top: 8px; word-break: break-all; }
        .thanks { text-align: center; font-size: 10px; margin-top: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="center">
        <p class="school-name">{{ $payment->school->name ?? 'Établissement' }}</p>
        @if($payment->school?->phone)
            <p class="school-meta">{{ $payment->school->phone }}</p>
        @endif
        <p class="title">Reçu de paiement</p>
        <p class="receipt-number">{{ $payment->receipt_number }}</p>
    </div>

    <hr class="divider">

    <table class="info">
        <tr><td class="label">Élève</td><td>{{ $payment->student->name }}</td></tr>
        <tr><td class="label">Matricule</td><td>{{ $payment->student->identifier ?? '—' }}</td></tr>
        <tr><td class="label">Date</td><td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="label">Mode</td><td>{{ $payment->methodLabel() }}</td></tr>
        <tr><td class="label">Encaissé par</td><td>{{ $payment->recordedBy->name }}</td></tr>
    </table>

    <hr class="divider">

    <table class="lines">
        @foreach($payment->allocations as $allocation)
            <tr>
                <td>{{ $allocation->studentInvoice->label }}</td>
                <td class="amount">{{ number_format($allocation->amount, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        <tr class="total-row">
            <td>TOTAL</td>
            <td class="amount">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <hr class="divider">

    <p class="thanks">Merci !</p>

    <div class="qr-wrap">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR">
    </div>

    <p class="footer-note">
        Vérification d'authenticité par QR code — généré le {{ $generatedAt }}
    </p>
</body>
</html>
