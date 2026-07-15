@extends('accounting.layouts.caisse')

@section('title', 'Guichet — Caisse')

@section('content')
<div class="text-center mb-4">
    <h1 class="h3 mb-1"><i class="fas fa-cash-register me-2"></i>Guichet</h1>
    <p class="text-muted mb-0">Espace caissier</p>
</div>

<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-tools fa-2x text-warning mb-3"></i>
        <h5 class="mb-2">Module Comptabilité en construction</h5>
        <p class="text-muted mb-0">
            Votre accès guichet est prêt. La recherche élève/employé, l'encaissement et
            l'ouverture/clôture de caisse arrivent dans les prochaines étapes.
        </p>
    </div>
</div>
@endsection
