@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $meta['label'])

@section('content')
<div class="mb-4">
    <a href="{{ route('directeur.personnel.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-2 small text-muted">
        <i class="fas fa-arrow-left me-2"></i>Retour à Personnel &amp; Élèves
    </a>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="h3 mb-0"><i class="fas {{ $meta['icon'] }} me-2"></i>{{ $meta['label'] }}</h1>
        <span class="count-chip">{{ $people->total() }} {{ mb_strtolower($meta['label']) }}</span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($credentials = session('new_staff_credentials'))
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <h6 class="alert-heading mb-3"><i class="fas fa-key me-2"></i>Identifiants de connexion — à noter maintenant</h6>
        <p class="small text-danger fw-semibold mb-2">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Affiché une seule fois : il ne sera plus jamais visible ensuite.
        </p>
        <ul class="list-unstyled small mb-0">
            <li class="mb-1"><span class="text-muted">Nom :</span> {{ $credentials['name'] }}</li>
            <li class="mb-1"><span class="text-muted">Identifiant :</span> <code class="user-select-all">{{ $credentials['identifier'] }}</code></li>
            <li class="mb-1"><span class="text-muted">Email :</span> {{ $credentials['email'] }}</li>
            <li><span class="text-muted">Mot de passe :</span> <code class="user-select-all fs-6 fw-bold text-primary">{{ $credentials['password'] }}</code></li>
        </ul>
    </div>
@endif

<form method="GET" action="{{ route('directeur.personnel.show', $group) }}" class="search-bar mb-4">
    <div class="search-field" style="flex: 0 1 280px;">
        <label for="personnel-search" class="visually-hidden">Rechercher un nom ou matricule</label>
        <i class="fas fa-search"></i>
        <input type="text" id="personnel-search" name="search" placeholder="Nom ou matricule..." value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn-pill-primary"><i class="fas fa-search"></i>Rechercher</button>
    @if(request('search'))
        <a href="{{ route('directeur.personnel.show', $group) }}" class="btn-pill-outline"><i class="fas fa-times"></i>Effacer</a>
    @endif
</form>

@if($people->isEmpty())
    <div class="empty-state"><i class="fas fa-users"></i><p class="mb-0">Aucun résultat.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Matricule</th>
                            <th>Email</th>
                            <th>Statut</th>
                            @if(in_array($group, ['comptables', 'caissiers']))
                                <th class="text-end">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($people as $person)
                            <tr>
                                <td>
                                    <div class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($person->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $person->name }}</span>
                                    </div>
                                </td>
                                <td><code>{{ $person->identifier ?? '—' }}</code></td>
                                <td class="text-muted">{{ $person->email ?? '—' }}</td>
                                <td>
                                    @if($person->status === 'approved')
                                        <span class="status-badge status-badge-success">Approuvé</span>
                                    @elseif($person->status === 'pending')
                                        <span class="status-badge status-badge-warning">En attente</span>
                                    @else
                                        <span class="status-badge status-badge-danger">Rejeté</span>
                                    @endif
                                </td>
                                @if(in_array($group, ['comptables', 'caissiers']))
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('directeur.personnel.regenerate-credentials', $person) }}"
                                              onsubmit="return confirm('Régénérer le mot de passe de {{ $person->name }} ? L\'ancien mot de passe cessera de fonctionner immédiatement.');">
                                            @csrf
                                            <button type="submit" class="btn-pill-outline btn-sm">
                                                <i class="fas fa-key"></i> Régénérer le mot de passe
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-center">
                {{ $people->links() }}
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
