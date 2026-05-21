@extends('platform.layouts.app')

@section('title', $subject->name . ' — ' . $school->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted small mb-1">
            <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => 'subjects']) }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Retour aux matières
            </a>
        </p>
        <h1 class="h3 mb-1">{{ $subject->name }}</h1>
        <p class="text-muted mb-0">{{ $school->name }}</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Informations</h6></div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Code</dt>
                    <dd class="col-sm-8"><code>{{ $subject->code ?? '—' }}</code></dd>
                    <dt class="col-sm-4 text-muted">Coefficient</dt>
                    <dd class="col-sm-8">{{ $subject->coefficient ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Heures / semaine</dt>
                    <dd class="col-sm-8">{{ $subject->hours_per_week ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Département</dt>
                    <dd class="col-sm-8">{{ $subject->department ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Statut</dt>
                    <dd class="col-sm-8">
                        @if($subject->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </dd>
                    @if($subject->description)
                        <dt class="col-sm-4 text-muted">Description</dt>
                        <dd class="col-sm-8">{{ $subject->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0">Niveaux concernés</h6></div>
            <div class="card-body small">
                @if($subject->levels->isNotEmpty())
                    <ul class="mb-0 ps-3">
                        @foreach($subject->levels as $level)
                            <li>{{ $level->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">Aucun niveau associé.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
