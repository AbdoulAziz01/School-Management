@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@section('title', 'Dépenses')

@section('content')
<a href="{{ route('comptable.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2"></i>Dépenses</h1>
        <p class="text-muted mb-0">Fournitures, matériel, factures, entretien, primes...</p>
    </div>
    <a href="{{ route('comptable.expenses.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Enregistrer une dépense
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('comptable.expenses.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Catégorie</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    @foreach($categories as $value => $label)
                        <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                <a href="{{ route('comptable.expenses.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($expenses->isEmpty())
            <div class="alert alert-info mb-0">Aucune dépense enregistrée.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Catégorie</th>
                            <th>Bénéficiaire</th>
                            <th>Motif</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                            <tr class="{{ $expense->isCancelled() ? 'table-secondary text-decoration-line-through' : '' }}">
                                <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td><span class="badge bg-info">{{ $expense->categoryLabel() }}</span></td>
                                <td>{{ $expense->beneficiary }}</td>
                                <td>{{ $expense->motif }}</td>
                                <td>{{ number_format($expense->amount, 0, ',', ' ') }} FCFA</td>
                                <td>
                                    @if($expense->isCancelled())
                                        <span class="badge bg-danger">Annulée</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->justificatif_path)
                                        <a href="{{ route('comptable.expenses.justificatif', $expense) }}" class="btn btn-sm btn-outline-secondary" title="Justificatif">
                                            <i class="fas fa-paperclip"></i>
                                        </a>
                                    @endif
                                    @if(!$expense->isCancelled())
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelExpense{{ $expense->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div class="modal fade" id="cancelExpense{{ $expense->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('comptable.expenses.cancel', $expense) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Annuler cette dépense ?</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Motif de l'annulation</label>
                                                            <textarea name="reason" class="form-control" required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            <button type="submit" class="btn btn-danger">Annuler la dépense</button>
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
                    {{ $expenses->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
