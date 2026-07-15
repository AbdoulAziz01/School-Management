@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_DIRECTEUR ? 'directeur' : 'comptable';
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Journal des opérations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>Journal des opérations</h1>
        <p class="text-muted mb-0">Grand livre — toutes les écritures comptables</p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route($portalPrefix.'.ledger.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="recette" {{ request('type') === 'recette' ? 'selected' : '' }}>Recettes</option>
                    <option value="depense" {{ request('type') === 'depense' ? 'selected' : '' }}>Dépenses</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Du</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Au</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                <a href="{{ route($portalPrefix.'.ledger.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($entries->isEmpty())
            <div class="alert alert-info mb-0">Aucune écriture trouvée.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td>{{ $entry->recorded_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge {{ $entry->entry_type === 'recette' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $entry->entry_type === 'recette' ? 'Recette' : 'Dépense' }}
                                    </span>
                                </td>
                                <td>{{ $entry->description }}</td>
                                <td class="text-end {{ $entry->amount < 0 ? 'text-muted' : '' }}">
                                    {{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3 d-flex justify-content-center">
                    {{ $entries->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
