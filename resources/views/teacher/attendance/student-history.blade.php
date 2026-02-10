@extends('teacher.layouts.app')

@section('title', 'Historique de présence - ' . $student->name)

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Tableau de bord</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.attendance.index') }}">Présences</a></li>
            <li class="breadcrumb-item active">{{ $student->name }}</li>
        </ol>
    </nav>
    
    <h1 class="mb-0 h3">Historique de présence</h1>
    <p class="text-muted">Élève: {{ $student->name }}</p>
</div>

<div class="row g-4">
    {{-- Statistiques --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Présences</span>
                        <span class="badge bg-success">{{ $stats['present'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php
                            $total = array_sum($stats);
                            $presentPercent = $total > 0 ? ($stats['present'] / $total) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ $presentPercent }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Absences</span>
                        <span class="badge bg-danger">{{ $stats['absent'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php $absentPercent = $total > 0 ? ($stats['absent'] / $total) * 100 : 0; @endphp
                        <div class="progress-bar bg-danger" style="width: {{ $absentPercent }}%"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Retards</span>
                        <span class="badge bg-warning">{{ $stats['late'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php $latePercent = $total > 0 ? ($stats['late'] / $total) * 100 : 0; @endphp
                        <div class="progress-bar bg-warning" style="width: {{ $latePercent }}%"></div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Excusés</span>
                        <span class="badge bg-info">{{ $stats['excused'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        @php $excusedPercent = $total > 0 ? ($stats['excused'] / $total) * 100 : 0; @endphp
                        <div class="progress-bar bg-info" style="width: {{ $excusedPercent }}%"></div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <h4 class="{{ $presentPercent >= 80 ? 'text-success' : ($presentPercent >= 60 ? 'text-warning' : 'text-danger') }}">
                        {{ number_format($presentPercent, 1) }}%
                    </h4>
                    <small class="text-muted">Taux de présence</small>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Historique --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historique</h5>
            </div>
            <div class="card-body">
                @if($attendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Statut</th>
                                    <th>Remarque</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendances as $attendance)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') }}</td>
                                        <td class="text-center">
                                            @switch($attendance->status)
                                                @case('present')
                                                    <span class="badge bg-success">Présent</span>
                                                    @break
                                                @case('absent')
                                                    <span class="badge bg-danger">Absent</span>
                                                    @break
                                                @case('late')
                                                    <span class="badge bg-warning">Retard</span>
                                                    @break
                                                @case('excused')
                                                    <span class="badge bg-info">Excusé</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $attendance->status }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $attendance->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $attendances->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun historique de présence</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
