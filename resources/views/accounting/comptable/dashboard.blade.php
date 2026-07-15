@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@section('title', 'Tableau de bord — Comptable')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-book me-2"></i>Comptabilité</h1>
        <p class="text-muted mb-0">Supervision des recettes, dépenses et salaires</p>
    </div>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-tools fa-2x text-warning mb-3"></i>
        <h5 class="mb-2">Module Comptabilité en construction</h5>
        <p class="text-muted mb-0">
            Votre espace comptable est prêt. La gestion des dépenses, des salaires et des écritures
            arrive dans les prochaines étapes.
        </p>
    </div>
</div>
@endsection
