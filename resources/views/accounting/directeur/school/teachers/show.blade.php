@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $teacher->name.' — Directeur')

@section('content')
<a href="{{ route('directeur.school.teachers.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Enseignants
</a>

<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <span class="rounded-circle bg-warning bg-opacity-25 text-warning d-flex align-items-center justify-content-center fw-bold fs-3" style="width:80px;height:80px;">
            {{ strtoupper(substr($teacher->name, 0, 1)) }}
        </span>
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
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Classes</div>
            <div class="h5 mb-0">{{ $classes->count() }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Élèves suivis</div>
            <div class="h5 mb-0">{{ $studentsCount }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Matières</div>
            <div class="h5 mb-0">{{ $teacher->subjects->count() }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Créneaux / semaine</div>
            <div class="h5 mb-0">{{ $schedule->count() }}</div>
        </div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Classes & performances --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Classes &amp; résultats</h5></div>
            <div class="card-body p-0">
                @if($classPerformance->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucune classe affectée.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($classPerformance as $row)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('directeur.school.classes.show', $row['class']) }}">{{ $row['class']->name }}</a>
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
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Emploi du temps</h5></div>
            <div class="card-body p-0">
                @if($schedule->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucun créneau planifié.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($schedule as $slot)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small fw-semibold">{{ \App\Support\ScheduleHelper::DAYS[$slot->day_of_week] ?? $slot->day_of_week }} · {{ substr($slot->start_time, 0, 5) }}–{{ substr($slot->end_time, 0, 5) }}</div>
                                    <div class="small text-muted">{{ $slot->subject->name ?? '—' }} · {{ $slot->schoolClass->name ?? '—' }}</div>
                                </div>
                                @if($slot->room)
                                    <span class="badge bg-light text-dark border">{{ $slot->room }}</span>
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
<div class="card">
    <div class="card-header"><h5 class="mb-0">Historique des affectations</h5></div>
    <div class="card-body p-0">
        @if($assignments->isEmpty())
            <p class="text-muted p-3 mb-0">Aucune affectation enregistrée.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
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
@endsection
