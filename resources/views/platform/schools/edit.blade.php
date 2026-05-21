@extends('platform.layouts.app')

@section('title', 'Modifier — ' . $school->name . ' — ' . $platformName)

@section('content')
<div class="mb-4">
    <a href="{{ route('platform.schools.show', $school) }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour à la fiche
    </a>
    <h1 class="h3 mt-2 mb-1">Fiche établissement — {{ $school->name }}</h1>
    <p class="text-muted mb-0">
        En tant que <strong>super administrateur</strong>, vous gérez ici la fiche officielle complète de l'établissement
        (identité, direction, localisation, paramètres).
    </p>
</div>

<form method="POST" action="{{ route('platform.schools.update', $school) }}" enctype="multipart/form-data" class="card border-0 shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-school me-2"></i>Profil officiel de l'établissement</h5>
    </div>
    <div class="card-body row g-3">
        <div class="col-12">
            <label class="form-label">Logo de l'établissement</label>
            @if($school->logo_data)
                <div class="mb-2"><img src="{{ \App\Support\SchoolLogoStorage::dataUri($school) }}" alt="Logo" style="height:56px;" class="border rounded"></div>
            @endif
            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp,image/svg+xml">
            @if($school->logo_data)
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                    <label class="form-check-label" for="remove_logo">Supprimer le logo</label>
                </div>
            @endif
        </div>

        @include('platform.schools._profile-fields', ['school' => $school, 'academicYears' => $academicYears])

        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $school->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Établissement actif (accès autorisé)</label>
            </div>
        </div>
        <div class="col-12">
            <p class="small text-muted mb-0">
                <strong>Code inscription :</strong> <code>{{ $school->code }}</code> — régénérable depuis la fiche établissement.
            </p>
        </div>
    </div>
    <div class="card-footer bg-white d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Enregistrer la fiche
        </button>
        <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
@endsection
