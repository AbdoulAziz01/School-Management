@extends('layouts.student')

@section('title', 'Mes paiements')

@section('content')
<div class="mb-4">
    <h1 class="h4 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Mes paiements</h1>
    <p class="text-muted mb-0">Frais scolaires — ce que je dois et ce que j'ai déjà payé</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Montant restant dû</div>
                <div class="h3 mb-0 {{ $totalDue > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($totalDue, 0, ',', ' ') }} <span class="fs-6 text-muted">FCFA</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-muted mb-1">Statut</div>
                <div class="h5 mb-0">
                    @if($totalDue > 0)
                        <span class="badge bg-danger">Solde à régler</span>
                    @else
                        <span class="badge bg-success">À jour</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Mes factures</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Motif</th>
                        <th>Échéance</th>
                        <th class="text-end">Dû</th>
                        <th class="text-end">Payé</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->label }}</td>
                            <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($invoice->amount_due, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($invoice->amount_paid, 0, ',', ' ') }}</td>
                            <td>
                                @php
                                    $badgeClass = match($invoice->status) {
                                        'paid' => 'bg-success',
                                        'partial' => 'bg-warning text-dark',
                                        'cancelled' => 'bg-secondary',
                                        default => 'bg-danger',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $invoice->statusLabel() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-3">Aucune facture pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Historique de mes paiements</h5>
    </div>
    <div class="card-body p-0">
        @if($paymentHistory->isEmpty())
            <p class="text-muted p-3 mb-0">Aucun paiement enregistré pour le moment.</p>
        @else
            <ul class="list-group list-group-flush">
                @foreach($paymentHistory as $payment)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small"><code>{{ $payment->receipt_number }}</code></div>
                            <div class="text-muted" style="font-size:.75rem">{{ $payment->paid_at->format('d/m/Y H:i') }} — {{ $payment->methodLabel() }}</div>
                        </div>
                        <span class="{{ $payment->isCancelled() ? 'text-muted text-decoration-line-through' : 'fw-semibold' }}">
                            {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
