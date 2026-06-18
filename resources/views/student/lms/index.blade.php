@extends('layouts.student')

@section('title', 'Espace E-Learning')

@push('styles')
<style>
    /* ── Onglets ───────────────────────────────────────────────── */
    .lms-tabs .nav-link { border-radius: 10px 10px 0 0; font-weight: 600; color: #78716c; padding: 10px 20px; }
    .lms-tabs .nav-link.active { background: #fff; color: #d97706; border-color: #e2e8f0 #e2e8f0 #fff; }

    /* ── Accordion matière ──────────────────────────────────────── */
    .subject-accordion .accordion-button {
        background: #fff;
        font-weight: 700;
        font-size: .95rem;
        color: #1e293b;
        border-radius: 12px !important;
        box-shadow: none !important;
        padding: 14px 18px;
    }
    .subject-accordion .accordion-button:not(.collapsed) {
        background: linear-gradient(90deg, #fffbeb, #fff7ed);
        color: #d97706;
        border-bottom: 1px solid #fed7aa;
        border-radius: 12px 12px 0 0 !important;
    }
    .subject-accordion .accordion-button::after { filter: none; }
    .subject-accordion .accordion-button:not(.collapsed)::after {
        filter: invert(55%) sepia(90%) saturate(500%) hue-rotate(5deg);
    }
    .subject-accordion .accordion-item {
        border: 1px solid #e2e8f0;
        border-radius: 12px !important;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .subject-accordion .accordion-body { padding: 0; }

    /* ── Ligne de document ─────────────────────────────────────── */
    .doc-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 18px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
    }
    .doc-row:last-child { border-bottom: none; }
    .doc-row:hover { background: #fafafa; }

    .doc-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .doc-icon-pdf   { background:#fee2e2; color:#b91c1c; }
    .doc-icon-doc   { background:#dbeafe; color:#1d4ed8; }
    .doc-icon-video { background:#dcfce7; color:#15803d; }
    .doc-icon-link  { background:#f0fdf4; color:#0d9488; }

    .doc-info { flex: 1; min-width: 0; }
    .doc-title { font-weight: 600; font-size: .9rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .doc-meta  { font-size: .75rem; color: #94a3b8; margin-top: 2px; }

    .doc-btn { padding: 5px 14px; font-size: .8rem; border-radius: 8px; white-space: nowrap; flex-shrink: 0; }

    /* ── Compteur badge dans le header accordion ─────────────── */
    .subject-count { font-size: .72rem; font-weight: 700; padding: 2px 8px;
                     background: #fff7ed; color: #d97706; border-radius: 99px;
                     border: 1px solid #fed7aa; }

    /* ── Devoirs & Quiz (inchangés) ────────────────────────────── */
    .assignment-row { border-radius: 12px; border: 1px solid #e2e8f0; padding: 14px 18px; background: #fff; transition: box-shadow .2s; }
    .assignment-row:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }

    .status-submitted  { background:#dcfce7; color:#15803d; }
    .status-graded     { background:#dbeafe; color:#1d4ed8; }
    .status-pending    { background:#fef9c3; color:#a16207; }
    .status-late       { background:#fee2e2; color:#b91c1c; }

    .quiz-card { border-radius: 14px; border: 1px solid #e2e8f0; background: #fff; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
    .quiz-card .quiz-header { background: linear-gradient(135deg,#fffbeb,#fff7ed); border-bottom: 1px solid #fed7aa; padding: 16px 20px; }
    .quiz-card .quiz-header h6 { color: #1e293b; }
    .quiz-card .quiz-header small { color: #78716c; }

    .option-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 6px; }
    .option-item.correct { background:#f0fdf4; border-color:#86efac; }
    .option-dot { width: 20px; height: 20px; border-radius: 50%; border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .option-dot.correct { background:#16a34a; border-color:#16a34a; color:#fff; font-size:.7rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- En-tête ─────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-graduation-cap me-2 text-warning"></i>Espace E-Learning</h1>
            <p class="text-muted small mb-0">Accédez à vos cours, devoirs et quiz</p>
        </div>
    </div>

    @if($lessonsBySubject->isEmpty() && $assignments->isEmpty() && $quizzes->isEmpty())
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="fas fa-book-open fa-4x text-muted opacity-50 mb-3"></i>
                <h4 class="text-muted">Aucun contenu disponible pour le moment</h4>
                <p class="text-muted">Vos enseignants n'ont pas encore publié de contenu e-learning.</p>
            </div>
        </div>
    @else

    {{-- Onglets ─────────────────────────────────────────────────────────── --}}
    <ul class="nav nav-tabs lms-tabs mb-0 border-bottom" id="lmsTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-lessons">
                <i class="fas fa-file-alt me-2"></i>Cours
                <span class="badge bg-warning text-dark ms-1">{{ $lessonsBySubject->flatten()->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-assignments">
                <i class="fas fa-tasks me-2"></i>Devoirs
                <span class="badge bg-warning text-dark ms-1">{{ $assignments->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-quizzes">
                <i class="fas fa-question-circle me-2"></i>Quiz
                <span class="badge bg-warning text-dark ms-1">{{ $quizzes->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content bg-white border border-top-0 rounded-bottom p-4" style="border-radius:0 0 14px 14px;">

        {{-- ─── ONGLET COURS ────────────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="tab-lessons">
            @if($lessonsBySubject->isEmpty())
                <p class="text-center text-muted py-4"><i class="fas fa-folder-open me-2"></i>Aucun cours disponible.</p>
            @else

            {{-- Boutons Tout ouvrir / Tout fermer ──────────────── --}}
            <div class="d-flex justify-content-end gap-2 mb-3">
                <button class="btn btn-sm btn-outline-secondary" id="expandAll">
                    <i class="fas fa-chevron-down me-1"></i>Tout ouvrir
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="collapseAll">
                    <i class="fas fa-chevron-up me-1"></i>Tout fermer
                </button>
            </div>

            <div class="accordion subject-accordion" id="lessonsAccordion">
                @foreach($lessonsBySubject as $subjectName => $lessons)
                @php
                    $subjectSlug = 'subj-' . Str::slug($subjectName);
                    $isFirst     = $loop->first;
                    $pdfCount    = $lessons->whereIn('file_type', ['pdf','doc'])->count();
                    $videoCount  = $lessons->whereIn('file_type', ['video','link'])->count();
                @endphp
                <div class="accordion-item">
                    <h2 class="accordion-header" id="head-{{ $subjectSlug }}">
                        <button class="accordion-button {{ $isFirst ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#body-{{ $subjectSlug }}"
                                aria-expanded="{{ $isFirst ? 'true' : 'false' }}">

                            {{-- Icône matière ─────────────────── --}}
                            <span class="me-3" style="width:34px;height:34px;border-radius:9px;
                                  background:#fff7ed;display:inline-flex;align-items:center;
                                  justify-content:center;flex-shrink:0;">
                                <i class="fas fa-book text-warning" style="font-size:.9rem;"></i>
                            </span>

                            {{-- Nom matière ───────────────────── --}}
                            <span class="flex-grow-1">{{ $subjectName }}</span>

                            {{-- Compteurs ─────────────────────── --}}
                            <span class="d-flex gap-1 me-3" onclick="event.stopPropagation()">
                                @if($pdfCount)
                                    <span class="subject-count">
                                        <i class="fas fa-file-pdf me-1 text-danger"></i>{{ $pdfCount }}
                                    </span>
                                @endif
                                @if($videoCount)
                                    <span class="subject-count">
                                        <i class="fab fa-youtube me-1 text-success"></i>{{ $videoCount }}
                                    </span>
                                @endif
                                <span class="subject-count">{{ $lessons->count() }} doc.</span>
                            </span>
                        </button>
                    </h2>

                    <div id="body-{{ $subjectSlug }}"
                         class="accordion-collapse collapse {{ $isFirst ? 'show' : '' }}"
                         data-bs-parent="">{{-- pas de data-bs-parent → chaque section s'ouvre indépendamment --}}
                        <div class="accordion-body">
                            @foreach($lessons as $lesson)
                            @php
                                $iconClass = match($lesson->file_type) {
                                    'pdf'   => 'doc-icon-pdf',
                                    'doc'   => 'doc-icon-doc',
                                    'video' => 'doc-icon-video',
                                    default => 'doc-icon-link',
                                };
                                $iconFa = match($lesson->file_type) {
                                    'pdf'   => 'fas fa-file-pdf',
                                    'doc'   => 'fas fa-file-word',
                                    'video' => 'fab fa-youtube',
                                    default => 'fas fa-link',
                                };
                                $typeLabel = match($lesson->file_type) {
                                    'pdf'   => 'PDF',
                                    'doc'   => 'Word',
                                    'video' => 'Vidéo',
                                    default => 'Lien',
                                };
                                $isMedia = in_array($lesson->file_type, ['video','link']);
                            @endphp
                            <div class="doc-row">
                                {{-- Icône type ──────────────── --}}
                                <div class="doc-icon {{ $iconClass }}">
                                    <i class="{{ $iconFa }}"></i>
                                </div>

                                {{-- Infos ───────────────────── --}}
                                <div class="doc-info">
                                    <div class="doc-title">{{ $lesson->title }}</div>
                                    <div class="doc-meta">
                                        <span class="badge rounded-pill me-1"
                                              style="background:#f1f5f9;color:#64748b;font-size:.68rem;">
                                            {{ $typeLabel }}
                                        </span>
                                        <i class="fas fa-chalkboard-teacher me-1"></i>{{ $lesson->teacher->name ?? '—' }}
                                        @if($lesson->description)
                                            &nbsp;·&nbsp;{{ Str::limit($lesson->description, 55) }}
                                        @endif
                                    </div>
                                </div>

                                {{-- Bouton action ───────────── --}}
                                @if($isMedia)
                                    <a href="{{ $lesson->file_path }}" target="_blank"
                                       class="btn btn-outline-success doc-btn">
                                        <i class="fab fa-youtube me-1"></i>Regarder
                                    </a>
                                @else
                                    <a href="{{ route('student.lms.download', $lesson->id) }}"
                                       class="btn btn-outline-danger doc-btn">
                                        <i class="fas fa-download me-1"></i>Télécharger
                                    </a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ─── ONGLET DEVOIRS ──────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-assignments">
            @if($assignments->isEmpty())
                <p class="text-center text-muted py-4"><i class="fas fa-folder-open me-2"></i>Aucun devoir disponible.</p>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($assignments as $assignment)
                        @php
                            $submission = $assignment->submissions->first();
                            $isLate     = now()->isAfter($assignment->due_date);
                            if ($submission && $submission->status === 'graded') {
                                $statusClass = 'status-graded';
                                $statusLabel = 'Corrigé — ' . number_format($submission->grade, 1) . '/' . $assignment->points;
                                $statusIcon  = 'fas fa-check-circle';
                            } elseif ($submission) {
                                $statusClass = 'status-submitted';
                                $statusLabel = 'Rendu';
                                $statusIcon  = 'fas fa-paper-plane';
                            } elseif ($isLate) {
                                $statusClass = 'status-late';
                                $statusLabel = 'En retard';
                                $statusIcon  = 'fas fa-exclamation-circle';
                            } else {
                                $statusClass = 'status-pending';
                                $statusLabel = 'À rendre';
                                $statusIcon  = 'fas fa-clock';
                            }
                        @endphp
                        <div class="assignment-row d-flex align-items-start gap-3 flex-wrap">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <h6 class="mb-0 fw-semibold">{{ $assignment->title }}</h6>
                                    <span class="badge {{ $statusClass }} rounded-pill small">
                                        <i class="{{ $statusIcon }} me-1"></i>{{ $statusLabel }}
                                    </span>
                                </div>
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-book me-1"></i>{{ $assignment->subject->name ?? '—' }}
                                    &nbsp;·&nbsp;
                                    <i class="fas fa-calendar-alt me-1"></i>Date limite :
                                    <strong>{{ \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y') }}</strong>
                                    &nbsp;·&nbsp;
                                    <i class="fas fa-star me-1"></i>{{ $assignment->points }} pts
                                </div>
                                @if($assignment->instructions)
                                    <p class="small text-muted mb-0" style="border-left:3px solid #fbbf24; padding-left:10px;">
                                        {{ Str::limit($assignment->instructions, 150) }}
                                    </p>
                                @endif
                                @if($submission && $submission->feedback)
                                    <div class="mt-2 p-2 rounded" style="background:#eff6ff; border-left:3px solid #3b82f6;">
                                        <small class="text-primary fw-semibold"><i class="fas fa-comment me-1"></i>Retour du prof :</small>
                                        <p class="small mb-0">{{ $submission->feedback }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ─── ONGLET QUIZ ─────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-quizzes">
            @if($quizzes->isEmpty())
                <p class="text-center text-muted py-4"><i class="fas fa-folder-open me-2"></i>Aucun quiz disponible.</p>
            @else
                <div class="row g-4">
                    @foreach($quizzes as $quiz)
                    <div class="col-md-6 col-lg-4">
                        <div class="quiz-card h-100">
                            <div class="quiz-header">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $quiz->title }}</h6>
                                        <small class="opacity-75">{{ $quiz->subject->name ?? '—' }}</small>
                                    </div>
                                    <span class="badge fw-bold" style="background:#fff7ed;color:#d97706;border:1px solid #fed7aa;">
                                        {{ $quiz->questions->count() }} Q
                                    </span>
                                </div>
                                @if($quiz->time_limit)
                                    <div class="mt-2 small" style="color:#92400e;">
                                        <i class="fas fa-clock me-1"></i>{{ $quiz->time_limit }} min
                                    </div>
                                @endif
                            </div>
                            <div class="card-body p-3">
                                @if($quiz->description)
                                    <p class="text-muted small mb-3">{{ Str::limit($quiz->description, 100) }}</p>
                                @endif
                                {{-- Prévisualisation des questions (accordéon) --}}
                                <div class="accordion accordion-flush" id="quiz{{ $quiz->id }}Acc">
                                    @foreach($quiz->questions->take(3) as $qIdx => $question)
                                    <div class="accordion-item border rounded mb-2" style="border-radius:8px!important; overflow:hidden;">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 small fw-semibold" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#q{{ $quiz->id }}-{{ $qIdx }}">
                                                <span class="badge me-2" style="background:#fff7ed;color:#d97706;border:1px solid #fed7aa;">Q{{ $qIdx+1 }}</span>
                                                {{ Str::limit($question->question_text, 60) }}
                                            </button>
                                        </h2>
                                        <div id="q{{ $quiz->id }}-{{ $qIdx }}" class="accordion-collapse collapse"
                                             data-bs-parent="#quiz{{ $quiz->id }}Acc">
                                            <div class="accordion-body pt-1 pb-2">
                                                @foreach($question->options as $option)
                                                    <div class="option-item {{ $option->is_correct ? 'correct' : '' }}">
                                                        <div class="option-dot {{ $option->is_correct ? 'correct' : '' }}">
                                                            @if($option->is_correct)<i class="fas fa-check" style="font-size:.6rem;"></i>@endif
                                                        </div>
                                                        <span class="small">{{ $option->option_text }}</span>
                                                    </div>
                                                @endforeach
                                                <div class="text-end mt-1">
                                                    <small class="text-muted">{{ $question->points }} pt(s)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @if($quiz->questions->count() > 3)
                                        <p class="text-center text-muted small mt-1">
                                            +{{ $quiz->questions->count() - 3 }} question(s) supplémentaire(s)
                                        </p>
                                    @endif
                                </div>
                                {{-- Pied de carte : tentatives + CTA --}}
                                @php
                                    $attemptsDone = $quiz->attempts
                                        ->where('user_id', auth()->id())
                                        ->filter(fn($a) => $a->completed_at !== null)
                                        ->count();
                                    $totalStarted = $quiz->attempts
                                        ->where('user_id', auth()->id())
                                        ->count();
                                    $remaining    = max(0, 3 - $totalStarted);
                                @endphp
                                <div class="px-3 pb-3 pt-2 d-flex align-items-center justify-content-between"
                                     style="border-top:1px solid #f1f5f9; margin-top:8px;">
                                    <div class="d-flex gap-1 align-items-center">
                                        @for($i = 1; $i <= 3; $i++)
                                            <div style="width:10px;height:10px;border-radius:50%;
                                                        background:{{ $i <= $totalStarted ? '#f59e0b' : '#e2e8f0' }};"
                                                 title="{{ $i <= $totalStarted ? 'Tentative utilisée' : 'Disponible' }}"></div>
                                        @endfor
                                        <span class="ms-2 text-muted" style="font-size:.75rem;">
                                            {{ $remaining }}/3 restante{{ $remaining !== 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('student.quiz.show', $quiz) }}"
                                       class="btn btn-sm fw-semibold"
                                       style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;border-radius:8px;padding:5px 14px;font-size:.8rem;
                                              {{ $remaining === 0 ? 'opacity:.5;pointer-events:none;' : '' }}">
                                        @if($totalStarted === 0)
                                            <i class="fas fa-play me-1"></i>Commencer
                                        @elseif($remaining > 0)
                                            <i class="fas fa-redo me-1"></i>Refaire
                                        @else
                                            <i class="fas fa-eye me-1"></i>Résultats
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const acc = document.getElementById('lessonsAccordion');
    if (!acc) return;

    document.getElementById('expandAll')?.addEventListener('click', function () {
        acc.querySelectorAll('.accordion-collapse').forEach(function (el) {
            bootstrap.Collapse.getOrCreateInstance(el).show();
        });
    });

    document.getElementById('collapseAll')?.addEventListener('click', function () {
        acc.querySelectorAll('.accordion-collapse').forEach(function (el) {
            bootstrap.Collapse.getOrCreateInstance(el).hide();
        });
    });
});
</script>
@endpush
