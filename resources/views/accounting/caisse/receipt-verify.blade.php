<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification du reçu — {{ config('platform.name', 'AzelieEdu') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); min-height: 100vh; }
        .verify-card { max-width: 640px; margin: 2rem auto; }
        .badge-authentic { background: linear-gradient(135deg, #16a34a, #15803d); font-size: 1rem; padding: .6rem 1.2rem; }
        .header-bar { background: linear-gradient(135deg, #1c1917, #292524); color: #fbbf24; padding: 1.5rem; border-radius: 12px 12px 0 0; }
    </style>
</head>
<body>
<div class="verify-card">
    <div class="card shadow-lg border-0">
        <div class="header-bar text-center">
            <h4 class="mb-1"><i class="fas fa-shield-alt me-2"></i>Vérification d'authenticité</h4>
            <p class="mb-0 opacity-75" style="font-size:.85rem">{{ config('platform.name', 'AzelieEdu') }} — Système de Gestion Scolaire</p>
        </div>
        <div class="card-body p-4">
            <div class="text-center mb-4">
                @if($payment->isCancelled())
                    <span class="badge bg-danger rounded-pill" style="font-size:1rem;padding:.6rem 1.2rem;">
                        <i class="fas fa-times-circle me-2"></i>Ce paiement a été annulé
                    </span>
                @else
                    <span class="badge badge-authentic text-white rounded-pill">
                        <i class="fas fa-check-circle me-2"></i>Reçu authentique et vérifié
                    </span>
                @endif
            </div>

            <table class="table table-bordered table-sm mb-0">
                <tr><th style="width:40%">Numéro de reçu</th><td>{{ $payment->receipt_number }}</td></tr>
                <tr><th>Élève</th><td>{{ $payment->student->name }}</td></tr>
                <tr><th>Date</th><td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td></tr>
                <tr><th>Montant</th><td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td></tr>
                <tr><th>Mode de paiement</th><td>{{ $payment->methodLabel() }}</td></tr>
            </table>
        </div>
    </div>
</div>
</body>
</html>
