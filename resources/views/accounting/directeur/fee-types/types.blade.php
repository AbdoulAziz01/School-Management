@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Types de frais')

@section('content')
<a href="{{ route('directeur.fee-types.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Retour à la grille tarifaire
</a>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Types de frais</h1>
        <p class="text-muted mb-0">Chaque type de frais devient une colonne de la grille tarifaire par niveau.</p>
    </div>
    <a href="{{ route('directeur.fee-types.create') }}" class="btn-pill-primary">
        <i class="fas fa-plus"></i>Ajouter un type de frais
    </a>
</div>

@if($feeTypes->isEmpty())
    <div class="empty-state"><i class="fas fa-file-invoice-dollar"></i><p class="mb-0">Aucun type de frais configuré. Commencez par ajouter les frais d'inscription, de réinscription et la mensualité.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Récurrent</th>
                            <th>Montant</th>
                            <th style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($feeTypes as $feeType)
                            <tr>
                                <td><code>{{ $feeType->code }}</code></td>
                                <td class="fw-semibold">{{ $feeType->name }}</td>
                                <td><span class="status-badge status-badge-neutral">{{ $categories[$feeType->category] ?? $feeType->category }}</span></td>
                                <td class="text-muted">{{ $feeType->is_recurring ? 'Oui (mensuel)' : 'Non' }}</td>
                                <td>
                                    @if($defaultAmounts[$feeType->id] ?? null)
                                        <strong>{{ number_format($defaultAmounts[$feeType->id], 0, ',', ' ') }} FCFA</strong>
                                    @else
                                        <span class="status-badge status-badge-danger"><i class="fas fa-exclamation-triangle me-1"></i>Non défini</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('directeur.fee-types.edit', $feeType) }}" class="btn-view" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('directeur.fee-types.destroy', $feeType) }}" method="POST"
                                              onsubmit="return confirm('Supprimer ce type de frais ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-view" style="color:#dc2626;border-color:#fecaca;background:#fef2f2;" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
