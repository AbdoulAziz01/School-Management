@extends('platform.layouts.app')

@section('title', 'Établissements — ' . $platformName)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h1 class="h3 mb-0">Établissements</h1>
    <a href="{{ route('platform.schools.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Créer un établissement
    </a>
</div>

<form method="GET" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Recherche</label>
                <input type="search" name="q" class="form-control" placeholder="Nom, code, ville…" value="{{ request('q') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Filtre</label>
                <select name="status" class="form-select">
                    <option value="">Tous les établissements</option>
                    <option value="active" @selected(request('status') === 'active')>Actifs uniquement</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactifs uniquement</option>
                    <option value="no_admin" @selected(request('status') === 'no_admin')>Sans administrateur</option>
                    <option value="pending" @selected(request('status') === 'pending')>Avec inscriptions en attente</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">Filtrer</button>
                <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th>Établissement</th>
                    <th>Ville</th>
                    <th>Code</th>
                    <th>Élèves</th>
                    <th>Profs</th>
                    <th>Classes</th>
                    <th>En attente</th>
                    <th>Admin</th>
                    <th>Année courante</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schools as $school)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $school->name }}</div>
                            <small class="text-muted">Créé {{ $school->created_at?->format('d/m/Y') }}</small>
                        </td>
                        <td>{{ $school->city ?? '—' }}</td>
                        <td><code>{{ $school->code }}</code></td>
                        <td>{{ $school->students_count }}</td>
                        <td>{{ $school->teachers_count }}</td>
                        <td>{{ $school->classes_count }}</td>
                        <td>
                            @if($school->pending_count > 0)
                                <span class="badge bg-danger">{{ $school->pending_count }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($school->admins_count > 0)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-danger">Non</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($currentYears[$school->id]))
                                <small>{{ $currentYears[$school->id] }}</small>
                            @else
                                <span class="text-warning small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($school->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-sm btn-outline-primary">Gérer</a>
                            <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5">Aucun établissement trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($schools->hasPages())
        <div class="card-footer bg-white">{{ $schools->links() }}</div>
    @endif
</div>
@endsection
