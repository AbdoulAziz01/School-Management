@extends('teacher.layouts.app')

@section('title', 'Tableau de bord - Enseignant')

@section('content')
<div class="mb-4">
    <h1 class="mb-0 h3">Tableau de bord</h1>
    <p class="text-muted mb-0">Bienvenue, {{ $teacher->name }}</p>
</div>

@if($gradesLocked ?? false)
    <div class="alert alert-warning py-2 small mb-4">
        <i class="fas fa-lock me-2"></i>{{ $gradesLockedMessage ?? 'Saisie des notes bloquée pour cette année.' }}
    </div>
@endif

@if(isset($selectedYear) && !($isSelectedYearCurrent ?? true))
    <div class="alert alert-light border py-2 small mb-4">
        <i class="fas fa-info-circle me-1 text-muted"></i>
        Consultation de l'année <strong>{{ $selectedYear->name }}</strong>
        @if($selectedYear->isClosed()) (terminée) @endif
        — les statistiques affichées concernent uniquement cette période.
    </div>
@endif

{{-- Section Aperçu --}}
<div class="mb-4 row g-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('teacher.classes.index') }}" class="card stat-card stat-card-link">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Mes Classes</h6>
                        <h2 class="stat-value">{{ $classesCount ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle">
                        <i class="fas fa-users text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="btn btn-sm btn-outline-primary w-100">Voir mes classes</span>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ ($singleClassId ?? null) ? route('teacher.classes.show', $singleClassId) . '#liste-eleves' : route('teacher.classes.index') }}" class="card stat-card stat-card-link">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Élèves Suivis</h6>
                        <h2 class="stat-value">{{ $studentsCount ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle">
                        <i class="fas fa-user-graduate text-primary fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Répartis dans {{ $classesCount ?? 0 }} classe(s)</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ ($singleClassId ?? null) ? route('teacher.classes.show', $singleClassId) . '#matieres-classe' : route('teacher.classes.index') }}" class="card stat-card stat-card-link">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Matières</h6>
                        <h2 class="stat-value">{{ $subjectsCount ?? 0 }}</h2>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 rounded-circle">
                        <i class="fas fa-book text-info fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    @if(isset($subjects) && $subjects->count() > 0)
                        <small class="text-muted">{{ $subjects->pluck('name')->take(2)->implode(', ') }}{{ $subjects->count() > 2 ? '...' : '' }}</small>
                    @else
                        <small class="text-muted">Aucune matière assignée</small>
                    @endif
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('teacher.classes.index') }}" class="card stat-card stat-card-link">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="stat-label">Année scolaire</h6>
                        <h2 class="stat-value" style="font-size: 1.2rem;">{{ $selectedYear ? $selectedYear->name : 'N/A' }}</h2>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 rounded-circle">
                        <i class="fas fa-calendar-alt text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3">
                    @if($isSelectedYearCurrent ?? false)
                        <small class="text-muted">Année en cours</small>
                    @elseif($selectedYear?->isClosed())
                        <small class="text-muted">Année terminée</small>
                    @else
                        <small class="text-muted">Année archivée</small>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Actions Rapides + Notes Récentes --}}
