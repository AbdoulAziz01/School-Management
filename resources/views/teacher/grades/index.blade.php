@extends('teacher.layouts.app')

@section('title', 'Notes - Enseignant')

@section('content')
<div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 class="mb-0 h3">Gestion des Notes</h1>
        <p class="text-muted mb-0">Consultez les notes de vos élèves (consultation seule — les notes enregistrées ne peuvent pas être modifiées)</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @include('partials.dashboard-year-filter', [
            'action' => route('teacher.grades.index'),
            'academicYears' => $academicYears ?? collect(),
            'selectedYear' => $selectedYear ?? null,
            'queryParams' => array_filter([
                'class_id' => $selectedClassId ?? null,
                'subject_id' => $selectedSubjectId ?? null,
            ]),
        ])
        @unless($gradesLocked ?? false)
            <a href="{{ route('teacher.grades.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Saisir des notes
            </a>
        @endunless
    </div>
</div>

@if(isset($selectedYear) && !($isSelectedYearCurrent ?? true))
    <div class="alert alert-light border py-2 small mb-3">
        <i class="fas fa-info-circle me-1 text-muted"></i>
        Consultation de l'année <strong>{{ $selectedYear->name }}</strong> — données filtrées pour cette période.
    </div>
@endif

@if($gradesLocked ?? false)
    <div class="alert alert-warning">
        <i class="fas fa-lock me-2"></i>{{ $gradesLockedMessage ?? 'Saisie des notes bloquée.' }}
    </div>
@endif

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.grades.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="class_id" class="form-label">Classe</label>
                <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Sélectionner une classe --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                            {{ $class->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="subject_id" class="form-label">Matière</label>
                <select name="subject_id" id="subject_id" class="form-select" {{ !$selectedClassId ? 'disabled' : '' }}>
                    <option value="">-- Sélectionner une matière --</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ $selectedSubjectId == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Afficher
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedClassId && $selectedSubjectId)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="fas fa-star me-2"></i>
                Notes — {{ optional($classes->firstWhere('id', $selectedClassId))->display_name ?? '' }}
                / {{ $subjects->firstWhere('id', $selectedSubjectId)->name ?? '' }}
            </h5>
            @unless($gradesLocked ?? false)
                <a href="{{ route('teacher.grades.create', ['class_id' => $selectedClassId, 'subject_id' => $selectedSubjectId]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Ajouter des notes
                </a>
            @endunless
        </div>
        <div class="card-body p-0">
            @if($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="align-middle" style="min-width: 160px;">Élève</th>
                                <th colspan="3" class="text-center border-bottom-0">Semestre 1</th>
                                <th colspan="3" class="text-center border-bottom-0">Semestre 2</th>
                                <th rowspan="2" class="text-center align-middle" style="min-width: 90px;">Moyenne</th>
                            </tr>
                            <tr>
                                @foreach($evaluationColumns as $col)
                                    <th class="text-center small" title="{{ $col['label'] }}">{{ $col['header'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php
                                    $studentGrades = $grades->get($student->id, collect());
                                    $findGrade = function (int $semester, string $type) use ($studentGrades) {
                                        return $studentGrades->first(
                                            fn ($g) => (int) $g->semester === $semester && $g->type === $type
                                        );
                                    };
                                    $subjectAverage = $studentGrades->avg('grade');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                                                 style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($student->name, 0, 2)) }}
                                            </div>
                                            <span class="small fw-medium">{{ $student->name }}</span>
                                        </div>
                                    </td>
                                    @foreach($evaluationColumns as $col)
                                        @php $grade = $findGrade($col['semester'], $col['type']); @endphp
                                        <td class="text-center p-1">
                                            @if($grade)
                                                <span class="badge {{ $grade->grade >= 10 ? 'bg-success' : 'bg-danger' }}"
                                                      title="{{ $col['label'] }} — enregistrée">
                                                    {{ number_format($grade->grade, 1) }}
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        @if($subjectAverage !== null)
                                            <span class="badge {{ $subjectAverage >= 10 ? 'bg-success' : 'bg-danger' }}">
                                                {{ number_format($subjectAverage, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-2 border-top bg-light small text-muted">
                    <span class="me-3"><i class="fas fa-lock me-1"></i>Les notes enregistrées sont définitives.</span>
                    <span class="me-3"><strong>S1 · D1</strong> = 1<sup>er</sup> devoir</span>
                    <span class="me-3"><strong>S1 · D2</strong> = 2<sup>e</sup> devoir</span>
                    <span class="me-3"><strong>S1 · Compo</strong> = composition</span>
                    <span class="text-muted">(idem pour S2)</span>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Aucun élève dans cette classe</p>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-filter fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">Sélectionnez une classe et une matière</h4>
            <p class="text-muted">Utilisez les filtres ci-dessus pour afficher les notes des élèves.</p>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.getElementById('subject_id')?.addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush
