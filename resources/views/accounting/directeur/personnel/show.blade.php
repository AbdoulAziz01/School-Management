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
