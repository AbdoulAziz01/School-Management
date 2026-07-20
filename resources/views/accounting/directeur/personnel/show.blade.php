@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $meta['label'])

@section('content')
<div class="mb-4">
    <a href="{{ route('directeur.personnel.index') }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour à Personnel & Élèves
    </a>
    <h1 class="h3 mt-2 mb-0"><i class="fas {{ $meta['icon'] }} me-2"></i>{{ $meta['label'] }}</h1>
    <p class="text-muted mb-0">{{ $people->total() }} {{ mb_strtolower($meta['label']) }}</p>
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

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('directeur.personnel.show', $group) }}" class="d-flex gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom ou matricule..." value="{{ request('search') }}" style="max-width: 260px;">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('directeur.personnel.show', $group) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($people->isEmpty())
            <div class="alert alert-info mb-0">Aucun résultat.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($people as $person)
                            <tr>
                                <td><code>{{ $person->identifier ?? '—' }}</code></td>
                                <td>{{ $person->name }}</td>
                                <td>{{ $person->email ?? '—' }}</td>
                                <td>
                                    @if($person->status === 'approved')
                                        <span class="badge bg-success">Approuvé</span>
                                    @elseif($person->status === 'pending')
                                        <span class="badge bg-warning text-dark">En attente</span>
                                    @else
                                        <span class="badge bg-danger">Rejeté</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3 d-flex justify-content-center">
                    {{ $people->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
