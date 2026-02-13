@extends('layouts.student')

@section('title', 'Bulletin Semestriel')

@push('styles')
<style>
    .bulletin-header {
        background: linear-gradient(135deg, #1a5f2a 0%, #2d8a3e 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 2rem;
    }
    
    .bulletin-header .school-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .bulletin-header .subtitle {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .bulletin-card {
        border: 2px solid #1a5f2a;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 2rem;
    }
    
    .student-info {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border-bottom: 2px solid #1a5f2a;
    }
    
    .student-info .info-item {
        margin-bottom: 0.5rem;
    }
    
    .student-info .info-label {
        font-weight: 600;
        color: #1a5f2a;
    }
    
    .grades-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .grades-table th {
        background-color: #1a5f2a;
        color: white;
        padding: 0.75rem;
        text-align: center;
        font-size: 0.85rem;
        border: 1px solid #155724;
    }
    
    .grades-table td {
        padding: 0.6rem;
        text-align: center;
        border: 1px solid #dee2e6;
        font-size: 0.9rem;
    }
    
    .grades-table tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .grades-table .subject-name {
        text-align: left;
        font-weight: 500;
    }
    
    .grades-table .total-row {
        background-color: #1a5f2a !important;
        color: white;
        font-weight: 700;
    }
    
    .grade-excellent { color: #155724; font-weight: 700; }
    .grade-good { color: #0c5460; font-weight: 600; }
    .grade-average { color: #856404; }
    .grade-poor { color: #721c24; font-weight: 600; }
    
    .appreciation-cell {
        font-size: 0.8rem;
        font-style: italic;
    }
    
    .summary-box {
        background: linear-gradient(135deg, #fff9c4 0%, #fff59d 100%);
        border: 2px solid #f9a825;
        border-radius: 10px;
        padding: 1.5rem;
        margin: 1rem;
    }
    
    .summary-box .average {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a5f2a;
    }
    
    .rank-badge {
        background-color: #ffd700;
        color: #1a5f2a;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        display: inline-block;
    }
    
    .semester-tabs {
        margin-bottom: 1.5rem;
    }
    
    .semester-tabs .nav-link {
        border: 2px solid #1a5f2a;
        color: #1a5f2a;
        margin-right: 0.5rem;
        border-radius: 20px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
    }
    
    .semester-tabs .nav-link.active {
        background-color: #1a5f2a;
        color: white;
    }
    
    .class-stats {
        background-color: #e3f2fd;
        border-radius: 10px;
        padding: 1rem;
        margin: 1rem;
    }
    
    .print-btn {
        background-color: #1a5f2a;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 25px;
        font-weight: 600;
    }
    
    .print-btn:hover {
        background-color: #155724;
        color: white;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        
        .bulletin-card {
            border: 1px solid #000;
            box-shadow: none;
        }
        
        .grades-table th {
            background-color: #333 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Sélection du semestre -->
    <div class="semester-tabs no-print">
        <nav class="nav">
            <a class="nav-link {{ $semester == 1 ? 'active' : '' }}" href="{{ route('student.bulletin', ['semester' => 1]) }}">
                <i class="fas fa-calendar-alt me-2"></i>Semestre 1
            </a>
            <a class="nav-link {{ $semester == 2 ? 'active' : '' }}" href="{{ route('student.bulletin', ['semester' => 2]) }}">
                <i class="fas fa-calendar-alt me-2"></i>Semestre 2
            </a>
            <a class="nav-link" href="{{ route('student.bulletin.annual') }}">
                <i class="fas fa-graduation-cap me-2"></i>Bulletin Annuel
            </a>
        </nav>
    </div>

    <!-- Bulletin -->
    <div class="bulletin-card">
        <!-- En-tête du bulletin -->
        <div class="bulletin-header text-center">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="/images/senegal-flag.png" alt="Drapeau" class="img-fluid" style="max-height: 60px;" onerror="this.style.display='none'">
                </div>
                <div class="col-md-8">
                    <div class="school-name">RÉPUBLIQUE DU SÉNÉGAL</div>
                    <div class="subtitle">Un Peuple - Un But - Une Foi</div>
                    <hr style="border-color: rgba(255,255,255,0.3); margin: 0.5rem 0;">
                    <div class="school-name">ÉTABLISSEMENT SCOLAIRE</div>
                    <div class="subtitle">Bulletin de Notes - Semestre {{ $semester }}</div>
                    <div class="subtitle">Année Scolaire {{ $academicYear->name }}</div>
                </div>
                <div class="col-md-2 text-center">
                    <i class="fas fa-graduation-cap fa-3x" style="opacity: 0.8;"></i>
                </div>
            </div>
        </div>

        <!-- Informations élève -->
        <div class="student-info">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <span class="info-label">Nom et Prénom:</span>
                        <span>{{ $studentInfo['name'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Matricule:</span>
                        <span>{{ $studentInfo['identifier'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date de naissance:</span>
                        <span>{{ $studentInfo['date_of_birth'] }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <span class="info-label">Classe:</span>
                        <span>{{ $studentInfo['class'] }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Série:</span>
                        <span>{{ $studentInfo['serie'] ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Effectif de la classe:</span>
                        <span>{{ $rankData['total'] }} élèves</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des notes -->
        <div class="table-responsive">
            <table class="grades-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Matière</th>
                        <th style="width: 8%;">Coef</th>
                        <th style="width: 10%;">Devoir 1</th>
                        <th style="width: 10%;">Devoir 2</th>
                        <th style="width: 10%;">Moy. Dev.</th>
                        <th style="width: 10%;">Compo</th>
                        <th style="width: 10%;">Moyenne</th>
                        <th style="width: 10%;">Points</th>
                        <th style="width: 12%;">Appréciation</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalCoef = 0;
                        $totalPoints = 0;
                    @endphp
                    @forelse($bulletinData as $data)
                        @php
                            $gradeClass = '';
                            if ($data['moyenne_matiere'] !== null) {
                                if ($data['moyenne_matiere'] >= 16) $gradeClass = 'grade-excellent';
                                elseif ($data['moyenne_matiere'] >= 12) $gradeClass = 'grade-good';
                                elseif ($data['moyenne_matiere'] >= 10) $gradeClass = 'grade-average';
                                else $gradeClass = 'grade-poor';
                                
                                $totalCoef += $data['coefficient'];
                                $totalPoints += $data['points'];
                            }
                        @endphp
                        <tr>
                            <td class="subject-name">{{ $data['subject'] }}</td>
                            <td><strong>{{ $data['coefficient'] }}</strong></td>
                            <td>{{ $data['devoir1'] ?? '-' }}</td>
                            <td>{{ $data['devoir2'] ?? '-' }}</td>
                            <td>{{ $data['moyenne_devoirs'] ?? '-' }}</td>
                            <td>{{ $data['composition'] ?? '-' }}</td>
                            <td class="{{ $gradeClass }}">
                                <strong>{{ $data['moyenne_matiere'] ?? '-' }}</strong>
                            </td>
                            <td>{{ $data['points'] ?? '-' }}</td>
                            <td class="appreciation-cell">{{ $data['appreciation'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucune note enregistrée pour ce semestre
                            </td>
                        </tr>
                    @endforelse
                    
                    @if(count($bulletinData) > 0)
                        <tr class="total-row">
                            <td class="subject-name">TOTAUX</td>
                            <td>{{ $totalCoef }}</td>
                            <td colspan="5"></td>
                            <td>{{ number_format($totalPoints, 2) }}</td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Résumé -->
        <div class="row">
            <div class="col-md-6">
                <div class="summary-box text-center">
                    <h5 class="mb-3">Résultats du Semestre {{ $semester }}</h5>
                    <div class="average">{{ number_format($generalAverage, 2) }}/20</div>
                    <p class="mb-2">Moyenne Générale</p>
                    @if($rankData['rank'])
                        <div class="rank-badge">
                            <i class="fas fa-trophy me-2"></i>
                            {{ $rankData['rank'] }}{{ $rankData['rank'] == 1 ? 'er' : 'ème' }} / {{ $rankData['total'] }}
                        </div>
                    @endif
                    <div class="mt-3">
                        @if($generalAverage >= 16)
                            <span class="badge bg-success fs-6">Mention Très Bien</span>
                        @elseif($generalAverage >= 14)
                            <span class="badge bg-primary fs-6">Mention Bien</span>
                        @elseif($generalAverage >= 12)
                            <span class="badge bg-info fs-6">Mention Assez Bien</span>
                        @elseif($generalAverage >= 10)
                            <span class="badge bg-warning text-dark fs-6">Passable</span>
                        @else
                            <span class="badge bg-danger fs-6">Insuffisant</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="class-stats">
                    <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Statistiques de la classe</h5>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-bold text-primary fs-4">{{ $classStats['average'] ?? '-' }}</div>
                            <small>Moy. Classe</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success fs-4">{{ $classStats['highest'] ?? '-' }}</div>
                            <small>Plus haute</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-danger fs-4">{{ $classStats['lowest'] ?? '-' }}</div>
                            <small>Plus basse</small>
                        </div>
                    </div>
                </div>
                
                <!-- Observations -->
                <div class="p-3 mx-3 mb-3 border rounded">
                    <h6 class="fw-bold">Observations du Conseil de Classe:</h6>
                    <p class="mb-0 fst-italic">
                        @if($generalAverage >= 14)
                            Excellent travail. Félicitations pour ces résultats remarquables. Continuez ainsi!
                        @elseif($generalAverage >= 12)
                            Bon travail d'ensemble. Maintenez vos efforts pour progresser davantage.
                        @elseif($generalAverage >= 10)
                            Résultats convenables mais des progrès sont attendus. Travaillez régulièrement.
                        @elseif($generalAverage >= 8)
                            Résultats insuffisants. Un effort soutenu est indispensable pour le prochain semestre.
                        @else
                            Résultats très insuffisants. Un travail sérieux et régulier est impératif.
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="row p-4 border-top">
            <div class="col-md-4 text-center">
                <p class="mb-4">Le Chef d'Établissement</p>
                <div style="height: 60px;"></div>
                <p class="border-top pt-2 mx-4">(Signature et cachet)</p>
            </div>
            <div class="col-md-4 text-center">
                <p class="mb-4">Le Professeur Principal</p>
                <div style="height: 60px;"></div>
                <p class="border-top pt-2 mx-4">(Signature)</p>
            </div>
            <div class="col-md-4 text-center">
                <p class="mb-4">Le Parent / Tuteur</p>
                <div style="height: 60px;"></div>
                <p class="border-top pt-2 mx-4">(Signature)</p>
            </div>
        </div>
    </div>

    <!-- Boutons d'action -->
    <div class="text-center no-print">
        <button onclick="window.print()" class="print-btn me-2">
            <i class="fas fa-print me-2"></i>Imprimer le bulletin
        </button>
        <a href="{{ route('student.grades') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour aux notes
        </a>
    </div>
</div>
@endsection
