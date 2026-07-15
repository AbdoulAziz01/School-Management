<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu {{ $payment->receipt_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; margin: 0; padding: 20px; }
        .header { background: #92400e; color: #fff; padding: 14px; text-align: center; margin-bottom: 14px; }
        .header h1 { margin: 0; font-size: 16px; }
        .header p { margin: 4px 0 0; font-size: 10px; opacity: 0.9; }
        .info { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .info td { padding: 4px 6px; vertical-align: top; }
        .info .label { font-weight: bold; color: #92400e; width: 30%; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.lines th { background: #fef3c7; color: #92400e; padding: 6px; border: 1px solid #fde68a; text-align: left; }
        table.lines td { border: 1px solid #eee; padding: 6px; }
        table.lines tr.total td { background: #fef3c7; font-weight: bold; }
        .qr-block { position: absolute; right: 20px; top: 20px; text-align: center; }
        .qr-block img { width: 80px; height: 80px; }
        .qr-block p { font-size: 7px; color: #888; margin: 2px 0 0; width: 90px; }
        .footer-note { font-size: 8px; color: #666; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="qr-block">
        <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR">
        <p>Scanner pour vérifier l'authenticité de ce reçu</p>
    </div>

    <div class="header">
        <h1>Reçu de paiement</h1>
        <p>{{ $payment->receipt_number }}</p>
    </div>

    <table class="info">
        <tr><td class="label">Élève</td><td>{{ $payment->student->name }} ({{ $payment->student->identifier ?? '—' }})</td></tr>
        <tr><td class="label">Date</td><td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="label">Mode de paiement</td><td>{{ $payment->methodLabel() }}</td></tr>
        <tr><td class="label">Encaissé par</td><td>{{ $payment->recordedBy->name }}</td></tr>
    </table>

    <table class="lines">
        <thead>
            <tr><th>Motif</th><th>Montant</th></tr>
        </thead>
        <tbody>
            @foreach($payment->allocations as $allocation)
                <tr>
                    <td>{{ $allocation->studentInvoice->label }}</td>
                    <td>{{ number_format($allocation->amount, 0, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>Total</td>
                <td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    <p class="footer-note">
        Document généré le {{ $generatedAt }} — Vérification : {{ $verifyUrl }}
    </p>
</body>
</html>
