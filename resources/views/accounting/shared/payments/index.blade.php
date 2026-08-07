@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_DIRECTEUR ? 'directeur' : 'comptable';
    $canCancel = auth()->user()->can('paiement.annuler');
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Paiements élèves')

@section('content')
<a href="{{ route($portalPrefix . '.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i>Paiements élèves</h1>
    <p class="text-muted mb-0">Toutes les recettes{{ $canCancel ? ' — correction et annulation tracées' : '' }}</p>
</div>

<form method="GET" action="{{ route($portalPrefix.'.payments.index') }}" class="search-bar mb-4">
    <div class="search-field" style="flex: 1 1 260px;">
        <label for="payments-search" class="visually-hidden">Rechercher</label>
        <i class="fas fa-search"></i>
        <input type="text" id="payments-search" name="search" placeholder="N° reçu ou nom élève" value="{{ request('search') }}">
    </div>
    <label for="payments-status" class="visually-hidden">Statut</label>
    <select id="payments-status" name="status" class="search-select">
        <option value="">Tous les statuts</option>
        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complété</option>
        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
    </select>
    <button type="submit" class="btn-pill-primary"><i class="fas fa-filter"></i>Filtrer</button>
    <a href="{{ route($portalPrefix.'.payments.index') }}" class="btn-pill-outline">Réinitialiser</a>
</form>

@if($payments->isEmpty())
    <div class="empty-state"><i class="fas fa-receipt"></i><p class="mb-0">Aucun paiement trouvé.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Reçu</th>
                            <th>Élève</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Encaissé par</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th style="min-width: 90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr class="{{ $payment->isCancelled() ? 'table-secondary' : '' }}">
                                <td><code>{{ $payment->receipt_number }}</code></td>
                                <td>
                                    <div class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($payment->student->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $payment->student->name }}</span>
                                    </div>
                                </td>
                                <td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                                <td class="text-muted">
                                    {{ $payment->methodLabel() }}
                                    @if($payment->payment_reference)
                                        <div style="font-size:.75rem">{{ $payment->referenceLabel() }} : {{ $payment->payment_reference }}@if($payment->payment_bank) — {{ $payment->payment_bank }}@endif</div>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $payment->recordedBy->name }}</td>
                                <td class="text-muted">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($payment->isCancelled())
                                        <span class="status-badge status-badge-danger" title="{{ $payment->cancellation_reason }}">Annulé</span>
                                    @else
                                        <span class="status-badge status-badge-success">Complété</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$payment->isCancelled() && $canCancel)
                                        <button type="button" class="btn-view" style="color:#dc2626;border-color:#fecaca;background:#fef2f2;" data-bs-toggle="modal" data-bs-target="#cancelPayment{{ $payment->id }}">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <div class="modal fade" id="cancelPayment{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route($portalPrefix.'.payments.cancel', $payment) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Annuler / rembourser ce paiement ?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small text-muted">Les factures concernées redeviendront dues pour le montant annulé.</p>
                                                            <label class="form-label" for="cancel-reason-{{ $payment->id }}">Motif (erreur de saisie, remboursement...)</label>
                                                            <textarea id="cancel-reason-{{ $payment->id }}" name="reason" class="form-control" required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            <button type="submit" class="btn btn-danger">Confirmer l'annulation</button>
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
            <div class="p-3 d-flex justify-content-center">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
