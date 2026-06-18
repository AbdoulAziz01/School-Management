{{--
    Partiel : bloc question pré-rempli depuis old() après échec de validation.
    Variables reçues : $qIdx (int), $qOld (array)
--}}
<button type="button" class="btn btn-sm btn-outline-danger btn-remove-question" onclick="removeQuestion(this)">
    <i class="fas fa-times"></i>
</button>

<div class="d-flex align-items-center gap-3 mb-3">
    <div class="question-number">{{ $qIdx + 1 }}</div>
    <input type="text"
           name="questions[{{ $qIdx }}][text]"
           class="form-control fw-semibold"
           placeholder="Rédigez votre question ici…"
           value="{{ $qOld['text'] ?? '' }}"
           required>
</div>

<div class="row g-2 mb-3">
    <div class="col-auto">
        <label class="col-form-label small fw-semibold">Points</label>
    </div>
    <div class="col-auto" style="width:110px;">
        <input type="number"
               name="questions[{{ $qIdx }}][points]"
               class="form-control form-control-sm"
               value="{{ $qOld['points'] ?? 1 }}"
               min="0.5" max="10" step="0.5" required>
    </div>
</div>

<div class="options-container d-flex flex-column gap-2 mb-3">
    @foreach($qOld['options'] ?? [] as $oIdx => $oOld)
    <div class="option-row">
        <input type="hidden"
               name="questions[{{ $qIdx }}][options][{{ $oIdx }}][correct]"
               value="0">
        <input type="checkbox"
               name="questions[{{ $qIdx }}][options][{{ $oIdx }}][correct]"
               value="1"
               class="form-check-input mt-0"
               title="Bonne réponse"
               {{ ! empty($oOld['correct']) ? 'checked' : '' }}>
        <input type="text"
               name="questions[{{ $qIdx }}][options][{{ $oIdx }}][text]"
               class="form-control form-control-sm"
               placeholder="Option…"
               value="{{ $oOld['text'] ?? '' }}"
               required>
        <button type="button"
                class="btn btn-sm btn-outline-secondary px-2 py-1"
                onclick="removeOption(this)"
                title="Supprimer"
                style="{{ count($qOld['options'] ?? []) <= 2 ? 'display:none;' : '' }}">
            <i class="fas fa-minus" style="font-size:.7rem;"></i>
        </button>
    </div>
    @endforeach
</div>

<div class="d-flex align-items-center gap-2">
    <button type="button"
            class="btn btn-outline-secondary btn-add-option btn-sm"
            onclick="addOption(this, {{ $qIdx }})">
        <i class="fas fa-plus me-1"></i>Ajouter une option
    </button>
    <small class="text-muted">Cochez la ou les bonnes réponses</small>
</div>