<div class="mb-4 row g-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="gap-2 d-grid">
                    @unless($gradesLocked ?? false)
                        <a href="{{ route('teacher.grades.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Saisir des notes
                        </a>
                    @endunless
                    <a href="{{ route('teacher.attendance.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-clipboard-check me-2"></i>Faire l'appel
                    </a>
                    <a href="{{ route('teacher.classes.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users me-2"></i>Voir mes classes
                    </a>
                    <a href="{{ route('teacher.schedule') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-calendar-alt me-2"></i>Emploi du temps
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i>Notes Récentes</h5>
                <a href="{{ route('teacher.grades.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if(isset($recentGrades) && $recentGrades->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Élève</th>
                                    <th>Matière</th>
                                    <th>Note</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentGrades as $grade)
                                @php
                                    $maxGradeRow = $grade->max_grade_display ?? 20;
                                @endphp
                                <tr>
                                    <td>{{ $grade->user->name ?? 'N/A' }}</td>
                                    <td>{{ $grade->subject->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $grade->grade >= $maxGradeRow / 2 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($grade->grade, 2) }}/{{ rtrim(rtrim(number_format($maxGradeRow, 2), '0'), '.') }}
                                        </span>
                                    </td>
                                    <td>{{ \App\Support\Grading\GradeSequence::labels()[$grade->type] ?? ucfirst($grade->type ?? 'N/A') }}</td>
                                    <td>{{ $grade->date ? $grade->date->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune note récente pour cette année scolaire</p>
                        @unless($gradesLocked ?? false)
                            <a href="{{ route('teacher.grades.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-2"></i>Saisir des notes
                            </a>
                        @endunless
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Performance Moyenne par Classe --}}
@if(isset($classAverages) && count($classAverages) > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Moyenne par Classe</h5>
    </div>
    <div class="card-body">
        <div class="row g-4">
            {{-- Liste des classes --}}
            <div class="col-lg-4">
                <p class="text-muted small mb-3">Sélectionnez une classe pour voir l'évolution des notes (D1, D2, Compo par semestre).</p>
                <div class="list-group class-performance-list">
                    @foreach($classAverages as $index => $item)
                        @php
                            $itemMaxGrade = $item['max_grade'] ?? 20;
                            $badgeColor = ($item['average'] ?? null) === null
                                ? '#6c757d'
                                : \App\Support\Grading\GradeSequence::gradeBadgeColor($item['average'], $itemMaxGrade, $item['is_primaire'] ?? false);
                        @endphp
                        <button type="button"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center class-performance-item {{ $index === 0 ? 'active' : '' }}"
                                data-class-index="{{ $index }}">
                            <span class="fw-medium">
                                <i class="fas fa-users me-2 text-muted"></i>
                                {{ $item['class']->display_name ?? ($item['class']->name ?? 'N/A') }}
                            </span>
                            <span class="badge rounded-pill" style="background-color: {{ $badgeColor }};">
                                {{ ($item['average'] ?? null) !== null ? number_format($item['average'], 2).'/'.rtrim(rtrim(number_format($itemMaxGrade, 2), '0'), '.') : '—' }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Évolution par évaluation --}}
            <div class="col-lg-8">
                <div class="border rounded p-3 h-100 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            <span id="chart-class-title">{{ $classAverages[0]['class']->display_name ?? ($classAverages[0]['class']->name ?? 'Classe') }}</span>
                        </h6>
                        @php $firstMaxGrade = $classAverages[0]['max_grade'] ?? 20; @endphp
                        <span class="small text-muted">
                            Moyenne : <strong id="chart-class-average" style="color: {{ \App\Support\Grading\GradeSequence::gradeBadgeColor($classAverages[0]['average'] ?? 0, $firstMaxGrade, $classAverages[0]['is_primaire'] ?? false) }};">{{ number_format($classAverages[0]['average'] ?? 0, 2) }}/{{ rtrim(rtrim(number_format($firstMaxGrade, 2), '0'), '.') }}</strong>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <label for="chart-subject-select" class="small text-muted mb-0">Matière :</label>
                        <select id="chart-subject-select" class="form-select form-select-sm" style="max-width: 280px;"></select>
                    </div>
                    <p id="chart-legend" class="text-muted small mb-3"></p>
                    <div id="chart-wrapper" class="subject-chart-wrapper">
                        <canvas id="subjectPerformanceChart"></canvas>
                    </div>
                    <p id="chart-empty-msg" class="text-muted text-center py-5 mb-0 d-none">
                        Aucune note saisie pour cette classe et cette matière.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="class-performance-data">@json($classPerformanceJson)</script>
