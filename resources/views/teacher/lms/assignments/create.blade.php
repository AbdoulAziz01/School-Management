@extends('teacher.layouts.app')

@section('title', 'Créer un devoir')

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('teacher.lms.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="h3 mb-0">Créer un devoir</h1>
        <p class="text-muted small mb-0">Définissez les consignes et la date limite</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger small">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body p-4">
        <form action="{{ route('teacher.lms.assignment.store') }}" method="POST">
            @csrf

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matière <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Titre du devoir <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" placeholder="Ex : Devoir maison — Chapitre 2" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description <span class="text-muted fw-normal">(facultatif)</span></label>
                <textarea name="description" class="form-control" rows="2"
                          placeholder="Présentation générale du devoir…">{{ old('description') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Consignes détaillées</label>
                <textarea name="instructions" class="form-control @error('instructions') is-invalid @enderror" rows="4"
                          placeholder="Décrivez précisément ce que l'élève doit faire, les critères d'évaluation…">{{ old('instructions') }}</textarea>
                @error('instructions')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date limite <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="due_date"
                           class="form-control @error('due_date') is-invalid @enderror"
                           value="{{ old('due_date') }}" required
                           min="{{ now()->addDay()->format('Y-m-d\TH:i') }}">
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Barème (points) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="points" class="form-control @error('points') is-invalid @enderror"
                               value="{{ old('points', 20) }}" min="1" max="100" required>
                        <span class="input-group-text">pts</span>
                    </div>
                    @error('points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end">
                <a href="{{ route('teacher.lms.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-paper-plane me-2"></i>Publier le devoir
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
