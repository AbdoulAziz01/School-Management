@extends('layouts.student')

@section('title', 'Mes Notes')

@push('styles')
<style>
    /* ═══════════════════════════════════════
       HEADER & BULLETIN BUTTON
    ═══════════════════════════════════════ */
    .grades-page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .grades-page-title { font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0; line-height: 1.2; }
    .grades-year-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #92400e;
        border-radius: 99px;
        padding: 0.2rem 0.7rem;
        font-size: 0.72rem;
        font-weight: 600;
        margin-top: 0.3rem;
    }
    .bulletin-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #1c1917;
        border: none;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-weight: 700;
        font-size: 0.82rem;
        white-space: nowrap;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 3px 10px rgba(245,158,11,0.35);
        flex-shrink: 0;
    }
    .bulletin-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(245,158,11,0.45); color: #1c1917; }

    /* ═══════════════════════════════════════
       STATS STRIP — flat, sans carte
    ═══════════════════════════════════════ */
    .stats-strip {
        display: flex;
        align-items: stretch;
        background: rgba(245,158,11,0.07);
        border-radius: 14px;
        padding: 0.875rem 0;
        margin-bottom: 1rem;
    }
    .stats-strip-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0 0.5rem;
        text-align: center;
        border-right: 1.5px solid rgba(245,158,11,0.22);
    }
    .stats-strip-item:last-child { border-right: none; }

    .ss-value {
        font-size: 1.5rem;
        font-weight: 900;
        color: #1e293b;
        line-height: 1;
    }
    .ss-value.accent { color: #d97706; }
    .ss-denom {
        font-size: 0.7rem;
        font-weight: 600;
        color: #b45309;
        margin-left: 1px;
    }
    .ss-label {
        font-size: 0.58rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-top: 4px;
        white-space: nowrap;
    }

    /* barre de progression sous la moyenne */
    .ss-progress-wrap {
        width: 100%;
        padding: 0 1rem;
        margin-top: 0.625rem;
    }
    .ss-progress-track {
        height: 4px;
        background: rgba(245,158,11,0.2);
        border-radius: 99px;
        overflow: hidden;
    }
    .ss-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #f59e0b, #d97706);
        border-radius: 99px;
        transition: width .8s ease;
    }

    /* ═══════════════════════════════════════
       CARTE ÉLÈVE
    ═══════════════════════════════════════ */
    .student-info-card {
        background: #fff;
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 1rem 1.125rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 2px 8px rgba(245,158,11,0.08);
    }
    .student-info-name {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.5rem;
    }
    .student-meta-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }
    .sip { /* student-info-pill */
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 0.22rem 0.6rem;
        white-space: nowrap;
    }
    .sip-class  { background:#fff7ed; color:#92400e; border:1px solid #fde68a; }
    .sip-id     { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
    .sip-year   { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .sip-trim   { background: linear-gradient(135deg,#f59e0b,#d97706); color:#1c1917; border:none; font-size:.78rem; padding:.3rem .8rem; border-radius:8px; }

    /* ═══════════════════════════════════════
       SUBJECT CARD
    ═══════════════════════════════════════ */
    .subject-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .subject-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,0.12); }

    .subject-card .card-header {
        border: none;
        padding: 0.875rem 1rem 0.75rem;
        color: #fff;
    }
    .subject-icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        background: rgba(255,255,255,0.22);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; flex-shrink: 0;
    }
    .avg-score-pill {
        background: rgba(0,0,0,0.20);
        border-radius: 8px;
        padding: 3px 9px;
        display: inline-flex;
        align-items: baseline;
        gap: 1px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .avg-score-pill .val { font-size: 1.2rem; font-weight: 800; line-height: 1; }
    .avg-score-pill .denom { font-size: 0.75rem; font-weight: 600; opacity:.85; }

    .avg-progress-track {
        height: 5px; border-radius: 99px;
        background: rgba(255,255,255,0.25);
        overflow: hidden; margin-top: 5px;
    }
    .avg-progress-fill {
        height: 100%; border-radius: 99px;
        background: rgba(255,255,255,0.85);
        transition: width 0.7s ease;
    }

    /* ═══════════════════════════════════════
       GRADE PILLS
    ═══════════════════════════════════════ */
    .grade-pill-grid { display: flex; flex-direction: column; gap: 7px; }
    .grade-pill {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px;
        border-radius: 11px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: background 0.15s;
    }
    .grade-pill:hover { background: #f1f5f9; }

    .grade-pill-icon {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.78rem; font-weight: 700; flex-shrink: 0;
    }
    .pill-icon-exam    { background: #ede9fe; color: #7c3aed; }
    .pill-icon-compo   { background: #dbeafe; color: #1d4ed8; }
    .pill-icon-devoir  { background: #fef3c7; color: #b45309; }
    .pill-icon-default { background: #e0f2fe; color: #0369a1; }

    .grade-pill-info { flex: 1; min-width: 0; }
    .grade-pill-type {
        font-size: 0.82rem; font-weight: 600; color: #374151;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .grade-pill-date { font-size: 0.7rem; color: #9ca3af; margin-top: 1px; }

    .grade-pill-score {
        font-size: 0.85rem; font-weight: 700;
        padding: 4px 10px; border-radius: 99px;
        white-space: nowrap; flex-shrink: 0;
    }
    .score-excellent { background: #dcfce7; color: #15803d; }
    .score-good      { background: #fef9c3; color: #a16207; }
    .score-average   { background: #ffedd5; color: #c2410c; }
    .score-poor      { background: #fee2e2; color: #b91c1c; }

    /* ═══════════════════════════════════════
       VOIR PLUS TOGGLE
    ═══════════════════════════════════════ */
    .toggle-more-btn {
        background: none; border: 1px dashed #cbd5e1;
        border-radius: 9px; color: #64748b;
        font-size: 0.8rem; padding: 6px;
        width: 100%; text-align: center; cursor: pointer;
        transition: all 0.15s; margin-top: 2px;
    }
    .toggle-more-btn:hover { background: #f1f5f9; border-color: #94a3b8; color: #334155; }

    /* ═══════════════════════════════════════
       APPRECIATION
    ═══════════════════════════════════════ */
    .appreciation-box {
        background: #fffbeb; border-left: 3px solid #fbbf24;
        border-radius: 0 8px 8px 0;
        padding: 7px 11px;
        font-size: 0.8rem; font-style: italic; color: #78716c;
    }

    /* ═══════════════════════════════════════
       BOTTOM CTA
    ═══════════════════════════════════════ */
    .bottom-cta-wrap { text-align: center; margin: 2rem 0 1rem; }
    .bottom-cta-btn {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #1c1917; border: none; border-radius: 12px;
        padding: 0.75rem 2rem; font-weight: 700; font-size: 0.9rem;
        text-decoration: none; transition: all 0.25s;
        box-shadow: 0 4px 15px rgba(245,158,11,0.35);
    }
    .bottom-cta-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,158,11,0.45); color: #1c1917; }

    /* ═══════════════════════════════════════
       RESPONSIVE XS (≤575px)
    ═══════════════════════════════════════ */
    @media (max-width: 575.98px) {
        .grades-page-title { font-size: 1.2rem; }

        .ss-value { font-size: 1.25rem; }
        .stats-strip { padding: 0.75rem 0; }

.subject-card .card-header { padding: 0.75rem 0.875rem 0.6rem; }
        .avg-score-pill .val { font-size: 1.05rem; }

        .grade-pill { padding: 7px 9px; gap: 8px; }
        .grade-pill-icon { width: 28px; height: 28px; border-radius: 7px; }
        .grade-pill-score { font-size: 0.78rem; padding: 3px 8px; }

        .bottom-cta-btn { width: 100%; justify-content: center; padding: 0.75rem 1rem; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── En-tête ── --}}
    <div class="grades-page-header">
        <div>
            <h1 class="grades-page-title">Mes Notes</h1>
            @if($selectedYear ?? null)
                <span class="grades-year-badge">
                    <i class="fas fa-calendar-alt"></i>{{ $selectedYear->name }}
                </span>
            @endif
        </div>
        @if($grades->isNotEmpty())
            <a href="{{ route('student.bulletin', ['academic_year_id' => $selectedYear?->id]) }}"
               class="bulletin-btn">
                <i class="fas fa-file-alt"></i>
                <span class="d-none d-sm-inline">Voir mon bulletin</span>
                <span class="d-sm-none">Bulletin</span>
            </a>
        @endif
    </div>

    @if(isset($selectedYear) && !($isSelectedYearCurrent ?? true))
        <div class="alert alert-light border py-2 small mb-3" style="border-radius:10px;">
            <i class="fas fa-info-circle me-1 text-muted"></i>
            Notes de l'année <strong>{{ $selectedYear->name }}</strong> uniquement.
        </div>
    @endif

    @if($grades->isEmpty())
    <div class="card mb-4" style="border-radius:14px;">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted opacity-50 mb-3 d-block"></i>
            <h5 class="text-muted mb-2">Aucune note disponible</h5>
            <p class="text-muted small mb-0">Vos notes apparaîtront ici une fois que vos professeurs les auront saisies.</p>
        </div>
    </div>
    @else

    @php
        $totalNotes = 0;
        foreach($grades as $g) { $totalNotes += $g['grades']->count(); }
    @endphp

    {{-- ── Stats strip (flat, sans carte) ── --}}
    <div class="stats-strip">
        <div class="stats-strip-item">
            <div>
                <span class="ss-value accent">{{ $generalAverage !== null ? number_format($generalAverage, 2) : '—' }}</span>
                @if($generalAverage !== null)<span class="ss-denom">/20</span>@endif
            </div>
            <div class="ss-label">Moy. générale</div>
            @if($generalAverage !== null)
                <div class="ss-progress-wrap">
                    <div class="ss-progress-track">
                        <div class="ss-progress-fill" style="width:{{ min(100, $generalAverage / 20 * 100) }}%;"></div>
                    </div>
                </div>
            @endif
        </div>
        <div class="stats-strip-item">
            <div><span class="ss-value">{{ number_format((float)$grades->max('average'), 2) }}</span><span class="ss-denom">/20</span></div>
            <div class="ss-label">Meilleure mat.</div>
        </div>
        <div class="stats-strip-item">
            <div class="ss-value">{{ $grades->count() }}</div>
            <div class="ss-label">Matières</div>
        </div>
        <div class="stats-strip-item">
            <div class="ss-value">{{ $totalNotes }}</div>
            <div class="ss-label">Notes saisies</div>
        </div>
    </div>

    {{-- ── Carte élève ── --}}
    <div class="student-info-card">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="min-w-0">
                <p class="student-info-name">{{ $studentInfo['name'] }}</p>
                <div class="student-meta-pills">
                    <span class="sip sip-class">
                        <i class="fas fa-graduation-cap"></i>{{ $studentInfo['class'] }}
                    </span>
                    <span class="sip sip-id">
                        <i class="fas fa-id-card"></i>{{ $studentInfo['identifier'] }}
                    </span>
                    <span class="sip sip-year">
                        <i class="fas fa-calendar"></i>{{ $studentInfo['academic_year'] }}
                    </span>
                </div>
            </div>
            <span class="sip sip-trim flex-shrink-0">T{{ $studentInfo['trimester'] }}</span>
        </div>
    </div>

    {{-- ── Notes par matière ── --}}
    <div class="row g-3">
        @foreach($grades as $subjectData)
            @php
                $subjectSlug  = Str::slug($subjectData['subject']);
                $avg          = (float) $subjectData['average'];
                $progressPct  = min(100, $avg / 20 * 100);
                $headerColor  = $subjectData['subject_color'] ?? '#f59e0b';
                $visibleGrades = $subjectData['grades']->take(4);
                $hiddenGrades  = $subjectData['grades']->slice(4);
            @endphp
            <div class="col-lg-6">
                <div class="card subject-card h-100">

                    {{-- Header --}}
                    <div class="card-header" style="background:{{ $headerColor }};">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <div class="subject-icon"><i class="fas fa-book"></i></div>
                            <h6 class="mb-0 fw-bold flex-grow-1 text-truncate" style="min-width:0;color:#fff;">
                                {{ $subjectData['subject'] }}
                            </h6>
                            <div class="avg-score-pill">
                                <span class="val">{{ number_format($avg, 2) }}</span>
                                <span class="denom">/20</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between" style="font-size:0.75rem;opacity:.85;color:#fff;">
                            <span class="text-truncate" style="min-width:0;">
                                <i class="fas fa-chalkboard-teacher me-1"></i>{{ $subjectData['teacher'] ?? 'Professeur' }}
                            </span>
                            <span class="flex-shrink-0 ms-2">Coef. {{ $subjectData['coefficient'] ?? 1 }}</span>
                        </div>
                        <div class="avg-progress-track">
                            <div class="avg-progress-fill" style="width:{{ $progressPct }}%;"></div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="card-body d-flex flex-column gap-2" style="padding:0.875rem;">

                        <div class="grade-pill-grid">
                            @foreach($visibleGrades as $grade)
                                @php
                                    $gradeValue = $grade->grade !== null ? (float) $grade->grade : null;
                                    $scoreClass = $gradeValue === null ? '' :
                                        ($gradeValue >= 16 ? 'score-excellent' :
                                        ($gradeValue >= 12 ? 'score-good'      :
                                        ($gradeValue >= 10 ? 'score-average'   : 'score-poor')));
                                    $typeRaw   = strtolower($grade->type ?? '');
                                    $iconClass = str_contains($typeRaw,'examen') ? 'pill-icon-exam' :
                                                (str_contains($typeRaw,'compos') ? 'pill-icon-compo' :
                                                (str_contains($typeRaw,'devoir') ? 'pill-icon-devoir' : 'pill-icon-default'));
                                    $iconChar  = str_contains($typeRaw,'examen') ? 'E' :
                                                (str_contains($typeRaw,'compos') ? 'C' :
                                                (str_contains($typeRaw,'devoir') ? 'D' : '?'));
                                    $typeLabel = \App\Support\SenegalGradeSequence::LABELS[$grade->type] ?? ucfirst($grade->type ?? 'Devoir');
                                    $dateLabel = $grade->date
                                        ? $grade->date->format('d/m/Y')
                                        : ($grade->created_at ? $grade->created_at->format('d/m/Y') : '—');
                                @endphp
                                <div class="grade-pill">
                                    <div class="grade-pill-icon {{ $iconClass }}">{{ $iconChar }}</div>
                                    <div class="grade-pill-info">
                                        <div class="grade-pill-type">{{ $typeLabel }}</div>
                                        <div class="grade-pill-date">
                                            <i class="fas fa-calendar-alt me-1"></i>{{ $dateLabel }}
                                        </div>
                                    </div>
                                    @if($gradeValue !== null)
                                        <span class="grade-pill-score {{ $scoreClass }}">
                                            {{ number_format($gradeValue, 2) }}/20
                                        </span>
                                    @else
                                        <span class="grade-pill-score" style="background:#f1f5f9;color:#94a3b8;">—</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($hiddenGrades->count() > 0)
                            <div class="collapse" id="more-{{ $subjectSlug }}">
                                <div class="grade-pill-grid">
                                    @foreach($hiddenGrades as $grade)
                                        @php
                                            $gradeValue = $grade->grade !== null ? (float) $grade->grade : null;
                                            $scoreClass = $gradeValue === null ? '' :
                                                ($gradeValue >= 16 ? 'score-excellent' :
                                                ($gradeValue >= 12 ? 'score-good'      :
                                                ($gradeValue >= 10 ? 'score-average'   : 'score-poor')));
                                            $typeRaw   = strtolower($grade->type ?? '');
                                            $iconClass = str_contains($typeRaw,'examen') ? 'pill-icon-exam' :
                                                        (str_contains($typeRaw,'compos') ? 'pill-icon-compo' :
                                                        (str_contains($typeRaw,'devoir') ? 'pill-icon-devoir' : 'pill-icon-default'));
                                            $iconChar  = str_contains($typeRaw,'examen') ? 'E' :
                                                        (str_contains($typeRaw,'compos') ? 'C' :
                                                        (str_contains($typeRaw,'devoir') ? 'D' : '?'));
                                            $typeLabel = \App\Support\SenegalGradeSequence::LABELS[$grade->type] ?? ucfirst($grade->type ?? 'Devoir');
                                            $dateLabel = $grade->date
                                                ? $grade->date->format('d/m/Y')
                                                : ($grade->created_at ? $grade->created_at->format('d/m/Y') : '—');
                                        @endphp
                                        <div class="grade-pill">
                                            <div class="grade-pill-icon {{ $iconClass }}">{{ $iconChar }}</div>
                                            <div class="grade-pill-info">
                                                <div class="grade-pill-type">{{ $typeLabel }}</div>
                                                <div class="grade-pill-date">
                                                    <i class="fas fa-calendar-alt me-1"></i>{{ $dateLabel }}
                                                </div>
                                            </div>
                                            @if($gradeValue !== null)
                                                <span class="grade-pill-score {{ $scoreClass }}">
                                                    {{ number_format($gradeValue, 2) }}/20
                                                </span>
                                            @else
                                                <span class="grade-pill-score" style="background:#f1f5f9;color:#94a3b8;">—</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <button class="toggle-more-btn"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#more-{{ $subjectSlug }}"
                                    data-more-text="Voir {{ $hiddenGrades->count() }} note(s) de plus ▾"
                                    data-less-text="Réduire ▴">
                                Voir {{ $hiddenGrades->count() }} note(s) de plus ▾
                            </button>
                        @endif

                        @if(!empty($subjectData['appreciation']))
                            <div class="appreciation-box mt-auto">
                                <i class="fas fa-quote-left me-1 opacity-60"></i>
                                {{ $subjectData['appreciation'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    @if($grades->isNotEmpty())
    {{-- ── Graphique évolution ── --}}
    <div class="card mt-4 mb-3" style="border-radius:14px;border:1px solid #fde68a;">
        <div class="card-header py-2">
            <h6 class="m-0 fw-bold" style="color:#92400e;">
                <i class="fas fa-chart-line me-2"></i>Évolution des performances
            </h6>
        </div>
        <div class="card-body" style="padding:1rem;">
            <p class="text-muted small mb-3">
                <i class="fas fa-info-circle me-1"></i>Moyenne mensuelle pondérée du début à la fin de l'année scolaire.
            </p>
            <div style="position:relative;height:260px;" id="gradesEvolutionChartWrap">
                <canvas id="gradesEvolutionChart"></canvas>
            </div>
            <div id="gradesEvolutionChartEmpty" class="d-none py-4 text-center text-muted">
                <i class="fas fa-chart-line fa-3x mb-3 opacity-50 d-block"></i>
                <p class="mb-0 small">Pas encore de notes pour afficher l'évolution.</p>
            </div>
        </div>
    </div>

    {{-- ── CTA bas de page ── --}}
    <div class="bottom-cta-wrap">
        <a href="{{ route('student.bulletin', ['academic_year_id' => $selectedYear?->id]) }}"
           class="bottom-cta-btn">
            <i class="fas fa-print"></i>Accéder à mon bulletin complet
        </a>
        <p class="text-muted mt-2 mb-0 small">Consultez et imprimez votre bulletin scolaire officiel</p>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle label
    document.querySelectorAll('.toggle-more-btn').forEach(function(btn) {
        var target = document.querySelector(btn.getAttribute('data-bs-target'));
        if (!target) return;
        target.addEventListener('shown.bs.collapse',  function() { btn.textContent = btn.dataset.lessText; });
        target.addEventListener('hidden.bs.collapse', function() { btn.textContent = btn.dataset.moreText; });
    });

    var canvas    = document.getElementById('gradesEvolutionChart');
    var chartWrap = document.getElementById('gradesEvolutionChartWrap');
    var emptyState = document.getElementById('gradesEvolutionChartEmpty');
    if (!canvas) return;

    var gradesData = @json($gradesEvolution ?? []);
    var labels = gradesData.map(function(i) { return i.month; });
    var grades = gradesData.map(function(i) { return i.grade; });
    var counts = gradesData.map(function(i) { return i.count || 0; });
    var hasAnyGrade = grades.some(function(v) { return v !== null && v !== undefined; });

    if (!hasAnyGrade) {
        chartWrap.classList.add('d-none');
        emptyState.classList.remove('d-none');
        return;
    }

    var ctx = canvas.getContext('2d');
    var gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(245,158,11,0.75)');
    gradient.addColorStop(1, 'rgba(245,158,11,0.05)');
    var passingLine = labels.map(function() { return 10; });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Ma moyenne mensuelle',
                    data: grades,
                    borderColor: '#f59e0b',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: grades.map(function(v) { return v === null ? 0 : 4; }),
                    pointHoverRadius: 6
                },
                {
                    label: 'Seuil de réussite (10/20)',
                    data: passingLine,
                    borderColor: '#1cc88a',
                    borderWidth: 1.5,
                    borderDash: [6,4],
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var v = ctx.parsed.y;
                            return ctx.dataset.label + ' : ' + (v === null || v === undefined ? '—' : v + '/20');
                        },
                        afterLabel: function(ctx) {
                            if (ctx.datasetIndex === 0 && counts[ctx.dataIndex])
                                return 'Notes saisies : ' + counts[ctx.dataIndex];
                            return '';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true, max: 20,
                    ticks: { stepSize: 2, callback: function(v) { return v + '/20'; } },
                    title: { display: true, text: 'Moyenne' }
                },
                x: {
                    ticks: { maxRotation: 45, autoSkip: true, maxTicksLimit: 12 },
                    title: { display: true, text: 'Période' }
                }
            }
        }
    });
});
</script>
@endpush
