@extends('admin.layouts.app')

@section('title', 'Mon établissement')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Mon établissement</h1>
            <p class="text-muted mb-0">
                Le nom ci-dessous est celui de <strong>votre école</strong> (affiché aux élèves et enseignants).
                <strong>EduManager</strong> est le nom de la plateforme.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.school.settings.update') }}" class="card border-0 shadow-sm">
                @csrf
                @method('PUT')
                <div class="card-header bg-white">
                    <h5 class="mb-0">Identité de l'établissement</h5>
                </div>
                <div class="card-body row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Nom de l'établissement *</label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               value="{{ old('name', $school->name) }}"
                               placeholder="Ex : Collège Les Almadies, Lycée Blaise Diagne…"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ce nom remplace « Établissement principal » dans les menus et tableaux de bord.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email de l'établissement</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="city" class="form-label">Ville</label>
                        <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $school->city) }}">
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Adresse</label>
                        <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $school->address) }}</textarea>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Informations système</h6>
                    <p class="mb-1"><strong>Code d'inscription :</strong><br><code>{{ $school->code }}</code></p>
                    <p class="mb-1"><strong>Identifiant technique :</strong><br><code>{{ $school->slug }}</code></p>
                    <p class="mb-0 small text-muted">
                        Le code d'inscription est géré par l'administrateur de la plateforme (super admin).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
