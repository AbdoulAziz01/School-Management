@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Ajouter un type de frais')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Ajouter un type de frais</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('directeur.fee-types.store') }}">
                    @csrf

                    <x-admin.form-field type="text" name="code" label="Code" :value="old('code')" required
                        help="Identifiant court, ex. INSCRIPTION, MENSUALITE." />
                    <x-admin.form-field type="text" name="name" label="Nom" :value="old('name')" required
                        help="Nom affiché aux caissiers/comptable, ex. « Mensualité »." />

                    <x-admin.form-field type="select" name="category" label="Catégorie" required>
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-admin.form-field>

                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_recurring" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_recurring" name="is_recurring" value="1"
                               @checked(old('is_recurring'))>
                        <label class="form-check-label" for="is_recurring">
                            Frais récurrent (dû chaque mois, ex. mensualité)
                        </label>
                    </div>

                    <x-admin.form-field type="number" name="amount" label="Montant (FCFA)" :value="old('amount')"
                        help="Montant appliqué à toutes les classes. Laissez vide pour le configurer plus tard, ou pour définir un montant différent par niveau depuis la liste des types de frais." />

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Créer
                        </button>
                        <a href="{{ route('directeur.fee-types.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
