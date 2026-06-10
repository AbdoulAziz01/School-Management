@extends('admin.layouts.app')

@section('title', 'Tableau de bord')

@push('styles')
<style>
    .stat-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
        transition: transform 0.2s;
        height: 100%;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }
    
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 1.25rem 1.5rem;
    }
    
    .card-header h5 {
        margin: 0;
        font-weight: 600;
        color: #2d3748;
    }
    
    .quick-actions .btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 0.5rem;
        text-align: center;
        transition: all 0.2s;
        height: 100%;
        border-radius: 8px;
    }
    
    .quick-actions .btn i {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }
    
    .quick-actions .btn span {
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h1 class="mb-0 h3">Tableau de bord</h1>
</div>

@if($selectedYear && !$isSelectedYearCurrent)
    <div class="mb-3 alert alert-light border py-2 small mb-4">
        <i class="fas fa-info-circle me-1 text-muted"></i>
        Statistiques affichées pour <strong>{{ $selectedYear->name }}</strong>.
        L'année courante est <strong>{{ $currentYear?->name ?? '—' }}</strong>.
    </div>
@endif

    <!-- Cartes de statistiques -->
    <div class="mb-4 row g-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stat-label">Inscriptions en attente</h6>
                            <h2 class="stat-value">{{ $pendingCount ?? 0 }}</h2>
                        </div>
                        <div class="p-3 rounded-circle" style="background-color: rgba(253, 126, 20, 0.1);">
                            <i class="fas fa-user-clock fs-4" style="color: #fd7e14;"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.registrations.pending') }}" class="btn btn-sm btn-outline-primary w-100">Voir les inscriptions</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stat-label">
                                Total des classes
                                @if($selectedYear)                                  git add .
                                    <span class="text-muted fw-normal">({{ $selectedYear->name }})</span>
                                @endif
                            </h6>
                            <h2 class="stat-value">{{ $classesCount ?? 0 }}</h2>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 rounded-circle">
                            <i class="fas fa-chalkboard text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">  
                        <a href="{{ route('admin.classes.index') }}" class="btn btn-sm btn-outline-success w-100">Voir les classes</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stat-label">
                                Total élèves
                                @if($selectedYear)
                                    <span class="text-muted fw-normal">({{ $selectedYear->name }})</span>
                                @endif
                            </h6>
                            <h2 class="stat-value">{{ $totalStudentsCount ?? 0 }}</h2>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 rounded-circle">
                            <i class="fas fa-user-graduate text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-warning w-100">Voir les élèves</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stat-label">Année scolaire</h6>
                            <h2 class="stat-value fs-5">{{ $selectedYear ? $selectedYear->name : 'Aucune' }}</h2>
                            @if($selectedYear)
                                <div class="mt-1">
                                    @if($selectedYear->is_current)
                                        <span class="badge bg-success">Courante</span>
                                    @elseif($selectedYear->isClosed())
                                        <span class="badge bg-secondary">Terminée</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Archivée</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="p-3 bg-info bg-opacity-10 rounded-circle">
                            <i class="fas fa-calendar-alt text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-outline-info w-100">Gérer les années</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des actions rapides -->
    <div class="mb-4 card">
        <div class="card-header">
            <h5 class="mb-0">Actions rapides</h5>
        </div>
        <div class="card-body">
            <div class="row quick-actions g-3">
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.students.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i>
                        <span>Ajouter un élève</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.teachers.create') }}" class="btn btn-outline-success">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Ajouter un enseignant</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.classes.create') }}" class="btn btn-outline-info">
                        <i class="fas fa-chalkboard"></i>
                        <span>Créer une classe</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.academic-years.create') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-plus"></i>
                        <span>Nouvelle année scolaire</span>
                    </a>
                </div>
            </div>
            
            <div class="mt-4 row g-3">
                <div class="col-6 col-md-2">
                    <a href="{{ route('admin.students.index') }}" class="py-2 btn btn-light w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-graduate me-2"></i>
                        <span>Élèves</span>
                    </a>
                </div>
                <div class="col-6 col-md-2">
                    <a href="{{ route('admin.teachers.index') }}" class="py-2 btn btn-light w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        <span>Enseignants</span>
                    </a>
                </div>
                <div class="col-6 col-md-2">
                    <a href="{{ route('admin.classes.index') }}" class="py-2 btn btn-light w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-chalkboard me-2"></i>
                        <span>Classes</span>
                    </a>
                </div>
                <div class="col-6 col-md-2">
                    <a href="{{ route('admin.subjects.index') }}" class="py-2 btn btn-light w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-book me-2"></i>
                        <span>Matières</span>
                    </a>
                </div>
                <div class="col-6 col-md-2">
                    <a href="{{ route('admin.academic-years.index') }}" class="py-2 btn btn-light w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <span>Années</span>
                    </a>
                </div>
                <div class="col-6 col-md-2">
                    <a href="{{ route('admin.registrations.pending') }}" class="py-2 btn btn-light w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-clock me-2"></i>
                        <span>Inscriptions</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
