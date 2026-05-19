@extends('admin.layouts.app')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* Style Select2 */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem);
    }
    
    /* Limiter la hauteur du dropdown */
    .select2-dropdown {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .select2-results__options {
        max-height: 250px;
        overflow-y: auto;
    }
    
    .select2-results__option {
        padding: 8px 12px;
    }
    
    .select2-results__option--highlighted {
        background-color: #0d6efd !important;
        color: white;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }

    .stat-cards-row > [class*="col"] {
        display: flex;
    }

    .stat-card-btn,
    .stat-card-box,
    .bucket-card-btn {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card-btn {
        min-height: 10.5rem;
        display: flex !important;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        padding: 1rem !important;
    }

    .stat-card-score {
        line-height: 1.1;
        margin-bottom: 0.35rem;
    }

    .stat-card-title {
        line-height: 1.3;
    }

    .stat-card-meta {
        display: block;
        min-height: 1.35rem;
        line-height: 1.35rem;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .stat-card-action {
        display: block;
        min-height: 1.25rem;
        line-height: 1.25rem;
        margin-top: 0.25rem;
    }

    .stat-card-box {
        min-height: 10.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .stat-card-btn:hover,
    .bucket-card-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }
</style>
@endpush

@section('content')
    <div class="flex-wrap pt-3 pb-2 mb-4 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
        <h1 class="mb-0 h3">Affectation des Élèves aux Classes</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{--
    Section commentée car la variable $unassignedStudents n'est pas définie
    Cette section affichait la liste des élèves non affectés à une classe
    Pour la réactiver, il faudrait :
    1. Récupérer les élèves non affectés dans ClassController@show
    2. Passer cette variable à la vue
    3. Décommenter cette section
    --}}
    
    <!-- Informations de la classe -->
    <div class="mb-4 shadow-sm card">
        <div class="text-white card-header bg-primary">
            <h5 class="mb-0">
                <i class="fas fa-info-circle"></i> Informations de la classe : {{ $class->name }}
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p class="mb-1"><strong><i class="fas fa-calendar me-2"></i>Année scolaire :</strong></p>
                    <p class="text-muted">{{ $class->academicYear->name ?? 'Non définie' }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong><i class="fas fa-layer-group me-2"></i>Niveau :</strong></p>
                    <p class="text-muted">{{ $class->level->name ?? 'Non défini' }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong><i class="fas fa-users me-2"></i>Effectif :</strong></p>
                    <p class="text-muted">{{ $classStats['total_students'] }} élève(s)</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1"><strong><i class="fas fa-chalkboard-teacher me-2"></i>Enseignants :</strong></p>
                    <p class="text-muted">{{ $class->teachers->count() }} professeur(s)</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistiques de la classe -->
    <div class="mb-4 shadow-sm card">
        <div class="card-header" style="background-color: #fd7e14; color: white;">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar"></i> Statistiques et Performances de la Classe
            </h5>
        </div>
        <div class="card-body">
            <!-- Cartes de statistiques principales -->
            <div class="mb-4 row g-3 stat-cards-row align-items-stretch">
                <div class="col-6 col-md-3">
                    <button type="button" class="w-100 btn text-center border rounded bg-light stat-card-btn"
                            data-bs-toggle="modal" data-bs-target="#classRankingModal">
                        <div class="stat-card-score display-6 fw-bold" style="color: #fd7e14;">{{ $classStats['average'] }}/20</div>
                        <small class="text-muted stat-card-title"><i class="fas fa-chart-line me-1"></i>Moyenne Générale</small>
                        <small class="stat-card-meta text-muted">{{ $classStats['students_with_grades'] ?? 0 }} élève(s) noté(s)</small>
                        <small class="stat-card-action text-primary">Voir le classement</small>
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    @if(!empty($classStats['best_student']))
                        <button type="button" class="w-100 btn text-center border rounded bg-light stat-card-btn"
                                data-bs-toggle="modal" data-bs-target="#bestStudentModal">
                            <div class="stat-card-score display-6 fw-bold text-success">{{ $classStats['best_average'] }}/20</div>
                            <small class="text-muted stat-card-title"><i class="fas fa-arrow-up me-1"></i>Meilleure Moyenne</small>
                            <small class="stat-card-meta text-success fw-semibold">{{ $classStats['best_student']['name'] }}</small>
                            <small class="stat-card-action text-primary">Voir l'élève</small>
                        </button>
                    @else
                        <div class="p-3 text-center border rounded bg-light">
                            <div class="mb-2 display-6 fw-bold text-success">—</div>
                            <small class="text-muted">Meilleure Moyenne</small>
                        </div>
                    @endif
                </div>
                <div class="col-6 col-md-3">
                    @if(!empty($classStats['lowest_student']))
                        <button type="button" class="w-100 btn text-center border rounded bg-light stat-card-btn"
                                data-bs-toggle="modal" data-bs-target="#lowestStudentModal">
                            <div class="stat-card-score display-6 fw-bold text-danger">{{ $classStats['lowest_average'] }}/20</div>
                            <small class="text-muted stat-card-title"><i class="fas fa-arrow-down me-1"></i>Plus Basse Moyenne</small>
                            <small class="stat-card-meta text-danger fw-semibold">{{ $classStats['lowest_student']['name'] }}</small>
                            <small class="stat-card-action text-primary">Voir l'élève</small>
                        </button>
                    @else
                        <div class="p-3 text-center border rounded bg-light">
                            <div class="mb-2 display-6 fw-bold text-danger">—</div>
                            <small class="text-muted">Plus Basse Moyenne</small>
                        </div>
                    @endif
                </div>
                <div class="col-6 col-md-3">
                    <button type="button" class="w-100 btn text-center border rounded bg-light stat-card-btn"
                            data-bs-toggle="modal" data-bs-target="#classPassFailModal">
                        <div class="stat-card-score display-6 fw-bold text-primary">{{ $classStats['pass_rate'] }}%</div>
                        <small class="text-muted stat-card-title"><i class="fas fa-check-circle me-1"></i>Taux de Réussite</small>
                        <small class="stat-card-meta text-muted">{{ $classStats['pass_count'] }} admis · {{ $classStats['fail_count'] }} en difficulté</small>
                        <small class="stat-card-action text-primary">Voir les élèves</small>
                    </button>
                </div>
            </div>
            
            <!-- Statistiques détaillées -->
            <div class="row g-3">
                <!-- Répartition Réussite/Échec -->
                <div class="col-md-4">
                    <div class="p-3 border rounded">
                        <h6 class="mb-3"><i class="fas fa-users me-2"></i>Répartition des Résultats</h6>
                        <button type="button" class="mb-2 w-100 btn btn-sm btn-outline-success d-flex justify-content-between align-items-center"
                                data-bs-toggle="modal" data-bs-target="#classPassFailModal">
                            <span><i class="fas fa-check text-success me-2"></i>Élèves >= 10/20</span>
                            <span class="badge bg-success">{{ $classStats['pass_count'] }}</span>
                        </button>
                        <button type="button" class="mb-2 w-100 btn btn-sm btn-outline-danger d-flex justify-content-between align-items-center"
                                data-bs-toggle="modal" data-bs-target="#classPassFailModal">
                            <span><i class="fas fa-times text-danger me-2"></i>Élèves &lt; 10/20</span>
                            <span class="badge bg-danger">{{ $classStats['fail_count'] }}</span>
                        </button>
                        <div class="d-flex justify-content-between">
                            <span><i class="fas fa-clipboard text-secondary me-2"></i>Total Notes</span>
                            <span class="badge bg-secondary">{{ $classStats['total_grades'] }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Distribution des moyennes -->
                <div class="col-md-8">
                    <div class="p-3 border rounded">
                        <h6 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Distribution des Moyennes</h6>
                        <div class="text-center row g-2" id="grade-distribution-buckets">
                            <div class="col">
                                <button type="button" class="w-100 btn bucket-card-btn p-2 rounded border-0"
                                        style="background-color: #28a745; color: white;"
                                        data-bs-toggle="modal" data-bs-target="#bucketModalexcellent">
                                    <div class="fs-4 fw-bold">{{ $classStats['grade_distribution']['excellent'] }}</div>
                                    <small>Excellent<br>(≥16)</small>
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="w-100 btn bucket-card-btn p-2 rounded border-0"
                                        style="background-color: #17a2b8; color: white;"
                                        data-bs-target="#bucketModalgood" data-bs-toggle="modal">
                                    <div class="fs-4 fw-bold">{{ $classStats['grade_distribution']['good'] }}</div>
                                    <small>Bien<br>(14-15)</small>
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="w-100 btn bucket-card-btn p-2 rounded border-0"
                                        style="background-color: #ffc107; color: #212529;"
                                        data-bs-target="#bucketModalaverage" data-bs-toggle="modal">
                                    <div class="fs-4 fw-bold">{{ $classStats['grade_distribution']['average'] }}</div>
                                    <small>Assez Bien<br>(12-13)</small>
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="w-100 btn bucket-card-btn p-2 rounded border-0"
                                        style="background-color: #fd7e14; color: white;"
                                        data-bs-target="#bucketModalpassing" data-bs-toggle="modal">
                                    <div class="fs-4 fw-bold">{{ $classStats['grade_distribution']['passing'] }}</div>
                                    <small>Passable<br>(10-11)</small>
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" class="w-100 btn bucket-card-btn p-2 rounded border-0"
                                        style="background-color: #dc3545; color: white;"
                                        data-bs-target="#bucketModalfailing" data-bs-toggle="modal">
                                    <div class="fs-4 fw-bold">{{ $classStats['grade_distribution']['failing'] }}</div>
                                    <small>Insuffisant<br>(&lt;10)</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($classStats['best_student']))
    <div class="modal fade" id="bestStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-trophy me-2"></i>Meilleure moyenne — {{ $class->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1 text-muted">Élève en tête de la classe</p>
                    <h4 class="mb-3">{{ $classStats['best_student']['name'] }}</h4>
                    <ul class="list-unstyled mb-0">
                        <li><strong>Moyenne :</strong> {{ $classStats['best_average'] }}/20</li>
                        <li><strong>Identifiant :</strong> {{ $classStats['best_student']['identifier'] ?? '—' }}</li>
                        <li><strong>Email :</strong> {{ $classStats['best_student']['email'] ?? '—' }}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <a href="{{ $classStats['best_student']['url'] }}" class="btn btn-success">Voir la fiche complète</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($classStats['lowest_student']))
    <div class="modal fade" id="lowestStudentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Plus basse moyenne — {{ $class->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1 text-muted">Élève à accompagner en priorité</p>
                    <h4 class="mb-3">{{ $classStats['lowest_student']['name'] }}</h4>
                    <ul class="list-unstyled mb-0">
                        <li><strong>Moyenne :</strong> {{ $classStats['lowest_average'] }}/20</li>
                        <li><strong>Identifiant :</strong> {{ $classStats['lowest_student']['identifier'] ?? '—' }}</li>
                        <li><strong>Email :</strong> {{ $classStats['lowest_student']['email'] ?? '—' }}</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <a href="{{ $classStats['lowest_student']['url'] }}" class="btn btn-danger">Voir la fiche complète</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modales : classement, réussite/échec, tranches de moyennes --}}
    <div class="modal fade" id="classRankingModal" tabindex="-1" aria-labelledby="classRankingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="classRankingModalLabel">Classement de la classe {{ $class->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Élève</th>
                                <th>Identifiant</th>
                                <th class="text-end">Moyenne</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classStats['ranking'] ?? [] as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $student['name'] }}</td>
                                    <td><small>{{ $student['identifier'] ?? '—' }}</small></td>
                                    <td class="text-end fw-bold">{{ $student['average'] }}/20</td>
                                    <td class="text-end">
                                        <a href="{{ $student['url'] }}" class="btn btn-sm btn-outline-primary">Voir la fiche</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">Aucun élève avec des notes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="classPassFailModal" tabindex="-1" aria-labelledby="classPassFailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="classPassFailModalLabel">Réussite et échec — {{ $class->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-check me-1"></i> Élèves admis (≥ 10/20)</h6>
                            <ul class="list-group list-group-flush">
                                @forelse($classStats['passing_students'] ?? [] as $student)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $student['name'] }} <small class="text-muted">({{ $student['average'] }}/20)</small></span>
                                        <a href="{{ $student['url'] }}" class="btn btn-sm btn-outline-success">Fiche</a>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Aucun</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger"><i class="fas fa-times me-1"></i> Élèves en difficulté (&lt; 10/20)</h6>
                            <ul class="list-group list-group-flush">
                                @forelse($classStats['failing_students'] ?? [] as $student)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $student['name'] }} <small class="text-muted">({{ $student['average'] }}/20)</small></span>
                                        <a href="{{ $student['url'] }}" class="btn btn-sm btn-outline-danger">Fiche</a>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Aucun</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $bucketTitles = [
            'excellent' => 'Élèves — Excellent (≥16)',
            'good' => 'Élèves — Bien (14-15)',
            'average' => 'Élèves — Assez bien (12-13)',
            'passing' => 'Élèves — Passable (10-11)',
            'failing' => 'Élèves — Insuffisant (<10)',
        ];
    @endphp
    @foreach($bucketTitles as $bucketKey => $bucketTitle)
        <div class="modal fade" id="bucketModal{{ $bucketKey }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $bucketTitle }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-group list-group-flush">
                            @forelse($classStats['students_by_bucket'][$bucketKey] ?? [] as $student)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $student['name'] }} <small class="text-muted">({{ $student['average'] }}/20)</small></span>
                                    <a href="{{ $student['url'] }}" class="btn btn-sm btn-outline-primary">Fiche</a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">Aucun élève dans cette tranche.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    
    <!-- Graphique d'évolution -->
    <div class="mb-4 shadow-sm card">
        <div class="text-white card-header bg-info">
            <h5 class="mb-0">
                <i class="fas fa-chart-line"></i> Évolution des Performances
            </h5>
        </div>
        <div class="card-body">
            @if(!empty($classStats['evolution_period']))
                <p class="text-muted small mb-2">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Période : <strong>{{ $classStats['evolution_period'] }}</strong>
                    @if($class->academicYear)
                        ({{ $class->academicYear->name }})
                    @endif
                </p>
            @endif
            <div style="position: relative; height: 320px;" id="evolutionChartWrap">
                <canvas id="evolutionChart"></canvas>
            </div>
            <div id="evolutionChartEmpty" class="d-none py-5 text-center text-muted">
                <i class="fas fa-chart-line fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">Aucune note enregistrée sur cette période.</p>
            </div>
            <div class="mt-3 text-center">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Moyenne mensuelle de la classe (notes du mois) — courbe avec variations d'un mois à l'autre.
                    La ligne verte pointillée indique le seuil de réussite (10/20).
                </small>
            </div>
        </div>
    </div>
    
    <!-- Enseignants affectés -->

    <!-- Enseignants affectés -->
    <div class="mb-4 shadow-sm card">
        <div class="text-white card-header" style="background-color: #fd7e14;">
            <h5 class="mb-0">
                <i class="fas fa-chalkboard-teacher"></i> Enseignants affectés
                <span class="badge bg-light text-dark float-end">{{ $class->teachers->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($class->teachers->isEmpty())
                <div class="py-3 text-center text-muted">
                    Aucun enseignant n'est affecté à cette classe.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Matières</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($class->teachers as $teacher)
                                <tr>
                                    <td>{{ $teacher->name }}</td>
                                    <td><small>{{ $teacher->email }}</small></td>
                                    <td>
                                        @if($teacher->subjects->isNotEmpty())
                                            @foreach($teacher->subjects as $subject)
                                                <span class="badge bg-info">{{ $subject->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Aucune matière</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-sm" style="color: #fd7e14; border-color: #fd7e14;">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Élèves affectés -->
    <div class="shadow-sm card">
        <div class="text-white card-header bg-success">
            <h5 class="mb-0">
                <i class="fas fa-user-graduate"></i> Élèves affectés
                <span class="badge bg-light text-dark float-end">{{ $assignedStudents->total() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($assignedStudents->isEmpty())
                <div class="py-5 text-center text-muted">
                    <i class="mb-3 fas fa-user-graduate fa-4x"></i>
                    <p class="h5">Aucun élève affecté pour le moment</p>
                    <p class="text-muted">Les élèves affectés apparaîtront ici</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="12%">Identifiant</th>
                                <th width="25%">Nom complet</th>
                                <th width="25%">Email</th>
                                <th width="25%">Classe</th>
                                <th width="13%" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedStudents as $student)
                            <tr>
                                <td><strong class="text-success">{{ $student->identifier }}</strong></td>
                                <td>{{ $student->name }}</td>
                                <td><small>{{ $student->email }}</small></td>
                                <td>
                                    <span class="badge fs-6" style="background-color: #fd7e14;">
                                        {{ $student->class->name ?? 'Non affecté' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('admin.students.unassign', $student) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir retirer {{ $student->name }} de sa classe ?')">
                                            <i class="fas fa-user-minus"></i> Retirer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3 d-flex justify-content-center">
                    {{ $assignedStudents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<!-- jQuery (requis pour Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialiser Select2 sur tous les selects de classe
    $('.class-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Sélectionner une classe',
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true,
        language: {
            noResults: function() {
                return "Aucune classe trouvée";
            },
            searching: function() {
                return "Recherche en cours...";
            }
        }
    });
    
    // Trier les options par nom de classe
    $('.class-select').each(function() {
        const select = $(this);
        const options = select.find('option:not(:first)').sort(function(a, b) {
            return $(a).text().localeCompare($(b).text());
        });
        
        select.find('option:not(:first)').remove();
        select.append(options);
        
        // Réinitialiser Select2 après le tri
        select.trigger('change.select2');
    });
    
    // Gérer le clic sur le bouton Affecter
    $('.btn-assign').on('click', function() {
        const studentId = $(this).data('student-id');
        const form = $(this).closest('tr').find('.assign-form');
        const classId = form.find('select[name="class_id"]').val();
        
        if (!classId) {
            alert('Veuillez sélectionner une classe avant d\'affecter l\'élève.');
            return;
        }
        
        // Soumettre le formulaire
        form.submit();
    });
    
    // Animation de chargement lors de la soumission
    $('form').on('submit', function() {
        const btn = $(this).closest('tr').find('.btn-assign');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Affectation...');
    });
});
</script>

<!-- Chart.js pour le graphique d'évolution -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('evolutionChart');
    const chartWrap = document.getElementById('evolutionChartWrap');
    const emptyState = document.getElementById('evolutionChartEmpty');
    if (!canvas) {
        return;
    }

    const monthlyData = @json($classStats['monthly_averages']);
    const labels = monthlyData.map(item => item.month);
    const averages = monthlyData.map(item => item.average);
    const counts = monthlyData.map(item => item.count);
    const hasAnyGrade = averages.some(value => value !== null && value !== undefined);

    if (!hasAnyGrade) {
        chartWrap.classList.add('d-none');
        emptyState.classList.remove('d-none');
        return;
    }

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(253, 126, 20, 0.8)');
    gradient.addColorStop(1, 'rgba(253, 126, 20, 0.1)');

    const passingLine = labels.map(() => 10);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Moyenne de la classe',
                    data: averages,
                    borderColor: '#fd7e14',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    spanGaps: true,
                    pointBackgroundColor: '#fd7e14',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: averages.map(v => (v === null ? 0 : 5)),
                    pointHoverRadius: 7
                },
                {
                    label: 'Ligne de passage (10/20)',
                    data: passingLine,
                    borderColor: '#28a745',
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
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            if (value === null || value === undefined) {
                                return context.dataset.label + ' : —';
                            }
                            return context.dataset.label + ' : ' + value + '/20';
                        },
                        afterLabel: function(context) {
                            const index = context.dataIndex;
                            if (context.datasetIndex === 0 && counts[index]) {
                                return 'Notes prises en compte : ' + counts[index];
                            }
                            if (context.datasetIndex === 0 && (averages[index] === null || averages[index] === undefined)) {
                                return 'Pas encore de notes à cette date';
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
                        minRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 12
                    },
                    title: {
                        display: true,
                        text: 'Période'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
});
</script>
@endpush
