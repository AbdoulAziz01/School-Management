@php
    $portalPrefix = auth()->user()->role === \App\Models\User::ROLE_DIRECTEUR ? 'directeur' : 'comptable';
@endphp
@extends('admin.layouts.app', ['sidebarView' => "accounting.{$portalPrefix}.sidebar", 'navbarView' => "accounting.{$portalPrefix}.navbar"])

@section('title', 'Élèves débiteurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-user-clock me-2"></i>Élèves débiteurs</h1>
        <p class="text-muted mb-0">{{ $debtors->count() }} élève(s) avec au moins une facture en attente</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($debtors->isEmpty())
            <div class="alert alert-success mb-0">
                <i class="fas fa-check-circle me-1"></i> Aucun élève débiteur actuellement.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Élève</th>
                            <th>Classe</th>
                            <th>Matricule</th>
                            <th class="text-end">Montant dû</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($debtors as $row)
                            <tr>
                                <td>{{ $row['student']->name }}</td>
                                <td>{{ $row['student']->schoolClass?->name ?? '—' }}</td>
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
        @endif
    </div>
</div>
@endsection
