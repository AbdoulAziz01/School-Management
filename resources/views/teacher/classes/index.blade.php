@extends('teacher.layouts.app')

@section('title', 'Mes Classes - Enseignant')

@section('content')
<a href="{{ route('teacher.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
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
                <div class="class-card h-100">
                    <div class="class-card-top">
                        <div class="class-card-avatar">{{ mb_substr($classData['class']->name ?? '?', 0, 2) }}</div>
                        <div class="class-card-title">
                            <h5 class="mb-0">{{ $classData['class']->name ?? 'N/A' }}</h5>
                            <span class="class-card-level">{{ $classData['class']->level->name ?? 'N/A' }}</span>
                        </div>
                        <div class="class-card-students" title="Nombre d'élèves">
                            <i class="fas fa-user-graduate"></i>
                            <span>{{ $classData['students_count'] }}</span>
                        </div>
                    </div>

                    <div class="class-card-body">
                        <div class="class-card-label">
                            <i class="fas fa-book-open me-1"></i>
                            Matières enseignées ({{ $classData['subjects']->count() }})
                        </div>
                        <div class="subject-chips">
                            @if($classData['subjects']->count() > 0)
                                @foreach($classData['subjects']->take(6) as $subject)
                                    <span class="subject-chip">{{ $subject->name }}</span>
                                @endforeach
                                @if($classData['subjects']->count() > 6)
                                    <span class="subject-chip subject-chip--more">+{{ $classData['subjects']->count() - 6 }} autres</span>
                                @endif
                            @else
                                <span class="text-muted small">Aucune matière assignée</span>
                            @endif
                        </div>
                    </div>

                    <div class="class-card-footer">
                        <a href="{{ route('teacher.classes.show', $classData['class']->id) }}" class="btn-class btn-class-outline">
                            <i class="fas fa-eye me-1"></i> Voir
                        </a>
                        <a href="{{ route('teacher.grades.index', ['class_id' => $classData['class']->id]) }}" class="btn-class btn-class-solid">
                            <i class="fas fa-star me-1"></i> Notes
                        </a>
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

<style>
    .class-card {
        display: flex;
        flex-direction: column;
        background: #ffffff;
        border: 1px solid #fde68a;
        border-radius: 18px;
        box-shadow: 0 4px 16px rgba(245, 158, 11, 0.08);
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .class-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(245, 158, 11, 0.18);
    }
    .class-card-top {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 1.1rem 1.25rem;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-bottom: 1px solid #fde68a;
    }
    .class-card-avatar {
        flex: none;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #fff;
        font-weight: 700;
        font-size: 0.95rem;
        text-transform: uppercase;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
    }
    .class-card-title {
        flex: 1;
        min-width: 0;
    }
    .class-card-title h5 {
        font-weight: 700;
        color: #1c1917;
    }
    .class-card-level {
        font-size: 0.78rem;
        color: #92400e;
        font-weight: 500;
    }
    .class-card-students {
        flex: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.1rem;
        color: #d97706;
        font-weight: 700;
        font-size: 1rem;
    }
    .class-card-students i {
        font-size: 0.85rem;
        opacity: 0.7;
    }
    .class-card-body {
        padding: 1.1rem 1.25rem;
        flex: 1;
    }
    .class-card-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #78716c;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.6rem;
    }
    .subject-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }
    .subject-chip {
        display: inline-block;
        padding: 0.32rem 0.7rem;
        border-radius: 999px;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-size: 0.78rem;
        font-weight: 500;
        line-height: 1.2;
    }
    .subject-chip--more {
        background: #f3f4f6;
        color: #6b7280;
        border-color: #e5e7eb;
    }
    .class-card-footer {
        display: flex;
        gap: 0.6rem;
        padding: 1rem 1.25rem;
        border-top: 1px solid #f3f4f6;
    }
    .btn-class {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .btn-class-outline {
        border: 1.5px solid #fde68a;
        color: #b45309;
        background: #fff;
    }
    .btn-class-outline:hover {
        background: #fffbeb;
        color: #92400e;
    }
    .btn-class-solid {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #1c1917;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    }
    .btn-class-solid:hover {
        color: #1c1917;
        filter: brightness(1.05);
    }
</style>
@endsection
