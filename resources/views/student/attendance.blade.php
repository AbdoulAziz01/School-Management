@extends('layouts.student')

@section('title', 'Mes Présences')

@push('styles')
<style>
    .attendance-card {
        border-left: 4px solid #f59e0b;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }
    .attendance-card.absent {
        border-left-color: #e74a3b;
    }
    .attendance-card.late {
        border-left-color: #f6c23e;
    }
    .attendance-card.present {
        border-left-color: #1cc88a;
    }
    .attendance-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    .progress {
        height: 1rem;
        border-radius: 0.35rem;
    }
    .stats-card {
        border-radius: 0.5rem;
        transition: all 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    }
    .attendance-details {
        font-size: 0.9rem;
    }
    .subject-badge {
        font-size: 0.8em;
        margin-right: 0.3rem;
        margin-bottom: 0.3rem;
    }
    
    /* Calendrier compact */
    #attendanceCalendar .fc-toolbar-title {
        font-size: 0.9rem !important;
    }
    #attendanceCalendar .fc-button {
        padding: 0.2rem 0.4rem !important;
        font-size: 0.75rem !important;
    }
    #attendanceCalendar .fc-daygrid-day-number {
        font-size: 0.75rem !important;
        padding: 2px 4px !important;
    }
    #attendanceCalendar .fc-col-header-cell-cushion {
        font-size: 0.7rem !important;
    }
    #attendanceCalendar .fc-daygrid-day {
        min-height: 30px !important;
    }
    #attendanceCalendar .fc-daygrid-day-frame {
        min-height: 30px !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mes Présences</h1>
        <div class="d-none d-sm-inline-block">
            <button class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#requestAbsenceModal">
                <i class="fas fa-plus-circle fa-sm text-white-50"></i> Demande d'absence
            </button>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Taux de présence</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['attendance_rate'] }}%
                            </div>
                            <div class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $stats['attendance_rate'] }}%" 
                                         aria-valuenow="{{ $stats['attendance_rate'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Jours présents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['present_days'] }}
                                @if($stats['total_days'] > 0)
                                <small class="text-muted">/ {{ $stats['total_days'] }} jours</small>
                                @endif
                            </div>
                            @if($stats['total_days'] > 0)
                            <div class="mt-2">
                                <span class="badge bg-success text-white">
                                    <i class="fas fa-check-circle me-1"></i> À jour
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Retards</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['late_days'] }}
                                @if($stats['late_days'] > 0)
                                <small class="text-muted">cours</small>
                                @endif
                            </div>
                            @if($stats['late_days'] > 0)
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clock me-1"></i> Retard
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Absences non justifiées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['absent_days'] }}
                                @if($stats['absent_days'] > 0)
                                <small class="text-muted">cours</small>
                                @endif
                            </div>
                            @if($stats['absent_days'] > 0)
                            <div class="mt-2">
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-circle me-1"></i> À justifier
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Liste des présences -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Historique des présences</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="#">Tout afficher</a></li>
                            <li><a class="dropdown-item" href="#">Présences uniquement</a></li>
                            <li><a class="dropdown-item" href="#">Absences uniquement</a></li>
                            <li><a class="dropdown-item" href="#">Retards uniquement</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    @if($attendances->count() > 0)
                        @foreach($attendances as $attendance)
                            <div class="card mb-3 attendance-card {{ $attendance->status }}">
                                <div class="card-body py-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center">
                                            <div class="h5 mb-0">
                                                {{ $attendance->date->format('d') }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ $attendance->date->translatedFormat('M') }}
                                            </div>
                                            <div class="small">
                                                {{ $attendance->date->translatedFormat('l') }}
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    {{ $attendance->subject->name ?? 'Cours' }}
                                                    @if($attendance->session_type)
                                                        <span class="badge bg-secondary subject-badge">
                                                            {{ $attendance->session_type }}
                                                        </span>
                                                    @endif
                                                </h6>
                                                <span class="badge status-badge 
                                                    {{ $attendance->status === 'present' ? 'bg-success' : '' }}
                                                    {{ $attendance->status === 'absent' ? 'bg-danger' : '' }}
                                                    {{ $attendance->status === 'late' ? 'bg-warning text-dark' : '' }}
                                                ">
                                                    @if($attendance->status === 'present')
                                                        <i class="fas fa-check-circle me-1"></i> Présent
                                                    @elseif($attendance->status === 'absent')
                                                        <i class="fas fa-times-circle me-1"></i> Absent
                                                    @elseif($attendance->status === 'late')
                                                        <i class="fas fa-clock me-1"></i> Retard
                                                    @endif
                                                    @if($attendance->minutes_late > 0)
                                                        ({{ $attendance->minutes_late }} min)
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            <div class="attendance-details mt-2">
                                                <div class="mb-1">
                                                    <i class="far fa-clock text-muted me-1"></i>
                                                    {{ $attendance->start_time }} - {{ $attendance->end_time }}
                                                </div>
                                                @if($attendance->teacher)
                                                    <div class="mb-1">
                                                        <i class="fas fa-chalkboard-teacher text-muted me-1"></i>
                                                        {{ $attendance->teacher->name ?? '' }}
                                                    </div>
                                                @endif
                                                @if($attendance->classroom)
                                                    <div>
                                                        <i class="fas fa-door-open text-muted me-1"></i>
                                                        {{ $attendance->classroom }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            @if($attendance->status === 'absent' && $attendance->justification_status)
                                                <div class="alert alert-warning p-2 mt-2 mb-0">
                                                    <div class="d-flex">
                                                        <i class="fas fa-info-circle mt-1 me-2"></i>
                                                        <div>
                                                            <strong>Justification :</strong>
                                                            {{ $attendance->justification_status === 'pending' ? 'En attente de validation' : 'Validée' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-3 text-end">
                                            @if($attendance->status === 'absent' && ! in_array($attendance->justification_status, ['pending', 'approved']))
                                                <button class="btn btn-sm btn-outline-primary justify-content-end"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#justifyAbsenceModal"
                                                        data-attendance-id="{{ $attendance->id }}">
                                                    <i class="fas fa-pen-alt me-1"></i> Justifier
                                                </button>
                                            @elseif($attendance->status === 'absent' && $attendance->justification_status)
                                                <button class="btn btn-sm btn-outline-info justify-content-end"
                                                        data-bs-toggle="tooltip"
                                                        title="Voir la justification">
                                                    <i class="fas fa-eye me-1"></i> Voir
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $attendances->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-clipboard-list fa-4x text-gray-300"></i>
                            </div>
                            <h5 class="text-gray-700">Aucune présence enregistrée</h5>
                            <p class="text-muted">Vos présences apparaîtront ici une fois enregistrées par vos professeurs.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Calendrier des absences -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-alt me-2"></i>Calendrier
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div id="attendanceCalendar" style="font-size: 0.75rem;"></div>
                    <div class="mt-3 d-flex justify-content-center gap-3 small">
                        <span><i class="fas fa-circle text-success"></i> Présent</span>
                        <span><i class="fas fa-circle text-warning"></i> Retard</span>
                        <span><i class="fas fa-circle text-danger"></i> Absent</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de justification d'absence - À implémenter plus tard
<div class="modal fade" id="justifyAbsenceModal" tabindex="-1" aria-labelledby="justifyAbsenceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="justificationForm" action="#" method="POST">
                @csrf
                <input type="hidden" name="attendance_id" id="attendanceId">
                <div class="modal-header">
                    <h5 class="modal-title" id="justifyAbsenceModalLabel">Justifier une absence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="justificationType" class="form-label">Type de justification</label>
                        <select class="form-select" id="justificationType" name="type" required>
                            <option value="">Sélectionnez un motif</option>
                            <option value="illness">Maladie</option>
                            <option value="medical">Rendez-vous médical</option>
                            <option value="family">Raison familiale</option>
                            <option value="transport">Problème de transport</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="justificationComment" class="form-label">Commentaire (optionnel)</label>
                        <textarea class="form-control" id="justificationComment" name="comment" rows="3" 
                                  placeholder="Précisez la raison de votre absence..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="justificationFile" class="form-label">Pièce justificative (optionnel)</label>
                        <input class="form-control" type="file" id="justificationFile" name="file">
                        <div class="form-text">Format accepté : PDF, JPG, PNG (max 2MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer la justification</button>
                </div>
            </form>
        </div>
    </div>
</div>
-->

{{-- Modal de demande d'absence - À implémenter plus tard
<div class="modal fade" id="requestAbsenceModal" tabindex="-1" aria-labelledby="requestAbsenceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="absenceRequestForm" action="#" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="requestAbsenceModalLabel">Demande d'absence</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="absenceType" class="form-label">Type d'absence</label>
                        <select class="form-select" id="absenceType" name="type" required>
                            <option value="">Sélectionnez un type</option>
                            <option value="planned">Absence prévue</option>
                            <option value="exceptional">Absence exceptionnelle</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Du</label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="endDate" class="form-label">Au</label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="absenceReason" class="form-label">Motif</label>
                        <select class="form-select" id="absenceReason" name="reason" required>
                            <option value="">Sélectionnez un motif</option>
                            <option value="illness">Maladie</option>
                            <option value="family">Raison familiale</option>
                            <option value="personal">Raison personnelle</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="absenceDetails" class="form-label">Détails (optionnel)</label>
                        <textarea class="form-control" id="absenceDetails" name="details" rows="3" 
                                 placeholder="Précisez les raisons de votre demande..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="absenceFile" class="form-label">Pièce justificative (si nécessaire)</label>
                        <input class="form-control" type="file" id="absenceFile" name="file">
                        <div class="form-text">Format accepté : PDF, JPG, PNG (max 2MB)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                </div>
            </form>
        </div>
    </div>
</div>
--}}

@push('scripts')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation du calendrier
        var calendarEl = document.getElementById('attendanceCalendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'fr',
            height: 'auto',
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            titleFormat: { month: 'short', year: 'numeric' },
            dayHeaderFormat: { weekday: 'narrow' },
            events: @json($calendarEvents ?? []),
            eventDidMount: function(info) {
                if (info.event.extendedProps.description) {
                    info.el.setAttribute('title', info.event.extendedProps.description);
                    info.el.setAttribute('data-bs-toggle', 'tooltip');
                }
            }
        });
        calendar.render();
        
        // Initialiser les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection
