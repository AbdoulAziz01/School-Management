@extends('layouts.student')

@section('title', 'Mes Notes')

@push('styles')
<style>
    /* ── Subject card ─────────────────────────────────────────── */
    .subject-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        overflow: hidden;
    }
    .subject-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 28px rgba(0,0,0,0.13);
    }

    /* ── Card header ──────────────────────────────────────────── */
    .subject-card .card-header {
        border: none;
        padding: 14px 16px 12px;
        color: white;
    }
    .subject-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255,255,255,0.22);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    /* Score pill — toujours lisible, ne peut pas être coupé */
    .avg-score-pill {
        background: rgba(0,0,0,0.20);
        border-radius: 8px;
        padding: 4px 10px;
        display: inline-flex;
        align-items: baseline;
        gap: 1px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .avg-score-pill .val {
        font-size: 1.3rem;
        font-weight: 800;
        line-height: 1;
    }
    .avg-score-pill .denom {
        font-size: 0.8rem;
        font-weight: 600;
        opacity: .85;
    }

    /* ── Progress bar ─────────────────────────────────────────── */
    .avg-progress-track {
        height: 6px;
        border-radius: 99px;
        background: rgba(255,255,255,0.25);
        overflow: hidden;
        margin-top: 6px;
    }
    .avg-progress-fill {
        height: 100%;
        border-radius: 99px;
        background: rgba(255,255,255,0.85);
        transition: width 0.7s ease;
    }

    /* ── Grade pill (individual note) ─────────────────────────── */
    .grade-pill-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .grade-pill {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: background 0.15s;
    }
    .grade-pill:hover { background: #f1f5f9; }

    .grade-pill-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .pill-icon-exam    { background: #ede9fe; color: #7c3aed; }
    .pill-icon-compo   { background: #dbeafe; color: #1d4ed8; }
    .pill-icon-devoir  { background: #fef3c7; color: #b45309; }
    .pill-icon-default { background: #e0f2fe; color: #0369a1; }

    .grade-pill-info { flex: 1; min-width: 0; }
    .grade-pill-type {
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .grade-pill-date { font-size: 0.72rem; color: #9ca3af; margin-top: 1px; }

    .grade-pill-score {
        font-size: 0.9rem;
        font-weight: 700;
        padding: 5px 11px;
        border-radius: 99px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .score-excellent { background: #dcfce7; color: #15803d; }
    .score-good      { background: #fef9c3; color: #a16207; }
    .score-average   { background: #ffedd5; color: #c2410c; }
    .score-poor      { background: #fee2e2; color: #b91c1c; }

    /* ── "Voir plus" toggle ───────────────────────────────────── */
    .toggle-more-btn {
        background: none;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        color: #64748b;
        font-size: 0.82rem;
        padding: 7px;
        width: 100%;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s;
        margin-top: 4px;
    }
    .toggle-more-btn:hover { background: #f1f5f9; border-color: #94a3b8; color: #334155; }

    /* ── Appreciation ─────────────────────────────────────────── */
    .appreciation-box {
        background: #fffbeb;
        border-left: 3px solid #fbbf24;
        border-radius: 0 8px 8px 0;
        padding: 8px 12px;
        font-size: 0.82rem;
        font-style: italic;
        color: #78716c;
    }

    /* ── Stat cards ───────────────────────────────────────────── */
    /* ── Stat cards ───────────────────────────────────────────── */
    .stat-card {
        border-radius: 16px;
        border: 1px solid #e9ecef;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .stat-card .stat-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.1;
    }
    .stat-card .stat-label {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 2px;
    }

    /* ── Bulletin button ──────────────────────────────────────── */
    .bulletin-btn {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        border: none;
        padding: 12px 28px;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.25s;
    }
    .bulletin-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(245,158,11,0.4);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Mes Notes</h1>
            @if($selectedYear ?? null)
                <p class="text-muted small mb-0">{{ $selectedYear->name }}</p>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($grades->isNotEmpty())
                <a href="{{ route('student.bulletin', ['academic_year_id' => $selectedYear?->id]) }}" class="btn btn-primary bulletin-btn">
                    <i class="fas fa-file-alt me-2"></i>Voir mon bulletin
                </a>
            @endif
        </div>
    </div>

    @if(isset($selectedYear) && !($isSelectedYearCurrent ?? true))
        <div class="alert alert-light border py-2 small mb-4">
            <i class="fas fa-info-circle me-1 text-muted"></i>
            Notes de l'année <strong>{{ $selectedYear->name }}</strong> uniquement.
        </div>
    @endif

    @if($grades->isEmpty())
    <!-- État vide - Aucune note -->
    <div class="card mb-4">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-clipboard-list fa-4x text-muted opacity-50"></i>
            </div>
            <h4 class="text-muted mb-3">Aucune note disponible</h4>
            <p class="text-muted mb-0">
                Vos notes apparaîtront ici une fois que vos professeurs les auront saisies.
            </p>
        </div>
    </div>
    @else

    @php
        $totalNotes = 0;
        foreach($grades as $g) { $totalNotes += $g['grades']->count(); }
    @endphp

    <!-- Statistiques générales -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:#fff7ed;color:#f59e0b;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $generalAverage !== null ? number_format($generalAverage, 2) : '—' }}</div>
                        <div class="stat-label">Moyenne générale</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ number_format((float)$grades->max('average'), 2) }}</div>
                        <div class="stat-label">Meilleure matière</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:#eff6ff;color:#3b82f6;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $grades->count() }}</div>
                        <div class="stat-label">Matières</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="stat-icon" style="background:#faf5ff;color:#8b5cf6;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $totalNotes }}</div>
                        <div class="stat-label">Notes saisies</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations élève -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1">{{ $studentInfo['name'] }}</h5>
                    <p class="text-muted mb-0">
                        <span class="me-3"><i class="fas fa-graduation-cap me-1"></i>{{ $studentInfo['class'] }}</span>
                        <span class="me-3"><i class="fas fa-id-card me-1"></i>{{ $studentInfo['identifier'] }}</span>
                        <span><i class="fas fa-calendar me-1"></i>{{ $studentInfo['academic_year'] }}</span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge px-3 py-2 fs-6" style="background:#fff7ed;color:#d97706;border:1px solid #fed7aa;">
                        Trimestre {{ $studentInfo['trimester'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes par matière -->
    <div class="row g-4">
        @foreach($grades as $subjectData)
            @php
                $subjectSlug = Str::slug($subjectData['subject']);
                $avg = (float) $subjectData['average'];
                $progressPct = min(100, $avg / 20 * 100);
                $headerColor = $subjectData['subject_color'] ?? '#f59e0b';
                $visibleGrades = $subjectData['grades']->take(4);
                $hiddenGrades  = $subjectData['grades']->slice(4);
            @endphp
            <div class="col-lg-6">
                <div class="card subject-card h-100">

                    {{-- ── Header ── --}}
                    <div class="card-header" style="background: {{ $headerColor }};">
                        {{-- Row 1: icon + subject name + score pill --}}
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="subject-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h6 class="mb-0 fw-bold flex-grow-1 text-truncate" style="min-width:0;">{{ $subjectData['subject'] }}</h6>
                            <div class="avg-score-pill flex-shrink-0">
                                <span class="val">{{ number_format($avg, 2) }}</span>
                                <span class="denom">/20</span>
                            </div>
                        </div>
                        {{-- Row 2: teacher + coef + progress bar --}}
                        <div class="d-flex align-items-center justify-content-between" style="font-size:0.78rem;opacity:.85;">
                            <span class="text-truncate" style="min-width:0;">
                                <i class="fas fa-chalkboard-teacher me-1"></i>{{ $subjectData['teacher'] ?? 'Professeur' }}
                            </span>
                            <span class="flex-shrink-0 ms-2">Coef. {{ $subjectData['coefficient'] ?? 1 }}</span>
                        </div>
                        <div class="avg-progress-track mt-2">
                            <div class="avg-progress-fill" style="width:{{ $progressPct }}%;"></div>
                        </div>
                    </div>

                    {{-- ── Body ── --}}
                    <div class="card-body d-flex flex-column gap-3">

                        {{-- Pills visible --}}
                        <div class="grade-pill-grid">
                            @foreach($visibleGrades as $grade)
                                @php
                                    $gradeValue = $grade->grade !== null ? (float) $grade->grade : null;
                                    $scoreClass = $gradeValue === null ? '' :
                                        ($gradeValue >= 16 ? 'score-excellent' :
                                        ($gradeValue >= 12 ? 'score-good'      :
                                        ($gradeValue >= 10 ? 'score-average'   : 'score-poor')));
                                    $typeRaw = strtolower($grade->type ?? '');
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
                                        <div class="grade-pill-date"><i class="fas fa-calendar-alt me-1"></i>{{ $dateLabel }}</div>
                                    </div>
                                    @if($gradeValue !== null)
                                        <span class="grade-pill-score {{ $scoreClass }}">{{ number_format($gradeValue, 2) }}/20</span>
                                    @else
                                        <span class="grade-pill-score" style="background:#f1f5f9;color:#94a3b8;">—</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Hidden pills --}}
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
                                            $typeRaw = strtolower($grade->type ?? '');
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
                                                <div class="grade-pill-date"><i class="fas fa-calendar-alt me-1"></i>{{ $dateLabel }}</div>
                                            </div>
                                            @if($gradeValue !== null)
                                                <span class="grade-pill-score {{ $scoreClass }}">{{ number_format($gradeValue, 2) }}/20</span>
                                            @else
                                                <span class="grade-pill-score" style="background:#f1f5f9;color:#94a3b8;">—</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <button class="toggle-more-btn" data-bs-toggle="collapse" data-bs-target="#more-{{ $subjectSlug }}"
                                    data-more-text="Voir {{ $hiddenGrades->count() }} note(s) de plus ▾"
                                    data-less-text="Réduire ▴">
                                Voir {{ $hiddenGrades->count() }} note(s) de plus ▾
                            </button>
                        @endif

                        {{-- Appreciation --}}
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
    <!-- Diagramme d'évolution des notes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Évolution des performances
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Moyenne mensuelle pondérée du début à la fin de l'année scolaire.
                    </p>
                    <div style="position: relative; height: 300px;" id="gradesEvolutionChartWrap">
                        <canvas id="gradesEvolutionChart"></canvas>
                    </div>
                    <div id="gradesEvolutionChartEmpty" class="d-none py-4 text-center text-muted">
                        <i class="fas fa-chart-line fa-3x mb-3 opacity-50"></i>
                        <p class="mb-0">Pas encore de notes pour afficher l'évolution.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bouton bulletin en bas -->
    @if($grades->isNotEmpty())
    <div class="text-center mt-5 mb-4">
        <a href="{{ route('student.bulletin', ['academic_year_id' => $selectedYear?->id]) }}" class="btn btn-lg btn-primary bulletin-btn">
            <i class="fas fa-print me-2"></i>Accéder à mon bulletin complet
        </a>
        <p class="text-muted mt-2">
            <small>Consultez et imprimez votre bulletin scolaire officiel</small>
        </p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle "Voir plus / Réduire" button label
    document.querySelectorAll('.toggle-more-btn').forEach(function(btn) {
        var target = document.querySelector(btn.getAttribute('data-bs-target'));
        if (!target) return;
        target.addEventListener('shown.bs.collapse',  function() { btn.textContent = btn.dataset.lessText; });
        target.addEventListener('hidden.bs.collapse', function() { btn.textContent = btn.dataset.moreText; });
    });

    var canvas = document.getElementById('gradesEvolutionChart');
    var chartWrap = document.getElementById('gradesEvolutionChartWrap');
    var emptyState = document.getElementById('gradesEvolutionChartEmpty');
    if (!canvas) {
        return;
    }

    var gradesData = @json($gradesEvolution ?? []);
    var labels = gradesData.map(function(item) { return item.month; });
    var grades = gradesData.map(function(item) { return item.grade; });
    var counts = gradesData.map(function(item) { return item.count || 0; });
    var hasAnyGrade = grades.some(function(value) {
        return value !== null && value !== undefined;
    });

    if (!hasAnyGrade) {
        chartWrap.classList.add('d-none');
        emptyState.classList.remove('d-none');
        return;
    }

    var ctx = canvas.getContext('2d');
    var gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(245, 158, 11, 0.8)');
    gradient.addColorStop(1, 'rgba(245, 158, 11, 0.1)');
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
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: grades.map(function(v) { return (v === null ? 0 : 5); }),
                    pointHoverRadius: 7
                },
                {
                    label: 'Seuil de réussite (10/20)',
                    data: passingLine,
                    borderColor: '#1cc88a',
                    borderWidth: 2,
                    borderDash: [6, 4],
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var value = context.parsed.y;
                            if (value === null || value === undefined) {
                                return context.dataset.label + ' : —';
                            }
                            return context.dataset.label + ' : ' + value + '/20';
                        },
                        afterLabel: function(context) {
                            if (context.datasetIndex === 0 && counts[context.dataIndex]) {
                                return 'Notes saisies : ' + counts[context.dataIndex];
                            }
                            if (context.datasetIndex === 0 && (grades[context.dataIndex] === null || grades[context.dataIndex] === undefined)) {
                                return 'Aucune note ce mois-ci';
                            }
                            return '';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 20,
                    ticks: {
                        stepSize: 2,
                        callback: function(value) {
                            return value + '/20';
                        }
                    },
                    title: {
                        display: true,
                        text: 'Moyenne'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 12
                    },
                    title: {
                        display: true,
                        text: 'Période'
                    }
                }
            }
        }
    });
});
</script>
@endpush
