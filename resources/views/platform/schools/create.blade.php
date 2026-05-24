@extends('platform.layouts.app')

@section('title', 'Créer un établissement — ' . $platformName)

@section('content')
<p class="text-muted small mb-3">
    <a href="{{ route('platform.schools.index') }}" class="text-decoration-none">
        <i class="fas fa-arrow-left me-1"></i> Retour aux établissements
    </a>
</p>
<h1 class="h3 mb-2">Nouvel établissement</h1>
<p class="text-muted mb-4">Créez la fiche officielle de l'école et le compte administrateur initial.</p>

<form method="POST" action="{{ route('platform.schools.store') }}" enctype="multipart/form-data" class="card border-0 shadow-sm">
    @csrf
    <div class="card-header bg-white">
        <h5 class="mb-0">Profil officiel de l'établissement</h5>
    </div>
    <div class="card-body row g-3">
        <div class="col-12">
            <label class="form-label">Logo (optionnel)</label>
            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
        </div>

        @include('platform.schools._profile-fields', ['school' => $school, 'academicYears' => collect()])

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Activer l'accès immédiatement</label>
            </div>
        </div>
    </div>

    <div class="card-header bg-white border-top">
        <h5 class="mb-0">Administrateur de l'établissement</h5>
    </div>
    <div class="card-body row g-3">
        <div class="col-md-6">
            <label class="form-label">Nom complet *</label>
            <input type="text" name="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name') }}" required>
            @error('admin_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Email admin *</label>
            <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}" required>
            @error('admin_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Après la création, l’identifiant et le mot de passe s’afficheront sur la fiche établissement pour que vous puissiez les transmettre à l’admin (un email sera aussi envoyé si la messagerie est configurée).</div>
        </div>
    </div>

    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-primary">Créer l'établissement</button>
        <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection
