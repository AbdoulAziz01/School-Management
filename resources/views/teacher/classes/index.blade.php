@extends('teacher.layouts.app')

@section('title', 'Mes Classes - Enseignant')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0 h3">Mes Classes</h1>
        <p class="text-muted">{{ $currentYear ? 'Année scolaire ' . $currentYear->name : '' }}</p>
    </div>
</div>

@if($classes->count() > 0)
    <div class="row g-4">
        @foreach($classes as $classData)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            {{ $classData['class']->name ?? 'N/A' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Niveau:</strong> 
                            <span class="text-muted">{{ $classData['class']->level->name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Matières enseignées:</strong>
                            <div class="mt-2">
                                @if($classData['subjects']->count() > 0)
                                    @foreach($classData['subjects'] as $subject)
                                        <span class="badge bg-primary me-1 mb-1">{{ $subject->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Aucune matière assignée</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Nombre d'élèves:</strong> 
                            <span class="badge bg-info">{{ $classData['students_count'] }}</span>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('teacher.classes.show', $classData['class']->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                <i class="fas fa-eye me-1"></i> Voir
                            </a>
                            <a href="{{ route('teacher.grades.index', ['class_id' => $classData['class']->id]) }}" class="btn btn-sm btn-primary flex-fill">
                                <i class="fas fa-star me-1"></i> Notes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-chalkboard fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">Aucune classe assignée</h4>
            <p class="text-muted">Vous n'avez pas encore de classes assignées pour cette année scolaire.</p>
            <p class="text-muted small">Contactez l'administration pour être affecté à des classes.</p>
        </div>
    </div>
@endif
@endsection
