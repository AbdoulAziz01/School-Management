@extends('platform.layouts.app')

@section('title', 'Créer un établissement')

@section('content')
<h1 class="h3 mb-4">Nouvel établissement</h1>

<form method="POST" action="{{ route('platform.schools.store') }}" enctype="multipart/form-data" class="card border-0 shadow-sm">
    @csrf
    <div class="card-body">
        <h5 class="mb-3">Informations de l'établissement</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email contact</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Ville</label>
                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Adresse</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Logo (optionnel)</label>
                <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Activer l'accès immédiatement</label>
                </div>
            </div>
        </div>

        <hr class="my-4">
        <h5 class="mb-3">Administrateur de l'établissement</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom complet *</label>
                <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name') }}" required>
                @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Email admin *</label>
                <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}" required>
                @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Mot de passe *</label>
                <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" required>
                @error('admin_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirmer le mot de passe *</label>
                <input type="password" name="admin_password_confirmation" class="form-control" required>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-primary">Créer l'établissement</button>
        <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection
