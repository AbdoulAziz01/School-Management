@extends('admin.layouts.app', ['sidebarView' => 'accounting.comptable.sidebar', 'navbarView' => 'accounting.comptable.navbar'])

@section('title', 'Enregistrer une dépense')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Enregistrer une dépense</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('comptable.expenses.store') }}" enctype="multipart/form-data">
                    @csrf

                    <x-admin.form-field type="select" name="category" label="Catégorie" required>
                        <option value="">Sélectionner...</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-admin.form-field>

                    <x-admin.form-field type="text" name="beneficiary" label="Bénéficiaire" :value="old('beneficiary')" required
                        help="Fournisseur, prestataire ou employé concerné." />
                    <x-admin.form-field type="text" name="motif" label="Motif" :value="old('motif')" required />
                    <x-admin.form-field type="number" name="amount" label="Montant (FCFA)" :value="old('amount')" required />
                    <x-admin.form-field type="date" name="expense_date" label="Date" :value="old('expense_date', now()->format('Y-m-d'))" required />

                    <x-admin.form-field type="select" name="payment_method" label="Mode de paiement" required>
                        <option value="">Sélectionner...</option>
                        @foreach($methods as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-admin.form-field>

                    <x-admin.form-field type="file" name="justificatif" label="Justificatif (optionnel)"
                        help="Facture, reçu fournisseur... (PDF ou image, 5 Mo max)" />

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        <a href="{{ route('comptable.expenses.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
