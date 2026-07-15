@extends('teacher.layouts.app')

@section('title', 'Mon salaire')

@section('content')
<div class="mb-4">
    <h1 class="h4 mb-0"><i class="fas fa-money-check-alt me-2"></i>Mon salaire</h1>
    <p class="text-muted mb-0">{{ $currentPeriod->locale('fr')->translatedFormat('F Y') }}</p>
</div>

<div class="card mb-4">
    <div class="card-body text-center py-4">
        @if(! $currentPayment)
            <i class="fas fa-hourglass-half fa-2x text-muted mb-2"></i>
            <h5 class="mb-0">Salaire pas encore généré pour ce mois</h5>
        @elseif($currentPayment->status === 'paid')
            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
            <h5 class="mb-1">Salaire payé</h5>
            <p class="text-muted mb-0">{{ number_format($currentPayment->amount_paid, 0, ',', ' ') }} FCFA — le {{ $currentPayment->paid_at?->format('d/m/Y') }}</p>
        @elseif($currentPayment->status === 'partial')
            <i class="fas fa-exclamation-circle fa-2x text-warning mb-2"></i>
            <h5 class="mb-1">Partiellement payé</h5>
            <p class="text-muted mb-0">{{ number_format($currentPayment->amount_paid, 0, ',', ' ') }} / {{ number_format($currentPayment->amount_due, 0, ',', ' ') }} FCFA</p>
        @elseif($currentPayment->status === 'cancelled')
            <i class="fas fa-times-circle fa-2x text-secondary mb-2"></i>
            <h5 class="mb-0">Paiement annulé</h5>
        @else
            <i class="fas fa-clock fa-2x text-danger mb-2"></i>
            <h5 class="mb-1">En attente de paiement</h5>
            <p class="text-muted mb-0">{{ number_format($currentPayment->amount_due, 0, ',', ' ') }} FCFA</p>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Historique</h5>
    </div>
    <div class="card-body p-0">
        @if($history->isEmpty())
            <p class="text-muted p-3 mb-0">Aucun historique de salaire pour le moment.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Période</th>
                            <th class="text-end">Montant</th>
                            <th>Statut</th>
                            <th>Payé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $entry)
                            <tr>
                                <td>{{ $entry->period->locale('fr')->translatedFormat('F Y') }}</td>
                                <td class="text-end">{{ number_format($entry->amount_due, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @php
                                        $badgeClass = match($entry->status) {
                                            'paid' => 'bg-success',
                                            'partial' => 'bg-warning text-dark',
                                            'cancelled' => 'bg-secondary',
                                            default => 'bg-danger',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $entry->statusLabel() }}</span>
                                </td>
                                <td>{{ $entry->paid_at?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
