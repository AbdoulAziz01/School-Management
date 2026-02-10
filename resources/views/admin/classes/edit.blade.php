@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Modifier la classe : {{ $class->name }}
                    </h5>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.classes.update', $class) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de la classe</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="{{ old('name', $class->name) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="level_id" class="form-label">Niveau</label>
                            <select class="form-select" id="level_id" name="level_id" required>
                                <option value="">Sélectionner un niveau</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" 
                                        {{ old('level_id', $class->level_id) == $level->id ? 'selected' : '' }}>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label">Année scolaire</label>
                            <select class="form-select" id="academic_year_id" name="academic_year_id" required disabled>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" 
                                        {{ old('academic_year_id', $class->academic_year_id) == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="academic_year_id" value="{{ $class->academic_year_id }}">
                            <small class="form-text text-muted">L'année scolaire ne peut pas être modifiée après la création de la classe.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="capacity" class="form-label">Capacité maximale</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" 
                                   value="{{ old('capacity', $class->capacity) }}" min="1" required>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
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
        // Désactiver la modification de l'année scolaire après la création
        const academicYearSelect = document.getElementById('academic_year_id');
        if (academicYearSelect) {
            academicYearSelect.disabled = true;
        }
    });
</script>
@endpush

@endsection
