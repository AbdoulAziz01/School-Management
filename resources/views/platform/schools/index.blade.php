@extends('platform.layouts.app')

@section('title', 'Établissements')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Établissements</h1>
    <a href="{{ route('platform.schools.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Créer un établissement
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Code inscription</th>
                    <th>Élèves</th>
                    <th>Staff</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schools as $school)
                    <tr>
                        <td class="fw-semibold">{{ $school->name }}</td>
                        <td><code>{{ $school->code }}</code></td>
                        <td>{{ $school->students_count }}</td>
                        <td>{{ $school->staff_count ?? $school->admins_count ?? 0 }}</td>
                        <td>
                            @if($school->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-sm btn-outline-primary">Gérer</a>
                            <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">Aucun établissement enregistré.</td>
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
