<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin Scolaire - {{ $studentInfo['name'] }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
        
        * {
            font-family: 'Roboto', sans-serif;
        }
        
        body {
            background: #f1f5f9;
        }
        
        .bulletin-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .bulletin-paper {
            background: white;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        .school-header {
            text-align: center;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            background: #1e3a8a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2rem;
        }
        
        .school-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 5px;
        }
        
        .school-subtitle {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .bulletin-title {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 1.3rem;
            font-weight: 600;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .student-info-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #64748b;
            width: 150px;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .grades-table th {
            background: #1e3a8a;
            color: white;
            padding: 12px 10px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .grades-table th:first-child {
            text-align: left;
            border-radius: 8px 0 0 0;
        }
        
        .grades-table th:last-child {
            border-radius: 0 8px 0 0;
        }
        
        .grades-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
        }
        
        .grades-table tr:hover {
            background: #f8fafc;
        }
        
        .grades-table .subject-cell {
            font-weight: 500;
        }
        
        .grades-table .grade-cell {
            text-align: center;
            font-weight: 600;
        }
        
        .grades-table .appreciation-cell {
            font-style: italic;
            color: #64748b;
            font-size: 0.85rem;
        }
        
        .grade-excellent { color: #166534; }
        .grade-good { color: #1e40af; }
        .grade-average { color: #92400e; }
        .grade-poor { color: #991b1b; }
        
        .summary-box {
            background: #f0f9ff;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .summary-title {
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3a8a;
        }
        
        .summary-label {
            font-size: 0.8rem;
            color: #64748b;
        }
        
        .appreciation-box {
            background: #fefce8;
            border-left: 4px solid #eab308;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 0 8px 8px 0;
        }
        
        .appreciation-title {
            font-weight: 600;
            color: #854d0e;
            margin-bottom: 10px;
        }
        
        .appreciation-text {
            color: #713f12;
            font-style: italic;
        }
        
        .signatures-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }
        
        .signature-box {
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #1f2937;
            margin-top: 60px;
            padding-top: 10px;
        }
        
        .signature-title {
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .print-actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        @media print {
            body {
                background: white;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            .bulletin-container {
                padding: 0;
                max-width: 100%;
            }
            
            .bulletin-paper {
                box-shadow: none;
                padding: 20px;
            }
            
            .print-actions {
                display: none !important;
            }
            
            .grades-table th {
                background: #1e3a8a !important;
                -webkit-print-color-adjust: exact !important;
            }
            
            .summary-box, .appreciation-box, .student-info-box {
                -webkit-print-color-adjust: exact !important;
            }
        }
        
        .simulation-banner {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="bulletin-container">
        
        <div class="bulletin-paper">
            <!-- En-tête de l'école -->
            <div class="school-header">
                <div class="school-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="school-name">{{ config('app.name', 'École Supérieure') }}</div>
                <div class="school-subtitle">Établissement d'enseignement secondaire</div>
            </div>
            
            <!-- Titre du bulletin -->
            <div class="bulletin-title">
                <i class="fas fa-file-alt me-2"></i>
                BULLETIN SCOLAIRE - TRIMESTRE {{ $trimester }}
            </div>
            
            <!-- Informations de l'élève -->
            <div class="student-info-box">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Nom et Prénom :</span>
                            <span class="info-value">{{ $studentInfo['name'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Classe :</span>
                            <span class="info-value">{{ $studentInfo['class'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">N° Matricule :</span>
                            <span class="info-value">{{ $studentInfo['identifier'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-row">
                            <span class="info-label">Année scolaire :</span>
                            <span class="info-value">{{ $studentInfo['academic_year'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Date de naissance :</span>
                            <span class="info-value">{{ $studentInfo['date_of_birth'] ?? 'Non renseignée' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Niveau :</span>
                            <span class="info-value">{{ $studentInfo['level'] ?? 'Non renseigné' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            @if($grades->isEmpty())
            <!-- État vide - Aucune note -->
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-4" style="opacity: 0.5;"></i>
                <h4 class="text-muted mb-3">Aucune note disponible</h4>
                <p class="text-muted">Le bulletin sera généré une fois que les notes seront saisies.</p>
            </div>
            @else
            <!-- Tableau des notes -->
            <table class="grades-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Matière</th>
                        <th style="width: 8%;">Coef.</th>
                        <th style="width: 10%;">Moyenne</th>
                        <th style="width: 10%;">Moy. Classe</th>
                        <th style="width: 8%;">Min</th>
                        <th style="width: 8%;">Max</th>
                        <th style="width: 31%;">Appréciation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grades as $subjectData)
                        @php
                            $avg = $subjectData['average'];
                            $gradeClass = $avg >= 16 ? 'grade-excellent' : 
                                          ($avg >= 12 ? 'grade-good' : 
                                          ($avg >= 10 ? 'grade-average' : 'grade-poor'));
                        @endphp
                        <tr>
                            <td class="subject-cell">{{ $subjectData['subject'] }}</td>
                            <td class="grade-cell">{{ $subjectData['coefficient'] ?? 1 }}</td>
                            <td class="grade-cell {{ $gradeClass }}">{{ number_format($avg, 2) }}</td>
                            <td class="grade-cell">{{ $subjectData['class_average'] ?? '-' }}</td>
                            <td class="grade-cell">{{ $subjectData['lowest'] ?? '-' }}</td>
                            <td class="grade-cell">{{ $subjectData['highest'] ?? '-' }}</td>
                            <td class="appreciation-cell">{{ $subjectData['appreciation'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Résumé -->
            <div class="summary-box">
                <div class="summary-title">
                    <i class="fas fa-chart-bar me-2"></i>RÉSUMÉ DU TRIMESTRE
                </div>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value">{{ number_format($generalAverage, 2) }}</div>
                        <div class="summary-label">Moyenne Générale</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">
                            @if($rank)
                                {{ $rank }}<sup>e</sup>/{{ $totalStudents }}
                            @else
                                -
                            @endif
                        </div>
                        <div class="summary-label">Rang</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">{{ $classStats['average'] ?? '-' }}</div>
                        <div class="summary-label">Moyenne de Classe</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value">
                            @if($generalAverage >= 10)
                                <span class="text-success">Admis</span>
                            @else
                                <span class="text-danger">Non admis</span>
                            @endif
                        </div>
                        <div class="summary-label">Décision</div>
                    </div>
                </div>
            </div>
            
            <!-- Appréciation générale -->
            <div class="appreciation-box">
                <div class="appreciation-title">
                    <i class="fas fa-comment-alt me-2"></i>Appréciation du Conseil de Classe
                </div>
                <p class="appreciation-text mb-0">
                    @if($generalAverage >= 16)
                        Excellent trimestre ! Félicitations pour ce travail remarquable. Continuez sur cette lancée.
                    @elseif($generalAverage >= 14)
                        Très bon trimestre. Des résultats très satisfaisants qui témoignent d'un travail sérieux et régulier.
                    @elseif($generalAverage >= 12)
                        Bon trimestre dans l'ensemble. Des capacités certaines mais qui mériteraient d'être davantage exploitées.
                    @elseif($generalAverage >= 10)
                        Trimestre passable. Des efforts sont à fournir pour améliorer ces résultats. Ne vous découragez pas.
                    @else
                        Trimestre difficile. Un travail plus soutenu et une meilleure implication sont nécessaires. Ressaisissez-vous.
                    @endif
                </p>
            </div>
            @endif
            
            <!-- Signatures -->
            <div class="signatures-section">
                <div class="signature-box">
                    <div class="signature-title">Le Chef d'Établissement</div>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Le Professeur Principal</div>
                    <div class="signature-line"></div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Le Parent / Tuteur</div>
                    <div class="signature-line"></div>
                </div>
            </div>
            
            <!-- Pied de page -->
            <div class="text-center mt-4 pt-3 border-top">
                <small class="text-muted">
                    Bulletin édité le {{ now()->format('d/m/Y à H:i') }} | 
                    {{ config('app.name', 'École') }} - Tous droits réservés
                </small>
            </div>
        </div>
    </div>
    
    <!-- Actions d'impression -->
    <div class="print-actions">
        <a href="{{ route('student.grades') }}" class="btn btn-secondary btn-action">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-action">
            <i class="fas fa-print me-2"></i>Imprimer
        </button>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
