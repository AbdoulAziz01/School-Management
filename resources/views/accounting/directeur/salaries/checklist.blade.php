@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@php
    $roleGroupLabels = [
        'teachers' => 'Enseignants',
        'surveillants' => 'Surveillants',
        'admin' => 'Personnel administratif',
        'accounting' => 'Comptables & Caissiers',
    ];
@endphp

@section('title', 'Suivi des paiements de salaire')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-clipboard-check me-2"></i>Suivi des paiements de salaire</h1>
        <p class="text-muted mb-0">
            {{ $period->locale('fr')->translatedFormat('F Y') }}
            @if($roleGroup && isset($roleGroupLabels[$roleGroup])) — {{ $roleGroupLabels[$roleGroup] }} @endif
            · vous pouvez payer directement ; chaque paiement enregistre qui l'a effectué, en toute transparence
        </p>
    </div>
    <form method="GET" action="{{ route('directeur.salaries.checklist') }}" class="d-flex gap-2">
        @if($roleGroup)
            <input type="hidden" name="role_group" value="{{ $roleGroup }}">
        @endif
        <input type="month" name="period" class="form-control form-control-sm" value="{{ $period->format('Y-m') }}" onchange="this.form.submit()">
    </form>
</div>

<div class="filter-pills mb-4">
    <a href="{{ route('directeur.salaries.checklist', ['period' => $period->format('Y-m')]) }}" class="filter-pill {{ ! $roleGroup ? 'is-active' : '' }}">Tous</a>
    @foreach($roleGroupLabels as $key => $label)
        <a href="{{ route('directeur.salaries.checklist', ['period' => $period->format('Y-m'), 'role_group' => $key]) }}" class="filter-pill {{ $roleGroup === $key ? 'is-active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if($payments->isEmpty())
    <div class="empty-state">
        <i class="fas fa-file-invoice-dollar"></i>
        <p class="mb-0">
            Aucun salaire généré pour {{ $period->locale('fr')->translatedFormat('F Y') }}
            (le comptable doit d'abord lancer la génération du mois).
        </p>
    </div>
@else
    @php
        $paidCount = $payments->where('status', 'paid')->count();
        $totalDue = $payments->sum('amount_due');
        $totalPaid = $payments->sum('amount_paid');
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-tile kpi-tile-static">
                <div class="kpi-icon kpi-icon-green"><i class="fas fa-circle-check"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">Payés</div>
                    <div class="kpi-value">{{ $paidCount }} <span class="kpi-value-suffix">/ {{ $payments->count() }}</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-tile kpi-tile-static">
                <div class="kpi-icon kpi-icon-amber"><i class="fas fa-scale-balanced"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">Montant dû (total)</div>
                    <div class="kpi-value">{{ number_format($totalDue, 0, ',', ' ') }} <span class="kpi-value-suffix">FCFA</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-tile kpi-tile-static">
                <div class="kpi-icon kpi-icon-green"><i class="fas fa-sack-dollar"></i></div>
                <div class="kpi-body">
                    <div class="kpi-label">Déjà versé</div>
                    <div class="kpi-value text-success">{{ number_format($totalPaid, 0, ',', ' ') }} <span class="kpi-value-suffix">FCFA</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Rôle</th>
                            <th class="text-end">Montant dû</th>
                            <th class="text-end">Payé</th>
                            <th>Statut</th>
                            <th>Payé par</th>
                            <th style="min-width:160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $roleLabels = ['admin' => 'Admin', 'surveillant' => 'Surveillant', 'teacher' => 'Enseignant', 'professeur' => 'Enseignant', 'comptable' => 'Comptable', 'caissier' => 'Caissier'];
                                $badgeClass = match($payment->status) {
                                    'paid' => 'status-badge status-badge-success',
                                    'partial' => 'status-badge status-badge-warning',
                                    'cancelled' => 'status-badge status-badge-danger',
                                    default => 'status-badge status-badge-neutral',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($payment->user->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $payment->user->name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $roleLabels[$payment->user->role] ?? $payment->user->role }}</td>
                                <td class="text-end">{{ number_format($payment->amount_due, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end">{{ number_format($payment->amount_paid, 0, ',', ' ') }} FCFA</td>
                                <td><span class="{{ $badgeClass }}">{{ $payment->statusLabel() }}</span></td>
                                <td class="small text-muted">{{ $payment->paidBy?->name ?? '—' }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if(!in_array($payment->status, ['paid', 'cancelled']))
                                            <a href="#pay-drawer-{{ $payment->id }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-hand-holding-usd"></i> Payer
                                            </a>
                                        @endif
                                        @if(in_array($payment->status, ['paid', 'partial']))
                                            <a href="{{ route('directeur.salary-receipts.show', $payment) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-receipt"></i> Reçu
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Panneaux glissants (pur CSS, aucun JS — pas de modals Bootstrap empilés) --}}
    @foreach($payments as $payment)
        @if(!in_array($payment->status, ['paid', 'cancelled']))
            <div class="drawer-backdrop" id="pay-drawer-{{ $payment->id }}">
                <a href="#" class="drawer-backdrop-close" aria-label="Fermer"></a>
                <div class="drawer-panel">
                    <form method="POST" action="{{ route('directeur.salaries.pay', $payment) }}">
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
                            <p class="small text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Ce paiement sera enregistré à votre nom ({{ auth()->user()->name }}).
                            </p>
                        </div>
                        <div class="drawer-footer">
                            <a href="#" class="btn btn-secondary">Fermer</a>
                            <button type="submit" class="btn btn-primary">Confirmer le paiement</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
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
