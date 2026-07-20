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
                    <form action="{{ route('admin.teachers.classes.update', $teacher) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <h5 class="mb-3">Sélectionnez les classes de primaire pour {{ $teacher->name }} :</h5>
                            <p class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Au primaire, une classe n'a normalement qu'un seul enseignant titulaire (il enseigne
                                toutes les matières). Les classes déjà affectées à quelqu'un d'autre sont signalées
                                en orange ci-dessous.
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                Pour le collège et le lycée, une classe a plusieurs enseignants (un par matière, et
                                une même matière peut être partagée entre plusieurs) — utilisez plutôt
                                <a href="{{ route('admin.teachers.assignments', $teacher) }}">Affectations (classe + matière)</a>.
                            </p>

                            @php $checkedIds = session('pending_class_ids', $assignedClasses); @endphp

                            @if($classes->isEmpty())
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Aucune classe de primaire pour l'année courante. S'il s'agit d'un enseignant de
                                    collège/lycée, utilisez
                                    <a href="{{ route('admin.teachers.assignments', $teacher) }}" class="alert-link">Affectations</a>
                                    plutôt que cette page.
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
                                                       {{ in_array($class->id, $checkedIds) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="class_{{ $class->id }}">
                                                    {{ $class->name }}
                                                    <small class="text-muted d-block">
                                                        {{ $class->level->name ?? 'N/A' }} - {{ $class->academicYear->name ?? '' }}
                                                    </small>
                                                    @if($otherTeacherByClass->has($class->id))
                                                        <small class="d-block" style="color:#fd7e14;">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                            Déjà à {{ $otherTeacherByClass->get($class->id) }}
                                                        </small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="force" value="1" id="force_reassign">
                                    <label class="form-check-label" for="force_reassign">
                                        Forcer le remplacement des classes déjà affectées à un autre enseignant
                                        <small class="text-muted d-block">À cocher uniquement pour un cas exceptionnel (départ, maladie, décès...).</small>
                                    </label>
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
                                        <span class="badge ms-2" style="background-color: #fd7e14;">
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
