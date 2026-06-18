@extends('layouts.student')

@section('title', 'Bulletin Semestriel')

@push('styles')
<style>
    /* ── Écran ─────────────────────────────────────────────── */
    .bulletin-header {
        background: linear-gradient(135deg, #1a5f2a 0%, #2d8a3e 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 1.5rem 2rem;
    }
    .bulletin-header .school-name { font-size: 1.4rem; font-weight: 700; margin-bottom: 0.2rem; }
    .bulletin-header .subtitle    { font-size: 0.88rem; opacity: 0.9; }

    .bulletin-card {
        border: 2px solid #1a5f2a;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .student-info {
        background-color: #f8f9fa;
        padding: 1.2rem 1.5rem;
        border-bottom: 2px solid #1a5f2a;
    }
    .student-info .info-item  { margin-bottom: 0.4rem; font-size: 0.9rem; }
    .student-info .info-label { font-weight: 600; color: #1a5f2a; min-width: 130px; display: inline-block; }

    .qr-section svg { width: 90px; height: 90px; }
    .qr-section small { font-size: 0.6rem; color: #1a5f2a; display: block; margin-top: 2px; }

    .grades-table { width: 100%; border-collapse: collapse; }
    .grades-table th {
        background-color: #1a5f2a;
        color: white;
        padding: 0.6rem 0.5rem;
        text-align: center;
        font-size: 0.82rem;
        border: 1px solid #155724;
    }
    .grades-table td {
        padding: 0.5rem 0.5rem;
        text-align: center;
        border: 1px solid #dee2e6;
        font-size: 0.88rem;
    }
    .grades-table tr:nth-child(even) { background-color: #f8f9fa; }
    .grades-table .subject-name { text-align: left; font-weight: 500; }
    .grades-table .total-row {
        background-color: #1a5f2a !important;
        color: white;
        font-weight: 700;
    }

    .grade-excellent { color: #155724; font-weight: 700; }
    .grade-good      { color: #0c5460; font-weight: 600; }
    .grade-average   { color: #856404; }
    .grade-poor      { color: #721c24; font-weight: 600; }
    .appreciation-cell { font-size: 0.78rem; font-style: italic; }

    .summary-box {
        background: linear-gradient(135deg, #fff9c4 0%, #fff59d 100%);
        border: 2px solid #f9a825;
        border-radius: 10px;
        padding: 1.5rem;
        margin: 1rem;
    }
    .summary-box .average { font-size: 2.5rem; font-weight: 700; color: #1a5f2a; }
    .rank-badge {
        background-color: #ffd700;
        color: #1a5f2a;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-weight: 700;
        display: inline-block;
    }
    .class-stats { background-color: #e3f2fd; border-radius: 10px; padding: 1rem; margin: 1rem; }

    .semester-tabs { margin-bottom: 1.5rem; }
    .semester-tabs .nav-link {
        border: 2px solid #1a5f2a; color: #1a5f2a;
        margin-right: 0.5rem; border-radius: 20px;
        padding: 0.5rem 1.5rem; font-weight: 600;
    }
    .semester-tabs .nav-link.active { background-color: #1a5f2a; color: white; }

    .print-btn { background-color: #1a5f2a; color: white; border: none; padding: 0.75rem 2rem; border-radius: 25px; font-weight: 600; }
    .print-btn:hover { background-color: #155724; color: white; }

    .bulletin-print-footer,
    .bulletin-bottom-print,
    .signatures-print { display: none; }

    /* ── Impression ────────────────────────────────────────── */
    @media print {
        /* Force toutes les couleurs et fonds */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm;
        }

        body {
            background: #fff !important;
            font-size: 9pt;
            color: #000 !important;
        }

        /* Masquer navigation, navbar, boutons */
        .sidebar, .sidebar-overlay, .top-navbar,
        .portal-top-navbar, .portal-page-body > .alert,
        .no-print, .semester-tabs {
            display: none !important;
        }

        .wrapper, .main-content {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-height: auto !important;
        }

        .portal-page-body { padding: 0 !important; }
        .container-fluid  { padding: 0 !important; max-width: 100% !important; }

        /* ── Card : remplit la page A4 en colonne ── */
        .bulletin-card {
            display: flex !important;
            flex-direction: column !important;
            min-height: 277mm !important;          /* A4 277 = 297 − 8 − 8 − pied */
            border: 1.5px solid #1a5f2a !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
        }

        /* ── En-tête ── */
        .bulletin-header {
            background: #1a5f2a !important;
            color: #fff !important;
            padding: 0.6rem 1.2rem !important;
            border-radius: 0 !important;
        }
        .bulletin-header .row {
            display: flex !important;
            align-items: center !important;
        }
        .bulletin-header .col-md-2 {
            flex: 0 0 16.66% !important;
            max-width: 16.66% !important;
            display: block !important;
        }
        .bulletin-header .col-md-8 {
            flex: 0 0 66.66% !important;
            max-width: 66.66% !important;
        }
        .bulletin-header .school-name {
            font-size: 12pt !important;
            color: #fff !important;
            font-weight: 700 !important;
            margin-bottom: 0.15rem !important;
        }
        .bulletin-header .subtitle { font-size: 9pt !important; color: rgba(255,255,255,.9) !important; }
        .bulletin-header hr        { border-color: rgba(255,255,255,.3) !important; margin: 0.25rem 0 !important; }
        .bulletin-header .fa-graduation-cap { font-size: 2.2rem !important; }

        /* ── Infos élève ── */
        .student-info {
            padding: 0.7rem 1.2rem !important;
            background: #f0faf3 !important;
            border-bottom: 1.5px solid #1a5f2a !important;
        }
        .student-info .row {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
        }
        .student-info .col-md-5  { flex: 0 0 41% !important; max-width: 41% !important; }
        .student-info .qr-section {
            flex: 0 0 18% !important;
            max-width: 18% !important;
            text-align: center !important;
        }
        .student-info .info-item  { margin-bottom: 0.3rem !important; font-size: 9pt !important; }
        .student-info .info-label {
            font-weight: 700 !important;
            color: #1a5f2a !important;
            min-width: 125px !important;
            display: inline-block !important;
        }
        .qr-section svg   { width: 82px !important; height: 82px !important; }
        .qr-section small { font-size: 6.5pt !important; color: #1a5f2a !important; }

        /* ── Tableau des notes ── */
        .table-responsive { overflow: visible !important; }
        .grades-table th {
            background-color: #1a5f2a !important;
            color: #fff !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 8.5pt !important;
            border: 1px solid #155724 !important;
        }
        .grades-table td {
            padding: 0.35rem 0.4rem !important;
            font-size: 9pt !important;
            border: 1px solid #ccc !important;
            line-height: 1.4 !important;
        }
        .grades-table tr:nth-child(even) { background-color: #f5faf6 !important; }
        .grades-table .total-row { background-color: #1a5f2a !important; color: #fff !important; font-weight: 700 !important; }
        .grade-excellent { color: #155724 !important; font-weight: 700 !important; }
        .grade-good      { color: #0c5460 !important; font-weight: 600 !important; }
        .grade-average   { color: #856404 !important; }
        .grade-poor      { color: #721c24 !important; font-weight: 600 !important; }
        .appreciation-cell { font-size: 7.5pt !important; font-style: italic !important; }

        /* ── Résumé version écran ── */
        .bulletin-bottom-screen {
            display: flex !important;
            flex-wrap: wrap !important;
        }
        .bulletin-bottom-screen > .col-md-6 {
            flex: 0 0 50% !important;
            max-width: 50% !important;
        }
        .bulletin-bottom-screen > .col-md-4 {
            flex: 0 0 33.33% !important;
            max-width: 33.33% !important;
        }

        /* Boîte résumé jaune */
        .summary-box {
            background: linear-gradient(135deg, #fff9c4, #fff59d) !important;
            border: 1.5px solid #f9a825 !important;
            border-radius: 8px !important;
            padding: 1rem !important;
            margin: 0.6rem !important;
        }
        .summary-box h5       { font-size: 10pt !important; margin-bottom: 0.4rem !important; }
        .summary-box .average { font-size: 22pt !important; }
        .summary-box p        { font-size: 8.5pt !important; margin-bottom: 0.3rem !important; }
        .rank-badge {
            background-color: #ffd700 !important;
            color: #1a5f2a !important;
            padding: 0.25rem 0.7rem !important;
            font-size: 8.5pt !important;
            border-radius: 15px !important;
        }

        /* Badges Bootstrap (Mention) */
        .badge      { padding: 0.25rem 0.6rem !important; font-size: 8.5pt !important; }
        .bg-success { background-color: #198754 !important; color: #fff !important; }
        .bg-primary { background-color: #0d6efd !important; color: #fff !important; }
        .bg-info    { background-color: #0dcaf0 !important; color: #000 !important; }
        .bg-warning { background-color: #ffc107 !important; color: #000 !important; }
        .bg-danger  { background-color: #dc3545 !important; color: #fff !important; }

        /* Stats classe bleues */
        .class-stats {
            background-color: #e3f2fd !important;
            border-radius: 8px !important;
            padding: 0.7rem !important;
            margin: 0.6rem !important;
        }
        .class-stats h5    { font-size: 9.5pt !important; margin-bottom: 0.4rem !important; }
        .class-stats .fs-4 { font-size: 14pt !important; }
        .class-stats small { font-size: 7.5pt !important; }
        .text-primary { color: #0d6efd !important; }
        .text-success { color: #198754 !important; }
        .text-danger  { color: #dc3545 !important; }

        /* Observations */
        .p-3.mx-3.mb-3 {
            padding: 0.5rem 0.6rem !important;
            margin: 0 0.6rem 0.6rem !important;
            font-size: 8.5pt !important;
        }
        .p-3.mx-3.mb-3 h6 { font-size: 9pt !important; }

        /* Signatures — poussées vers le bas */
        .row.p-4.border-top.bulletin-bottom-screen {
            margin-top: auto !important;       /* colle les signatures en bas */
            padding: 0.8rem 1.2rem !important;
            border-top: 1.5px solid #dee2e6 !important;
        }
        .row.p-4.border-top.bulletin-bottom-screen p {
            font-size: 8.5pt !important;
            margin-bottom: 0.3rem !important;
        }
        .row.p-4.border-top.bulletin-bottom-screen [style*="height: 60px"] {
            height: 40px !important;
        }

        /* Masquer version compacte */
        .bulletin-bottom-print,
        .signatures-print { display: none !important; }

        /* ── Pied de page (flux normal, pas fixed) ── */
        .bulletin-print-footer {
            display: block !important;
            position: static !important;
            text-align: center;
            font-size: 7.5pt;
            color: #444;
            border-top: 1.5px solid #1a5f2a !important;
            padding: 3mm 10mm;
            background: #fff !important;
            margin-top: auto !important;
        }
        .bulletin-print-footer strong { color: #1a5f2a !important; }
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
                <div class="col-md-2 text-center print-hide">
                    <img src="/images/senegal-flag.png" alt="Drapeau" class="img-fluid" style="max-height: 60px;" onerror="this.style.display='none'">
                </div>
                <div class="col-md-8">
                    <div class="school-name">RÉPUBLIQUE DU SÉNÉGAL</div>
                    <div class="subtitle">Un Peuple - Un But - Une Foi</div>
                    <hr style="border-color: rgba(255,255,255,0.3); margin: 0.5rem 0;">
                    <div class="school-name">{{ $schoolName ?? config('app.school_name', 'Établissement scolaire') }}</div>
                    <div class="subtitle">Bulletin de Notes - Semestre {{ $semester }}</div>
                    <div class="subtitle">Année Scolaire {{ $academicYear->name }}</div>
                </div>
                <div class="col-md-2 text-center print-hide">
                    <i class="fas fa-graduation-cap fa-3x" style="opacity: 0.8;"></i>
                </div>
            </div>
        </div>

        <!-- Informations élève -->
        <div class="student-info">
            <div class="row align-items-center">
                <div class="col-md-5">
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
                <div class="col-md-5">
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
                @if(!empty($verifyUrl))
                <div class="col-md-2 text-center qr-section">
                    {!! QrCode::size(90)->generate($verifyUrl) !!}
                    <div style="font-size:0.65rem;color:#1a5f2a;margin-top:2px;">Vérifier l'authenticité</div>
                </div>
                @endif
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

        @php
            $mentionLabel = match (true) {
                $generalAverage >= 16 => 'Mention Très Bien',
                $generalAverage >= 14 => 'Mention Bien',
                $generalAverage >= 12 => 'Mention Assez Bien',
                $generalAverage >= 10 => 'Passable',
                default => 'Insuffisant',
            };
            $observationText = match (true) {
                $generalAverage >= 14 => 'Excellent travail. Continuez ainsi.',
                $generalAverage >= 12 => 'Bon travail. Maintenez vos efforts.',
                $generalAverage >= 10 => 'Résultats convenables. Progrès attendus.',
                $generalAverage >= 8 => 'Résultats insuffisants. Effort indispensable.',
                default => 'Résultats très insuffisants. Travail régulier requis.',
            };
            $schoolDisplayName = $schoolName ?? config('app.school_name', 'Établissement scolaire');
        @endphp

        <!-- Résumé (écran) -->
        <div class="row bulletin-bottom-screen">
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

        <!-- Signatures (écran) -->
        <div class="row p-4 border-top bulletin-bottom-screen">
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

        <!-- Résumé compact (impression) -->
        <div class="bulletin-bottom-print">
            <div>
                <strong>Semestre {{ $semester }}</strong> —
                <span class="print-average">{{ number_format($generalAverage, 2) }}/20</span>
                · {{ $mentionLabel }}
                @if(!empty($rankData['rank']))
                    · {{ $rankData['rank'] }}{{ $rankData['rank'] == 1 ? 'er' : 'ème' }}/{{ $rankData['total'] }}
                @endif
            </div>
            <div class="text-muted">{{ $observationText }}</div>
        </div>

        <div class="signatures-print">
            <div>
                <div>Chef d'établissement</div>
                <div class="sig-line">Signature</div>
            </div>
            <div>
                <div>Professeur principal</div>
                <div class="sig-line">Signature</div>
            </div>
            <div>
                <div>Parent / tuteur</div>
                <div class="sig-line">Signature</div>
            </div>
        </div>
    </div>

    <footer class="bulletin-print-footer">
        <strong>{{ $platformName }}</strong> — {{ $schoolDisplayName }}
    </footer>

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
