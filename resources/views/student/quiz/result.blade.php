@extends('layouts.student')

@section('title', 'Résultat — ' . $quiz->title)

@push('styles')
<style>
    /* Cercle de score */
    .score-ring {
        width:140px; height:140px; border-radius:50%;
        display:flex; flex-direction:column; align-items:center; justify-content:center;
        font-size:2.5rem; font-weight:900; line-height:1;
        margin:0 auto; border:6px solid;
    }
    .score-ring .pct  { font-size:.85rem; font-weight:600; opacity:.75; margin-top:2px; }

    /* Couleurs selon note */
    .grade-A { border-color:#86efac; background:#f0fdf4; color:#16a34a; }
    .grade-B { border-color:#93c5fd; background:#eff6ff; color:#1d4ed8; }
    .grade-C { border-color:#fcd34d; background:#fffbeb; color:#d97706; }
    .grade-D { border-color:#d1d5db; background:#f8fafc; color:#64748b; }
    .grade-F { border-color:#fca5a5; background:#fef2f2; color:#dc2626; }

    /* Carte de révision question */
    .review-card { border-radius:12px; overflow:hidden; margin-bottom:14px; border:1.5px solid #e2e8f0; }
    .review-card .r-head { padding:12px 18px; display:flex; align-items:flex-start; gap:12px; border-bottom:1px solid #e2e8f0; }
    .review-card.correct .r-head  { background:#f0fdf4; border-bottom-color:#bbf7d0; }
    .review-card.wrong .r-head    { background:#fef2f2; border-bottom-color:#fecaca; }
    .review-card.skipped .r-head  { background:#f8fafc; }

    .r-icon { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.75rem; }
    .r-icon.correct { background:#16a34a; color:#fff; }
    .r-icon.wrong   { background:#dc2626; color:#fff; }
    .r-icon.skipped { background:#94a3b8; color:#fff; }

    .opt-row { display:flex; align-items:center; gap:10px; padding:10px 18px; font-size:.9rem; border-bottom:1px solid #f1f5f9; }
    .opt-row:last-child { border-bottom:none; }
    .opt-row.is-correct       { background:#f0fdf4; color:#15803d; font-weight:600; }
    .opt-row.is-selected-wrong { background:#fef2f2; color:#dc2626; }
</style>
@endpush

@section('content')
<div class="container" style="max-width:820px;">

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('student.lms.index') }}" class="text-warning text-decoration-none">E-Learning</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('student.quiz.show', $quiz) }}" class="text-warning text-decoration-none">
                    {{ $quiz->title }}
                </a>
            </li>
            <li class="breadcrumb-item active">Résultats</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Résumé --}}
    <div class="card border-0 shadow-sm mb-5" style="border-radius:16px; overflow:hidden;">
        <div class="card-body p-4">
            <div class="row align-items-center g-4">
                <div class="col-md-4 text-center">
                    @php
                        $letter = $attempt->gradeLetter();
                        $pct    = $attempt->percentage();
                    @endphp
                    <div class="score-ring grade-{{ $letter }}">
                        {{ $pct }}%
                        <div class="pct">{{ $letter }}</div>
                    </div>
                    <div class="mt-3 text-muted small">
                        {{ number_format((float)$attempt->score, 1) }}
                        / {{ number_format((float)$attempt->max_score, 1) }} pts
                    </div>
                </div>

                <div class="col-md-8">
                    <h2 class="fw-bold mb-1" style="color:#1e293b;">{{ $quiz->title }}</h2>
                    <p class="text-muted mb-3">
                        <i class="fas fa-book me-1"></i>{{ $quiz->subject->name ?? '—' }}
                        &nbsp;·&nbsp;
                        <i class="fas fa-redo me-1"></i>Tentative {{ $attempt->attempt_number }}/3
                        &nbsp;·&nbsp;
                        <i class="fas fa-calendar me-1"></i>{{ $attempt->completed_at->format('d/m/Y H:i') }}
                    </p>

                    @php
                        $correctCount = 0;
                        foreach ($quiz->questions as $q) {
                            $sel = isset($attempt->answers[(string)$q->id]) ? (int)$attempt->answers[(string)$q->id] : null;
                            $co  = $q->options->firstWhere('is_correct', true);
                            if ($sel && $co && $sel === $co->id) $correctCount++;
                        }
                        $wrongCount   = $quiz->questions->count() - $correctCount - ($quiz->questions->count() - collect($attempt->answers)->filter()->count() - $correctCount);
                        $skippedCount = $quiz->questions->count() - collect($attempt->answers)->filter()->count();
                    @endphp

                    <div class="row g-2 mb-4">
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center" style="background:#f0fdf4;">
                                <div class="fw-bold fs-5 text-success">{{ $correctCount }}</div>
                                <div class="small text-muted">Correctes</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center" style="background:#fef2f2;">
                                <div class="fw-bold fs-5 text-danger">
                                    {{ $quiz->questions->count() - $correctCount - $skippedCount }}
                                </div>
                                <div class="small text-muted">Incorrectes</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 rounded-3 text-center" style="background:#f8fafc;">
                                <div class="fw-bold fs-5 text-secondary">{{ $skippedCount }}</div>
                                <div class="small text-muted">Passées</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('student.quiz.show', $quiz) }}"
                           class="btn btn-outline-secondary" style="border-radius:10px;">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                        @if($canRetry)
                            <form action="{{ route('student.quiz.start', $quiz) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning fw-bold" style="border-radius:10px;">
                                    <i class="fas fa-redo me-1"></i>
                                    Refaire
                                    <span class="badge bg-dark ms-1">{{ $remainingAttempts }} restante{{ $remainingAttempts > 1 ? 's' : '' }}</span>
                                </button>
                            </form>
                        @else
                            <span class="btn btn-secondary disabled" style="border-radius:10px;">
                                <i class="fas fa-ban me-1"></i>Tentatives épuisées
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Correction détaillée --}}
    <h5 class="fw-bold mb-3" style="color:#1e293b;">
        <i class="fas fa-list-check me-2 text-warning"></i>Correction détaillée
    </h5>

    @foreach($quiz->questions as $question)
        @php
            $selectedId    = isset($attempt->answers[(string)$question->id]) ? (int)$attempt->answers[(string)$question->id] : null;
            $correctOption = $question->options->firstWhere('is_correct', true);
            $isCorrect     = $selectedId !== null && $correctOption && $selectedId === $correctOption->id;
            $isSkipped     = $selectedId === null;
            $statusClass   = $isSkipped ? 'skipped' : ($isCorrect ? 'correct' : 'wrong');
        @endphp
        <div class="review-card {{ $statusClass }}">
            <div class="r-head">
                <div class="r-icon {{ $statusClass }}">
                    @if($isCorrect)   <i class="fas fa-check"></i>
                    @elseif($isSkipped) <i class="fas fa-minus"></i>
                    @else             <i class="fas fa-times"></i>
                    @endif
                </div>
                <div style="flex:1;">
                    <div class="fw-semibold" style="color:#1e293b;">{{ $question->question_text }}</div>
                    <div class="small mt-1">
                        @if($isCorrect)
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Correct · +{{ $question->points }} pt{{ $question->points != 1 ? 's' : '' }}</span>
                        @elseif($isSkipped)
                            <span class="text-muted"><i class="fas fa-minus-circle me-1"></i>Non répondu · 0 pt</span>
                        @else
                            <span class="text-danger"><i class="fas fa-times-circle me-1"></i>Incorrect · 0 pt</span>
                        @endif
                    </div>
                </div>
                <span class="text-muted small">Q{{ $question->order }}</span>
            </div>
            <div>
                @foreach($question->options as $option)
                    @php
                        $isSel     = $selectedId === $option->id;
                        $optClass  = $option->is_correct ? 'is-correct' : ($isSel ? 'is-selected-wrong' : '');
                    @endphp
                    <div class="opt-row {{ $optClass }}">
                        @if($option->is_correct)
                            <i class="fas fa-check-circle text-success"></i>
                        @elseif($isSel)
                            <i class="fas fa-times-circle text-danger"></i>
                        @else
                            <i class="far fa-circle text-muted"></i>
                        @endif
                        <span>{{ $option->option_text }}</span>
                        @if($isSel && $option->is_correct)
                            <span class="badge bg-success-subtle text-success ms-auto">Votre réponse ✓</span>
                        @elseif($isSel)
                            <span class="badge bg-danger-subtle text-danger ms-auto">Votre réponse</span>
                        @elseif($option->is_correct)
                            <span class="badge bg-success-subtle text-success ms-auto">Bonne réponse</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="text-center my-5">
        <a href="{{ route('student.lms.index') }}" class="btn btn-light fw-semibold" style="border-radius:10px;">
            <i class="fas fa-graduation-cap me-2"></i>Retour à l'espace E-Learning
        </a>
    </div>

</div>
@endsection
