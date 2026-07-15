@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Salaires du personnel')

@php
    $roleLabels = ['teacher' => 'Enseignant', 'professeur' => 'Enseignant', 'surveillant' => 'Surveillant', 'admin' => 'Administrateur'];
    $roleGroupLabels = ['teachers' => 'Enseignants', 'surveillants' => 'Surveillants', 'admin' => 'Administration'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-money-check-alt me-2"></i>Salaires du personnel @if($roleGroup && isset($roleGroupLabels[$roleGroup])) — {{ $roleGroupLabels[$roleGroup] }} @endif</h1>
        <p class="text-muted mb-0">Enseignants, surveillants et administratifs</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('directeur.salaries.index') }}" class="d-flex gap-2">
            @if($roleGroup)
                <input type="hidden" name="role_group" value="{{ $roleGroup }}">
            @endif
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}" style="max-width: 260px;">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
            @if(request('search'))
                <a href="{{ route('directeur.salaries.index', ['role_group' => $roleGroup]) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
            @endif
        </form>
    </div>
    <div class="card-body">
        @if($employees->isEmpty())
            <div class="alert alert-info mb-0">Aucun employé trouvé.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nom</th>
                            <th>Rôle</th>
                            <th>Salaire mensuel actuel</th>
                            <th style="min-width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            @php $profile = $currentSalaries[$employee->id] ?? null; @endphp
                            <tr>
                                <td>{{ $employee->name }}</td>
                                <td><span class="badge bg-secondary">{{ $roleLabels[$employee->role] ?? $employee->role }}</span></td>
                                <td>
                                    @if($profile)
                                        <strong>{{ number_format($profile->monthly_amount, 0, ',', ' ') }} FCFA</strong>
                                    @else
                                        <span class="text-muted">Non défini</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('directeur.salaries.edit', $employee) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> {{ $profile ? 'Modifier' : 'Définir' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4 d-flex justify-content-center">
                    {{ $employees->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
