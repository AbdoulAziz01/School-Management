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

<div class="mb-3 d-flex flex-wrap gap-2">
    <a href="{{ route('directeur.salaries.checklist', ['period' => $period->format('Y-m')]) }}" class="btn btn-sm {{ ! $roleGroup ? 'btn-primary' : 'btn-outline-secondary' }}">Tous</a>
    @foreach($roleGroupLabels as $key => $label)
        <a href="{{ route('directeur.salaries.checklist', ['period' => $period->format('Y-m'), 'role_group' => $key]) }}" class="btn btn-sm {{ $roleGroup === $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
    @endforeach
</div>

@if($payments->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <p class="text-muted mb-0">
                Aucun salaire généré pour {{ $period->locale('fr')->translatedFormat('F Y') }}
                (le comptable doit d'abord lancer la génération du mois).
            </p>
        </div>
    </div>
@else
    @php
        $paidCount = $payments->where('status', 'paid')->count();
        $totalDue = $payments->sum('amount_due');
        $totalPaid = $payments->sum('amount_paid');
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Payés</div>
                <div class="h4 mb-0">{{ $paidCount }} / {{ $payments->count() }}</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Montant dû (total)</div>
                <div class="h4 mb-0">{{ number_format($totalDue, 0, ',', ' ') }} FCFA</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Déjà versé</div>
                <div class="h4 mb-0 text-success">{{ number_format($totalPaid, 0, ',', ' ') }} FCFA</div>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>Employé</th>
                            <th>Rôle</th>
                            <th class="text-end">Montant dû</th>
                            <th class="text-end">Payé</th>
                            <th>Statut</th>
                            <th>Payé par</th>
                            <th style="min-width:140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $roleLabels = ['admin' => 'Admin', 'surveillant' => 'Surveillant', 'teacher' => 'Enseignant', 'professeur' => 'Enseignant', 'comptable' => 'Comptable', 'caissier' => 'Caissier'];
                                $badgeClass = match($payment->status) {
                                    'paid' => 'bg-success',
                                    'partial' => 'bg-warning text-dark',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    @if($payment->status === 'paid')
                                        <i class="fas fa-check-circle text-success"></i>
                                    @elseif($payment->status === 'cancelled')
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @else
                                        <i class="far fa-circle text-muted"></i>
                                    @endif
                                </td>
                                <td>{{ $payment->user->name }}</td>
                                <td>{{ $roleLabels[$payment->user->role] ?? $payment->user->role }}</td>
                                <td class="text-end">{{ number_format($payment->amount_due, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end">{{ number_format($payment->amount_paid, 0, ',', ' ') }} FCFA</td>
                                <td><span class="badge {{ $badgeClass }}">{{ $payment->statusLabel() }}</span></td>
                                <td class="small text-muted">{{ $payment->paidBy?->name ?? '—' }}</td>
                                <td>
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
