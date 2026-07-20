@extends('layouts.student')

@section('title', 'Mes Présences')

@push('styles')
<style>
    /* ═══════════════════════════════════════
       PAGE HEADER
    ═══════════════════════════════════════ */
    .attend-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 1.125rem;
        flex-wrap: wrap;
    }
    .attend-title {
        font-size: 1.4rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .btn-absence-request {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #1c1917;
        border: none;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-weight: 700;
        font-size: 0.82rem;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 3px 10px rgba(245,158,11,0.3);
    }
    .btn-absence-request:hover { transform: translateY(-1px); box-shadow: 0 5px 14px rgba(245,158,11,0.4); color: #1c1917; }

    /* ═══════════════════════════════════════
       STATS STRIP
    ═══════════════════════════════════════ */
    .attend-stats-strip {
        display: flex;
        align-items: stretch;
        background: rgba(245,158,11,0.07);
        border-radius: 14px;
        margin-bottom: 1.125rem;
        overflow: hidden;
    }
    .attend-stat {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0.875rem 0.5rem;
        text-align: center;
        border-right: 1.5px solid rgba(245,158,11,0.2);
        min-width: 0;
    }
    .attend-stat:last-child { border-right: none; }
    .as-value {
        font-size: 1.5rem;
        font-weight: 900;
        line-height: 1;
        color: #1e293b;
    }
    .as-value.amber { color: #d97706; }
    .as-value.danger { color: #dc2626; }
    .as-label {
        font-size: 0.57rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
        margin-top: 4px;
    }
    /* Barre de progression du taux */
    .as-progress-wrap {
        width: 80%;
        margin-top: 5px;
    }
    .as-progress-track {
        height: 4px;
        background: rgba(245,158,11,0.2);
        border-radius: 99px;
        overflow: hidden;
    }
    .as-progress-fill {
        height: 100%;
        border-radius: 99px;
        background: linear-gradient(90deg, #f59e0b, #d97706);
        transition: width .8s ease;
    }
    @media (max-width: 575.98px) {
        .as-value { font-size: 1.2rem; }
        .attend-stat { padding: 0.75rem 0.3rem; }
        .attend-title { font-size: 1.2rem; }
    }

    /* ═══════════════════════════════════════
       HISTORIQUE : cartes présence
    ═══════════════════════════════════════ */
    .attendance-item {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        border-left: 4px solid #e2e8f0;
        padding: 0.875rem 1rem;
        margin-bottom: 0.625rem;
        transition: box-shadow 0.15s;
    }
    .attendance-item:hover { box-shadow: 0 3px 12px rgba(0,0,0,0.07); }
    .attendance-item.present { border-left-color: #10b981; }
    .attendance-item.absent  { border-left-color: #ef4444; }
    .attendance-item.late    { border-left-color: #f59e0b; }

    .attend-item-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .attend-date-block { flex-shrink: 0; text-align: center; min-width: 38px; }
    .attend-date-day   { font-size: 1.2rem; font-weight: 800; color: #1e293b; line-height: 1; }
    .attend-date-month { font-size: 0.62rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; }
    .attend-date-dow   { font-size: 0.6rem; color: #cbd5e1; }

    .attend-subject    { font-size: 0.9rem; font-weight: 700; color: #1e293b; }
    .attend-meta       { font-size: 0.75rem; color: #64748b; margin-top: 2px; }
    .attend-meta i     { width: 12px; opacity: .6; }

    /* Status pill */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 99px;
        padding: 3px 10px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .pill-present { background: #dcfce7; color: #15803d; }
    .pill-absent  { background: #fee2e2; color: #b91c1c; }
    .pill-late    { background: #fef3c7; color: #b45309; }

    /* Action */
    .attend-item-action {
        display: flex;
        justify-content: flex-end;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #f8fafc;
    }
    .btn-justify {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #d97706;
        color: #d97706;
        background: #fff;
        border-radius: 8px;
        padding: 0.3rem 0.75rem;
        cursor: pointer;
        transition: all .15s;
        text-decoration: none;
    }
    .btn-justify:hover { background: #fff7ed; color: #b45309; }

    /* Justification notice */
    .justif-notice {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.72rem;
        color: #b45309;
        background: #fff7ed;
        border-radius: 6px;
        padding: 4px 10px;
        margin-top: 0.4rem;
    }

    /* ═══════════════════════════════════════
       CALENDRIER
    ═══════════════════════════════════════ */
    #attendanceCalendar .fc-toolbar-title { font-size: 0.88rem !important; }
    #attendanceCalendar .fc-button       { padding: 0.2rem 0.4rem !important; font-size: 0.72rem !important; }
    #attendanceCalendar .fc-daygrid-day-number { font-size: 0.72rem !important; padding: 2px 4px !important; }
    #attendanceCalendar .fc-col-header-cell-cushion { font-size: 0.68rem !important; }
    #attendanceCalendar .fc-daygrid-day,
    #attendanceCalendar .fc-daygrid-day-frame { min-height: 28px !important; }

    .cal-legend { display: flex; justify-content: center; gap: 1rem; margin-top: 0.75rem; }
    .cal-legend-item { display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: #64748b; }
    .cal-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

    /* ═══════════════════════════════════════
       SECTION TITLES
    ═══════════════════════════════════════ */
    .section-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .section-title i { color: #d97706; }

    @media print {
        .attend-header, .attend-stats-strip { display: none !important; }
    }
</style>
@endpush

@section('content')
<a href="{{ route('student.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="container-fluid">

    {{-- ── En-tête ── --}}
    <div class="attend-header">
        <h1 class="attend-title">Mes Présences</h1>
        <button class="btn-absence-request" data-bs-toggle="modal" data-bs-target="#requestAbsenceModal">
            <i class="fas fa-plus-circle"></i>
            <span class="d-none d-sm-inline">Demande d'absence</span>
            <span class="d-sm-none">Absence</span>
        </button>
    </div>

    {{-- ── Stats strip ── --}}
    <div class="attend-stats-strip">
        <div class="attend-stat">
            <span class="as-value amber">{{ $stats['attendance_rate'] }}%</span>
            <span class="as-label">Taux de présence</span>
            <div class="as-progress-wrap">
                <div class="as-progress-track">
                    <div class="as-progress-fill" style="width:{{ $stats['attendance_rate'] }}%;"></div>
                </div>
            </div>
        </div>
        <div class="attend-stat">
            <span class="as-value">{{ $stats['present_days'] }}
                @if($stats['total_days'] > 0)
                    <span style="font-size:.75rem;font-weight:600;color:#94a3b8;">/{{ $stats['total_days'] }}</span>
                @endif
            </span>
            <span class="as-label">Jours présents</span>
        </div>
        <div class="attend-stat">
            <span class="as-value {{ $stats['late_days'] > 0 ? 'amber' : '' }}">{{ $stats['late_days'] }}</span>
            <span class="as-label">Retards</span>
        </div>
        <div class="attend-stat">
            <span class="as-value {{ $stats['absent_days'] > 0 ? 'danger' : '' }}">{{ $stats['absent_days'] }}</span>
            <span class="as-label">Abs. non just.</span>
        </div>
    </div>

    {{-- ── Contenu principal ── --}}
    <div class="row g-3">

        {{-- Historique --}}
        <div class="col-lg-8">
            <p class="section-title"><i class="fas fa-history"></i> Historique des présences</p>

            @if($attendances->count() > 0)
                @foreach($attendances as $attendance)
                <div class="attendance-item {{ $attendance->status }}">

                    {{-- Ligne haute : date | matière + statut --}}
                    <div class="attend-item-top">
                        <div class="attend-date-block">
                            <div class="attend-date-day">{{ $attendance->date->format('d') }}</div>
                            <div class="attend-date-month">{{ $attendance->date->translatedFormat('M') }}</div>
                            <div class="attend-date-dow">{{ $attendance->date->translatedFormat('D') }}</div>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                <span class="attend-subject">
                                    {{ $attendance->subject->name ?? 'Cours' }}
                                    @if($attendance->session_type)
                                        <span class="badge bg-light text-secondary border ms-1" style="font-size:.62rem;">{{ $attendance->session_type }}</span>
                                    @endif
                                </span>
                                @if($attendance->status === 'present')
                                    <span class="status-pill pill-present"><i class="fas fa-check-circle"></i> Présent</span>
                                @elseif($attendance->status === 'absent')
                                    <span class="status-pill pill-absent"><i class="fas fa-times-circle"></i> Absent</span>
                                @elseif($attendance->status === 'late')
                                    <span class="status-pill pill-late">
                                        <i class="fas fa-clock"></i> Retard
                                        @if($attendance->minutes_late > 0)({{ $attendance->minutes_late }} min)@endif
                                    </span>
                                @endif
                            </div>
                            <div class="attend-meta mt-1">
                                <i class="far fa-clock"></i> {{ $attendance->start_time }} – {{ $attendance->end_time }}
                                @if($attendance->teacher)
                                    &nbsp;·&nbsp; <i class="fas fa-user-tie"></i> {{ $attendance->teacher->name ?? '' }}
                                @endif
                                @if($attendance->classroom)
                                    &nbsp;·&nbsp; <i class="fas fa-door-open"></i> {{ $attendance->classroom }}
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Justification status --}}
                    @if($attendance->status === 'absent' && $attendance->justification_status)
                        <div class="justif-notice">
                            <i class="fas fa-info-circle"></i>
                            Justification : {{ $attendance->justification_status === 'pending' ? 'En attente de validation' : 'Validée' }}
                        </div>
                    @endif

                    {{-- Action --}}
                    @if($attendance->status === 'absent' && ! in_array($attendance->justification_status, ['pending', 'approved']))
                        <div class="attend-item-action">
                            <button class="btn-justify"
                                    data-bs-toggle="modal"
                                    data-bs-target="#justifyAbsenceModal"
                                    data-attendance-id="{{ $attendance->id }}">
                                <i class="fas fa-pen-alt"></i> Justifier
                            </button>
                        </div>
                    @elseif($attendance->status === 'absent' && $attendance->justification_status === 'approved')
                        <div class="attend-item-action">
                            <span class="status-pill pill-present" style="font-size:.68rem;">
                                <i class="fas fa-check"></i> Justifiée
                            </span>
                        </div>
                    @endif
                </div>
                @endforeach

                <div class="d-flex justify-content-center mt-3">
                    {{ $attendances->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3 d-block opacity-25"></i>
                    <p class="mb-0 small">Aucune présence enregistrée pour le moment.</p>
                </div>
            @endif
        </div>

        {{-- Calendrier --}}
        <div class="col-lg-4">
            <p class="section-title"><i class="fas fa-calendar-alt"></i> Calendrier</p>
            <div class="bg-white border rounded-3 p-2" style="border-color:#fde68a!important;">
                <div id="attendanceCalendar" style="font-size:.75rem;"></div>
                <div class="cal-legend">
                    <span class="cal-legend-item"><span class="cal-dot" style="background:#10b981;"></span>Présent</span>
                    <span class="cal-legend-item"><span class="cal-dot" style="background:#f59e0b;"></span>Retard</span>
                    <span class="cal-legend-item"><span class="cal-dot" style="background:#ef4444;"></span>Absent</span>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modals (commentés, fonctionnalité à implémenter) --}}
{{--
<div class="modal fade" id="justifyAbsenceModal" ...>...</div>
<div class="modal fade" id="requestAbsenceModal" ...>...</div>
--}}

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('attendanceCalendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        height: 'auto',
        headerToolbar: { left: 'prev', center: 'title', right: 'next' },
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

    var tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.forEach(function(el) { new bootstrap.Tooltip(el); });

    // Injecter l'id dans le modal de justification
    document.querySelectorAll('[data-attendance-id]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.attendanceId;
            var input = document.getElementById('attendanceId');
            if (input) input.value = id;
        });
    });
});
</script>
@endpush
@endsection
