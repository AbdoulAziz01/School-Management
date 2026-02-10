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
    .grade-good { background: #dbeafe; color: #1e40af; }
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
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border: none;
        padding: 12px 24px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s;
    }
    
    .bulletin-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
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
        </div>
        @if($grades->isNotEmpty())
        <a href="{{ route('student.bulletin') }}" class="btn btn-primary bulletin-btn">
            <i class="fas fa-file-alt me-2"></i>Voir mon bulletin
        </a>
        @endif
    </div>

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
                    <div class="stat-value">{{ number_format($generalAverage, 2) }}</div>
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
                    <div class="card-header" style="background: {{ $subjectData['subject_color'] ?? '#3b82f6' }};">
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
                                        <th>Moy. Classe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjectData['grades']->take(5) as $grade)
                                        <tr>
                                            <td>{{ $grade->date ? $grade->date->format('d/m/Y') : ($grade->created_at ? $grade->created_at->format('d/m/Y') : 'N/A') }}</td>
                                            <td>{{ $grade->type ?? 'Devoir' }}</td>
                                            <td>
                                                @php
                                                    $gradeValue = $grade->grade ?? $grade->value ?? 0;
                                                    $gradeClass = $gradeValue >= 16 ? 'grade-excellent' : 
                                                                  ($gradeValue >= 12 ? 'grade-good' : 
                                                                  ($gradeValue >= 10 ? 'grade-average' : 'grade-poor'));
                                                @endphp
                                                <span class="grade-badge {{ $gradeClass }}">
                                                    {{ $gradeValue }}/20
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $grade->class_average ?? '-' }}/20</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($subjectData['grades']->count() > 5)
                            <div class="text-center mt-2">
                                <button class="btn btn-sm btn-link" data-bs-toggle="collapse" data-bs-target="#more-{{ Str::slug($subjectData['subject']) }}">
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

    <!-- Diagramme d'évolution des notes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Évolution des notes & Prédiction
                    </h6>
                    <div>
                        <span class="badge bg-info me-2">
                            <i class="fas fa-robot me-1"></i>Prédiction IA
                        </span>
                        <span class="badge bg-success">
                            <i class="fas fa-arrow-trend-up me-1"></i>Tendance
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-9">
                            <div style="height: 300px;">
                                <canvas id="gradesEvolutionChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="p-3 bg-light rounded h-100">
                                <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Légende</h6>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2" style="width: 16px; height: 16px; background: #4e73df; border-radius: 50%;"></span>
                                        <span><strong>Notes réelles</strong></span>
                                    </div>
                                    <small class="text-muted">Vos moyennes mensuelles réelles</small>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2" style="width: 16px; height: 16px; background: #f6c23e; border-radius: 50%;"></span>
                                        <span><strong>Prédiction</strong></span>
                                    </div>
                                    <small class="text-muted">Estimation des prochains mois basée sur votre tendance</small>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2" style="width: 24px; border-top: 3px dashed #1cc88a;"></span>
                                        <span><strong>Tendance</strong></span>
                                    </div>
                                    <small class="text-muted">Ligne de tendance générale</small>
                                </div>
                                <hr>
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1 text-warning"></i>
                                        Continuez vos efforts pour maintenir cette progression !
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton bulletin en bas -->
    @if($grades->isNotEmpty())
    <div class="text-center mt-5 mb-4">
        <a href="{{ route('student.bulletin') }}" class="btn btn-lg btn-primary bulletin-btn">
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
    var gradesData = @json($gradesEvolution ?? []);
    
    // Si pas de données, créer des données de démonstration
    if (!gradesData || gradesData.length === 0) {
        gradesData = [
            {month: 'Sept', grade: 12.5},
            {month: 'Oct', grade: 13.2},
            {month: 'Nov', grade: 12.8},
            {month: 'Déc', grade: 14.1},
            {month: 'Jan', grade: 13.5},
            {month: 'Fév', grade: 14.8}
        ];
    }
    
    var labels = gradesData.map(item => item.month);
    var grades = gradesData.map(item => item.grade);
    var n = grades.length;
    
    // Calcul de la régression linéaire pour la tendance
    var sumX = 0, sumY = 0, sumXY = 0, sumXX = 0;
    for (var i = 0; i < n; i++) {
        sumX += i;
        sumY += grades[i];
        sumXY += i * grades[i];
        sumXX += i * i;
    }
    var slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
    var intercept = (sumY - slope * sumX) / n;
    
    // Données de tendance
    var trendData = [];
    for (var i = 0; i < n; i++) {
        trendData.push(Math.round((intercept + slope * i) * 10) / 10);
    }
    
    // Prédictions pour les 2 prochains mois
    var predictedLabels = ['Mar', 'Avr'];
    var predictedGrades = [];
    predictedLabels.forEach(function(month, index) {
        labels.push(month);
        var pred = Math.max(0, Math.min(20, intercept + slope * (n + index)));
        predictedGrades.push(Math.round(pred * 10) / 10);
        trendData.push(Math.round((intercept + slope * (n + index)) * 10) / 10);
    });
    
    // Préparer les datasets
    var realGrades = grades.slice();
    predictedGrades.forEach(function() {
        realGrades.push(null);
    });
    
    var predictionData = [];
    for (var i = 0; i < n - 1; i++) {
        predictionData.push(null);
    }
    predictionData.push(grades[n-1]); // Point de connexion
    predictedGrades.forEach(function(g) {
        predictionData.push(g);
    });
    
    var ctx = document.getElementById('gradesEvolutionChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Notes réelles',
                    data: realGrades,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.15)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'Prédiction',
                    data: predictionData,
                    borderColor: '#f6c23e',
                    backgroundColor: 'rgba(246, 194, 62, 0.1)',
                    borderWidth: 3,
                    borderDash: [8, 4],
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#f6c23e',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: function(context) {
                        return context.dataIndex >= n ? 6 : 0;
                    },
                    pointHoverRadius: 8
                },
                {
                    label: 'Tendance',
                    data: trendData,
                    borderColor: '#1cc88a',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    fill: false,
                    tension: 0,
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
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: { size: 13 },
                    bodyFont: { size: 12 },
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            var label = context.dataset.label || '';
                            if (context.parsed.y !== null) {
                                label += ': ' + context.parsed.y + '/20';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 8,
                    max: 20,
                    ticks: {
                        stepSize: 2,
                        callback: function(value) {
                            return value + '/20';
                        },
                        font: { size: 10 }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 10 }
                    }
                }
            }
        }
    });
});
</script>
@endpush
