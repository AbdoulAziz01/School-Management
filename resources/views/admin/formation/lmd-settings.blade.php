@extends('admin.layouts.app')

@section('title', 'Paramètres LMD')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Modèle LMD par défaut</h1>
        <p class="text-muted mb-0">
            Valeurs proposées à la création d'un nouveau module.
            La pondération CC / Examen se configure sur chaque module dans
            <a href="{{ route('admin.subjects.index') }}">Modules</a>.
            Modèle actuel : <strong>{{ $settings->formulaLabel() }}</strong>
        </p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.formation.lmd-settings.update') }}" class="card border-0 shadow-sm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <label for="cc_weight_percent" class="form-label">Contrôle continu (CC) %</label>
                    <input type="number" name="cc_weight_percent" id="cc_weight_percent"
                           class="form-control @error('cc_weight_percent') is-invalid @enderror"
                           min="0" max="100" value="{{ old('cc_weight_percent', $settings->ccWeightPercent) }}" required>
                    @error('cc_weight_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="exam_weight_percent" class="form-label">Examen %</label>
                    <input type="number" name="exam_weight_percent" id="exam_weight_percent"
                           class="form-control @error('exam_weight_percent') is-invalid @enderror"
                           min="0" max="100" value="{{ old('exam_weight_percent', $settings->examWeightPercent) }}" required>
                    @error('exam_weight_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">CC + Examen = 100 %</div>
                </div>
                <div class="col-md-4">
                    <label for="passing_grade_min" class="form-label">Seuil validation module (/20)</label>
                    <input type="number" name="passing_grade_min" id="passing_grade_min" step="0.5"
                           class="form-control @error('passing_grade_min') is-invalid @enderror"
                           min="0" max="20" value="{{ old('passing_grade_min', $settings->passingGradeMin) }}" required>
                    @error('passing_grade_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="mb-3">Types de notes = Contrôle continu</h6>
                    @foreach($gradeTypes as $type)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="cc_grade_types[]"
                                   id="cc_{{ $type }}" value="{{ $type }}"
                                   {{ in_array($type, old('cc_grade_types', $settings->ccGradeTypes), true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cc_{{ $type }}">
                                {{ $typeLabels[$type] ?? $type }} <code class="small">{{ $type }}</code>
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3">Types de notes = Examen</h6>
                    @foreach($gradeTypes as $type)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="exam_grade_types[]"
                                   id="exam_{{ $type }}" value="{{ $type }}"
                                   {{ in_array($type, old('exam_grade_types', $settings->examGradeTypes), true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="exam_{{ $type }}">
                                {{ $typeLabels[$type] ?? $type }} <code class="small">{{ $type }}</code>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="alert alert-light border mt-4 mb-0 small">
                <strong>Règle :</strong> si CC et Examen existent pour un module → moyenne =
                moyenne(CC) × {{ $settings->ccWeightPercent }} % + moyenne(Examen) × {{ $settings->examWeightPercent }} %.
                Sinon, la note disponible compte à <strong>100 %</strong> (pas de 30/0 artificiel).
            </div>
        </div>
        <div class="card-footer bg-white">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
