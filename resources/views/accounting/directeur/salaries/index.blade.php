@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Salaires du personnel')

@php
    $roleLabels = ['teacher' => 'Enseignant', 'professeur' => 'Enseignant', 'surveillant' => 'Surveillant', 'admin' => 'Administrateur', 'comptable' => 'Comptable', 'caissier' => 'Caissier', 'directeur' => 'Directeur'];
    $roleGroupLabels = ['teachers' => 'Enseignants', 'surveillants' => 'Surveillants', 'admin' => 'Administration', 'accounting' => 'Comptables & Caissiers', 'direction' => 'Direction'];
@endphp

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-money-check-alt me-2"></i>Salaires du personnel @if($roleGroup && isset($roleGroupLabels[$roleGroup])) — {{ $roleGroupLabels[$roleGroup] }} @endif</h1>
    <p class="text-muted mb-0">Direction, enseignants, surveillants, administratifs, comptables et caissiers</p>
</div>

{{-- Filtre rapide par catégorie de personnel (miroir des liens de la barre latérale) --}}
<div class="filter-pills mb-3">
    <a href="{{ route('directeur.salaries.index', array_filter(['search' => request('search')])) }}" class="filter-pill {{ ! $roleGroup ? 'is-active' : '' }}">Tous</a>
    @foreach($roleGroupLabels as $group => $label)
        <a href="{{ route('directeur.salaries.index', array_filter(['role_group' => $group, 'search' => request('search')])) }}"
           class="filter-pill {{ $roleGroup === $group ? 'is-active' : '' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('directeur.salaries.index') }}" class="search-bar mb-4">
    @if($roleGroup)
        <input type="hidden" name="role_group" value="{{ $roleGroup }}">
    @endif
    <div class="search-field">
        <i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
    </div>
    <button type="submit" class="btn-pill-primary"><i class="fas fa-search"></i>Rechercher</button>
    @if(request('search'))
        <a href="{{ route('directeur.salaries.index', ['role_group' => $roleGroup]) }}" class="btn-pill-outline"><i class="fas fa-times"></i>Effacer</a>
    @endif
</form>

@if($employees->isEmpty())
    <div class="empty-state"><i class="fas fa-users"></i><p class="mb-0">Aucun employé trouvé.</p></div>
@else
    <div class="card data-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Rôle</th>
                            <th>Salaire mensuel actuel</th>
                            <th style="min-width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            @php $profile = $currentSalaries[$employee->id] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $employee->name }}</span>
                                    </div>
                                </td>
                                <td><span class="status-badge status-badge-neutral">{{ $roleLabels[$employee->role] ?? $employee->role }}</span></td>
                                <td>
                                    @if($profile)
                                        <strong>{{ number_format($profile->monthly_amount, 0, ',', ' ') }} FCFA</strong>
                                    @else
                                        <span class="text-muted">Non défini</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('directeur.salaries.edit', $employee) }}" class="btn-pill-outline">
                                        <i class="fas fa-edit"></i>{{ $profile ? 'Modifier' : 'Définir' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 d-flex justify-content-center">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