@else
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Moyenne par Classe</h5>
    </div>
    <div class="card-body text-center py-5">
        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
        <p class="text-muted">Aucune donnée de performance disponible pour le moment.</p>
        <p class="text-muted small">Les statistiques apparaîtront une fois que vous aurez saisi des notes.</p>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .stat-card-link {
        display: block;
        text-decoration: none !important;
        color: inherit !important;
        cursor: pointer;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .stat-card-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
    }
    .class-performance-list .list-group-item {
        cursor: pointer;
        border-left: 3px solid transparent;
    }
    .class-performance-list .list-group-item.active {
        border-left-color: var(--bs-primary);
        font-weight: 500;
    }
    .subject-chart-wrapper {
        position: relative;
        height: 320px;
        width: 100%;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dataEl = document.getElementById('class-performance-data');
        const canvas = document.getElementById('subjectPerformanceChart');
        if (!dataEl || !canvas) return;

        const classData = JSON.parse(dataEl.textContent);
        const titleEl = document.getElementById('chart-class-title');
        const avgEl = document.getElementById('chart-class-average');
        const legendEl = document.getElementById('chart-legend');
        const emptyEl = document.getElementById('chart-empty-msg');
        const wrapperEl = document.getElementById('chart-wrapper');
        const subjectSelect = document.getElementById('chart-subject-select');
        let chart = null;
        let currentClassIndex = 0;

        // Couleur par pourcentage de réussite (jamais un seuil absolu type
        // "note >= 10", qui n'a de sens que sur /20) — 4 bandes pour le
        // primaire (barème souvent /10), réussite/échec simple sinon.
        function pointColor(avg, maxGrade, isPrimaire) {
            if (avg === null) return '#adb5bd';
            const ratio = maxGrade > 0 ? avg / maxGrade : 0;
            if (!isPrimaire) return ratio >= 0.5 ? '#198754' : '#dc3545';
            if (ratio >= 0.9) return '#15803d';
            if (ratio >= 0.7) return '#22c55e';
            if (ratio >= 0.5) return '#f59e0b';
            return '#dc2626';
        }

        function formatMax(maxGrade) {
            return Number.isInteger(maxGrade) ? String(maxGrade) : maxGrade.toFixed(1);
        }

        function populateSubjectSelect(subjects, selectedIndex) {
            subjectSelect.innerHTML = '';
            subjects.forEach(function (s, i) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = s.name + (s.average !== null ? ' (' + s.average.toFixed(2) + '/' + formatMax(s.max_grade || 20) + ')' : '');
                subjectSelect.appendChild(opt);
            });
            subjectSelect.value = String(selectedIndex);
            subjectSelect.classList.toggle('d-none', subjects.length <= 1);
            subjectSelect.previousElementSibling?.classList.toggle('d-none', subjects.length <= 1);
        }

        function renderEvolutionChart(classIndex, subjectIndex) {
            const item = classData[classIndex];
            if (!item) return;

            const subjects = item.subjects || [];
            const subject = subjects[subjectIndex];
            if (!subject) return;

            const maxGrade = subject.max_grade || 20;
            const isPrimaire = !!item.is_primaire;
            const evaluations = subject.evaluations || [];
            const withGrades = evaluations.filter(function (e) { return e.average !== null; });

            legendEl.textContent = isPrimaire
                ? 'Évolution des compositions — réussite à partir de ' + (maxGrade / 2).toFixed(1) + '/' + formatMax(maxGrade)
                : 'Évolution S1 · D1 → D2 → Compo puis S2 · D1 → D2 → Compo — seuil : ' + (maxGrade / 2) + '/' + formatMax(maxGrade);

            if (withGrades.length === 0) {
                wrapperEl.classList.add('d-none');
                emptyEl.classList.remove('d-none');
                if (chart) { chart.destroy(); chart = null; }
                return;
            }

            wrapperEl.classList.remove('d-none');
            emptyEl.classList.add('d-none');

            const labels = evaluations.map(function (e) { return e.label; });
            const values = evaluations.map(function (e) { return e.average; });
            const pointColors = evaluations.map(function (e) { return pointColor(e.average, maxGrade, isPrimaire); });

            if (chart) chart.destroy();

            chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: subject.name,
                        data: values,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.12)',
                        pointBackgroundColor: pointColors,
                        pointBorderColor: pointColors,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        borderWidth: 2,
                        fill: true,
                        spanGaps: false,
                        tension: 0.15,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const ev = evaluations[ctx.dataIndex];
                                    if (!ev || ev.average === null) return 'Non saisi';
                                    return ev.average.toFixed(2) + '/' + formatMax(maxGrade) + ' — ' + ev.count + ' note(s)';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: maxGrade,
                            ticks: { stepSize: maxGrade > 10 ? 2 : 1 },
                            title: { display: true, text: 'Moyenne / ' + formatMax(maxGrade) },
                            grid: { color: '#e9ecef' }
                        },
                        x: {
                            ticks: {
                                maxRotation: 0,
                                minRotation: 0,
                                font: { size: 11 }
                            },
                            grid: { display: false }
                        }
                    }
                },
                plugins: [{
                    id: 'passLine',
                    afterDraw: function (c) {
                        const yScale = c.scales.y;
                        const ctx = c.ctx;
                        const passValue = maxGrade / 2;
                        const y = yScale.getPixelForValue(passValue);
                        ctx.save();
                        ctx.strokeStyle = '#6c757d';
                        ctx.setLineDash([6, 4]);
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(c.chartArea.left, y);
                        ctx.lineTo(c.chartArea.right, y);
                        ctx.stroke();
                        ctx.fillStyle = '#6c757d';
                        ctx.font = '10px sans-serif';
                        ctx.fillText(String(passValue), c.chartArea.left - 18, y + 3);
                        ctx.restore();
                    }
                }]
            });
        }

        function renderClass(classIndex, subjectIndex) {
            const item = classData[classIndex];
            if (!item) return;

            currentClassIndex = classIndex;

            document.querySelectorAll('.class-performance-item').forEach(function (btn) {
                btn.classList.toggle('active', parseInt(btn.dataset.classIndex, 10) === classIndex);
            });

            const classMaxGrade = item.max_grade || 20;
            titleEl.textContent = item.class;
            avgEl.textContent = item.average !== null ? Number(item.average).toFixed(2) + '/' + formatMax(classMaxGrade) : '—';
            avgEl.style.color = pointColor(item.average, classMaxGrade, !!item.is_primaire);

            const subjects = item.subjects || [];
            if (subjects.length === 0) {
                subjectSelect.innerHTML = '';
                wrapperEl.classList.add('d-none');
                emptyEl.classList.remove('d-none');
                if (chart) { chart.destroy(); chart = null; }
                return;
            }

            const idx = typeof subjectIndex === 'number' ? subjectIndex : 0;
            populateSubjectSelect(subjects, idx);
            renderEvolutionChart(classIndex, idx);
        }

        document.querySelectorAll('.class-performance-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                renderClass(parseInt(this.dataset.classIndex, 10), 0);
            });
        });

        subjectSelect.addEventListener('change', function () {
            renderEvolutionChart(currentClassIndex, parseInt(this.value, 10));
        });

        renderClass(0, 0);
    });
</script>
@endpush
 