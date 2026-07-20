<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu salaire — {{ $salaryPayment->user->name }}</title>
    <style>
        /* Ticket 80mm — voir SalaryReceiptController::pdf() pour la taille de page. */
        @page { margin: 0; }
        /* Ni `width` explicite ni `box-sizing` sur body : DomPDF ne respecte
           pas toujours box-sizing, ce qui faisait déborder le padding
           au-delà de la page. `width: auto` (par défaut) réserve le padding
           à l'intérieur de la page de façon fiable, sans dépendre du moteur. */
        html, body { margin: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
            padding: 8px 10px;
        }
        .center { text-align: center; }
        .school-logo { max-width: 60px; max-height: 60px; margin: 0 auto 4px; display: block; }
        .school-name { font-size: 13px; font-weight: bold; margin: 0 0 2px; }
        .school-meta { font-size: 9px; color: #333; margin: 0 0 6px; }
        .title { font-size: 11px; font-weight: bold; margin: 6px 0 2px; text-transform: uppercase; }
        .receipt-number { font-size: 10px; margin: 0 0 6px; }
        .divider { border: none; border-top: 1px dashed #000; margin: 6px 0; }
        table.info { width: 100%; table-layout: fixed; border-collapse: collapse; margin-bottom: 4px; }
        table.info td { padding: 1px 0; font-size: 9.5px; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
        table.info td.label { width: 38%; color: #333; }
        table.lines { width: 100%; table-layout: fixed; border-collapse: collapse; margin-top: 4px; }
        table.lines td { padding: 2px 4px 2px 0; font-size: 9.5px; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }
        table.lines td.label { width: 55%; text-align: left; padding-right: 6px; }
        table.lines td.amount { width: 45%; text-align: right; padding-right: 0; white-space: normal; }
        .total-row td { font-weight: bold; font-size: 11px; padding-top: 4px; }
        .partial-badge {
            display: inline-block; border: 1px solid #000; padding: 2px 8px;
            font-size: 9.5px; font-weight: bold; text-transform: uppercase; margin: 4px 0;
        }
        .qr-wrap { margin-top: 10px; text-align: center; }
        .qr-wrap img { width: 70px; height: 70px; }
        .footer-note { font-size: 7.5px; color: #555; text-align: center; margin-top: 8px; word-break: break-all; }
        .thanks { text-align: center; font-size: 10px; margin-top: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="center">
        @if($logoUri = \App\Support\SchoolLogoStorage::dataUri($salaryPayment->school))
            <img src="{{ $logoUri }}" alt="Logo" class="school-logo">
        @endif
        <p class="school-name">{{ $salaryPayment->school->name ?? 'Établissement' }}</p>
        @if($salaryPayment->school?->phone)
            <p class="school-meta">{{ $salaryPayment->school->phone }}</p>
        @endif
        <p class="title">Reçu de paiement — salaire</p>
        <p class="receipt-number">{{ $salaryPayment->period->locale('fr')->translatedFormat('F Y') }}</p>
        @if($salaryPayment->status === 'partial')
            <p class="partial-badge">Paiement partiel</p>
        @endif
    </div>

    <hr class="divider">

    <table class="info">
        <tr><td class="label">Employé</td><td>{{ $salaryPayment->user->name }}</td></tr>
        <tr><td class="label">Matricule</td><td>{{ $salaryPayment->user->identifier ?? '—' }}</td></tr>
        <tr><td class="label">Date</td><td>{{ $salaryPayment->paid_at?->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="label">Mode</td><td>{{ \App\Models\Payment::METHOD_LABELS[$salaryPayment->payment_method] ?? $salaryPayment->payment_method }}</td></tr>
        <tr><td class="label">Payé par</td><td>{{ $salaryPayment->paidBy->name ?? '—' }}</td></tr>
    </table>

    <hr class="divider">

    <table class="lines">
        <tr>
            <td class="label">Salaire dû — {{ $salaryPayment->period->locale('fr')->translatedFormat('F Y') }}</td>
            <td class="amount">{{ number_format($salaryPayment->amount_due, 0, ',', ' ') }}</td>
        </tr>
        <tr class="total-row">
            <td class="label">{{ $salaryPayment->status === 'partial' ? 'TOTAL PAYÉ À CE JOUR' : 'TOTAL PAYÉ' }}</td>
            <td class="amount">{{ number_format($salaryPayment->amount_paid, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if($salaryPayment->status === 'partial')
            <tr>
                <td class="label">Reste à payer</td>
                <td class="amount">{{ number_format($salaryPayment->balanceDue(), 0, ',', ' ') }} FCFA</td>
            </tr>
        @endif
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
