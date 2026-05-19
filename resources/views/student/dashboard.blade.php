@extends('layouts.student')

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
    
    .welcome-banner {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 15px;
        color: #1f2937;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid #f59e0b;
    }
    
    .welcome-banner h2 {
        color: #92400e;
        font-weight: 700;
    }
    
    .welcome-banner p {
        color: #78350f;
    }
    
    .quick-link-card {
        border: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .quick-link-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .grade-badge {
        font-size: 1.1rem;
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 8px;
    }
    
    .grade-excellent { background-color: #d4edda; color: #155724; }
    .grade-good { background-color: #cce5ff; color: #004085; }
    .grade-average { background-color: #fff3cd; color: #856404; }
    .grade-poor { background-color: #f8d7da; color: #721c24; }
</style>
@endpush

@section('content')
<!-- Bannière de bienvenue -->
<div class="welcome-banner">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2 class="mb-2">Bonjour, {{ $user->name }} ! <i class="fas fa-smile"></i></h2>
            <p class="mb-0">Bienvenue sur votre espace élève. Consultez vos notes, votre emploi du temps et bien plus encore.</p>
        </div>
        <div class="col-md-4 text-end d-none d-md-block">
            <i class="fas fa-graduation-cap fa-4x" style="color: #d97706; opacity: 0.6;"></i>
        </div>
    </div>
</div>

<!-- Cartes de statistiques -->
<div class="mb-4 row g-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Moyenne générale</h6>
                        <h2 class="stat-value">{{ number_format($average ?? 0, 2) }}/20</h2>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle">
                        <i class="fas fa-chart-line text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Taux de présence</h6>
                        <h2 class="stat-value">{{ $attendanceRate ?? 0 }}%</h2>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 rounded-circle">
                        <i class="fas fa-user-check text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Cours prévus</h6>
                        <h2 class="stat-value">{{ $upcomingCourses->count() ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 rounded-circle">
                        <i class="fas fa-book text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Notes récentes</h6>
                        <h2 class="stat-value">{{ $recentGrades->count() ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 rounded-circle">
                        <i class="fas fa-star text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accès rapides -->
<div class="mb-4 row g-4">
    <div class="col-12">
        <h5 class="mb-3">Accès rapides</h5>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('student.grades') }}" class="text-decoration-none">
            <div class="py-4 text-center card quick-link-card h-100">
                <div class="card-body">
                    <i class="mb-3 fas fa-graduation-cap fa-2x text-primary"></i>
                    <h6 class="mb-0">Mes notes</h6>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('student.schedule') }}" class="text-decoration-none">
            <div class="py-4 text-center card quick-link-card h-100">
                <div class="card-body">
                    <i class="mb-3 fas fa-calendar-alt fa-2x text-success"></i>
                    <h6 class="mb-0">Emploi du temps</h6>
                </div>
            </div>  
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('student.attendance') }}" class="text-decoration-none">
            <div class="py-4 text-center card quick-link-card h-100">
                <div class="card-body">
                    <i class="mb-3 fas fa-user-check fa-2x text-info"></i>
                    <h6 class="mb-0">Mes présences</h6>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="{{ route('student.profile.index') }}" class="text-decoration-none">
            <div class="py-4 text-center card quick-link-card h-100">
                <div class="card-body">
                    <i class="mb-3 fas fa-user fa-2x text-warning"></i>
                    <h6 class="mb-0">Mon profil</h6>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Dernières notes -->
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Dernières notes</h5>
                <a href="{{ route('student.grades') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if($recentGrades->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Matière</th>
                                    <th>Note</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentGrades as $grade)
                                    <tr>
                                        <td>{{ $grade->subject->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="grade-badge {{ $grade->value >= 16 ? 'grade-excellent' : ($grade->value >= 12 ? 'grade-good' : ($grade->value >= 10 ? 'grade-average' : 'grade-poor')) }}">
                                                {{ $grade->value }}/20
                                            </span>
                                        </td>
                                        <td>{{ $grade->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-4 text-center">
                        <i class="mb-3 fas fa-inbox fa-3x text-muted"></i>
                        <p class="text-muted">Aucune note récente</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Cours à venir -->
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-day me-2 text-info"></i>Cours aujourd'hui</h5>
                <a href="{{ route('student.schedule') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if($upcomingCourses->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($upcomingCourses as $course)
                            <div class="px-0 list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $course->subject->name ?? 'N/A' }}</h6>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $course->start_time }} - {{ $course->end_time }}
                                        @if($course->room)
                                            <span class="ms-2"><i class="fas fa-door-open me-1"></i>{{ $course->room }}</span>
                                        @endif
                                    </small>
                                </div>
                                <span class="badge bg-primary">{{ $course->teacher->name ?? 'N/A' }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-4 text-center">
                        <i class="mb-3 fas fa-calendar-check fa-3x text-muted"></i>
                        <p class="text-muted">Aucun cours prévu aujourd'hui</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
