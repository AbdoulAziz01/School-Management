@extends('teacher.layouts.app')

@section('title', 'Saisir des notes - Enseignant')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Tableau de bord</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.grades.index') }}">Notes</a></li>
            <li class="breadcrumb-item active">Saisir des notes</li>
        </ol>
    </nav>
    
    <h1 class="mb-0 h3">Saisir des notes</h1>
    <p class="text-muted">Entrez les notes pour une classe et une matière</p>
</div>

<form method="POST" action="{{ route('teacher.grades.store') }}" id="gradesForm">
    @csrf
    
    {{-- Sélection classe/matière --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuration</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="class_id" class="form-label">Classe <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ ($selectedClassId == $class->id || old('class_id') == $class->id) ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="subject_id" class="form-label">Matière <span class="text-danger">*</span></label>
                    <select name="subject_id" id="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                        <option value="">-- Sélectionner --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ ($selectedSubjectId == $subject->id || old('subject_id') == $subject->id) ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-2">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" 
                           value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-2">
                    <label for="coefficient" class="form-label">Coefficient <span class="text-danger">*</span></label>
                    <input type="number" name="coefficient" id="coefficient" class="form-control @error('coefficient') is-invalid @enderror" 
                           value="{{ old('coefficient', 1) }}" min="0.5" max="5" step="0.5" required>
                    @error('coefficient')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if($selectedClassId && $selectedSubjectId)
                <div class="mt-3">
                    @if($nextAllowed)
                        <input type="hidden" name="semester" value="{{ old('semester', $nextAllowed['semester']) }}">
                        <input type="hidden" name="type" value="{{ old('type', $nextAllowed['type']) }}">
                        <p class="small mb-0">
                            <i class="fas fa-pen me-1 text-primary"></i>
                            <strong>Saisie en cours :</strong> Semestre {{ $nextAllowed['semester'] }} — {{ $nextAllowed['label'] }}
                        </p>
                    @else
                        <p class="small text-success mb-0">
                            <i class="fas fa-check-circle me-1"></i>
                            Toutes les évaluations sont saisies pour cette classe et matière.
                        </p>
                    @endif
                </div>
            @endif
            
            @if(!$selectedClassId || !$selectedSubjectId)
                <div class="mt-3">
                    <button type="button" id="loadStudents" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>Charger les élèves
                    </button>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Tableau de saisie des notes --}}
    @if($students->count() > 0 && ($nextAllowed ?? null))
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Saisie des notes ({{ $students->count() }} élèves)</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="fillAbsent">
                        <i class="fas fa-user-slash me-1"></i>Marquer absents
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Élève</th>
                                <th style="width: 120px;">Note /20</th>
                                <th>Commentaire (optionnel)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="grades[{{ $index }}][user_id]" value="{{ $student->id }}">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.85rem;">
                                                {{ strtoupper(substr($student->name, 0, 2)) }}
                                            </div>
                                            {{ $student->name }}
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="grades[{{ $index }}][grade]" 
                                               class="form-control grade-input" 
                                               min="0" 
                                               max="20" 
                                               step="0.25" 
                                               placeholder="--">
                                    </td>
                                    <td>
                                        <input type="text" 
                                               name="grades[{{ $index }}][comments]" 
                                               class="form-control" 
                                               placeholder="Remarque...">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('teacher.grades.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les notes
                    </button>
                </div>
            </div>
        </div>
    @elseif($selectedClassId && $selectedSubjectId && !($nextAllowed ?? null))
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                <h4 class="text-muted">Saisie terminée pour cette classe et matière</h4>
                <p class="text-muted">Les 6 évaluations (3 par semestre) sont enregistrées.</p>
            </div>
        </div>
    @elseif($selectedClassId)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-user-graduate fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Aucun élève dans cette classe</h4>
                <p class="text-muted">Il n'y a pas d'élèves inscrits dans cette classe.</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Sélectionnez une classe</h4>
                <p class="text-muted">Choisissez une classe ci-dessus puis cliquez sur "Charger les élèves".</p>
            </div>
        </div>
    @endif
</form>
@endsection

@push('scripts')
<script>
    // Charger les élèves après sélection de classe
    document.getElementById('loadStudents')?.addEventListener('click', function() {
        const classId = document.getElementById('class_id').value;
        const subjectId = document.getElementById('subject_id').value;
        
        if (!classId) {
            alert('Veuillez sélectionner une classe');
            return;
        }
        if (!subjectId) {
            alert('Veuillez sélectionner une matière');
            return;
        }

        window.location.href = '{{ route("teacher.grades.create") }}?class_id=' + classId + '&subject_id=' + subjectId;
    });
    
    // Marquer tous les élèves sans note comme absents (note: 0)
    document.getElementById('fillAbsent')?.addEventListener('click', function() {
        const inputs = document.querySelectorAll('.grade-input');
        inputs.forEach(input => {
            if (input.value === '' || input.value === null) {
                input.value = '0';
                input.classList.add('bg-danger', 'bg-opacity-10');
            }
        });
    });
    
    // Colorer les notes en fonction de leur valeur
    document.querySelectorAll('.grade-input').forEach(input => {
        input.addEventListener('change', function() {
            this.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-opacity-10');
            const value = parseFloat(this.value);
            if (!isNaN(value)) {
                if (value >= 14) {
                    this.classList.add('bg-success', 'bg-opacity-10');
                } else if (value >= 10) {
                    this.classList.add('bg-warning', 'bg-opacity-10');
                } else {
                    this.classList.add('bg-danger', 'bg-opacity-10');
                }
            }
        });
    });
</script>
@endpush
