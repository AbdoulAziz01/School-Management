@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Présence du jour — Directeur')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Centre de Commande
</a>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-calendar-day me-2"></i>Présence du jour</h1>
    <span class="count-chip">{{ now()->locale('fr')->translatedFormat('d F Y') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves présents aujourd'hui</div>
                <div class="kpi-value text-success">{{ $presentCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-red"><i class="fas fa-times-circle"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Élèves absents aujourd'hui</div>
                <div class="kpi-value text-danger">{{ $absentStudents->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-orange"><i class="fas fa-user-clock"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Enseignants absents</div>
                <div class="kpi-value text-warning">{{ $absentTeachers->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="panel-card">
            <div class="panel-card-header"><i class="fas fa-user-graduate me-2 text-danger"></i>Élèves absents aujourd'hui</div>
            @if($absentStudents->isEmpty())
                <div class="empty-state py-4"><i class="fas fa-check-circle"></i><p class="mb-0">Aucun élève marqué absent aujourd'hui.</p></div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 data-table">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Classe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($absentStudents as $row)
                                <tr>
                                    <td>
                                        @if($row->user)
                                            <a href="{{ route('directeur.school.students.show', $row->user) }}">{{ $row->user->name }}</a>
                                        @else
                                            <span class="text-muted">Élève supprimé</span>
                                        @endif
                                    </td>
                                    <td>{{ $row->user?->schoolClass?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel-card">
            <div class="panel-card-header"><i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Enseignants n'ayant pas fait l'appel</div>
            <p class="text-muted small px-4 pt-3 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                Un enseignant apparaît ici s'il a une classe prévue aujourd'hui à l'emploi du temps mais n'a saisi aucune présence dans aucune de ses classes.
            </p>
            @if($absentTeachers->isEmpty())
                <div class="empty-state py-4"><i class="fas fa-check-circle"></i><p class="mb-0">Tous les enseignants prévus aujourd'hui ont fait l'appel.</p></div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 data-table">
                        <thead>
                            <tr>
                                <th>Enseignant</th>
                                <th>Classe(s) prévues aujourd'hui</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($absentTeachers as $teacher)
                                <tr>
                                    <td><a href="{{ route('directeur.school.teachers.show', $teacher) }}">{{ $teacher->name }}</a></td>
                                    <td>
                                        @foreach($teacher->today_classes as $slot)
                                            <span class="class-chip">{{ $slot->schoolClass?->name }} @if($slot->subject) — {{ $slot->subject->name }} @endif</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
