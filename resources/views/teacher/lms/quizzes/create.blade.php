@extends('teacher.layouts.app')

@section('title', 'Créer un quiz')

@push('styles')
<style>
    .question-block {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        background: #fafafa;
        position: relative;
    }
    .question-number {
        width: 32px; height: 32px;
        background: #7c3aed; color: #fff;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .9rem; flex-shrink: 0;
    }
    .option-row { display: flex; align-items: center; gap: 10px; }
    .option-row .form-check-input { width: 18px; height: 18px; flex-shrink: 0; }
    .btn-add-option { font-size: .8rem; }
    .btn-remove-question { position: absolute; top: 14px; right: 14px; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('teacher.lms.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="h3 mb-0">Créer un quiz QCM</h1>
        <p class="text-muted small mb-0">Ajoutez des questions et leurs options de réponse</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger small">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('teacher.lms.quiz.store') }}" method="POST" id="quizForm">
@csrf

{{-- Infos générales ────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informations générales</h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
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
            <div class="col-md-6">
                <label class="form-label fw-semibold">Durée (minutes) <span class="text-muted fw-normal">(facultatif)</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                    <input type="number" name="time_limit" class="form-control" value="{{ old('time_limit') }}"
                           min="1" max="180" placeholder="Ex : 20">
                    <span class="input-group-text">min</span>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Titre du quiz <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" placeholder="Ex : Quiz — Chapitre 3 : Les fractions" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description <span class="text-muted fw-normal">(facultatif)</span></label>
                <textarea name="description" class="form-control" rows="2"
                          placeholder="Instructions générales pour les élèves…">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- Questions ──────────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-ol me-2 text-purple" style="color:#7c3aed;"></i>Questions</h5>
        <button type="button" class="btn btn-primary btn-sm" id="addQuestionBtn">
            <i class="fas fa-plus me-1"></i>Ajouter une question
        </button>
    </div>
    <div class="card-body p-4">
        <div id="questionsContainer" class="d-flex flex-column gap-3">
            {{-- Questions pré-remplies (si erreur de validation, repopuler) --}}
            @if(old('questions'))
                @foreach(old('questions') as $qIdx => $qOld)
                    <div class="question-block" data-question="{{ $qIdx }}">
                        @include('teacher.lms.quizzes._question_block', ['qIdx' => $qIdx, 'qOld' => $qOld])
                    </div>
                @endforeach
            @endif
        </div>

        <div id="emptyState" class="text-center py-5 {{ old('questions') ? 'd-none' : '' }}">
            <i class="fas fa-question-circle fa-3x text-muted mb-3 opacity-50"></i>
            <p class="text-muted">Aucune question ajoutée. Cliquez sur "Ajouter une question".</p>
        </div>
    </div>
</div>

<div class="d-flex gap-3 justify-content-end mb-4">
    <a href="{{ route('teacher.lms.index') }}" class="btn btn-outline-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="fas fa-paper-plane me-2"></i>Publier le quiz
    </button>
</div>

</form>

{{-- Template HTML pour une question (cloné par JS) ────────────────────────── --}}
<template id="questionTemplate">
    <div class="question-block" data-question="__QI__">
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-question" onclick="removeQuestion(this)">
            <i class="fas fa-times"></i>
        </button>
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="question-number">__QN__</div>
            <input type="text" name="questions[__QI__][text]"
                   class="form-control fw-semibold"
                   placeholder="Rédigez votre question ici…" required>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-auto">
                <label class="col-form-label small fw-semibold">Points</label>
            </div>
            <div class="col-auto" style="width:110px;">
                <input type="number" name="questions[__QI__][points]" class="form-control form-control-sm"
                       value="1" min="0.5" max="10" step="0.5" required>
            </div>
        </div>
        <div class="options-container d-flex flex-column gap-2 mb-3">
            <div class="option-row">
                <input type="hidden"   name="questions[__QI__][options][0][correct]" value="0">
                <input type="checkbox" name="questions[__QI__][options][0][correct]" value="1"
                       class="form-check-input mt-0" title="Bonne réponse">
                <input type="text" name="questions[__QI__][options][0][text]"
                       class="form-control form-control-sm" placeholder="Option A…" required>
                <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="removeOption(this)" title="Supprimer" style="display:none;">
                    <i class="fas fa-minus" style="font-size:.7rem;"></i>
                </button>
            </div>
            <div class="option-row">
                <input type="hidden"   name="questions[__QI__][options][1][correct]" value="0">
                <input type="checkbox" name="questions[__QI__][options][1][correct]" value="1"
                       class="form-check-input mt-0" title="Bonne réponse">
                <input type="text" name="questions[__QI__][options][1][text]"
                       class="form-control form-control-sm" placeholder="Option B…" required>
                <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="removeOption(this)" title="Supprimer" style="display:none;">
                    <i class="fas fa-minus" style="font-size:.7rem;"></i>
                </button>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-add-option btn-sm" onclick="addOption(this, __QI__)">
                <i class="fas fa-plus me-1"></i>Ajouter une option
            </button>
            <small class="text-muted">Cochez la ou les bonnes réponses</small>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
let questionCount = {{ old('questions') ? count(old('questions')) : 0 }};

document.getElementById('addQuestionBtn').addEventListener('click', function () {
    addQuestion();
});

function addQuestion() {
    const container  = document.getElementById('questionsContainer');
    const emptyState = document.getElementById('emptyState');
    const template   = document.getElementById('questionTemplate').innerHTML;
    const qi         = questionCount++;
    const qn         = container.querySelectorAll('.question-block').length + 1;

    const html = template.replaceAll('__QI__', qi).replaceAll('__QN__', qn);
    const div  = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    emptyState.classList.add('d-none');
    updateRemoveButtons();
}

function removeQuestion(btn) {
    const block      = btn.closest('.question-block');
    const container  = document.getElementById('questionsContainer');
    const emptyState = document.getElementById('emptyState');
    block.remove();
    renumberQuestions();
    if (container.querySelectorAll('.question-block').length === 0) {
        emptyState.classList.remove('d-none');
    }
}

function renumberQuestions() {
    document.querySelectorAll('.question-block').forEach(function (block, i) {
        const numEl = block.querySelector('.question-number');
        if (numEl) numEl.textContent = i + 1;
    });
}

function addOption(btn, qi) {
    const container = btn.closest('.question-block').querySelector('.options-container');
    const count     = container.querySelectorAll('.option-row').length;
    if (count >= 5) return;

    const letters = ['A','B','C','D','E'];
    const div = document.createElement('div');
    div.className = 'option-row';
    div.innerHTML = `
        <input type="hidden"   name="questions[${qi}][options][${count}][correct]" value="0">
        <input type="checkbox" name="questions[${qi}][options][${count}][correct]" value="1"
               class="form-check-input mt-0" title="Bonne réponse">
        <input type="text" name="questions[${qi}][options][${count}][text]"
               class="form-control form-control-sm" placeholder="Option ${letters[count]}…" required>
        <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-1" onclick="removeOption(this)" title="Supprimer">
            <i class="fas fa-minus" style="font-size:.7rem;"></i>
        </button>
    `;
    container.appendChild(div);
    updateRemoveButtons(container);
}

function removeOption(btn) {
    const row       = btn.closest('.option-row');
    const container = row.closest('.options-container');
    if (container.querySelectorAll('.option-row').length <= 2) return;
    row.remove();
    updateRemoveButtons(container);
}

function updateRemoveButtons(container) {
    if (!container) {
        document.querySelectorAll('.options-container').forEach(updateRemoveButtons);
        return;
    }
    const rows = container.querySelectorAll('.option-row');
    rows.forEach(function (row) {
        const btn = row.querySelector('button');
        if (btn) btn.style.display = rows.length > 2 ? '' : 'none';
    });
}

document.getElementById('quizForm').addEventListener('submit', function (e) {
    const blocks = document.querySelectorAll('.question-block');
    if (blocks.length === 0) {
        e.preventDefault();
        alert('Veuillez ajouter au moins une question.');
        return;
    }
    let valid = true;
    blocks.forEach(function (block) {
        const checked = block.querySelectorAll('input[type="checkbox"]:checked').length;
        if (checked === 0) {
            valid = false;
            block.style.borderColor = '#ef4444';
            block.style.background  = '#fff5f5';
        } else {
            block.style.borderColor = '';
            block.style.background  = '';
        }
    });
    if (!valid) {
        e.preventDefault();
        alert('Chaque question doit avoir au moins une bonne réponse cochée.');
    }
});

// Init si questions restaurées depuis old()
updateRemoveButtons();
</script>
@endpush
