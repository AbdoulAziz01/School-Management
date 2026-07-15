@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_DIRECTEUR ? 'directeur' : 'comptable';
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Situation financière — '.$student->name)

@section('content')
@include('accounting.shared.students._profile-header', ['student' => $student])

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Factures</h5>
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
                                <tr><td colspan="5" class="text-muted text-center py-3">Aucune facture.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Historique des paiements</h5>
            </div>
            <div class="card-body p-0">
                @if($paymentHistory->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucun paiement enregistré.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($paymentHistory as $payment)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="small"><code>{{ $payment->receipt_number }}</code></div>
                                    <div class="text-muted" style="font-size:.75rem">{{ $payment->paid_at->format('d/m/Y H:i') }} — {{ $payment->methodLabel() }}</div>
                                </div>
                                <span class="{{ $payment->isCancelled() ? 'text-muted text-decoration-line-through' : 'fw-semibold' }}">
                                    {{ number_format($payment->amount, 0, ',', ' ') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
