@extends('teacher.layouts.app')

@section('title', 'Tableau de bord - Enseignant')

@section('content')
<div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 class="mb-0 h3">Tableau de bord</h1>
        <p class="text-muted mb-0">Bienvenue, {{ $teacher->name }}</p>
    </div>
    @include('partials.dashboard-year-filter', [
        'action' => route('teacher.dashboard'),
        'academicYears' => $academicYears ?? collect(),
        'selectedYear' => $selectedYear ?? null,
    ])
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
        <div class="card stat-card">
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
                    <a href="{{ route('teacher.classes.index') }}" class="btn btn-sm btn-outline-primary w-100">Voir mes classes</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
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
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
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
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card stat-card">
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
        </div>
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
                                <tr>
                                    <td>{{ $grade->user->name ?? 'N/A' }}</td>
                                    <td>{{ $grade->subject->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $grade->grade >= 10 ? 'bg-success' : 'bg-danger' }}">
                                            {{ number_format($grade->grade, 2) }}/20
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($grade->type ?? 'N/A') }}</td>
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
                        <button type="button"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center class-performance-item {{ $index === 0 ? 'active' : '' }}"
                                data-class-index="{{ $index }}">
                            <span class="fw-medium">
                                <i class="fas fa-users me-2 text-muted"></i>
                                {{ $item['class']->display_name ?? ($item['class']->name ?? 'N/A') }}
                            </span>
                            <span class="badge {{ ($item['average'] ?? null) === null ? 'bg-secondary' : ($item['average'] >= 10 ? 'bg-success' : 'bg-danger') }} rounded-pill">
                                {{ ($item['average'] ?? null) !== null ? number_format($item['average'], 2).'/20' : '—' }}
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
                        <span class="small text-muted">
                            Moyenne : <strong id="chart-class-average" class="{{ ($classAverages[0]['average'] ?? 0) >= 10 ? 'text-success' : 'text-danger' }}">{{ number_format($classAverages[0]['average'] ?? 0, 2) }}/20</strong>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <label for="chart-subject-select" class="small text-muted mb-0">Matière :</label>
                        <select id="chart-subject-select" class="form-select form-select-sm" style="max-width: 280px;"></select>
                    </div>
                    <p class="text-muted small mb-3">Évolution S1 · D1 → D2 → Compo puis S2 · D1 → D2 → Compo — seuil : 10/20</p>
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
        const emptyEl = document.getElementById('chart-empty-msg');
        const wrapperEl = document.getElementById('chart-wrapper');
        const subjectSelect = document.getElementById('chart-subject-select');
        let chart = null;
        let currentClassIndex = 0;

        function pointColor(avg) {
            if (avg === null) return '#adb5bd';
            if (avg >= 14) return '#198754';
            if (avg >= 10) return '#f59e0b';
            return '#dc3545';
        }

        function populateSubjectSelect(subjects, selectedIndex) {
            subjectSelect.innerHTML = '';
            subjects.forEach(function (s, i) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = s.name + (s.average !== null ? ' (' + s.average.toFixed(2) + '/20)' : '');
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

            const evaluations = subject.evaluations || [];
            const withGrades = evaluations.filter(function (e) { return e.average !== null; });

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
            const pointColors = evaluations.map(function (e) { return pointColor(e.average); });

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
                                    return ev.average.toFixed(2) + '/20 — ' + ev.count + ' note(s)';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 20,
                            ticks: { stepSize: 2 },
                            title: { display: true, text: 'Moyenne / 20' },
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
                        const y = yScale.getPixelForValue(10);
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
                        ctx.fillText('10', c.chartArea.left - 18, y + 3);
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

            titleEl.textContent = item.class;
            avgEl.textContent = item.average !== null ? Number(item.average).toFixed(2) + '/20' : '—';
            avgEl.className = item.average !== null && item.average >= 10 ? 'text-success' : (item.average !== null ? 'text-danger' : 'text-muted');

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
 