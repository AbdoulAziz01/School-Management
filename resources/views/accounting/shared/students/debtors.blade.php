@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_DIRECTEUR ? 'directeur' : 'comptable';
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Élèves débiteurs')

@section('content')
<a href="{{ route($portalPrefix . '.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-user-clock me-2"></i>Élèves débiteurs</h1>
        <p class="text-muted mb-0">{{ $debtors->count() }} élève(s) avec au moins une facture en attente</p>
    </div>
    @if($debtors->isNotEmpty())
        <div class="kpi-tile kpi-tile-static" style="min-width: 220px;">
            <div class="kpi-icon kpi-icon-red"><i class="fas fa-sack-dollar"></i></div>
            <div class="kpi-body">
                <div class="kpi-label">Montant total dû</div>
                <div class="kpi-value kpi-value-sm text-danger">{{ number_format($debtors->sum('total_due'), 0, ',', ' ') }} <span class="kpi-value-suffix">FCFA</span></div>
            </div>
        </div>
    @endif
</div>

@if($debtors->isEmpty())
    <div class="empty-state"><i class="fas fa-circle-check"></i><p class="mb-0">Aucun élève débiteur actuellement.</p></div>
@else
    @php $groups = $debtors->groupBy(fn ($row) => $row['student']->schoolClass?->name ?? 'Sans classe'); @endphp

    @foreach($groups as $className => $rows)
        @php $collapseId = 'debtors-class-'.\Illuminate\Support\Str::slug($className); @endphp
        <div class="panel-card mb-3">
            <div class="panel-card-header" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="true">
                <span><i class="fas fa-chevron-down me-2 small text-muted"></i><i class="fas fa-users me-2 text-warning"></i>{{ $className }}</span>
                <div class="d-flex gap-2">
                    <span class="class-chip">{{ $rows->count() }} élève(s)</span>
                    <span class="status-badge status-badge-danger">{{ number_format($rows->sum('total_due'), 0, ',', ' ') }} FCFA dû</span>
                </div>
            </div>
            <div class="collapse show" id="{{ $collapseId }}">
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 data-table">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Matricule</th>
                                    <th class="text-end">Montant dû</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    <tr>
                                        <td>
                                            <div class="person-cell">
                                                <span class="person-avatar">{{ strtoupper(substr($row['student']->name, 0, 1)) }}</span>
                                                <span class="person-name">{{ $row['student']->name }}</span>
                                            </div>
                                        </td>
                                        <td><code>{{ $row['student']->identifier ?? '—' }}</code></td>
                                        <td class="text-end text-danger fw-semibold">{{ number_format($row['total_due'], 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end">
                                            <a href="{{ route($portalPrefix.'.students.show', $row['student']) }}" class="btn-view" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
<style>
[data-bs-toggle="collapse"] .fa-chevron-down { transition: transform .2s ease; }
[data-bs-toggle="collapse"][aria-expanded="false"] .fa-chevron-down { transform: rotate(-90deg); }
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (header) {
    var target = document.querySelector(header.getAttribute('data-bs-target'));
    if (!target) return;
    target.addEventListener('show.bs.collapse', function () { header.setAttribute('aria-expanded', 'true'); });
    target.addEventListener('hide.bs.collapse', function () { header.setAttribute('aria-expanded', 'false'); });
});
</script>
@endpush
@endsection
