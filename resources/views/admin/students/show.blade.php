@extends('admin.layouts.app')

@section('title', 'Détails de l\'élève')

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
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Matière</th>
                                        <th>Coef.</th>
                                        <th>Notes</th>
                                        <th>Moyenne</th>
                                        <th>Appréciation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gradesBySubject as $subject => $data)
                                        <tr>
                                            <td><strong>{{ $subject }}</strong></td>
                                            <td><span class="badge bg-secondary">{{ $data['coefficient'] }}</span></td>
                                            <td>
                                                @foreach($data['grades'] as $grade)
                                                    <span class="badge bg-{{ $grade->grade >= 10 ? 'success' : 'danger' }} me-1">
                                                        {{ number_format($grade->grade, 1) }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="fw-bold {{ $data['average'] >= 10 ? 'text-success' : 'text-danger' }}">
                                                    {{ $data['average'] }}/20
                                                </span>
                                            </td>
                                            <td>
                                                @if($data['average'] >= 16)
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
                                        <th colspan="3">Moyenne Générale Pondérée</th>
                                        <th colspan="2">
                                            <span class="fs-5 {{ $generalAverage >= 10 ? 'text-success' : 'text-danger' }}">
                                                {{ $generalAverage }}/20
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
