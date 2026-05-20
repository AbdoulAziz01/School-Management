@extends('teacher.layouts.app')

@section('title', 'Emploi du temps - Enseignant')

@section('content')
<div class="mb-4">
    <h1 class="mb-0 h3">Mon Emploi du Temps</h1>
    <p class="text-muted">{{ $currentYear ? 'Année scolaire ' . $currentYear->name : '' }}</p>
</div>

{{-- Résumé des cours --}}
<div class="mb-4 row g-3">
    <div class="col-md-4">
        <div class="card bg-primary bg-opacity-10 border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-white rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-primary">Mes Classes</h6>
                        <h3 class="mb-0">{{ $assignments->pluck('class_id')->unique()->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-primary bg-opacity-10 border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-white rounded-circle bg-primary d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-primary">Mes Matières</h6>
                        <h3 class="mb-0">{{ $assignments->pluck('subject_id')->unique()->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-info bg-opacity-10 border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="text-white rounded-circle bg-info d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-info">Affectations</h6>
                        <h3 class="mb-0">{{ $assignments->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Emploi du temps --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Emploi du temps hebdomadaire</h5>
    </div>
    <div class="p-0 card-body">
        <div class="table-responsive">
            <table class="table mb-0 table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Horaire</th>
                        @foreach($days as $dayName)
                            <th class="text-center">{{ $dayName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeSlots as $slot)
                        <tr>
                            <td class="text-center align-middle fw-bold bg-light">
                                <small>{{ $slot['label'] }}</small>
                            </td>
                            @foreach($days as $dayName)
                                <td class="p-2 text-center align-middle" style="min-width: 140px;">
                                    @if(isset($scheduleGrid[$dayName][$slot['label']]) && $scheduleGrid[$dayName][$slot['label']])
                                        @php $course = $scheduleGrid[$dayName][$slot['label']]; @endphp
                                        <div class="p-2 rounded bg-primary bg-opacity-10">
                                            <strong class="text-primary d-block">{{ $course['subject']->name ?? 'N/A' }}</strong>
                                            <small class="text-muted d-block">{{ $course['class']->name ?? 'N/A' }}</small>
                                            @if($course['room'])
                                                <small class="badge bg-secondary">{{ $course['room'] }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
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

{{-- Mes affectations --}}
<div class="mt-4 card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Mes affectations</h5>
    </div>
    <div class="card-body">
        @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Matière</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>
                                    <i class="fas fa-users text-success me-2"></i>
                                    {{ $assignment->schoolClass->name ?? 'N/A' }}
                                </td>
                                <td>
                                    <i class="fas fa-book text-primary me-2"></i>
                                    {{ $assignment->subject->name ?? 'N/A' }}
                                </td>
                                <td class="text-center">
                                    @if($assignment->schoolClass)
                                    <a href="{{ route('teacher.classes.show', $assignment->schoolClass->id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-eye me-1"></i>Voir la classe
                                    </a>
                                    <a href="{{ route('teacher.grades.index', ['class_id' => $assignment->schoolClass->id, 'subject_id' => $assignment->subject_id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-star me-1"></i>Notes
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-4 text-center">
                <i class="mb-3 fas fa-calendar-times fa-3x text-muted"></i>
                <p class="text-muted">Aucune affectation pour cette année scolaire</p>
            </div>
        @endif
    </div>
</div>
@endsection
