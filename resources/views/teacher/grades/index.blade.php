@extends('teacher.layouts.app')

@section('title', 'Notes - Enseignant')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0 h3">Gestion des Notes</h1>
        <p class="text-muted">Consultez et gérez les notes de vos élèves</p>
    </div>
    <a href="{{ route('teacher.grades.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Saisir des notes
    </a>
</div>

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
                            {{ $class->name }}
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

{{-- Tableau des notes --}}
@if($selectedClassId && $selectedSubjectId)
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-star me-2"></i>
                Notes - {{ $classes->firstWhere('id', $selectedClassId)->name ?? '' }} 
                - {{ $subjects->firstWhere('id', $selectedSubjectId)->name ?? '' }}
            </h5>
            <a href="{{ route('teacher.grades.create', ['class_id' => $selectedClassId, 'subject_id' => $selectedSubjectId]) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Ajouter des notes
            </a>
        </div>
        <div class="card-body">
            @if($students->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Moyenne</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                                @php
                                    $studentGrades = $grades->get($student->id, collect());
                                    $average = $studentGrades->avg('grade');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.85rem;">
                                                {{ strtoupper(substr($student->name, 0, 2)) }}
                                            </div>
                                            {{ $student->name }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($studentGrades->count() > 0)
                                            @foreach($studentGrades as $grade)
                                                <span class="badge {{ $grade->grade >= 10 ? 'bg-success' : 'bg-danger' }} me-1" 
                                                      title="{{ ucfirst($grade->type) }} - {{ $grade->date ? $grade->date->format('d/m/Y') : '' }}">
                                                    {{ number_format($grade->grade, 1) }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Aucune note</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($average !== null)
                                            <span class="badge fs-6 {{ $average >= 10 ? 'bg-success' : 'bg-danger' }}">
                                                {{ number_format($average, 2) }}/20
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @foreach($studentGrades->take(3) as $grade)
                                            <a href="{{ route('teacher.grades.edit', $grade->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Modifier la note du {{ $grade->date ? $grade->date->format('d/m/Y') : '' }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun élève dans cette classe</p>
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
    // Soumettre automatiquement le formulaire lors du changement de matière
    document.getElementById('subject_id').addEventListener('change', function() {
        this.form.submit();
    });
</script>
@endpush
