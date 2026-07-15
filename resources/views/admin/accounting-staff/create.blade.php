@extends('admin.layouts.app')

@section('title', 'Ajouter un compte Comptabilité')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un compte Comptabilité</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.accounting-staff.store') }}">
                        @csrf

                        <x-admin.form-field type="select" name="role" label="Rôle" required>
                            <option value="">Sélectionner un rôle</option>
                            @foreach($roleLabels as $value => $label)
                                <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </x-admin.form-field>

                        <x-admin.form-field type="text" name="name" label="Nom complet" :value="old('name')" required />
                        <x-admin.form-field type="email" name="email" label="Adresse email" :value="old('email')" required />
                        <x-admin.form-field type="tel" name="phone" label="Téléphone" :value="old('phone')" />

                        <p class="small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Un identifiant et un mot de passe seront générés automatiquement — ils s'afficheront
                            une seule fois sur la fiche du compte après création.
                        </p>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Créer le compte
                            </button>
                            <a href="{{ route('admin.accounting-staff.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
