@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Tableau de bord — Directeur')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-chart-line me-2"></i>Tableau de bord financier</h1>
        <p class="text-muted mb-0">Pilotage financier de {{ $schoolDisplayName ?? 'votre établissement' }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-tools fa-2x text-warning mb-3"></i>
        <h5 class="mb-2">Module Comptabilité en construction</h5>
        <p class="text-muted mb-0">
            Votre espace directeur est prêt. Le paramétrage des frais/salaires et les indicateurs
            financiers (solde, recettes, dépenses, élèves débiteurs...) arrivent dans les prochaines étapes.
        </p>
    </div>
</div>
@endsection
