@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Tableau de bord — Directeur')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>Tableau de bord financier</h1>
        <p class="text-muted mb-0">Pilotage financier de {{ $schoolDisplayName ?? 'votre établissement' }}</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <a href="{{ route('directeur.fee-types.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <i class="fas fa-file-invoice-dollar fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">Frais scolaires</h5>
                    <p class="text-muted mb-0 small">Types de frais et montants par niveau (inscription, mensualité...).</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a href="{{ route('directeur.salaries.index') }}" class="text-decoration-none">
            <div class="card h-100">
                <div class="card-body">
                    <i class="fas fa-money-check-alt fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">Salaires du personnel</h5>
                    <p class="text-muted mb-0 small">Enseignants, surveillants, administratifs.</p>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body text-center py-4">
        <i class="fas fa-tools fa-2x text-warning mb-3"></i>
        <h5 class="mb-2">Indicateurs financiers en construction</h5>
        <p class="text-muted mb-0">
            Le tableau de bord (solde, recettes, dépenses, élèves débiteurs...) et la caisse arrivent
            dans les prochaines étapes.
        </p>
    </div>
</div>
@endsection
