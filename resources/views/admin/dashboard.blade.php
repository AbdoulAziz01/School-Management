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
                        <div class="p-3 bg-primary bg-opacity-10 rounded-circle">
                            <i class="fas fa-user-clock text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ url('/admin/pending') }}" class="btn btn-sm btn-outline-primary w-100">Voir les inscriptions</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stat-label">Total des classes</h6>
                            <h2 class="stat-value">{{ $classesCount ?? 0 }}</h2>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 rounded-circle">
                            <i class="fas fa-chalkboard text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ url('/admin/classes') }}" class="btn btn-sm btn-outline-success w-100">Voir les classes</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stat-label">Élèves non affectés</h6>
                            <h2 class="stat-value">{{ $unassignedStudentsCount ?? 0 }}</h2>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 rounded-circle">
                            <i class="fas fa-user-graduate text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ url('/admin/students/assign') }}" class="btn btn-sm btn-outline-warning w-100">Affecter les élèves</a>
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
                            <h2 class="stat-value">{{ $currentYear ? $currentYear->name : 'Aucune' }}</h2>
                        </div>
                        <div class="p-3 bg-info bg-opacity-10 rounded-circle">
                            <i class="fas fa-calendar-alt text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ url('/admin/academic-years') }}" class="btn btn-sm btn-outline-info w-100">Gérer les années</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des actions rapides -->
    <div class="card mb-4">
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
            
            <div class="row mt-4 g-3">
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.students.index') }}" class="btn btn-light w-100 d-flex align-items-center justify-content-center py-2">
                        <i class="fas fa-user-graduate me-2"></i>
                        <span>Gérer les élèves</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-light w-100 d-flex align-items-center justify-content-center py-2">
                        <i class="fas fa-chalkboard-teacher me-2"></i>
                        <span>Gérer les enseignants</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.classes.index') }}" class="btn btn-light w-100 d-flex align-items-center justify-content-center py-2">
                        <i class="fas fa-chalkboard me-2"></i>
                        <span>Gérer les classes</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light w-100 d-flex align-items-center justify-content-center py-2">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <span>Années scolaires</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
