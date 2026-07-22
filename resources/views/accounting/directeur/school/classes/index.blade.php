@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Classes — Directeur')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Centre de Commande
</a>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-door-open me-2"></i>Classes</h1>
    <span class="badge bg-secondary fs-6">{{ $classes->count() }} classe(s)</span>
</div>

<div class="row g-3">
    @foreach($classes as $class)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('directeur.school.classes.show', $class) }}" class="text-decoration-none">
                <div class="card h-100 stat-card-link">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="mb-0 text-dark">{{ $class->name }}</h5>
                            <span class="badge bg-light text-dark border">{{ $class->level->name ?? 'N/A' }}</span>
                        </div>
                        <div class="small text-muted mb-1">
                            <i class="fas fa-user-graduate me-1"></i>{{ $class->students_count }} élève(s)
                        </div>
                        <div class="small">
                            @if($class->academic_summary['average'] !== null)
                                <i class="fas fa-star me-1 text-warning"></i>
                                Moyenne : {{ $class->academic_summary['average'] }}/{{ $class->academic_summary['max_grade'] }}
                                · Réussite : {{ $class->academic_summary['success_rate'] }}%
                            @else
                                <span class="text-muted">Pas encore de notes</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@push('styles')
<style>
    .stat-card-link { display: block; text-decoration: none !important; color: inherit !important; transition: transform .15s ease, box-shadow .15s ease; }
    .stat-card-link:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08); }
</style>
@endpush
@endsection
