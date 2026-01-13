@extends('admin.layouts.app')

@section('title', $class->exists ? 'Modifier la classe' : 'Créer une nouvelle classe')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('academic-years.index') }}">Années scolaires</a>
                    </li>
                    @if($class->exists)
                        <li class="breadcrumb-item">
                            <a href="{{ route('academic-years.show', $class->academic_year_id) }}">
                                {{ $class->academicYear->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('classes.show', $class) }}">{{ $class->name }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Modifier</li>
                    @else
                        <li class="breadcrumb-item">
                            <a href="{{ route('academic-years.show', $selectedAcademicYear) }}">
                                {{ $selectedAcademicYear->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Nouvelle classe</li>
                    @endif
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    {{ $class->exists ? 'Modifier la classe : ' . $class->name : 'Créer une nouvelle classe' }}
                </h1>
                <a href="{{ $class->exists ? route('classes.show', $class) : route('academic-years.show', $selectedAcademicYear) }}" 
                   class="btn btn-outline-secondary">
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
                    <form action="{{ $class->exists ? route('classes.update', $class) : route('classes.store') }}" 
                          method="POST"
                          class="needs-validation" 
                          novalidate>
                        @csrf
                        @if($class->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom de la classe *</label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $class->name) }}" 
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir un nom de classe.
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="level_id" class="form-label">Niveau *</label>
                                <select class="form-select @error('level_id') is-invalid @enderror" 
                                        id="level_id" 
                                        name="level_id" 
                                        required>
                                    <option value="" disabled {{ !$class->exists && !old('level_id') ? 'selected' : '' }}>
                                        Sélectionner un niveau
                                    </option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level->id }}" 
                                                {{ (old('level_id', $class->level_id) == $level->id) ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un niveau.
                                </div>
                                @error('level_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="academic_year_id" class="form-label">Année scolaire *</label>
                                <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                        id="academic_year_id" 
                                        name="academic_year_id" 
                                        required
                                        {{ $class->exists ? 'disabled' : '' }}>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" 
                                                {{ (old('academic_year_id', $class->academic_year_id ?? $selectedAcademicYear->id) == $year->id) ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($class->exists)
                                    <input type="hidden" name="academic_year_id" value="{{ $class->academic_year_id }}">
                                @endif
                                <div class="invalid-feedback">
                                    Veuillez sélectionner une année scolaire.
                                </div>
                                @error('academic_year_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="room_number" class="form-label">Salle</label>
                                <input type="text" 
                                       class="form-control @error('room_number') is-invalid @enderror" 
                                       id="room_number" 
                                       name="room_number" 
                                       value="{{ old('room_number', $class->room_number) }}">
                                @error('room_number')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="capacity" class="form-label">Capacité maximale</label>
                                <input type="number" 
                                       class="form-control @error('capacity') is-invalid @enderror" 
                                       id="capacity" 
                                       name="capacity" 
                                       min="1" 
                                       max="50"
                                       value="{{ old('capacity', $class->capacity) }}">
                                <div class="form-text">
                                    Laisser vide pour une capacité illimitée.
                                </div>
                                @error('capacity')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ $class->exists ? 'Mettre à jour' : 'Créer la classe' }}
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
    // Désactiver la soumission du formulaire si des champs invalides
    (function () {
        'use strict'
        
        // Récupérer tous les formulaires auxquels nous voulons appliquer des styles de validation Bootstrap
        var forms = document.querySelectorAll('.needs-validation')
        
        // Boucler dessus et empêcher la soumission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>
@endpush

@endsection
