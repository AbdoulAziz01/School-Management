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
        <div class="text-end">
            <div class="text-muted small">Montant total dû</div>
            <div class="h4 mb-0 text-danger fw-bold">{{ number_format($debtors->sum('total_due'), 0, ',', ' ') }} FCFA</div>
        </div>
    @endif
</div>

@if($debtors->isEmpty())
    <div class="card">
        <div class="card-body">
            <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle me-1"></i> Aucun élève débiteur actuellement.
            </div>
        </div>
    </div>
@else
    @php $groups = $debtors->groupBy(fn ($row) => $row['student']->schoolClass?->name ?? 'Sans classe'); @endphp

    @foreach($groups as $className => $rows)
        @php $collapseId = 'debtors-class-'.\Illuminate\Support\Str::slug($className); @endphp
        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="true">
                <h5 class="mb-0 fs-6">
                    <i class="fas fa-chevron-down me-2 small text-muted"></i>
                    <i class="fas fa-users me-2"></i>{{ $className }}
                </h5>
                <div>
                    <span class="badge bg-secondary">{{ $rows->count() }} élève(s)</span>
                    <span class="badge bg-danger">{{ number_format($rows->sum('total_due'), 0, ',', ' ') }} FCFA dû</span>
                </div>
            </div>
            <div class="collapse show" id="{{ $collapseId }}">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
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
                                        <td>{{ $row['student']->name }}</td>
                                        <td><code>{{ $row['student']->identifier ?? '—' }}</code></td>
                                        <td class="text-end text-danger fw-semibold">{{ number_format($row['total_due'], 0, ',', ' ') }} FCFA</td>
                                        <td>
                                            <a href="{{ route($portalPrefix.'.students.show', $row['student']) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir
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
