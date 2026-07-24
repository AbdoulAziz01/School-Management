@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $teacher->name.' — Directeur')

@section('content')
<a href="{{ route('directeur.school.teachers.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Enseignants
</a>

<div class="class-hero mb-4">
    <span class="class-hero-avatar">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
    <div class="flex-grow-1">
        <h1 class="h4 mb-1">{{ $teacher->name }}</h1>
        <div class="d-flex flex-wrap gap-3 small text-muted">
            <span><i class="fas fa-id-card me-1"></i>{{ $teacher->identifier ?? '—' }}</span>
            <span><i class="fas fa-envelope me-1"></i>{{ $teacher->email }}</span>
            @if($teacher->phone)
                <span><i class="fas fa-phone me-1"></i>{{ $teacher->phone }}</span>
            @endif
            <span>
                <i class="fas fa-circle-check me-1"></i>
                {{ $teacher->status === 'approved' ? 'Approuvé' : ($teacher->status === 'pending' ? 'En attente' : 'Rejeté') }}
            </span>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-amber"><i class="fas fa-door-open"></i></div>
            <div class="kpi-body"><div class="kpi-label">Classes</div><div class="kpi-value kpi-value-sm">{{ $classes->count() }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-blue"><i class="fas fa-user-graduate"></i></div>
            <div class="kpi-body"><div class="kpi-label">Élèves suivis</div><div class="kpi-value kpi-value-sm">{{ $studentsCount }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-purple"><i class="fas fa-book"></i></div>
            <div class="kpi-body"><div class="kpi-label">Matières</div><div class="kpi-value kpi-value-sm">{{ $teacher->subjects->count() }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-slate"><i class="fas fa-calendar-week"></i></div>
            <div class="kpi-body"><div class="kpi-label">Créneaux / semaine</div><div class="kpi-value kpi-value-sm">{{ $schedule->count() }}</div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Classes & performances --}}
    <div class="col-lg-6">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-door-open me-2 text-warning"></i>Classes &amp; résultats</div>
            <div class="p-0">
                @if($classPerformance->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-door-open"></i><p class="mb-0">Aucune classe affectée.</p></div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($classPerformance as $row)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('directeur.school.classes.show', $row['class']) }}" class="fw-semibold text-decoration-none">{{ $row['class']->name }}</a>
                                <span class="small text-muted">
                                    @if($row['summary']['average'] !== null)
                                        {{ $row['summary']['average'] }}/{{ $row['summary']['max_grade'] }} · réussite {{ $row['summary']['success_rate'] }}%
                                    @else
                                        Pas encore de notes
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Emploi du temps --}}
    <div class="col-lg-6">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-calendar-week me-2 text-warning"></i>Emploi du temps</div>
            <div class="p-0">
                @if($schedule->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-calendar-week"></i><p class="mb-0">Aucun créneau planifié.</p></div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($schedule as $slot)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small fw-semibold">{{ \App\Support\ScheduleHelper::DAYS[$slot->day_of_week] ?? $slot->day_of_week }} · {{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</div>
                                    <div class="small text-muted">{{ $slot->subject->name ?? '—' }} · {{ $slot->schoolClass->name ?? '—' }}</div>
                                </div>
                                @if($slot->room)
                                    <span class="class-chip">{{ $slot->room }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Historique des affectations --}}
<div class="panel-card">
    <div class="panel-card-header"><i class="fas fa-clock-rotate-left me-2 text-warning"></i>Historique des affectations</div>
    <div class="p-0">
        @if($assignments->isEmpty())
            <div class="empty-state py-4"><i class="fas fa-clock-rotate-left"></i><p class="mb-0">Aucune affectation enregistrée.</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Année scolaire</th>
                            <th>Classe</th>
                            <th>Matière</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->academicYear->name ?? '—' }}</td>
                                <td>{{ $assignment->schoolClass->name ?? '—' }}</td>
                                <td>{{ $assignment->subject->name ?? '—' }}</td>
                                <td>
                                    @if($assignment->is_active)
                                        <span class="status-badge status-badge-success">Active</span>
                                    @else
                                        <span class="status-badge status-badge-neutral">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
