@extends('platform.layouts.app')

@php
    $sections = \App\Http\Controllers\Platform\SchoolInspectionController::SECTIONS;
@endphp

@section('title', ($sections[$section]['label'] ?? 'Inspection') . ' — ' . $school->name)

@push('styles')
<style>
    .platform-stat-card {
        display: block;
        text-decoration: none;
        color: inherit;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .platform-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.35rem 1rem rgba(15, 23, 42, 0.12) !important;
        color: inherit;
    }
    .platform-stat-card.active {
        outline: 2px solid #4f46e5;
        outline-offset: 2px;
    }
    .platform-inspection-table tbody tr[data-href] {
        cursor: pointer;
    }
    .platform-inspection-table tbody tr[data-href]:hover {
        background-color: rgba(79, 70, 229, 0.05);
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted small mb-1">
            <a href="{{ route('platform.schools.show', $school) }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> {{ $school->name }}
            </a>
        </p>
        <h1 class="h3 mb-1">Inspection — {{ $sections[$section]['label'] }}</h1>
        @if($currentAcademicYear)
            <p class="text-muted mb-0 small">
                Année courante :
                <span class="badge bg-{{ $currentAcademicYear->statusBadgeClass() }}">{{ $currentAcademicYear->statusLabel() }}</span>
                {{ $currentAcademicYear->name }}
            </p>
        @else
            <p class="text-warning mb-0 small">Aucune année scolaire courante configurée.</p>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach($sections as $key => $meta)
        <div class="col-4 col-md-2">
            <a href="{{ route('platform.schools.inspection', array_filter(['school' => $school, 'section' => $key, 'academic_year_id' => $selectedYearId ?: null])) }}"
               class="platform-stat-card {{ $section === $key ? 'active' : '' }}">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3 text-center">
                        <div class="h4 mb-0 {{ $key === 'pending' && $statCounts[$key] ? 'text-danger' : '' }}">
                            {{ $statCounts[$key] }}
                        </div>
                        <small class="text-muted">
                            <i class="fas {{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                        </small>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<form method="GET" class="card border-0 shadow-sm mb-4">
    <input type="hidden" name="section" value="{{ $section }}">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            @if(in_array($section, ['students', 'classes'], true) && $academicYears->isNotEmpty())
                <div class="col-md-4">
                    <label class="form-label small text-muted">Année scolaire</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">Toutes les années</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected($selectedYearId === $year->id)>
                                {{ $year->name }} — {{ $year->statusLabel() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-muted">Recherche</label>
                    <input type="search" name="q" class="form-control" placeholder="Nom, identifiant, email…" value="{{ request('q') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1">Filtrer</button>
                    <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => $section]) }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            @else
                <div class="col-md-9">
                    <label class="form-label small text-muted">Recherche</label>
                    <input type="search" name="q" class="form-control" placeholder="Nom, identifiant, email…" value="{{ request('q') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary flex-grow-1">Filtrer</button>
                    <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => $section]) }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            @endif
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas {{ $sections[$section]['icon'] }} me-1 text-primary"></i>
            {{ $items->total() }} {{ strtolower($sections[$section]['label']) }}
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle platform-inspection-table">
            <thead class="table-light">
                <tr>
                    @if($section === 'students')
                        <th>Identifiant</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Classe</th>
                        <th>Année scolaire</th>
                        <th>Statut</th>
                        <th></th>
                    @elseif($section === 'teachers')
                        <th>Identifiant</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Affectations</th>
                        <th>Statut</th>
                        <th></th>
                    @elseif($section === 'staff')
                        <th>Identifiant</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th></th>
                    @elseif($section === 'classes')
                        <th>Classe</th>
                        <th>Niveau</th>
                        <th>Année</th>
                        <th>Statut année</th>
                        <th>Salle</th>
                        <th>Élèves</th>
                        <th></th>
                    @elseif($section === 'subjects')
                        <th>Matière</th>
                        <th>Code</th>
                        <th>Coef.</th>
                        <th>h/sem.</th>
                        <th>Statut</th>
                        <th></th>
                    @elseif($section === 'pending')
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle demandé</th>
                        <th>Inscrit le</th>
                        <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php
                        $detailUrl = match ($section) {
                            'students', 'teachers', 'staff', 'pending' => route('platform.schools.users.show', [$school, $item]),
                            'classes' => route('platform.schools.classes.show', [$school, $item]),
                            'subjects' => route('platform.schools.subjects.show', [$school, $item]),
                            default => null,
                        };
                    @endphp
                    <tr @if($detailUrl) data-href="{{ $detailUrl }}" @endif>
                        @if($section === 'students')
                            <td><code>{{ $item->identifier ?? $item->user_id ?? '—' }}</code></td>
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td><small>{{ $item->email ?? '—' }}</small></td>
                            <td>
                                @if($item->class)
                                    {{ $item->class->display_name }}
                                @else
                                    <span class="text-warning">Sans classe</span>
                                @endif
                            </td>
                            <td>@include('platform.schools._academic-year-badge', ['year' => $item->class?->academicYear])</td>
                            <td>@include('platform.schools._status-badge', ['status' => $item->status])</td>
                            <td class="text-end"><a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                        @elseif($section === 'teachers')
                            <td><code>{{ $item->identifier ?? '—' }}</code></td>
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td><small>{{ $item->email ?? '—' }}</small></td>
                            <td>{{ $item->teacher_assignments_count ?? 0 }}</td>
                            <td>@include('platform.schools._status-badge', ['status' => $item->status])</td>
                            <td class="text-end"><a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                        @elseif($section === 'staff')
                            <td><code>{{ $item->identifier ?? '—' }}</code></td>
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td><small>{{ $item->email ?? '—' }}</small></td>
                            <td>
                                @if($item->role === 'admin')
                                    <span class="badge bg-primary">Admin</span>
                                @else
                                    <span class="badge bg-info text-dark">Surveillant</span>
                                @endif
                            </td>
                            <td>@include('platform.schools._status-badge', ['status' => $item->status])</td>
                            <td class="text-end"><a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                        @elseif($section === 'classes')
                            <td class="fw-semibold">{{ $item->display_name }}</td>
                            <td>{{ $item->level?->name ?? '—' }}</td>
                            <td>{{ $item->academicYear?->name ?? '—' }}</td>
                            <td>@include('platform.schools._academic-year-badge', ['year' => $item->academicYear])</td>
                            <td>{{ $item->room_number ?? '—' }}</td>
                            <td>{{ $item->students_count }}</td>
                            <td class="text-end"><a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                        @elseif($section === 'subjects')
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td><code>{{ $item->code ?? '—' }}</code></td>
                            <td>{{ $item->coefficient ?? '—' }}</td>
                            <td>{{ $item->hours_per_week ?? '—' }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end"><a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                        @elseif($section === 'pending')
                            <td class="fw-semibold">{{ $item->name }}</td>
                            <td><small>{{ $item->email ?? '—' }}</small></td>
                            <td><span class="badge bg-secondary">{{ $item->role }}</span></td>
                            <td>{{ $item->created_at?->format('d/m/Y à H:i') ?? '—' }}</td>
                            <td class="text-end"><a href="{{ $detailUrl }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">Aucun enregistrement.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="card-footer bg-white">{{ $items->links() }}</div>
    @endif
</div>

@if($section === 'students' && $school->unassigned_students_count > 0)
    <div class="alert alert-warning mt-4 mb-0 small">
        <i class="fas fa-info-circle me-1"></i>
        {{ $school->unassigned_students_count }} élève(s) sans classe —
        <a href="{{ route('platform.schools.show', $school) }}#unassigned-students">affecter depuis la fiche établissement</a>.
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.platform-inspection-table tbody tr[data-href]').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('a, button')) {
                return;
            }
            window.location.href = row.dataset.href;
        });
    });
</script>
@endpush
