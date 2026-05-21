@extends('platform.layouts.app')

@php
    $backSection = match (true) {
        $user->isStudent() => 'students',
        in_array($user->role, \App\Models\User::ROLE_TEACHER_ALIASES, true) => 'teachers',
        $user->isSchoolStaff() => 'staff',
        $user->status === 'pending' => 'pending',
        default => 'students',
    };
@endphp

@section('title', $user->name . ' — ' . $school->name)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <p class="text-muted small mb-1">
            <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => $backSection]) }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
            </a>
        </p>
        <h1 class="h3 mb-1">{{ $user->name }}</h1>
        <p class="text-muted mb-0">{{ $school->name }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Identité</h6></div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Identifiant</dt>
                    <dd class="col-sm-8"><code>{{ $user->identifier ?? $user->user_id ?? '—' }}</code></dd>
                    <dt class="col-sm-4 text-muted">Email</dt>
                    <dd class="col-sm-8">{{ $user->email ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Rôle</dt>
                    <dd class="col-sm-8"><span class="badge bg-secondary">{{ $user->role }}</span></dd>
                    <dt class="col-sm-4 text-muted">Statut</dt>
                    <dd class="col-sm-8">@include('platform.schools._status-badge', ['status' => $user->status])</dd>
                    <dt class="col-sm-4 text-muted">Inscrit le</dt>
                    <dd class="col-sm-8">{{ $user->created_at?->format('d/m/Y à H:i') ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Scolarité</h6></div>
            <div class="card-body small">
                @if($user->isStudent())
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Classe</dt>
                        <dd class="col-sm-8">
                            @if($user->class)
                                <a href="{{ route('platform.schools.classes.show', [$school, $user->class]) }}">
                                    {{ $user->class->display_name }}
                                </a>
                            @else
                                <span class="text-warning">Sans classe</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4 text-muted">Année</dt>
                        <dd class="col-sm-8">@include('platform.schools._academic-year-badge', ['year' => $user->class?->academicYear])</dd>
                        @if($studentAverage !== null)
                            <dt class="col-sm-4 text-muted">Moyenne générale</dt>
                            <dd class="col-sm-8"><span class="badge bg-primary">{{ $studentAverage }}/20</span></dd>
                        @endif
                    </dl>
                @elseif(in_array($user->role, \App\Models\User::ROLE_TEACHER_ALIASES, true))
                    <p class="text-muted mb-0">{{ $user->teacherAssignments->count() }} affectation(s) classe/matière.</p>
                @else
                    <p class="text-muted mb-0">Compte administratif de l'établissement.</p>
                @endif
            </div>
        </div>
    </div>
</div>

@if(in_array($user->role, \App\Models\User::ROLE_TEACHER_ALIASES, true) && $user->teacherAssignments->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h6 class="mb-0">Affectations enseignant</h6></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Classe</th>
                        <th>Matière</th>
                        <th>Année</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->teacherAssignments as $assignment)
                        @php $assignmentClass = $assignment->schoolClass; @endphp
                        <tr data-href="{{ $assignmentClass ? route('platform.schools.classes.show', [$school, $assignmentClass]) : '#' }}" class="platform-click-row">
                            <td class="fw-semibold">{{ $assignmentClass?->display_name ?? '—' }}</td>
                            <td>{{ $assignment->subject?->name ?? '—' }}</td>
                            <td>@include('platform.schools._academic-year-badge', ['year' => $assignmentClass?->academicYear])</td>
                            <td class="text-end">
                                @if($assignmentClass)
                                    <a href="{{ route('platform.schools.classes.show', [$school, $assignmentClass]) }}" class="btn btn-sm btn-outline-primary">Classe</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.platform-click-row[data-href]').forEach(function (row) {
        if (row.dataset.href === '#') return;
        row.style.cursor = 'pointer';
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) return;
            window.location.href = row.dataset.href;
        });
    });
</script>
@endpush
