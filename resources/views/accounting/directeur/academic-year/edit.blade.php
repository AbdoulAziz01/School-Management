@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Année scolaire')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-calendar-alt me-2"></i>Année scolaire — {{ $academicYear->name }}</h1>
    <p class="text-muted mb-0">Ces dates déterminent les mois de mensualité générés automatiquement pour chaque élève.</p>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="panel-card">
            <div class="panel-card-header"><i class="fas fa-calendar-check me-2 text-warning"></i>Dates de l'année courante</div>
            <div class="p-4">
                <div class="alert alert-warning border-0 small mb-4">
                    <i class="fas fa-info-circle me-1"></i>
                    Modifier ces dates régénère automatiquement les mensualités manquantes pour tous les élèves de l'établissement (aucune facture existante n'est supprimée ni dupliquée).
                </div>

                <form method="POST" action="{{ route('directeur.academic-year.update') }}">
                    @csrf
                    @method('PUT')

                    <x-admin.form-field type="date" name="start_date" label="Date de début"
                        :value="old('start_date', $academicYear->start_date?->format('Y-m-d'))" required
                        help="Premier jour de l'année scolaire — c'est ce mois qui sert de point de départ pour les mensualités." />

                    <x-admin.form-field type="date" name="end_date" label="Date de fin"
                        :value="old('end_date', $academicYear->end_date?->format('Y-m-d'))" required
                        help="Dernier mois facturé en mensualité. Doit être postérieure à la date de début." />

                    <button type="submit" class="btn-pill-primary">
                        <i class="fas fa-save"></i>Enregistrer et régénérer les mensualités
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel-card">
            <div class="panel-card-header"><i class="fas fa-circle-info me-2 text-warning"></i>Ce que ces dates changent</div>
            <div class="p-4 small text-muted">
                <p class="mb-2"><i class="fas fa-check text-success me-1"></i>Une facture de mensualité est générée pour chaque mois compris entre la date de début et la date de fin, pour chaque élève inscrit.</p>
                <p class="mb-2"><i class="fas fa-check text-success me-1"></i>Toutes les mensualités de l'année apparaissent dès l'inscription de l'élève : un caissier peut donc encaisser à l'avance un ou plusieurs mois futurs, ou l'année complète en une seule fois.</p>
                <p class="mb-0"><i class="fas fa-triangle-exclamation text-warning me-1"></i>La création, la clôture et la réouverture d'une année scolaire restent gérées par l'administrateur de l'établissement (menu Admin → Années scolaires).</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
@include('accounting.directeur.partials.design-system')
@endpush
@endsection
