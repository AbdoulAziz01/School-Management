@extends('layouts.student')

@section('title', 'Quiz : ' . $quiz->title)

@push('styles')
<style>
    /* Barre fixe en haut pendant la passation */
    .quiz-topbar {
        position: sticky; top: 0; z-index: 200;
        background: #fff; border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
        padding: 12px 24px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        flex-wrap: wrap;
    }

    /* Timer */
    .quiz-timer { display:flex; align-items:center; gap:8px; border-radius:10px; padding:8px 16px; font-weight:700; font-size:1.05rem; }
    .quiz-timer.normal  { background:#fffbeb; border:1.5px solid #fed7aa; color:#d97706; }
    .quiz-timer.warning { background:#fef9c3; border:1.5px solid #fef08a; color:#ca8a04; }
    .quiz-timer.danger  { background:#fef2f2; border:1.5px solid #fecaca; color:#dc2626; animation: blink 1s step-end infinite; }
    @keyframes blink { 50% { opacity:.45; } }

    /* Barre de progression */
    .progress-bar-quiz { height:4px; border-radius:99px; background:#f1f5f9; overflow:hidden; }
    .progress-bar-quiz .fill { height:100%; background:linear-gradient(90deg,#f59e0b,#d97706); border-radius:99px; transition:width .3s; }

    /* Carte de question */
    .question-card {
        background:#fff; border:1.5px solid #e2e8f0; border-radius:14px;
        margin-bottom:20px; overflow:hidden; transition: border-color .2s, box-shadow .2s;
    }
    .question-card.answered { border-color:#86efac; box-shadow:0 0 0 3px rgba(134,239,172,.2); }
    .question-card .q-head {
        background:#f8fafc; border-bottom:1px solid #e2e8f0;
        padding:14px 20px; display:flex; align-items:flex-start; gap:12px;
    }
    .q-num { min-width:34px; height:34px; border-radius:8px; background:#fffbeb; color:#d97706;
             border:1px solid #fed7aa; font-weight:700; font-size:.9rem;
             display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .q-text { font-weight:600; font-size:.95rem; color:#1e293b; line-height:1.55; flex:1; }
    .q-pts  { white-space:nowrap; font-size:.78rem; font-weight:700; color:#94a3b8;
              background:#f1f5f9; border-radius:99px; padding:4px 10px; }

    /* Options */
    .options-list { padding:16px 20px; display:flex; flex-direction:column; gap:10px; }
    .opt-label {
        display:flex; align-items:center; gap:14px;
        padding:12px 16px; border:1.5px solid #e2e8f0; border-radius:10px;
        cursor:pointer; transition:all .15s; background:#fff; user-select:none;
    }
    .opt-label:hover { background:#fffbeb; border-color:#fbbf24; }
    .opt-label input[type="radio"] { accent-color:#d97706; width:18px; height:18px; flex-shrink:0; }
    .opt-label:has(input:checked) { background:#fffbeb; border-color:#fbbf24; font-weight:600; }
    .opt-letter { width:28px; height:28px; border-radius:8px; background:#f1f5f9; color:#475569;
                  font-weight:700; font-size:.8rem; display:flex; align-items:center;
                  justify-content:center; flex-shrink:0; }
</style>
@endpush

@section('content')
{{-- Barre supérieure --}}
<div class="quiz-topbar">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('student.quiz.show', $quiz) }}"
           class="btn btn-sm btn-outline-secondary" style="border-radius:8px;"
           title="Quitter le quiz">
            <i class="fas fa-times"></i>
        </a>
        <div>
            <div class="fw-bold" style="color:#1e293b; font-size:.95rem;">{{ $quiz->title }}</div>
            <div class="text-muted" style="font-size:.78rem;">Tentative {{ $attempt->attempt_number }}/3</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3 flex-wrap justify-content-end">
        <span class="text-muted small">
            <span id="answeredCount" class="fw-bold text-dark">0</span>
            / {{ $quiz->questions->count() }} réponses
        </span>
        <div class="progress-bar-quiz" style="width:120px;">
            <div class="fill" id="progressFill" style="width:0%;"></div>
        </div>
        @if($quiz->time_limit)
        <div class="quiz-timer normal" id="timerBox">
            <i class="fas fa-hourglass-half"></i>
            <span id="timerDisplay">{{ sprintf('%02d:%02d', $quiz->time_limit, 0) }}</span>
        </div>
        @endif
    </div>
</div>

<div class="container py-4" style="max-width:760px;">

    @foreach(['success','error'] as $t)
        @if(session($t))
            <div class="alert alert-{{ $t === 'error' ? 'danger' : $t }} alert-dismissible fade show" role="alert">
                {{ session($t) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    <form id="quizForm" action="{{ route('student.quiz.submit', $attempt) }}" method="POST"
          onsubmit="return onFormSubmit(event)">
        @csrf

        @foreach($quiz->questions as $qi => $question)
        <div class="question-card" id="qcard-{{ $question->id }}" data-qid="{{ $question->id }}">
            <div class="q-head">
                <div class="q-num">{{ $question->order }}</div>
                <div class="q-text">{{ $question->question_text }}</div>
                <div class="q-pts">{{ $question->points }} pt{{ $question->points != 1 ? 's' : '' }}</div>
            </div>
            <div class="options-list">
                @foreach($question->options as $oi => $option)
                @php $letter = chr(65 + $oi); @endphp
                <label class="opt-label">
                    <input type="radio"
                           name="answers[{{ $question->id }}]"
                           value="{{ $option->id }}"
                           onchange="onAnswer({{ $question->id }})">
                    <div class="opt-letter">{{ $letter }}</div>
                    <span class="opt-text">{{ $option->option_text }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center mt-4 mb-5 gap-3 flex-wrap">
            <a href="{{ route('student.quiz.show', $quiz) }}"
               class="btn btn-light fw-semibold" style="border-radius:10px;">
                <i class="fas fa-arrow-left me-1"></i>Quitter
            </a>
            <button type="button" class="btn btn-warning fw-bold px-5" style="border-radius:10px; font-size:1rem;"
                    onclick="confirmAndSubmit()">
                <i class="fas fa-paper-plane me-2"></i>Soumettre le quiz
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const TOTAL     = {{ $quiz->questions->count() }};
const answered  = new Set();

function onAnswer(qid) {
    const card = document.getElementById('qcard-' + qid);
    answered.add(qid);
    card.classList.add('answered');
    document.getElementById('answeredCount').textContent = answered.size;
    document.getElementById('progressFill').style.width = (answered.size / TOTAL * 100) + '%';
}

function onFormSubmit(e) {
    // Bloquer la double soumission
    document.querySelectorAll('#quizForm button[type="button"]').forEach(b => b.disabled = true);
    return true;
}

function confirmAndSubmit() {
    const left = TOTAL - answered.size;
    const msg  = left > 0
        ? `Attention : ${left} question(s) sans réponse.\n\nVoulez-vous quand même soumettre ?`
        : 'Soumettre définitivement votre quiz ?';
    if (confirm(msg)) {
        document.getElementById('quizForm').submit();
    }
}

@if($quiz->time_limit)
let remaining = {{ (int) $quiz->time_limit * 60 }};

function tick() {
    remaining--;
    const m = Math.floor(remaining / 60);
    const s = remaining % 60;
    document.getElementById('timerDisplay').textContent =
        String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');

    const box = document.getElementById('timerBox');
    box.className = 'quiz-timer';
    if (remaining <= 60)       box.classList.add('danger');
    else if (remaining <= 300) box.classList.add('warning');
    else                       box.classList.add('normal');

    if (remaining <= 0) {
        document.getElementById('quizForm').submit();
        return;
    }
    setTimeout(tick, 1000);
}
setTimeout(tick, 1000);
@endif
</script>
@endpush
