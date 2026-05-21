@extends('platform.layouts.app')

@section('title', 'Tableau de bord — ' . $platformName)

@push('styles')
<style>
    .platform-alert-link {
        display: block;
        text-decoration: none;
        color: inherit;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .platform-alert-link:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.25rem 0.75rem rgba(15, 23, 42, 0.08);
        color: inherit;
    }
    .platform-dashboard-table tbody tr[data-href] {
        cursor: pointer;
    }
    .platform-dashboard-table tbody tr[data-href]:hover {
        background-color: rgba(79, 70, 229, 0.05);
    }
    .platform-dashboard-table .table-actions {
        position: relative;
        z-index: 2;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="mb-0">
        <h1 class="h3 mb-1">Tableau de bord plateforme</h1>
        <p class="text-muted mb-0">Gestion multi-établissements {{ $platformName }}</p>
    </div>
    <a href="{{ route('platform.schools.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvel établissement
    </a>
</div>

@php
    $statCards = [
        ['label' => 'Établissements', 'value' => $stats['schools_total'], 'class' => ''],
        ['label' => 'Actifs', 'value' => $stats['schools_active'], 'class' => 'text-success'],
        ['label' => 'Inactifs', 'value' => $stats['schools_inactive'], 'class' => $stats['schools_inactive'] ? 'text-warning' : ''],
        ['label' => 'Utilisateurs', 'value' => $stats['users_total'], 'class' => ''],
        ['label' => 'Élèves', 'value' => $stats['students_total'], 'class' => ''],
        ['label' => 'Enseignants', 'value' => $stats['teachers_total'], 'class' => ''],
        ['label' => 'Staff (admin/surv.)', 'value' => $stats['staff_total'], 'class' => ''],
        ['label' => 'Inscriptions en attente', 'value' => $stats['pending_registrations'], 'class' => $stats['pending_registrations'] ? 'text-danger' : ''],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach($statCards as $card)
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="text-muted small">{{ $card['label'] }}</div>
                    <div class="h4 mb-0 {{ $card['class'] }}">{{ $card['value'] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@if($stats['schools_without_admin'] || $stats['schools_without_current_year'] || $stats['unassigned_students'])
<div class="row g-3 mb-4">
    @if($stats['schools_without_admin'] > 0)
        <div class="col-md-4">
            <a href="{{ route('platform.schools.index', ['status' => 'no_admin']) }}" class="platform-alert-link alert alert-danger mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>{{ $stats['schools_without_admin'] }}</strong> établissement(s) sans administrateur.</span>
                <i class="fas fa-arrow-right ms-auto small opacity-75"></i>
            </a>
        </div>
    @endif
    @if($stats['schools_without_current_year'] > 0)
        <div class="col-md-4">
            <a href="{{ route('platform.schools.index', ['status' => 'no_current_year']) }}" class="platform-alert-link alert alert-warning mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-calendar-times"></i>
                <span><strong>{{ $stats['schools_without_current_year'] }}</strong> établissement(s) actif(s) sans année scolaire courante.</span>
                <i class="fas fa-arrow-right ms-auto small opacity-75"></i>
            </a>
        </div>
    @endif
    @if($stats['unassigned_students'] > 0)
        <div class="col-md-4">
            <a href="{{ route('platform.schools.index') }}" class="platform-alert-link alert alert-info mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-user-slash"></i>
                <span><strong>{{ $stats['unassigned_students'] }}</strong> élève(s) sans classe (toutes écoles).</span>
                <i class="fas fa-arrow-right ms-auto small opacity-75"></i>
            </a>
        </div>
    @endif
</div>
@endif

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Derniers établissements</h5>
                <a href="{{ route('platform.schools.index') }}" class="btn btn-sm btn-link text-decoration-none">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0 platform-dashboard-table">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Élèves</th>
                            <th>Profs</th>
                            <th>En attente</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSchools as $school)
                            <tr data-href="{{ route('platform.schools.show', $school) }}">
                                <td class="fw-semibold">{{ $school->name }}</td>
                                <td>{{ $school->students_count }}</td>
                                <td>{{ $school->teachers_count }}</td>
                                <td>
                                    @if($school->pending_count > 0)
                                        <span class="badge bg-danger">{{ $school->pending_count }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($school->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td class="text-end table-actions">
                                    <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Aucun établissement.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Établissements à surveiller</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0 platform-dashboard-table">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Problème</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($watchlist['schools'] as $school)
                            @php
                                $issues = [];
                                if ($school->admins_count < 1) {
                                    $issues[] = 'Sans admin';
                                }
                                if ($school->pending_count > 0) {
                                    $issues[] = $school->pending_count . ' en attente';
                                }
                                if (! $school->is_active) {
                                    $issues[] = 'Inactif';
                                }
                                if ($school->is_active && empty($watchlist['years'][$school->id] ?? null)) {
                                    $issues[] = 'Sans année courante';
                                }
                            @endphp
                            <tr data-href="{{ route('platform.schools.show', $school) }}">
                                <td>{{ $school->name }}</td>
                                <td>
                                    @foreach($issues as $issue)
                                        <span class="badge bg-warning text-dark me-1">{{ $issue }}</span>
                                    @endforeach
                                </td>
                                <td class="text-end table-actions">
                                    <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-sm btn-outline-secondary">Gérer</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="fas fa-check-circle text-success me-1"></i> Aucune alerte particulière.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.platform-dashboard-table tbody tr[data-href]').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('a, button')) {
                return;
            }

            window.location.href = row.dataset.href;
        });

        row.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            window.location.href = row.dataset.href;
        });

        row.setAttribute('tabindex', '0');
        row.setAttribute('role', 'link');
    });
</script>
@endpush
