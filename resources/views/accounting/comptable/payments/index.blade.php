@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@section('title', 'Paiements élèves')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-receipt me-2"></i>Paiements élèves</h1>
        <p class="text-muted mb-0">Toutes les recettes — correction et annulation tracées</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('comptable.payments.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Recherche</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="N° reçu ou nom élève" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complété</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                <a href="{{ route('comptable.payments.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($payments->isEmpty())
            <div class="alert alert-info mb-0">Aucun paiement trouvé.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
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
                                <td>{{ $payment->student->name }}</td>
                                <td>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</td>
                                <td>{{ $payment->methodLabel() }}</td>
                                <td>{{ $payment->recordedBy->name }}</td>
                                <td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($payment->isCancelled())
                                        <span class="badge bg-danger" title="{{ $payment->cancellation_reason }}">Annulé</span>
                                    @else
                                        <span class="badge bg-success">Complété</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$payment->isCancelled())
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPayment{{ $payment->id }}">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <div class="modal fade" id="cancelPayment{{ $payment->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('comptable.payments.cancel', $payment) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Annuler / rembourser ce paiement ?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="small text-muted">Les factures concernées redeviendront dues pour le montant annulé.</p>
                                                            <label class="form-label">Motif (erreur de saisie, remboursement...)</label>
                                                            <textarea name="reason" class="form-control" required></textarea>
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
                <div class="mt-3 d-flex justify-content-center">
                    {{ $payments->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
