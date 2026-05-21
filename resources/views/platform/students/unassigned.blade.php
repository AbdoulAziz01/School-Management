@extends('platform.layouts.app')

@section('title', 'Élèves sans classe — ' . $platformName)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">Élèves sans classe</h1>
        <p class="text-muted mb-0">Tous les établissements — élèves approuvés ou en attente sans classe assignée.</p>
    </div>
    <a href="{{ route('platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Tableau de bord
    </a>
</div>

<form method="GET" class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Recherche</label>
                <input type="search" name="q" class="form-control" placeholder="Nom, identifiant, email…" value="{{ request('q') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">Établissement</label>
                <select name="school_id" class="form-select">
                    <option value="">Tous les établissements</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" @selected((string) request('school_id') === (string) $school->id)>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">Filtrer</button>
                <a href="{{ route('platform.students.unassigned') }}" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $students->total() }} élève(s) non affecté(s)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Identifiant</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Établissement</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr>
                        <td><code>{{ $student->identifier ?? $student->user_id ?? '—' }}</code></td>
                        <td class="fw-semibold">{{ $student->name }}</td>
                        <td><small>{{ $student->email ?? '—' }}</small></td>
                        <td>{{ $student->school?->name ?? '—' }}</td>
                        <td>
                            @if($student->status === 'approved')
                                <span class="badge bg-success">Approuvé</span>
                            @elseif($student->status === 'pending')
                                <span class="badge bg-warning text-dark">En attente</span>
                            @else
                                <span class="badge bg-secondary">{{ $student->status }}</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($student->school)
                                <a href="{{ route('platform.schools.show', $student->school) }}" class="btn btn-sm btn-outline-primary">
                                    Voir l'établissement
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Aucun élève sans classe.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
        <div class="card-footer bg-white">{{ $students->links() }}</div>
    @endif
</div>

<div class="alert alert-light border mt-4 mb-0 small">
    <i class="fas fa-info-circle me-1 text-muted"></i>
    L'affectation à une classe se fait par l'<strong>administrateur de l'établissement</strong>
    (menu Élèves → Affectation). En tant que super administrateur, vous pouvez consulter l'établissement concerné.
</div>
@endsection
