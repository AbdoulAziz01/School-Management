@extends('layouts.student')

@section('title', 'Bulletin Annuel')

@push('styles')
<style>
    .bulletin-header {
        background: linear-gradient(135deg, #1a5f2a 0%, #2d8a3e 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 2rem;
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
    
    .semester-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .semester-table th {
        background-color: #1a5f2a;
        color: white;
        padding: 0.75rem;
        text-align: center;
        font-size: 0.85rem;
        border: 1px solid #155724;
    }
    
    .semester-table td {
        padding: 0.5rem;
        text-align: center;
        border: 1px solid #dee2e6;
        font-size: 0.85rem;
    }
    
    .semester-title {
        background-color: #ffc107;
        color: #1a5f2a;
        padding: 0.75rem;
        font-weight: 700;
        text-align: center;
        border: 2px solid #1a5f2a;
        margin-top: 1rem;
    }
    
    .annual-summary {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: 3px solid #1a5f2a;
        border-radius: 15px;
        padding: 2rem;
        margin: 1.5rem;
        text-center;
    }
    
    .annual-average {
        font-size: 4rem;
        font-weight: 700;
        color: #1a5f2a;
    }
    
    .decision-box {
        padding: 1.5rem;
        border-radius: 10px;
        margin: 1rem;
        text-align: center;
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .decision-success {
        background-color: #d4edda;
        border: 2px solid #28a745;
        color: #155724;
    }
    
    .decision-warning {
        background-color: #fff3cd;
        border: 2px solid #ffc107;
        color: #856404;
    }
    
    .decision-danger {
        background-color: #f8d7da;
        border: 2px solid #dc3545;
        color: #721c24;
    }
    
    .grade-excellent { color: #155724; font-weight: 700; }
    .grade-good { color: #0c5460; font-weight: 600; }
    .grade-average { color: #856404; }
    .grade-poor { color: #721c24; font-weight: 600; }
    
    @media print {
        .no-print { display: none !important; }
        .bulletin-card { border: 1px solid #000; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Navigation -->
    <div class="mb-4 no-print">
        <a href="{{ route('student.bulletin', ['semester' => 1]) }}" class="btn btn-outline-success me-2">
            <i class="fas fa-calendar-alt me-2"></i>Semestre 1
        </a>
        <a href="{{ route('student.bulletin', ['semester' => 2]) }}" class="btn btn-outline-success me-2">
            <i class="fas fa-calendar-alt me-2"></i>Semestre 2
        </a>
        <a href="{{ route('student.bulletin.annual') }}" class="btn btn-success">
            <i class="fas fa-graduation-cap me-2"></i>Bulletin Annuel
        </a>
    </div>

    @if(isset($error))
        <div class="alert alert-warning">{{ $error }}</div>
    @else
    <!-- Bulletin Annuel -->
    <div class="bulletin-card">
        <!-- En-tête -->
        <div class="bulletin-header text-center">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <i class="fas fa-flag fa-3x" style="opacity: 0.8;"></i>
                </div>
                <div class="col-md-8">
                    <h2 class="mb-1">RÉPUBLIQUE DU SÉNÉGAL</h2>
                    <p class="mb-2">Un Peuple - Un But - Une Foi</p>
                    <hr style="border-color: rgba(255,255,255,0.3);">
                    <h3>BULLETIN ANNUEL</h3>
                    <p>Année Scolaire {{ $academicYear->name }}</p>
                </div>
                <div class="col-md-2">
                    <i class="fas fa-graduation-cap fa-3x" style="opacity: 0.8;"></i>
                </div>
            </div>
        </div>

        <!-- Informations élève -->
        <div class="student-info">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nom et Prénom:</strong> {{ $studentInfo['name'] }}</p>
                    <p><strong>Matricule:</strong> {{ $studentInfo['identifier'] }}</p>
                    <p><strong>Date de naissance:</strong> {{ $studentInfo['date_of_birth'] }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Classe:</strong> {{ $studentInfo['class'] }}</p>
                    <p><strong>Série:</strong> {{ $studentInfo['serie'] ?? 'N/A' }}</p>
                    <p><strong>Niveau:</strong> {{ $studentInfo['level'] }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Semestre 1 -->
            <div class="col-md-6">
                <div class="semester-title">
                    <i class="fas fa-calendar me-2"></i>SEMESTRE 1
                </div>
                <div class="table-responsive px-2">
                    <table class="semester-table">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Coef</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($semestre1Data['data'] as $data)
                            <tr>
                                <td class="text-start">{{ $data['subject'] }}</td>
                                <td>{{ $data['coefficient'] }}</td>
                                <td class="{{ $data['moyenne_matiere'] >= 10 ? 'grade-good' : 'grade-poor' }}">
                                    {{ $data['moyenne_matiere'] ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #ffc107;">
                                <td colspan="2" class="text-end fw-bold">Moyenne Semestre 1:</td>
                                <td class="fw-bold fs-5">{{ number_format($semestre1Data['moyenne'], 2) }}/20</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Semestre 2 -->
            <div class="col-md-6">
                <div class="semester-title">
                    <i class="fas fa-calendar me-2"></i>SEMESTRE 2
                </div>
                <div class="table-responsive px-2">
                    <table class="semester-table">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Coef</th>
                                <th>Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($semestre2Data['data'] as $data)
                            <tr>
                                <td class="text-start">{{ $data['subject'] }}</td>
                                <td>{{ $data['coefficient'] }}</td>
                                <td class="{{ $data['moyenne_matiere'] >= 10 ? 'grade-good' : 'grade-poor' }}">
                                    {{ $data['moyenne_matiere'] ?? '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #ffc107;">
                                <td colspan="2" class="text-end fw-bold">Moyenne Semestre 2:</td>
                                <td class="fw-bold fs-5">{{ number_format($semestre2Data['moyenne'], 2) }}/20</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Résumé Annuel -->
        <div class="annual-summary text-center">
            <h4 class="mb-3"><i class="fas fa-trophy me-2"></i>RÉSULTATS ANNUELS</h4>
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="fs-4">Semestre 1</div>
                    <div class="fs-2 fw-bold">{{ number_format($semestre1Data['moyenne'], 2) }}/20</div>
                </div>
                <div class="col-md-4">
                    <div class="annual-average">{{ number_format($moyenneAnnuelle, 2) }}</div>
                    <div class="fs-5">MOYENNE ANNUELLE /20</div>
                    @if($decision['mention'])
                        <span class="badge bg-success fs-5 mt-2">{{ $decision['mention'] }}</span>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="fs-4">Semestre 2</div>
                    <div class="fs-2 fw-bold">{{ number_format($semestre2Data['moyenne'], 2) }}/20</div>
                </div>
            </div>
        </div>

        <!-- Décision du conseil de classe -->
        <div class="decision-box decision-{{ $decision['color'] }}">
            <i class="fas fa-gavel me-2"></i>
            DÉCISION DU CONSEIL DE CLASSE: {{ strtoupper($decision['text']) }}
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
    @endif

    <!-- Boutons d'action -->
    <div class="text-center no-print">
        <button onclick="window.print()" class="btn btn-success btn-lg me-2">
            <i class="fas fa-print me-2"></i>Imprimer le bulletin
        </button>
        <a href="{{ route('student.grades') }}" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-arrow-left me-2"></i>Retour aux notes
        </a>
    </div>
</div>
@endsection
