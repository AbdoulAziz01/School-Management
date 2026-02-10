@extends('layouts.student')

@section('title', 'Mon Emploi du Temps')

@push('styles')
<style>
    .time-slot {
        border-left: 4px solid #3b82f6;
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
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
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
        color: #1e3a8a;
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

    @media print {
        .no-print { display: none !important; }
        .schedule-table { box-shadow: none; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1 h3">Mon Emploi du Temps</h1>
            @if(!isset($hasRealData) || !$hasRealData)
                <span class="simulation-badge">
                    <i class="fas fa-flask me-1"></i> Données de démonstration
                </span>
            @endif
        </div>
        <button class="btn btn-primary no-print" id="printSchedule">
            <i class="fas fa-print me-2"></i>Imprimer
        </button>
    </div>

    <!-- Vue mobile - Accordéon par jour -->
    <div class="d-block d-lg-none">
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
                                    <div class="time-slot" style="border-left-color: {{ $slot->subject->color ?? '#3b82f6' }};">
                                        <div class="subject-name" style="color: {{ $slot->subject->color ?? '#3b82f6' }};">
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
    <div class="card shadow-sm d-none d-lg-block">
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
                        @php
                            $timeSlots = [
                                ['start' => '08:00', 'end' => '09:00'],
                                ['start' => '09:00', 'end' => '10:00'],
                                ['start' => '10:15', 'end' => '11:15'],
                                ['start' => '11:15', 'end' => '12:15'],
                                ['start' => '14:00', 'end' => '15:00'],
                                ['start' => '15:00', 'end' => '16:00'],
                            ];
                        @endphp

                        @foreach($timeSlots as $timeSlot)
                            <tr>
                                <td class="time-column">
                                    {{ $timeSlot['start'] }}<br>
                                    <small class="text-muted">{{ $timeSlot['end'] }}</small>
                                </td>
                                
                                @foreach($days as $dayNumber => $dayName)
                                    @php
                                        $slot = $schedule->get($dayNumber, collect())->first(function($item) use ($timeSlot) {
                                            return $item->start_time == $timeSlot['start'];
                                        });
                                    @endphp
                                    
                                    <td>
                                        @if($slot)
                                            <div class="schedule-cell" style="background-color: {{ $slot->subject->color ?? '#3b82f6' }}20; border-left: 3px solid {{ $slot->subject->color ?? '#3b82f6' }};">
                                                <div class="subject" style="color: {{ $slot->subject->color ?? '#3b82f6' }};">
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
                                'color' => $course->subject->color ?? '#3b82f6'
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

    <!-- Statistiques -->
    <div class="row mt-4 no-print">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">
                        @php
                            $totalCourses = 0;
                            foreach($schedule as $dayCourses) {
                                $totalCourses += $dayCourses->count();
                            }
                        @endphp
                        {{ $totalCourses }}
                    </h3>
                    <small>Cours par semaine</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $subjects->count() }}</h3>
                    <small>Matières différentes</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $totalCourses }}h</h3>
                    <small>Heures de cours</small>
                </div>
            </div>
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
