@extends('admin.layouts.app', ['sidebarView' => 'accounting.caisse.sidebar', 'navbarView' => 'accounting.caisse.navbar'])

@section('title', 'Clôturer la caisse')

@section('content')
<div class="text-center mb-4">
    <h1 class="h3 mb-1"><i class="fas fa-lock me-2"></i>Clôturer la caisse</h1>
    <p class="text-muted mb-0">Comptez le fond de caisse réel et comparez-le au solde attendu.</p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-7">Fond de caisse à l'ouverture</dt>
            <dd class="col-5 text-end">{{ number_format($summary['opening_balance'], 0, ',', ' ') }} FCFA</dd>
            <dt class="col-7">Encaissements de la session</dt>
            <dd class="col-5 text-end">{{ number_format($summary['encaissements'], 0, ',', ' ') }} FCFA</dd>
            <dt class="col-7 fw-bold">Solde attendu</dt>
            <dd class="col-5 text-end fw-bold">{{ number_format($summary['expected_balance'], 0, ',', ' ') }} FCFA</dd>
        </dl>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('caisse.session.close') }}">
            @csrf
            <x-admin.form-field type="number" name="actual_closing_balance" label="Montant compté en caisse (FCFA)" required
                help="Comparé automatiquement au solde attendu — un écart sera enregistré si les montants diffèrent." />
            <x-admin.form-field type="textarea" name="notes" label="Remarques (optionnel)" />

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-lock me-1"></i> Clôturer
                </button>
                <a href="{{ route('caisse.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
