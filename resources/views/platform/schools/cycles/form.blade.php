@extends('platform.layouts.app')

@section('title', ($level->exists ? 'Modifier' : 'Nouveau') . ' cycle')

@section('content')
<div class="mb-4">
    <a href="{{ route('platform.schools.cycles.index', $school) }}" class="text-decoration-none small">
        <i class="fas fa-arrow-left me-1"></i> Retour aux cycles
    </a>
    <h1 class="h3 mt-2">{{ $level->exists ? 'Modifier le cycle' : 'Nouveau cycle' }} — {{ $school->name }}</h1>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ $level->exists ? route('platform.schools.cycles.update', [$school, $level]) : route('platform.schools.cycles.store', $school) }}">
            @csrf
            @if($level->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nom du cycle *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $level->name) }}"
                           placeholder="Année 1, Module Réseaux…" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ordre *</label>
                    <input type="number" name="order" class="form-control" min="1" value="{{ old('order', $level->order ?? 1) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Filière / spécialité</label>
                    <input type="text" name="serie" class="form-control" value="{{ old('serie', $level->serie) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $level->description) }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="{{ route('platform.schools.cycles.index', $school) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
