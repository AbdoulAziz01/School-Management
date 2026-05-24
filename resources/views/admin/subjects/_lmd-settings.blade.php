@php
    $lmdSettings = $lmdSettings ?? \App\Support\FormationLmdSettings::defaults();
    $gradeTypes = \App\Support\FormationLmdSettings::allSelectableGradeTypes();
    $typeLabels = \App\Support\SenegalGradeSequence::LABELS;
@endphp

<div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Évaluation LMD du module</h6>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Pondération propre à ce module. Si CC et examen existent : moyenne = moyenne(CC) × CC % + moyenne(Examen) × Examen %.
            Sinon la note disponible compte à 100 %.
        </p>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="cc_weight_percent" class="form-label">Contrôle continu (CC) % <span class="text-danger">*</span></label>
                <input type="number" name="cc_weight_percent" id="cc_weight_percent"
                       class="form-control @error('cc_weight_percent') is-invalid @enderror"
                       min="0" max="100" value="{{ old('cc_weight_percent', $lmdSettings->ccWeightPercent) }}" required>
                @error('cc_weight_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label for="exam_weight_percent" class="form-label">Examen % <span class="text-danger">*</span></label>
                <input type="number" name="exam_weight_percent" id="exam_weight_percent"
                       class="form-control @error('exam_weight_percent') is-invalid @enderror"
                       min="0" max="100" value="{{ old('exam_weight_percent', $lmdSettings->examWeightPercent) }}" required>
                @error('exam_weight_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">CC + Examen = 100 %</div>
            </div>
            <div class="col-md-4">
                <label for="passing_grade_min" class="form-label">Seuil validation (/20) <span class="text-danger">*</span></label>
                <input type="number" name="passing_grade_min" id="passing_grade_min" step="0.5"
                       class="form-control @error('passing_grade_min') is-invalid @enderror"
                       min="0" max="20" value="{{ old('passing_grade_min', $lmdSettings->passingGradeMin) }}" required>
                @error('passing_grade_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <h6 class="small text-muted mb-2">Types = Contrôle continu</h6>
                @foreach($gradeTypes as $type)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="cc_grade_types[]"
                               id="cc_{{ $type }}" value="{{ $type }}"
                               {{ in_array($type, old('cc_grade_types', $lmdSettings->ccGradeTypes), true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cc_{{ $type }}">
                            {{ $typeLabels[$type] ?? $type }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="col-md-6">
                <h6 class="small text-muted mb-2">Types = Examen</h6>
                @foreach($gradeTypes as $type)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="exam_grade_types[]"
                               id="exam_{{ $type }}" value="{{ $type }}"
                               {{ in_array($type, old('exam_grade_types', $lmdSettings->examGradeTypes), true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="exam_{{ $type }}">
                            {{ $typeLabels[$type] ?? $type }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const cc = document.getElementById('cc_weight_percent');
    const exam = document.getElementById('exam_weight_percent');
    if (!cc || !exam) return;
    cc.addEventListener('input', function () {
        const v = parseInt(cc.value, 10);
        if (!isNaN(v) && v >= 0 && v <= 100) {
            exam.value = 100 - v;
        }
    });
})();
</script>
@endpush
