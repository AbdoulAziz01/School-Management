@extends('layouts.student')

@section('title', 'Mon Emploi du Temps')

@push('styles')
<style>
    .time-slot {
        border-left: 4px solid #f59e0b;
        margin-bottom: 12px;
        padding: 12px 15px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .time-slot:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }
    
    .time-slot .subject-name {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 6px;
    }
    
    .time-slot .time-info {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .time-slot .teacher-info,
    .time-slot .room-info {
        font-size: 0.8rem;
        color: #9ca3af;
    }
    
    .schedule-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .schedule-table th {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: white;
        font-weight: 600;
        text-align: center;
        padding: 15px 10px;
        font-size: 0.9rem;
    }
    
    .schedule-table td {
        padding: 8px;
        vertical-align: top;
        border: 1px solid #e5e7eb;
        min-height: 80px;
    }
    
    .schedule-cell {
        min-height: 70px;
        border-radius: 8px;
        padding: 10px;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    
    .schedule-cell:hover {
        transform: scale(1.02);
    }
    
    .schedule-cell .subject {
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .schedule-cell .details {
        font-size: 0.75rem;
        opacity: 0.9;
    }
    
    .time-column {
        background: #f8fafc;
        font-weight: 600;
        color: #374151;
        text-align: center;
        width: 80px;
        font-size: 0.85rem;
    }
    
    .empty-cell {
        color: #d1d5db;
        text-align: center;
        padding: 20px;
    }
    
    .day-header {
        font-weight: 600;
        color: #d97706;
        font-size: 1.1rem;
        padding: 10px 0;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 15px;
    }
    
    .simulation-badge {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* ── Stats strip ── */
    .sched-stats-strip {
        display: flex;
        align-items: stretch;
        background: rgba(245,158,11,0.07);
        border-radius: 14px;
        padding: 1rem 0;
    }
    .sched-stat-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0 0.75rem;
        border-right: 1.5px solid rgba(245,158,11,0.22);
        text-align: center;
    }
    .sched-stat-item:last-child { border-right: none; }
    .sched-stat-value {
        font-size: 1.75rem;
        font-weight: 900;
        color: #d97706;
        line-height: 1;
    }
    .sched-stat-label {
        font-size: 0.62rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-top: 5px;
    }

    @media (max-width: 575.98px) {
        .sched-stat-value { font-size: 1.4rem; }
        .sched-stats-strip { padding: 0.75rem 0; }
    }

    .schedule-print-header {
        display: none;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            background: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sidebar,
        .sidebar-overlay,
        .mobile-header,
        .top-navbar,
        .no-print,
        .alert,
        iframe,
        #ai-agent-widget,
        .ai-agent-root {
            display: none !important;
        }

        .wrapper,
        .main-content,
        .content-area {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-height: auto !important;
        }

        .container-fluid {
            max-width: 100% !important;
            padding: 0 !important;
        }

        .schedule-print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d97706;
        }

        .schedule-print-header h2 {
            font-size: 1.25rem;
            margin-bottom: 4px;
            color: #92400e;
        }

        .schedule-print-header p {
            margin: 0;
            font-size: 0.85rem;
            color: #374151;
        }

        .schedule-mobile-view {
            display: none !important;
        }

        .schedule-desktop-view {
            display: block !important;
        }

        .schedule-desktop-view .card {
            box-shadow: none !important;
            border: 1px solid #e5e7eb !important;
            transform: none !important;
        }

        .schedule-table {
            width: 100% !important;
            box-shadow: none !important;
            font-size: 9pt;
            page-break-inside: avoid;
        }

        .schedule-table th {
            background: #d97706 !important;
            color: white !important;
            padding: 8px 4px !important;
            font-size: 9pt;
        }

        .schedule-table td {
            padding: 4px !important;
            vertical-align: middle !important;
        }

        .schedule-cell {
            transform: none !important;
            min-height: auto !important;
            padding: 6px !important;
            font-size: 8pt;
            page-break-inside: avoid;
        }

        .schedule-cell .details {
            font-size: 7pt;
        }

        .time-column {
            font-size: 8pt;
            width: 60px !important;
        }

        .empty-cell {
            padding: 8px !important;
        }

        .simulation-badge {
            background: #f3f4f6 !important;
            color: #374151 !important;
            border: 1px solid #d1d5db;
        }

        h1.h3 {
            font-size: 1.1rem !important;
            margin-bottom: 8px !important;
        }
    }
</style>
@endpush

@section('content')
<a href="{{ route('student.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="container-fluid">
    <div class="schedule-print-header">
        <h2>{{ $schoolDisplayName ?? 'Mon établissement' }}</h2>
        <p><strong>Emploi du temps</strong> — {{ Auth::user()->name }}</p>
        @if(Auth::user()->schoolClass)
            <p>Classe : {{ Auth::user()->schoolClass->name }}</p>
        @endif
        <p class="small">Imprimé le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

    <!-- En-tête -->
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1 h3">Mon Emploi du Temps</h1>
            @if(!$hasRealData)
                <span class="simulation-badge">
                    <i class="fas fa-info-circle me-1"></i> Aucun emploi du temps enregistré pour votre classe
                </span>
            @endif
        </div>
        <button class="btn btn-primary no-print" id="printSchedule">
            <i class="fas fa-print me-2"></i>Imprimer
        </button>
    </div>

    <!-- Vue mobile - Accordéon par jour -->
    <div class="d-block d-lg-none schedule-mobile-view">
        <div class="accordion" id="mobileSchedule">
            @foreach($days as $dayNumber => $dayName)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $dayNumber != 1 ? 'collapsed' : '' }}" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#collapse{{ $dayNumber }}">
                            <i class="fas fa-calendar-day me-2"></i>{{ $dayName }}
                            <span class="badge bg-primary ms-2">
                                {{ $schedule->get($dayNumber, collect())->count() }} cours
                            </span>
                        </button>
                    </h2>
                    <div id="collapse{{ $dayNumber }}" 
                         class="accordion-collapse collapse {{ $dayNumber == 1 ? 'show' : '' }}" 
                         data-bs-parent="#mobileSchedule">
                        <div class="accordion-body">
                            @php
                                $daySchedules = $schedule->get($dayNumber, collect());
                            @endphp
                            
                            @if($daySchedules->isNotEmpty())
                                @foreach($daySchedules as $slot)
                                    <div class="time-slot" style="border-left-color: {{ $slot->subject->color ?? '#f59e0b' }};">
                                        <div class="subject-name" style="color: {{ $slot->subject->color ?? '#f59e0b' }};">
                                            {{ $slot->subject->name }}
                                        </div>
                                        <div class="time-info">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $slot->start_time }} - {{ $slot->end_time }}
                                        </div>
                                        <div class="teacher-info">
                                            <i class="fas fa-user-tie me-1"></i>
                                            {{ $slot->teacher->name }}
                                        </div>
                                        <div class="room-info">
                                            <i class="fas fa-door-open me-1"></i>
                                            {{ $slot->classroom->name }}
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-coffee fa-2x mb-2"></i>
                                    <p class="mb-0">Pas de cours ce jour</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Vue desktop - Tableau complet -->
    <div class="card shadow-sm d-none d-lg-block schedule-desktop-view">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Heure</th>
                            @foreach($days as $dayNumber => $dayName)
                                <th>{{ $dayName }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeSlots as $timeSlot)
                            <tr>
                                <td class="time-column">
                                    {{ $timeSlot['start'] }}<br>
                                    <small class="text-muted">{{ $timeSlot['end'] }}</small>
                                </td>

                                @foreach($days as $dayNumber => $dayName)
                                    @php
                                        $slot = $schedule->get($dayNumber, collect())->first(function ($item) use ($timeSlot) {
                                            return $item->start_time === $timeSlot['start'];
                                        });
                                    @endphp

                                    <td>
                                        @if($slot)
                                            <div class="schedule-cell" style="background-color: {{ $slot->subject->color ?? '#f59e0b' }}20; border-left: 3px solid {{ $slot->subject->color ?? '#f59e0b' }};">
                                                <div class="subject" style="color: {{ $slot->subject->color ?? '#f59e0b' }};">
                                                    {{ $slot->subject->name }}
                                                </div>
                                                <div class="details text-muted">
                                                    <div><i class="fas fa-user-tie me-1"></i>{{ $slot->teacher->name }}</div>
                                                    <div><i class="fas fa-door-open me-1"></i>{{ $slot->classroom->name }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="empty-cell">
                                                <i class="fas fa-minus"></i>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Légende des matières -->
    <div class="card mt-4 no-print">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-palette me-2"></i>Légende des matières</h6>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3">
                @php
                    $subjects = collect();
                    foreach($schedule as $dayCourses) {
                        foreach($dayCourses as $course) {
                            $subjects->push((object)[
                                'name' => $course->subject->name,
                                'color' => $course->subject->color ?? '#f59e0b'
                            ]);
                        }
                    }
                    $subjects = $subjects->unique('name');
                @endphp
                
                @foreach($subjects as $subject)
                    <div class="d-flex align-items-center">
                        <span class="rounded-circle me-2" style="width: 12px; height: 12px; background-color: {{ $subject->color }};"></span>
                        <span class="small">{{ $subject->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Statistiques --}}
    @php
        $totalCourses = 0;
        foreach($schedule as $dayCourses) { $totalCourses += $dayCourses->count(); }
    @endphp
    <div class="sched-stats-strip mt-4 no-print">
        <div class="sched-stat-item">
            <span class="sched-stat-value">{{ $totalCourses }}</span>
            <span class="sched-stat-label">Cours / semaine</span>
        </div>
        <div class="sched-stat-item">
            <span class="sched-stat-value">{{ $subjectCount ?? 0 }}</span>
            <span class="sched-stat-label">Matières</span>
        </div>
        <div class="sched-stat-item">
            <span class="sched-stat-value">{{ $totalCourses }}h</span>
            <span class="sched-stat-label">Heures / semaine</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('printSchedule').addEventListener('click', function() {
        window.print();
    });
</script>
@endpush
@endsection
