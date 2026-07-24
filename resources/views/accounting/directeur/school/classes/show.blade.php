@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $class->name.' — Directeur')

@section('content')
<a href="{{ route('directeur.school.classes.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Classes
</a>

<div class="class-hero mb-4">
    <div class="class-hero-avatar"><i class="fas fa-door-open"></i></div>
    <div class="flex-grow-1">
        <h1 class="h4 mb-1">{{ $class->name }}</h1>
        <div class="d-flex flex-wrap gap-3 small text-muted">
            <span><i class="fas fa-layer-group me-1"></i>{{ $class->level->name ?? 'N/A' }}</span>
            @if($class->room_number)
                <span><i class="fas fa-map-marker-alt me-1"></i>Salle {{ $class->room_number }}</span>
            @endif
            <span><i class="fas fa-calendar me-1"></i>{{ $class->academicYear->name ?? '—' }}</span>
            @if($classTeachers->isNotEmpty())
                <span><i class="fas fa-chalkboard-teacher me-1"></i>Titulaire/référent : {{ $classTeachers->first()->name }}</span>
            @endif
        </div>
    </div>
</div>

{{-- Indicateurs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-amber"><i class="fas fa-user-graduate"></i></div>
            <div class="kpi-body"><div class="kpi-label">Élèves</div><div class="kpi-value kpi-value-sm">{{ $students->count() }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-blue"><i class="fas fa-venus-mars"></i></div>
            <div class="kpi-body"><div class="kpi-label">Garçons / Filles</div><div class="kpi-value kpi-value-sm">{{ $boys }} / {{ $girls }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-purple"><i class="fas fa-star"></i></div>
            <div class="kpi-body"><div class="kpi-label">Moyenne générale</div><div class="kpi-value kpi-value-sm">{{ $academicSummary['average'] !== null ? $academicSummary['average'].'/'.$academicSummary['max_grade'] : '—' }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-purple"><i class="fas fa-medal"></i></div>
            <div class="kpi-body"><div class="kpi-label">Taux de réussite</div><div class="kpi-value kpi-value-sm">{{ $academicSummary['success_rate'] !== null ? $academicSummary['success_rate'].'%' : '—' }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-green"><i class="fas fa-check-circle"></i></div>
            <div class="kpi-body"><div class="kpi-label">Présents aujourd'hui</div><div class="kpi-value kpi-value-sm text-success">{{ $presentToday }}</div></div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-tile kpi-tile-static">
            <div class="kpi-icon kpi-icon-red"><i class="fas fa-times-circle"></i></div>
            <div class="kpi-body"><div class="kpi-label">Absents aujourd'hui</div><div class="kpi-value kpi-value-sm text-danger">{{ $absentToday }}</div></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Matières & enseignants --}}
    <div class="col-lg-6">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-book me-2 text-warning"></i>Matières &amp; enseignants</div>
            <div class="p-0">
                @if($classSubjects->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-book"></i><p class="mb-0">Aucune matière configurée pour ce niveau.</p></div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($classSubjects as $subject)
                            @php
                                $teacherForSubject = $classTeachers->first(fn ($t) => $t->class_subjects->contains('id', $subject->id));
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('directeur.school.subjects.grades', [$class, $subject]) }}" class="fw-semibold text-decoration-none">{{ $subject->name }}</a>
                                    <div class="small text-muted">{{ $teacherForSubject?->name ?? 'Non affecté' }}</div>
                                </div>
                                <a href="{{ route('directeur.school.subjects.grades', [$class, $subject]) }}" class="btn-pill-outline">
                                    <i class="fas fa-star"></i>Notes
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- Enseignants de la classe --}}
    <div class="col-lg-6">
        <div class="panel-card h-100">
            <div class="panel-card-header"><i class="fas fa-chalkboard-teacher me-2 text-warning"></i>Enseignants ({{ $classTeachers->count() }})</div>
            <div class="p-0">
                @if($classTeachers->isEmpty())
                    <div class="empty-state py-4"><i class="fas fa-chalkboard-teacher"></i><p class="mb-0">Aucun enseignant affecté.</p></div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($classTeachers as $teacher)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('directeur.school.teachers.show', $teacher) }}" class="person-cell">
                                    <span class="person-avatar">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                                    <span class="person-name">{{ $teacher->name }}</span>
                                </a>
                                <span class="small text-muted">{{ $teacher->class_subjects->pluck('name')->join(', ') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Élèves --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0"><i class="fas fa-user-graduate me-2 text-warning"></i>Élèves</h2>
    <span class="count-chip">{{ $students->count() }} élève(s)</span>
</div>
@if($students->isEmpty())
    <div class="empty-state"><i class="fas fa-user-graduate"></i><p class="mb-0">Aucun élève affecté à cette classe.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Identifiant</th>
                            <th>Genre</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td>
                                    <a href="{{ route('directeur.school.students.show', $student) }}" class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($student->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $student->name }}</span>
                                    </a>
                                </td>
                                <td><code>{{ $student->identifier ?? '-' }}</code></td>
                                <td class="text-muted">{{ $student->gender === 'M' ? 'Garçon' : ($student->gender === 'F' ? 'Fille' : '—') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('directeur.school.students.show', $student) }}" class="btn-view" title="Voir le profil">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
