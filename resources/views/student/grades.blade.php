@extends('layouts.student')

@section('title', 'Mes Notes')

@push('styles')
<style>
    .grade-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
    }
    
    .grade-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .grade-card .card-header {
        border: none;
        padding: 15px 20px;
        color: white;
        font-weight: 600;
    }
    
    .grade-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .grade-excellent { background: #dcfce7; color: #166534; }
    .grade-good { background: #fef3c7; color: #b45309; }
    .grade-average { background: #fef3c7; color: #92400e; }
    .grade-poor { background: #fee2e2; color: #991b1b; }
    
    .stat-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    
    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 700;
    }
    
    .bulletin-btn {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        border: none;
        padding: 12px 24px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    .bulletin-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }
    
    .grade-table th {
        background: #f8fafc;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
    }
    
    .subject-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
    }
    
    .appreciation-text {
        font-style: italic;
        color: #64748b;
        font-size: 0.9rem;
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
            @include('partials.dashboard-year-filter', [
                'action' => route('student.grades'),
                'academicYears' => $academicYears ?? collect(),
                'selectedYear' => $selectedYear ?? null,
            ])
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

    <!-- Statistiques générales -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x mb-2 opacity-75"></i>
                    <div class="stat-value">{{ $generalAverage !== null ? number_format($generalAverage, 2) : '—' }}</div>
                    <div class="small opacity-75">Moyenne Générale</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-trophy fa-2x mb-2 opacity-75"></i>
                    <div class="stat-value">{{ $grades->max('average') }}</div>
                    <div class="small opacity-75">Meilleure Moyenne</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-2x mb-2 opacity-75"></i>
                    <div class="stat-value">{{ $grades->count() }}</div>
                    <div class="small opacity-75">Matières</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-check fa-2x mb-2 opacity-75"></i>
                    <div class="stat-value">
                        @php
                            $totalNotes = 0;
                            foreach($grades as $g) {
                                $totalNotes += $g['grades']->count();
                            }
                        @endphp
                        {{ $totalNotes }}
                    </div>
                    <div class="small opacity-75">Notes Total</div>
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
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        Trimestre {{ $studentInfo['trimester'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes par matière -->
    <div class="row g-4">
        @foreach($grades as $subjectData)
            <div class="col-lg-6">
                <div class="card grade-card">
                    <div class="card-header" style="background: {{ $subjectData['subject_color'] ?? '#f59e0b' }};">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="subject-icon me-3" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $subjectData['subject'] }}</h6>
                                    <small class="opacity-75">{{ $subjectData['teacher'] ?? 'Professeur' }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-4 fw-bold">{{ number_format($subjectData['average'], 2) }}/20</div>
                                <small class="opacity-75">Coef. {{ $subjectData['coefficient'] ?? 1 }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm grade-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subjectSlug = Str::slug($subjectData['subject']); @endphp
                                    @foreach($subjectData['grades']->take(5) as $grade)
                                        <tr>
                                            <td>{{ $grade->date ? $grade->date->format('d/m/Y') : ($grade->created_at ? $grade->created_at->format('d/m/Y') : 'N/A') }}</td>
                                            <td>{{ \App\Support\SenegalGradeSequence::LABELS[$grade->type] ?? ucfirst($grade->type ?? 'Devoir') }}</td>
                                            <td>
                                                @if($grade->grade !== null)
                                                    @php
                                                        $gradeValue = (float) $grade->grade;
                                                        $gradeClass = $gradeValue >= 16 ? 'grade-excellent' :
                                                                      ($gradeValue >= 12 ? 'grade-good' :
                                                                      ($gradeValue >= 10 ? 'grade-average' : 'grade-poor'));
                                                    @endphp
                                                    <span class="grade-badge {{ $gradeClass }}">
                                                        {{ number_format($gradeValue, 2) }}/20
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($subjectData['grades']->count() > 5)
                            <div class="collapse" id="more-{{ $subjectSlug }}">
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm grade-table mb-0">
                                        <tbody>
                                            @foreach($subjectData['grades']->slice(5) as $grade)
                                                <tr>
                                                    <td>{{ $grade->date ? $grade->date->format('d/m/Y') : ($grade->created_at ? $grade->created_at->format('d/m/Y') : 'N/A') }}</td>
                                                    <td>{{ \App\Support\SenegalGradeSequence::LABELS[$grade->type] ?? ucfirst($grade->type ?? 'Devoir') }}</td>
                                                    <td>
                                                        @if($grade->grade !== null)
                                                            {{ number_format((float) $grade->grade, 2) }}/20
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-sm btn-link" data-bs-toggle="collapse" data-bs-target="#more-{{ $subjectSlug }}">
                                    Voir plus ({{ $subjectData['grades']->count() - 5 }} notes)
                                </button>
                            </div>
                        @endif
                        
                        <!-- Appréciation -->
                        <div class="mt-3 pt-3 border-top">
                            <p class="appreciation-text mb-0">
                                <i class="fas fa-comment-alt me-2"></i>
                                {{ $subjectData['appreciation'] ?? 'Pas d\'appréciation disponible.' }}
                            </p>
                        </div>
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
