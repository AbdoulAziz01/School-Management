@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_DIRECTEUR ? 'directeur' : 'comptable';
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Reçu de salaire — '.$salaryPayment->user->name)

@section('content')
<div class="mb-4 d-print-none">
    <a href="{{ route($portalPrefix.'.salaries.'.($portalPrefix === 'directeur' ? 'checklist' : 'index')) }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour aux salaires
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header text-center">
                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Reçu de paiement de salaire</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="h4 mb-0">{{ $salaryPayment->period->locale('fr')->translatedFormat('F Y') }}</div>
                    <div class="text-muted small">{{ $salaryPayment->paid_at?->format('d/m/Y H:i') }}</div>
                    @if($salaryPayment->status === 'partial')
                        <span class="badge bg-warning text-dark mt-2">Paiement partiel</span>
                    @endif
                </div>

                <dl class="row mb-3">
                    <dt class="col-5">Employé</dt>
                    <dd class="col-7">{{ $salaryPayment->user->name }} ({{ $salaryPayment->user->identifier ?? '—' }})</dd>
                    <dt class="col-5">Mode de paiement</dt>
                    <dd class="col-7">{{ \App\Models\Payment::METHOD_LABELS[$salaryPayment->payment_method] ?? $salaryPayment->payment_method }}</dd>
                    <dt class="col-5">Payé par</dt>
                    <dd class="col-7">{{ $salaryPayment->paidBy->name ?? '—' }}</dd>
                    <dt class="col-5">Statut</dt>
                    <dd class="col-7">{{ $salaryPayment->statusLabel() }}</dd>
                </dl>

                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td>Montant dû</td>
                            <td class="text-end">{{ number_format($salaryPayment->amount_due, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td>{{ $salaryPayment->status === 'partial' ? 'Total payé à ce jour' : 'Montant payé' }}</td>
                            <td class="text-end">{{ number_format($salaryPayment->amount_paid, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @if($salaryPayment->status === 'partial')
                            <tr class="text-danger fw-bold">
                                <td>Reste à payer</td>
                                <td class="text-end">{{ number_format($salaryPayment->balanceDue(), 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endif
                    </tfoot>
                </table>

                <div class="d-flex gap-2 d-print-none mt-3">
                    <a href="{{ route($portalPrefix.'.salary-receipts.pdf', $salaryPayment) }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-file-pdf me-1"></i> Télécharger / Imprimer le reçu
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
