@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Élèves en difficulté — Directeur')

@section('content')
<a href="{{ route('directeur.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Centre de Commande
</a>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-triangle-exclamation me-2 text-danger"></i>Élèves en difficulté</h1>
    <span class="count-chip">{{ $rows->count() }} élève(s)</span>
</div>

<div class="alert alert-warning border-0 small mb-4">
    <i class="fas fa-info-circle me-1"></i>
    Un élève apparaît ici s'il a une moyenne générale inférieure à 50 % du barème sur l'année scolaire
    {{ $academicYear->name ?? 'courante' }} (toutes matières confondues, notes déjà saisies uniquement).
    Un élève sans aucune note saisie n'apparaît ni ici ni dans le taux de réussite.
</div>

@if($rows->isEmpty())
    <div class="empty-state py-4"><i class="fas fa-check-circle"></i><p class="mb-0">Aucun élève en difficulté pour le moment.</p></div>
@else
    <div class="card people-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 people-table">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Moyenne générale</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('directeur.school.students.show', $row['student']) }}" class="person-cell">
                                        <span class="person-avatar">{{ strtoupper(substr($row['student']->name, 0, 1)) }}</span>
                                        <span class="person-name">{{ $row['student']->name }}</span>
                                    </a>
                                </td>
                                <td>{{ $row['student']->schoolClass->name ?? '—' }}</td>
                                <td>
                                    <span class="status-badge status-badge-danger">{{ $row['average'] }} / {{ $row['max_grade'] }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('directeur.school.students.show', $row['student']) }}" class="btn-view" title="Voir le profil">
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
@endif

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
