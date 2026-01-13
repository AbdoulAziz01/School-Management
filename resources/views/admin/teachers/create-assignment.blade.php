@extends('admin.layouts.app')

@section('title', 'Nouvelle affectation - ' . $teacher->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.teachers.index') }}">Enseignants</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.teachers.show', $teacher) }}">{{ $teacher->name }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.teachers.assignments', $teacher) }}">Affectations</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Nouvelle affectation</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Nouvelle affectation pour {{ $teacher->name }}</h1>
                <a href="{{ route('admin.teachers.assignments', $teacher) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.teachers.assignments.store', $teacher) }}" method="POST" id="assignmentForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="academic_year_id" class="form-label">Année scolaire *</label>
                                <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                        id="academic_year_id" 
                                        name="academic_year_id" 
                                        required>
                                    <option value="" selected disabled>Sélectionner une année scolaire</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="class_id" class="form-label">Classe *</label>
                                <select class="form-select @error('class_id') is-invalid @enderror" 
                                        id="class_id" 
                                        name="class_id" 
                                        required
                                        {{ !old('academic_year_id') ? 'disabled' : '' }}>
                                    <option value="" selected disabled>Sélectionner d'abord une année</option>
                                    @if(old('academic_year_id'))
                                        @php
                                            $classes = \App\Models\SchoolClass::where('academic_year_id', old('academic_year_id'))
                                                ->orderBy('name')
                                                ->get();
                                        @endphp
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Sélectionnez d'abord une année scolaire pour voir les classes disponibles.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="subject_id" class="form-label">Matière *</label>
                                <select class="form-select @error('subject_id') is-invalid @enderror" 
                                        id="subject_id" 
                                        name="subject_id" 
                                        required>
                                    <option value="" selected disabled>Sélectionner une matière</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('subject_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer l'affectation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const academicYearSelect = document.getElementById('academic_year_id');
        const classSelect = document.getElementById('class_id');
        
        // Charger les classes lorsque l'année scolaire change
        academicYearSelect.addEventListener('change', function() {
            const yearId = this.value;
            
            if (!yearId) {
                classSelect.innerHTML = '<option value="" selected disabled>Sélectionner d\'abord une année</option>';
                classSelect.disabled = true;
                return;
            }
            
            // Afficher un indicateur de chargement
            classSelect.innerHTML = '<option value="" selected>Chargement des classes...</option>';
            classSelect.disabled = false;
            
            // Récupérer les classes via AJAX
            fetch(`/admin/teachers/assignments/classes/${yearId}`)
                .then(response => response.json())
                .then(classes => {
                    if (classes.length === 0) {
                        classSelect.innerHTML = '<option value="" selected disabled>Aucune classe disponible pour cette année</option>';
                        return;
                    }
                    
                    let options = '<option value="" selected disabled>Sélectionner une classe</option>';
                    classes.forEach(cls => {
                        options += `<option value="${cls.id}">${cls.name}</option>`;
                    });
                    
                    classSelect.innerHTML = options;
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des classes :', error);
                    classSelect.innerHTML = '<option value="" selected disabled>Erreur de chargement</option>';
                });
        });
        
        // Initialiser le sélecteur de classe si une année est déjà sélectionnée
        if (academicYearSelect.value) {
            academicYearSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endpush
@endsection
