@extends('admin.layouts.app')

@section('title', 'Détails de la matière')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>{{ $subject->name }}
                    </h5>
                    <div>
                        @if($subject->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                        @if($subject->is_core_subject)
                            <span class="badge bg-primary">Principale</span>
                        @else
                            <span class="badge bg-info">Optionnelle</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Code</p>
                            <p class="mb-0 fw-bold"><code>{{ $subject->code }}</code></p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-muted small">Coefficient</p>
                            <p class="mb-0 fw-bold">{{ $subject->coefficient }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 text-muted small">Heures/semaine</p>
                            <p class="mb-0 fw-bold">{{ $subject->hours_per_week ?? '-' }}h</p>
                        </div>
                    </div>
                    
                    @if($subject->description)
                    <div class="mb-3">
                        <p class="mb-1 text-muted small">Description</p>
                        <p class="mb-0">{{ $subject->description }}</p>
                    </div>
                    @endif
                    
                    @if($subject->levels->count() > 0)
                    <div class="mb-3">
                        <p class="mb-1 text-muted small">Niveaux associés</p>
                        <div>
                            @foreach($subject->levels as $level)
                                <span class="badge bg-light text-dark me-1">{{ $level->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                    <div>
                        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Enseignants affectés
                    </h6>
                    @php
                        $teacherCount = $subject->teacherAssignments->groupBy('teacher_id')->count();
                    @endphp
                    <span class="badge bg-primary">{{ $teacherCount }} professeur(s)</span>
                </div>
                <div class="card-body">
                    @if($subject->teacherAssignments->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($subject->teacherAssignments->groupBy('teacher_id') as $teacherId => $assignments)
                                @php $teacher = $assignments->first()->teacher; @endphp
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $teacher->name ?? 'N/A' }}</strong>
                                        <div class="small text-muted">
                                            @foreach($assignments as $assignment)
                                                <span class="badge bg-light text-dark">{{ $assignment->schoolClass->name ?? 'N/A' }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0 text-center py-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Aucun enseignant n'est affecté à cette matière.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
