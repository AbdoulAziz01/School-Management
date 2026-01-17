@extends('admin.layouts.app')

@section('title', 'Affecter des classes - ' . $teacher->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        Affectation des classes - {{ $teacher->name }}
                    </h4>
                    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
                
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.teachers.classes.update', $teacher) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <h5 class="mb-3">Sélectionnez les classes pour {{ $teacher->name }} :</h5>
                            
                            @if($classes->isEmpty())
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucune classe n'est disponible pour le moment.
                                </div>
                            @else
                                <div class="row">
                                    @foreach($classes as $class)
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="classes[]" 
                                                       value="{{ $class->id }}" 
                                                       id="class_{{ $class->id }}"
                                                       {{ in_array($class->id, $assignedClasses) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="class_{{ $class->id }}">
                                                    {{ $class->name }}
                                                    <small class="text-muted d-block">
                                                        {{ $class->level->name ?? 'N/A' }} - {{ $class->academicYear->name ?? '' }}
                                                    </small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                    
                    @if(!empty($assignedClasses))
                        <div class="mt-5">
                            <h5>Classes actuellement affectées :</h5>
                            <ul class="list-group">
                                @foreach($teacher->assignedClasses as $class)
                                    <li class="list-group-item">
                                        <i class="fas fa-chalkboard me-2"></i>
                                        {{ $class->name }}
                                        <span class="badge bg-primary ms-2">
                                            {{ $class->level->name ?? 'N/A' }}
                                        </span>
                                        <span class="badge bg-secondary ms-1">
                                            {{ $class->academicYear->name ?? '' }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
