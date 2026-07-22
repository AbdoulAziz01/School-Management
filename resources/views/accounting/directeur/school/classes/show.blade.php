@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', $class->name.' — Directeur')

@section('content')
<a href="{{ route('directeur.school.classes.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Classes
</a>

<div class="card mb-4">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <span class="rounded-circle bg-warning bg-opacity-25 text-warning d-flex align-items-center justify-content-center fw-bold fs-3" style="width:72px;height:72px;">
            <i class="fas fa-door-open"></i>
        </span>
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
</div>

{{-- Indicateurs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Élèves</div>
            <div class="h5 mb-0">{{ $students->count() }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Garçons / Filles</div>
            <div class="h5 mb-0">{{ $boys }} / {{ $girls }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Moyenne générale</div>
            <div class="h5 mb-0">{{ $academicSummary['average'] !== null ? $academicSummary['average'].'/'.$academicSummary['max_grade'] : '—' }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Taux de réussite</div>
            <div class="h5 mb-0">{{ $academicSummary['success_rate'] !== null ? $academicSummary['success_rate'].'%' : '—' }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Présents aujourd'hui</div>
            <div class="h5 mb-0 text-success">{{ $presentToday }}</div>
        </div></div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card h-100"><div class="card-body">
            <div class="small text-muted mb-1">Absents aujourd'hui</div>
            <div class="h5 mb-0 text-danger">{{ $absentToday }}</div>
        </div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Matières & enseignants --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Matières &amp; enseignants</h5></div>
            <div class="card-body p-0">
                @if($classSubjects->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucune matière configurée pour ce niveau.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($classSubjects as $subject)
                            @php
                                $teacherForSubject = $classTeachers->first(fn ($t) => $t->class_subjects->contains('id', $subject->id));
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('directeur.school.subjects.grades', [$class, $subject]) }}">{{ $subject->name }}</a>
                                    <div class="small text-muted">{{ $teacherForSubject?->name ?? 'Non affecté' }}</div>
                                </div>
                                <a href="{{ route('directeur.school.subjects.grades', [$class, $subject]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-star me-1"></i>Notes
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
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Enseignants ({{ $classTeachers->count() }})</h5></div>
            <div class="card-body p-0">
                @if($classTeachers->isEmpty())
                    <p class="text-muted p-3 mb-0">Aucun enseignant affecté.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($classTeachers as $teacher)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="{{ route('directeur.school.teachers.show', $teacher) }}">{{ $teacher->name }}</a>
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
<div class="card">
    <div class="card-header"><h5 class="mb-0">Élèves ({{ $students->count() }})</h5></div>
    <div class="card-body p-0">
        @if($students->isEmpty())
            <p class="text-muted p-3 mb-0">Aucun élève affecté à cette classe.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Nom</th>
                            <th>Genre</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td><code>{{ $student->identifier ?? '-' }}</code></td>
                                <td><a href="{{ route('directeur.school.students.show', $student) }}">{{ $student->name }}</a></td>
                                <td>{{ $student->gender === 'M' ? 'Garçon' : ($student->gender === 'F' ? 'Fille' : '—') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('directeur.school.students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
