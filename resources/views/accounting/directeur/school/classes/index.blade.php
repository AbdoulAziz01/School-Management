@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Classes — Directeur')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Centre de Commande
</a>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-door-open me-2"></i>Classes</h1>
    <span class="count-chip">{{ $classes->count() }} classe(s)</span>
</div>

<div class="row g-4">
    @foreach($classes as $class)
        <div class="col-md-6 col-lg-4">
            <a href="{{ route('directeur.school.classes.show', $class) }}" class="class-card-link">
                <div class="class-card h-100">
                    <div class="class-card-top">
                        <div class="class-card-avatar">{{ mb_substr($class->name, 0, 2) }}</div>
                        <div class="class-card-title">
                            <h5 class="mb-0">{{ $class->name }}</h5>
                            <span class="class-card-level">{{ $class->level->name ?? 'N/A' }}</span>
                        </div>
                        <div class="class-card-students" title="Nombre d'élèves">
                            <i class="fas fa-user-graduate"></i>
                            <span>{{ $class->students_count }}</span>
                        </div>
                    </div>
                    <div class="class-card-body">
                        @if($class->academic_summary['average'] !== null)
                            <div class="class-card-stat">
                                <i class="fas fa-star text-warning me-1"></i>
                                Moyenne <strong>{{ $class->academic_summary['average'] }}/{{ $class->academic_summary['max_grade'] }}</strong>
                            </div>
                            <div class="class-card-stat">
                                <i class="fas fa-medal text-primary me-1"></i>
                                Réussite <strong>{{ $class->academic_summary['success_rate'] }}%</strong>
                            </div>
                        @else
                            <span class="text-muted small">Pas encore de notes</span>
                        @endif
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
