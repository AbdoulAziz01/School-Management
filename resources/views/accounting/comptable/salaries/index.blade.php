@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@php
    $roleGroupLabels = ['teachers' => 'Enseignants', 'surveillants' => 'Surveillants', 'admin' => 'Administration'];
@endphp
@section('title', 'Salaires')

@section('content')
<a href="{{ route('comptable.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-money-check-alt me-2"></i>Salaires @if($roleGroup && isset($roleGroupLabels[$roleGroup])) — {{ $roleGroupLabels[$roleGroup] }} @endif</h1>
        <p class="text-muted mb-0">{{ $period->locale('fr')->translatedFormat('F Y') }}</p>
    </div>
    <form method="GET" action="{{ route('comptable.salaries.index') }}" class="d-flex gap-2">
        @if($roleGroup)
            <input type="hidden" name="role_group" value="{{ $roleGroup }}">
        @endif
        <input type="month" name="period" class="form-control form-control-sm" value="{{ $period->format('Y-m') }}" onchange="this.form.submit()">
    </form>
</div>

@if($payments->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-3">Aucun paiement de salaire généré pour {{ $period->locale('fr')->translatedFormat('F Y') }}.</p>
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
                                        <a href="#pay-drawer-{{ $payment->id }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-hand-holding-usd"></i> Payer
                                        </a>
                                    @endif
                                    @if(in_array($payment->status, ['paid', 'partial']))
                                        <a href="{{ route('comptable.salary-receipts.show', $payment) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-receipt"></i> Reçu
                                        </a>
                                    @endif
                                    @if($payment->status !== 'cancelled')
                                        <a href="#cancel-drawer-{{ $payment->id }}" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Panneaux glissants (pur CSS, aucun JS — évite tout bug de focus des modals Bootstrap empilés) --}}
    @foreach($payments as $payment)
        @if(!in_array($payment->status, ['paid','cancelled']))
            <div class="drawer-backdrop" id="pay-drawer-{{ $payment->id }}">
                <a href="#" class="drawer-backdrop-close" aria-label="Fermer"></a>
                <div class="drawer-panel">
                    <form method="POST" action="{{ route('comptable.salaries.pay', $payment) }}">
                        @csrf
                        <div class="drawer-header">
                            <h5 class="mb-0">Payer {{ $payment->user->name }}</h5>
                            <a href="#" class="btn-close" aria-label="Fermer"></a>
                        </div>
                        <div class="drawer-body">
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
                        <div class="drawer-footer">
                            <a href="#" class="btn btn-secondary">Fermer</a>
                            <button type="submit" class="btn btn-primary">Confirmer le paiement</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        @if($payment->status !== 'cancelled')
            <div class="drawer-backdrop" id="cancel-drawer-{{ $payment->id }}">
                <a href="#" class="drawer-backdrop-close" aria-label="Fermer"></a>
                <div class="drawer-panel">
                    <form method="POST" action="{{ route('comptable.salaries.cancel', $payment) }}">
                        @csrf
                        <div class="drawer-header">
                            <h5 class="mb-0">Annuler ce paiement ?</h5>
                            <a href="#" class="btn-close" aria-label="Fermer"></a>
                        </div>
                        <div class="drawer-body">
                            <label class="form-label">Motif</label>
                            <textarea name="reason" class="form-control" required></textarea>
                        </div>
                        <div class="drawer-footer">
                            <a href="#" class="btn btn-secondary">Fermer</a>
                            <button type="submit" class="btn btn-danger">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endif

@push('styles')
<style>
.drawer-backdrop {
    position: fixed; inset: 0; background: rgba(0,0,0,.5);
    z-index: 1055; visibility: hidden; opacity: 0;
    transition: opacity .2s ease, visibility .2s ease;
}
.drawer-backdrop:target { visibility: visible; opacity: 1; }
.drawer-backdrop-close { position: absolute; inset: 0; display: block; }
.drawer-panel {
    position: fixed; top: 0; right: 0; height: 100vh; width: 420px; max-width: 92vw;
    background: #fff; box-shadow: -4px 0 24px rgba(0,0,0,.15);
    display: flex; flex-direction: column;
    transform: translateX(100%); transition: transform .25s ease;
}
.drawer-backdrop:target .drawer-panel { transform: translateX(0); }
.drawer-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1.25rem; border-bottom: 1px solid #fde68a;
}
.drawer-body { padding: 1.25rem; overflow-y: auto; flex: 1; }
.drawer-footer { padding: 1.25rem; border-top: 1px solid #fde68a; display: flex; gap: .5rem; justify-content: flex-end; }
</style>
@endpush
@endsection
