@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@section('title', 'Tableau de bord — Comptable')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>Comptabilité</h1>
        <p class="text-muted mb-0">Supervision des recettes, dépenses et salaires</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <a href="{{ route('comptable.payments.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <i class="fas fa-receipt fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">Paiements élèves</h5>
                    <p class="text-muted mb-0 small">Consulter, corriger ou annuler un paiement.</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('comptable.expenses.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">Dépenses</h5>
                    <p class="text-muted mb-0 small">Fournitures, factures, entretien, primes...</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('comptable.salaries.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <i class="fas fa-money-check-alt fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">Salaires</h5>
                    <p class="text-muted mb-0 small">Générer et payer les salaires du mois.</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body text-center py-4">
        <i class="fas fa-tools fa-2x text-warning mb-3"></i>
        <h5 class="mb-2">Rapports en construction</h5>
        <p class="text-muted mb-0">
            Le tableau de bord chiffré (recettes, dépenses, masse salariale...) arrive en Phase 6.5.
        </p>
    </div>
</div>
@endsection
