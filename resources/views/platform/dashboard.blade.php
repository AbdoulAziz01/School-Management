@extends('platform.layouts.app')

@section('title', 'Tableau de bord — Plateforme')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mb-0">
        <h1 class="h3 mb-1">Tableau de bord plateforme</h1>
        <p class="text-muted mb-0">Gestion multi-établissements EduManager</p>
    </div>
    <a href="{{ route('platform.schools.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvel établissement
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Établissements</div>
                <div class="h3 mb-0">{{ $stats['schools_total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Actifs</div>
                <div class="h3 mb-0 text-success">{{ $stats['schools_active'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Utilisateurs</div>
                <div class="h3 mb-0">{{ $stats['users_total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Élèves</div>
                <div class="h3 mb-0">{{ $stats['students_total'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Derniers établissements</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSchools as $school)
                    <tr>
                        <td>{{ $school->name }}</td>
                        <td><code>{{ $school->code }}</code></td>
                        <td>
                            @if($school->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun établissement. Créez le premier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
