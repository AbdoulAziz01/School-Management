@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_DIRECTEUR ? 'directeur' : 'comptable';
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Journal des opérations')

@section('content')
<a href="{{ route($portalPrefix . '.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>Journal des opérations</h1>
    <p class="text-muted mb-0">Grand livre — toutes les écritures comptables</p>
</div>

<form method="GET" action="{{ route($portalPrefix.'.ledger.index') }}" class="search-bar mb-4">
    <label for="ledger-type" class="visually-hidden">Type</label>
    <select id="ledger-type" name="type" class="search-select">
        <option value="">Tous les types</option>
        <option value="recette" {{ request('type') === 'recette' ? 'selected' : '' }}>Recettes</option>
        <option value="depense" {{ request('type') === 'depense' ? 'selected' : '' }}>Dépenses</option>
    </select>
    <label for="ledger-from" class="visually-hidden">Du</label>
    <input type="date" id="ledger-from" name="from" class="search-select" value="{{ request('from') }}">
    <label for="ledger-to" class="visually-hidden">Au</label>
    <input type="date" id="ledger-to" name="to" class="search-select" value="{{ request('to') }}">
    <button type="submit" class="btn-pill-primary"><i class="fas fa-filter"></i>Filtrer</button>
    <a href="{{ route($portalPrefix.'.ledger.index') }}" class="btn-pill-outline">Réinitialiser</a>
</form>

@if($entries->isEmpty())
    <div class="empty-state"><i class="fas fa-book"></i><p class="mb-0">Aucune écriture trouvée.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Effectué par</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td class="text-muted">{{ $entry->recorded_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="status-badge {{ $entry->entry_type === 'recette' ? 'status-badge-success' : 'status-badge-danger' }}">
                                        {{ $entry->entry_type === 'recette' ? 'Recette' : 'Dépense' }}
                                    </span>
                                </td>
                                <td>{{ $entry->description }}</td>
                                <td class="small text-muted">{{ $entry->createdBy?->name ?? '—' }}</td>
                                <td class="text-end fw-semibold {{ $entry->amount < 0 ? 'text-muted' : '' }}">
                                    {{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-center">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
