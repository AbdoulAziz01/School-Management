@extends('platform.layouts.app')

@section('title', $class->display_name . ' — ' . $school->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted small mb-1">
            <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => 'classes', 'academic_year_id' => $class->academic_year_id]) }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Retour aux classes
            </a>
        </p>
        <h1 class="h3 mb-1">{{ $class->display_name }}</h1>
        <p class="text-muted mb-0">{{ $school->name }}</p>
    </div>
</div>

@if($isReadOnly)
    <div class="alert alert-secondary border mb-4 small">
        <i class="fas fa-archive me-1"></i>
        <strong>Archive — consultation seule.</strong>
        Les élèves affichés correspondent à l'année scolaire terminée ou passée.
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Informations de la classe</h6></div>
            <div class="card-body small">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong class="text-muted d-block">Niveau</strong>
                        {{ $class->level?->name ?? '—' }}
                    </div>
                    <div class="col-md-4">
                        <strong class="text-muted d-block">Année scolaire</strong>
                        @include('platform.schools._academic-year-badge', ['year' => $academicYear])
                    </div>
                    <div class="col-md-4">
                        <strong class="text-muted d-block">Salle</strong>
                        {{ $class->room_number ?? '—' }}
                    </div>
                    <div class="col-md-4">
                        <strong class="text-muted d-block">Capacité</strong>
                        {{ $class->capacity ?? '—' }}
                    </div>
                    <div class="col-md-4">
                        <strong class="text-muted d-block">Effectif</strong>
                        {{ $students->count() }} élève(s)
                    </div>
                    <div class="col-md-4">
                        <strong class="text-muted d-block">Enseignants affectés</strong>
                        {{ $class->teacherAssignments->count() }}
                    </div>
                    @if($class->description)
                        <div class="col-12">
                            <strong class="text-muted d-block">Description</strong>
                            {{ $class->description }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Période</h6></div>
            <div class="card-body small">
                @if($academicYear)
                    <p class="mb-1"><strong>{{ $academicYear->name }}</strong></p>
                    <p class="text-muted mb-0">{{ $academicYear->periodLabel() }}</p>
                @else
                    <p class="text-muted mb-0">Année non définie</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if($class->teacherAssignments->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0">Enseignants & matières</h6></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Enseignant</th>
                    <th>Identifiant</th>
                    <th>Matière</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($class->teacherAssignments as $assignment)
                    <tr class="platform-click-row" data-href="{{ route('platform.schools.users.show', [$school, $assignment->teacher]) }}">
                        <td class="fw-semibold">{{ $assignment->teacher?->name ?? '—' }}</td>
                        <td><code>{{ $assignment->teacher?->identifier ?? '—' }}</code></td>
                        <td>{{ $assignment->subject?->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('platform.schools.users.show', [$school, $assignment->teacher]) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Élèves ({{ $students->count() }})</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle platform-click-table">
            <thead class="table-light">
                <tr>
                    <th>Identifiant</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Moyenne</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr data-href="{{ route('platform.schools.users.show', [$school, $student]) }}">
                        <td><code>{{ $student->identifier ?? $student->user_id ?? '—' }}</code></td>
                        <td class="fw-semibold">{{ $student->name }}</td>
                        <td><small>{{ $student->email ?? '—' }}</small></td>
                        <td>
                            @if(isset($studentAverages[$student->id]))
                                <span class="badge bg-primary">{{ $studentAverages[$student->id] }}/20</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>@include('platform.schools._status-badge', ['status' => $student->status])</td>
                        <td class="text-end">
                            <a href="{{ route('platform.schools.users.show', [$school, $student]) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun élève dans cette classe.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.platform-click-table tbody tr[data-href]').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) return;
            window.location.href = row.dataset.href;
        });
    });
    document.querySelectorAll('.platform-click-row[data-href]').forEach(function (row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) return;
            window.location.href = row.dataset.href;
        });
    });
</script>
@endpush
