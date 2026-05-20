@extends('platform.layouts.app')

@section('title', 'Modifier — '.$school->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('platform.schools.show', $school) }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour à la fiche
    </a>
    <h1 class="h3 mt-2 mb-1">Modifier l'établissement</h1>
    <p class="text-muted mb-0">
        <strong>EduManager</strong> = nom de la plateforme ERP.
        Le champ « Nom » ci-dessous est le <strong>nom réel de l'école</strong> (visible par les utilisateurs de cet établissement).
    </p>
</div>

<form method="POST" action="{{ route('platform.schools.update', $school) }}" class="card border-0 shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-header bg-white">
        <h5 class="mb-0">Informations de l'établissement</h5>
    </div>
    <div class="card-body row g-3">
        <div class="col-12">
            <label class="form-label">Nom de l'établissement *</label>
            <input type="text"
                   name="name"
                   class="form-control form-control-lg @error('name') is-invalid @enderror"
                   value="{{ old('name', $school->name) }}"
                   placeholder="Ex : Lycée Blaise Diagne, Collège Saint-Louis…"
                   required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Remplace les libellés par défaut comme « Établissement principal ».</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email de contact</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Téléphone</label>
            <input type="text" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Ville</label>
            <input type="text" name="city" class="form-control" value="{{ old('city', $school->city) }}">
        </div>
        <div class="col-12">
            <label class="form-label">Adresse</label>
            <textarea name="address" class="form-control" rows="2">{{ old('address', $school->address) }}</textarea>
        </div>
        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $school->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Établissement actif (accès autorisé)</label>
            </div>
        </div>
        <div class="col-12">
            <p class="small text-muted mb-0">
                <strong>Code inscription :</strong> <code>{{ $school->code }}</code> — modifiable depuis la fiche établissement (régénération).
            </p>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Enregistrer les modifications
        </button>
        <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection
