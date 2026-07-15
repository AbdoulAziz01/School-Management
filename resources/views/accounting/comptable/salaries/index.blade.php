@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@section('title', 'Salaires')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-money-check-alt me-2"></i>Salaires</h1>
        <p class="text-muted mb-0">{{ $period->translatedFormat('F Y') }}</p>
    </div>
    <form method="GET" action="{{ route('comptable.salaries.index') }}" class="d-flex gap-2">
        <input type="month" name="period" class="form-control form-control-sm" value="{{ $period->format('Y-m') }}" onchange="this.form.submit()">
    </form>
</div>

@if($payments->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-3">Aucun paiement de salaire généré pour {{ $period->translatedFormat('F Y') }}.</p>
            <form method="POST" action="{{ route('comptable.salaries.generate') }}">
                @csrf
                <input type="hidden" name="period" value="{{ $period->format('Y-m') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync me-1"></i> Générer les salaires du mois
                </button>
            </form>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Employé</th>
                            <th>Montant dû</th>
                            <th>Payé</th>
                            <th>Statut</th>
                            <th style="min-width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->user->name }}</td>
                                <td>{{ number_format($payment->amount_due, 0, ',', ' ') }} FCFA</td>
                                <td>{{ number_format($payment->amount_paid, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @php
                                        $badgeClass = match($payment->status) {
                                            'paid' => 'bg-success',
                                            'partial' => 'bg-warning text-dark',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $payment->statusLabel() }}</span>
                                </td>
                                <td>
                                    @if(!in_array($payment->status, ['paid','cancelled']))
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#pay{{ $payment->id }}">
                                            <i class="fas fa-hand-holding-usd"></i> Payer
                                        </button>
                                        <div class="modal fade" id="pay{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('comptable.salaries.pay', $payment) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Payer {{ $payment->user->name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Montant (solde dû : {{ number_format($payment->balanceDue(), 0, ',', ' ') }} FCFA)</label>
                                                                <input type="number" name="amount" class="form-control" value="{{ $payment->balanceDue() }}" step="1" min="0" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Mode de paiement</label>
                                                                <select name="payment_method" class="form-select" required>
                                                                    @foreach(\App\Models\Payment::METHOD_LABELS as $value => $label)
                                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            <button type="submit" class="btn btn-primary">Confirmer le paiement</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($payment->status !== 'cancelled')
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancel{{ $payment->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="modal fade" id="cancel{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('comptable.salaries.cancel', $payment) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Annuler ce paiement ?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Motif</label>
                                                            <textarea name="reason" class="form-control" required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            <button type="submit" class="btn btn-danger">Annuler</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
