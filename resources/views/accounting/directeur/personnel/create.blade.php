@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Ajouter un compte')

@section('content')
<div class="mb-4">
    <a href="{{ route('directeur.personnel.index') }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour à Personnel & Élèves
    </a>
    <h1 class="h3 mt-2 mb-0"><i class="fas fa-user-plus me-2"></i>Ajouter un compte</h1>
    <p class="text-muted mb-0">Comptable ou caissier — rattaché à votre établissement.</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('directeur.personnel.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Rôle *</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="" disabled @selected(!old('role'))>— Choisir —</option>
                            <option value="comptable" @selected(old('role') === 'comptable')>Comptable</option>
                            <option value="caissier" @selected(old('role') === 'caissier')>Caissier</option>
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nom complet *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <p class="small text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Un identifiant et un mot de passe seront générés automatiquement — ils s'afficheront
                        une seule fois après création, sur la liste des comptes.
                    </p>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Créer le compte
                        </button>
                        <a href="{{ route('directeur.personnel.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
