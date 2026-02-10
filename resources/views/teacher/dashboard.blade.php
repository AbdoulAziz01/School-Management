@extends('teacher.layouts.app')

@section('title', 'Tableau de bord - Enseignant')

@section('content')
<div class="mb-4">
    <h1 class="mb-0 h3">Tableau de bord</h1>
    <p class="text-muted">Bienvenue, {{ $teacher->name }}</p>
</div>

{{-- Section Aperçu --}}
<div class="mb-4 row g-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Mes Classes</h6>
                        <h2 class="stat-value">{{ $classesCount ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle">
                        <i class="fas fa-users text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('teacher.classes.index') }}" class="btn btn-sm btn-outline-primary w-100">Voir mes classes</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Élèves Suivis</h6>
                        <h2 class="stat-value">{{ $studentsCount ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle">
                        <i class="fas fa-user-graduate text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Répartis dans {{ $classesCount ?? 0 }} classe(s)</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Matières</h6>
                        <h2 class="stat-value">{{ $subjectsCount ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 rounded-circle">
                        <i class="fas fa-book text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    @if(isset($subjects) && $subjects->count() > 0)
                        <small class="text-muted">{{ $subjects->pluck('name')->take(2)->implode(', ') }}{{ $subjects->count() > 2 ? '...' : '' }}</small>
                    @else
                        <small class="text-muted">Aucune matière assignée</small>
                    @endif
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
                        <h2 class="stat-value" style="font-size: 1.2rem;">{{ $currentYear ? $currentYear->name : 'N/A' }}</h2>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 rounded-circle">
                        <i class="fas fa-calendar-alt text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Année en cours</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Actions Rapides + Notes Récentes --}}
<div class="mb-4 row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="gap-2 d-grid">
                    <a href="{{ route('teacher.grades.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Saisir des notes
                    </a>
                    <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-clipboard-check me-2"></i>Faire l'appel
                    </a>
                    <a href="{{ route('teacher.classes.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users me-2"></i>Voir mes classes
                    </a>
                    <a href="{{ route('teacher.schedule') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-alt me-2"></i>Emploi du temps
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Notes Récentes</h5>
                <a href="{{ route('teacher.grades.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if(isset($recentGrades) && $recentGrades->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Matière</th>
                                    <th>Note</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentGrades as $grade)
                                <tr>
                                    <td>{{ $grade->user->name ?? 'N/A' }}</td>
                                    <td>{{ $grade->subject->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $grade->grade >= 10 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($grade->grade, 2) }}/20
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($grade->type ?? 'N/A') }}</td>
                                    <td>{{ $grade->date ? $grade->date->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune note récente</p>
                        <a href="{{ route('teacher.grades.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Saisir des notes
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Performance Moyenne par Classe --}}
@if(isset($classAverages) && count($classAverages) > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Moyenne par Classe</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small">Moyennes générales de vos matières par classe.</p>
        
        <div class="d-flex align-items-end justify-content-around flex-wrap" style="min-height: 200px; gap: 20px;">
            @foreach($classAverages as $item)
                @php
                    $heightPercent = ($item['average'] / 20) * 100;
                    $colorClass = $item['average'] >= 14 ? 'bg-success' : ($item['average'] >= 10 ? 'bg-warning' : 'bg-danger');
                @endphp
                <div class="text-center" style="min-width: 80px;">
                    <div class="mx-auto rounded {{ $colorClass }}" style="width: 60px; height: {{ $heightPercent }}%;"></div>
                    <small class="mt-2 d-block fw-bold">{{ $item['class']->name ?? 'N/A' }}</small>
                    <strong class="{{ $item['average'] >= 10 ? 'text-success' : 'text-danger' }}">{{ $item['average'] }}/20</strong>
                </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Moyenne par Classe</h5>
    </div>
    <div class="card-body text-center py-5">
        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
        <p class="text-muted">Aucune donnée de performance disponible pour le moment.</p>
        <p class="text-muted small">Les statistiques apparaîtront une fois que vous aurez saisi des notes.</p>
    </div>
</div>
@endif
@endsection
 