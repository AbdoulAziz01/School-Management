@extends('admin.layouts.app')

@section('title', 'Tableau de bord - Administration')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 mb-0">Tableau de bord</h1>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stat-label">Inscriptions en attente</h6>
                            <h2 class="stat-value">{{ $pendingCount ?? 0 }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-user-clock text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.pending') }}" class="btn btn-sm btn-outline-primary w-100">Voir les inscriptions</a>
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
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-chalkboard text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('classes.index') }}" class="btn btn-sm btn-outline-success w-100">Voir les classes</a>
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
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-user-graduate text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.students.assign') }}" class="btn btn-sm btn-outline-warning w-100">Affecter les élèves</a>
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
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-calendar-alt text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('academic-years.index') }}" class="btn btn-sm btn-outline-info w-100">Gérer les années</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-graduate me-2"></i> Gérer les élèves
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chalkboard-teacher me-2"></i> Gérer les professeurs
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('classes.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-chalkboard me-2"></i> Gérer les classes
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('academic-years.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-alt me-2"></i> Années scolaires
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
