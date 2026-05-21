@extends('admin.layouts.app')

@section('title', ($level->exists ? 'Modifier' : 'Nouveau') . ' cycle')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.cycles.index') }}" class="text-decoration-none small">
            <i class="fas fa-arrow-left me-1"></i> Retour aux cycles
        </a>
        <h1 class="h3 mt-2">{{ $level->exists ? 'Modifier le cycle' : 'Nouveau cycle de formation' }}</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ $level->exists ? route('admin.cycles.update', $level) : route('admin.cycles.store') }}">
                @csrf
                @if($level->exists) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="name" class="form-label">Nom du cycle *</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $level->name) }}"
                               placeholder="Ex. : Année 1, Année 2, Module Comptabilité" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Séparez clairement les étapes du parcours de formation.</small>
                    </div>
                    <div class="col-md-4">
                        <label for="order" class="form-label">Ordre *</label>
                        <input type="number" id="order" name="order" min="1" max="99"
                               class="form-control @error('order') is-invalid @enderror"
                               value="{{ old('order', $level->order ?? 1) }}" required>
                        @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="serie" class="form-label">Filière / spécialité (optionnel)</label>
                        <input type="text" id="serie" name="serie" class="form-control"
                               value="{{ old('serie', $level->serie) }}"
                               placeholder="Ex. : Informatique, Gestion, BTP">
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description du cycle</label>
                        <textarea id="description" name="description" class="form-control" rows="3"
                                  placeholder="Objectifs, durée, prérequis…">{{ old('description', $level->description) }}</textarea>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                    <a href="{{ route('admin.cycles.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
