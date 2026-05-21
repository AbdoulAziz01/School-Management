@extends('admin.layouts.app')

@section('title', 'Détails de l\'élève')

@push('styles')
<style>
    .student-grades-table .subject-col {
        min-width: 130px;
        white-space: nowrap;
    }
    .student-grades-table .coef-col {
        width: 52px;
    }
    .student-grades-table .eval-col {
        width: 72px;
        font-size: 0.875rem;
        white-space: nowrap;
    }
    .student-grades-table .avg-col {
        min-width: 88px;
        white-space: nowrap;
    }
    .student-grades-table thead th.eval-col {
        font-size: 0.8rem;
        padding-left: 0.35rem;
        padding-right: 0.35rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- En-tête avec informations de base -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center text-white" style="background-color: #fd7e14;">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Profil de l'élève
                    </h4>
                    <div>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <div class="avatar avatar-xxl mx-auto mb-3">
                                <span class="text-white avatar-text rounded-circle d-flex align-items-center justify-content-center" style="font-size: 2.5rem; width: 100px; height: 100px; background-color: #fd7e14;">
                                    {{ strtoupper(substr($student->name, 0, 1)) }}
                                </span>
                            </div>
                            <span class="badge bg-{{ $student->status === 'approved' ? 'success' : ($student->status === 'pending' ? 'warning' : 'danger') }} fs-6">
                                {{ $student->status === 'approved' ? 'Approuvé' : ($student->status === 'pending' ? 'En attente' : 'Rejeté') }}
                            </span>
                        </div>
                        <div class="col-md-5">
                            <h5 class="mb-3" style="color: #fd7e14;">{{ $student->name }}</h5>
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Identifiant :</strong></td><td>{{ $student->identifier ?? '-' }}</td></tr>
                                <tr><td><strong>Email :</strong></td><td>{{ $student->email }}</td></tr>
                                <tr><td><strong>Téléphone :</strong></td><td>{{ $student->phone ?? 'Non spécifié' }}</td></tr>
                                <tr><td><strong>Date de naissance :</strong></td><td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/Y') : '-' }}</td></tr>
                                <tr><td><strong>Adresse :</strong></td><td>{{ $student->address ?? 'Non spécifiée' }}</td></tr>
                            </table>
                            @include('admin.students._credentials-panel')
                        </div>
                        <div class="col-md-5">
                            <h5 class="mb-3" style="color: #fd7e14;">Informations scolaires</h5>
                            <table class="table table-sm table-borderless">
                                <tr><td><strong>Classe :</strong></td><td><span class="badge bg-info fs-6">{{ $student->class->name ?? 'Non affecté' }}</span></td></tr>
                                @if($student->class && $student->class->level)
                                <tr><td><strong>Niveau :</strong></td><td>{{ $student->class->level->name }}</td></tr>
                                @endif
                                @if($student->class && $student->class->academicYear)
                                <tr><td><strong>Année scolaire :</strong></td><td>{{ $student->class->academicYear->name }}</td></tr>
                                @endif
                                <tr><td><strong>Inscrit le :</strong></td><td>{{ $student->created_at->format('d/m/Y') }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white" style="background-color: #fd7e14;">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $generalAverage }}/20</h2>
                    <small>Moyenne Générale</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $attendanceStats['presence_rate'] ?? 100 }}%</h2>
                    <small>Taux de Présence</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $attendanceStats['absent'] ?? 0 }}</h2>
                    <small>Absences</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $attendanceStats['late'] ?? 0 }}</h2>
                    <small>Retards</small>
                </div>
            </div>
        </div>
    </div>

    @php
        $hasEvolutionData = collect($gradeEvolutionJson['subjects'] ?? [])
            ->flatMap(fn ($s) => $s['evaluations'] ?? [])
            ->contains(fn ($e) => ($e['grade'] ?? null) !== null);
    @endphp

    <!-- Évolution des notes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #0d6efd;">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution des notes</h5>
                </div>
                <div class="card-body">
                    @if(!$hasEvolutionData)
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>Aucune note saisie pour afficher l'évolution (D1, D2, Compo par semestre).
                        </div>
                    @else
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <label for="student-evolution-subject" class="small text-muted mb-0 fw-semibold">Matière :</label>
                            <select id="student-evolution-subject" class="form-select form-select-sm" style="max-width: 320px;">
                                @foreach($gradeEvolutionJson['subjects'] as $index => $subject)
                                    <option value="{{ $index }}" @selected($index === 0)>
                                        {{ $subject['name'] }}
                                        @if($subject['average'] !== null)
                                            ({{ number_format($subject['average'], 2, ',', ' ') }}/20)
                                        @endif
                                    </option>
                                @endforeach
                                <option value="all">Toutes les matières</option>
                            </select>
                        </div>
                        <p class="text-muted small mb-3">Notes réelles enregistrées — S1 · D1 → D2 → Compo puis S2 · D1 → D2 → Compo — seuil : 10/20</p>
                        <div class="student-evolution-chart-wrapper">
                            <canvas id="studentEvolutionChart"></canvas>
                        </div>
                        <script type="application/json" id="student-evolution-data">@json($gradeEvolutionJson)</script>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Notes par matière -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Notes par matière</h5>
                </div>
                <div class="card-body">
                    @if($gradesBySubject->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Aucune note enregistrée pour cet élève.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0 student-grades-table">
                                <thead class="table-light">
                                    <tr>
                                        <th class="subject-col">Matière</th>
                                        <th class="text-center coef-col">Coef.</th>
                                        @foreach($evaluationColumns as $column)
                                            <th class="text-center eval-col">{{ $column['header'] }}</th>
                                        @endforeach
                                        <th class="text-center avg-col">Moyenne</th>
                                        <th class="text-center">Appréciation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gradesBySubject as $subject => $data)
                                        <tr>
                                            <td class="subject-col"><strong>{{ $subject }}</strong></td>
                                            <td class="text-center coef-col">
                                                <span class="badge bg-secondary">{{ $data['coefficient'] }}</span>
                                            </td>
                                            @foreach($evaluationColumns as $column)
                                                @php $note = $data['slots'][$column['key']] ?? null; @endphp
                                                <td class="text-center eval-col">
                                                    @if($note !== null)
                                                        <span class="fw-semibold {{ $note >= 10 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format($note, 2, ',', ' ') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center avg-col">
                                                @if($data['average'] !== null)
                                                    <span class="fw-bold {{ $data['average'] >= 10 ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($data['average'], 2, ',', ' ') }}/20
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($data['average'] === null)
                                                    <span class="text-muted">—</span>
                                                @elseif($data['average'] >= 16)
                                                    <span class="badge bg-success">Excellent</span>
                                                @elseif($data['average'] >= 14)
                                                    <span class="badge" style="background-color: #fd7e14;">Très Bien</span>
                                                @elseif($data['average'] >= 12)
                                                    <span class="badge bg-info">Bien</span>
                                                @elseif($data['average'] >= 10)
                                                    <span class="badge bg-warning text-dark">Assez Bien</span>
                                                @else
                                                    <span class="badge bg-danger">Insuffisant</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-primary">
                                    <tr>
                                        <th colspan="{{ 2 + count($evaluationColumns) }}">Moyenne Générale Pondérée</th>
                                        <th colspan="2" class="text-center">
                                            <span class="fs-5 {{ $generalAverage >= 10 ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($generalAverage, 2, ',', ' ') }}/20
                                            </span>
                                            @if($generalAverage >= 16)
                                                <span class="badge bg-success ms-2">Mention Très Bien</span>
                                            @elseif($generalAverage >= 14)
                                                <span class="badge ms-2" style="background-color: #fd7e14;">Mention Bien</span>
                                            @elseif($generalAverage >= 12)
                                                <span class="badge bg-info ms-2">Mention Assez Bien</span>
                                            @elseif($generalAverage >= 10)
                                                <span class="badge bg-warning text-dark ms-2">Passable</span>
                                            @else
                                                <span class="badge bg-danger ms-2">Insuffisant</span>
                                            @endif
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Absences et Présences -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistiques de présence</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-check-circle text-success me-2"></i>Présences</span>
                            <span class="badge bg-success rounded-pill">{{ $attendanceStats['present'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-times-circle text-danger me-2"></i>Absences</span>
                            <span class="badge bg-danger rounded-pill">{{ $attendanceStats['absent'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clock text-warning me-2"></i>Retards</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $attendanceStats['late'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-medical text-info me-2"></i>Excusées</span>
                            <span class="badge bg-info rounded-pill">{{ $attendanceStats['excused'] ?? 0 }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-file-check me-2" style="color: #fd7e14;"></i>Justifiées</span>
                            <span class="badge rounded-pill" style="background-color: #fd7e14;">{{ $attendanceStats['justified'] ?? 0 }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-times me-2"></i>Historique des absences</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($attendances->isEmpty())
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>Aucune absence enregistrée.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Matière</th>
                                        <th>Statut</th>
                                        <th>Justifié</th>
                                        <th>Motif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attendances->where('status', '!=', 'present')->take(20) as $attendance)
                                        <tr>
                                            <td>{{ $attendance->date ? $attendance->date->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $attendance->subject->name ?? '-' }}</td>
                                            <td>
                                                @if($attendance->status === 'absent')
                                                    <span class="badge bg-danger">Absent</span>
                                                @elseif($attendance->status === 'late')
                                                    <span class="badge bg-warning text-dark">Retard</span>
                                                @elseif($attendance->status === 'excused')
                                                    <span class="badge bg-info">Excusé</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $attendance->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($attendance->justified)
                                                    <i class="fas fa-check text-success"></i>
                                                @else
                                                    <i class="fas fa-times text-danger"></i>
                                                @endif
                                            </td>
                                            <td>{{ $attendance->reason ?? $attendance->comments ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if(!empty($hasEvolutionData) && $hasEvolutionData)
@push('styles')
<style>
    .student-evolution-chart-wrapper {
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
        const dataEl = document.getElementById('student-evolution-data');
        const canvas = document.getElementById('studentEvolutionChart');
        const subjectSelect = document.getElementById('student-evolution-subject');
        if (!dataEl || !canvas || !subjectSelect) return;

        const evolutionData = JSON.parse(dataEl.textContent);
        const labels = evolutionData.labels || [];
        const palette = ['#0d6efd', '#198754', '#fd7e14', '#dc3545', '#6f42c1', '#20c997', '#e83e8c', '#6610f2', '#17a2b8', '#ffc107', '#28a745'];
        let chart = null;

        function formatGrade(grade) {
            if (grade === null || grade === undefined) return 'Non saisi';
            return Number(grade).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '/20';
        }

        function pointColor(grade) {
            if (grade === null) return '#adb5bd';
            if (grade >= 14) return '#198754';
            if (grade >= 10) return '#f59e0b';
            return '#dc3545';
        }

        function buildDatasets(mode) {
            const subjects = evolutionData.subjects || [];

            if (mode === 'all') {
                return subjects.map(function (subject, index) {
                    const color = palette[index % palette.length];
                    const values = (subject.evaluations || []).map(function (e) { return e.grade; });

                    return {
                        label: subject.name,
                        data: values,
                        borderColor: color,
                        backgroundColor: color,
                        pointBackgroundColor: values.map(pointColor),
                        pointBorderColor: values.map(pointColor),
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 2,
                        fill: false,
                        spanGaps: false,
                        tension: 0,
                    };
                });
            }

            const index = parseInt(mode, 10);
            const subject = subjects[index];
            if (!subject) return [];

            const values = (subject.evaluations || []).map(function (e) { return e.grade; });

            return [{
                label: subject.name,
                data: values,
                borderColor: '#0d6efd',
                backgroundColor: '#0d6efd',
                pointBackgroundColor: values.map(pointColor),
                pointBorderColor: values.map(pointColor),
                pointRadius: 7,
                pointHoverRadius: 9,
                borderWidth: 2,
                fill: false,
                spanGaps: false,
                tension: 0,
            }];
        }

        function renderChart(mode) {
            const datasets = buildDatasets(mode).filter(function (ds) {
                return ds.data.some(function (v) { return v !== null; });
            });

            if (datasets.length === 0) {
                if (chart) { chart.destroy(); chart = null; }
                return;
            }

            if (chart) chart.destroy();

            chart = new Chart(canvas, {
                type: 'line',
                data: { labels: labels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: mode === 'all' },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    return ctx.dataset.label + ' : ' + formatGrade(ctx.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 20,
                            ticks: { stepSize: 2 },
                            title: { display: true, text: 'Note / 20' },
                            grid: { color: '#e9ecef' }
                        },
                        x: {
                            ticks: { font: { size: 11 } },
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
                }, {
                    id: 'gradeLabels',
                    afterDatasetsDraw: function (c) {
                        if (mode === 'all') return;

                        const ctx = c.ctx;
                        c.data.datasets.forEach(function (dataset, datasetIndex) {
                            const meta = c.getDatasetMeta(datasetIndex);
                            meta.data.forEach(function (point, index) {
                                const value = dataset.data[index];
                                if (value === null || value === undefined) return;

                                ctx.save();
                                ctx.fillStyle = '#212529';
                                ctx.font = 'bold 11px sans-serif';
                                ctx.textAlign = 'center';
                                ctx.fillText(
                                    Number(value).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                                    point.x,
                                    point.y - 12
                                );
                                ctx.restore();
                            });
                        });
                    }
                }]
            });
        }

        subjectSelect.addEventListener('change', function () {
            renderChart(this.value);
        });

        renderChart(subjectSelect.value);
    });
</script>
@endpush
@endif
