@extends('admin.layouts.app')

@section('title', 'Présences')

@section('content')
@php
    $statusLabels = [
        'present' => 'Présent',
        'absent'  => 'Absent',
        'late'    => 'En retard',
        'excused' => 'Absence justifié',
    ];
    $statusBadge = [
        'present' => 'bg-success',
        'absent'  => 'bg-danger',
        'late'    => 'bg-warning text-dark',
        'excused' => 'bg-primary',
    ];
@endphp
<div class="container-fluid py-4">
    <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
        <i class="fas fa-arrow-left me-2"></i>Tableau de bord
    </a>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-clipboard-check me-2 text-warning"></i>Présences</h1>
            <p class="text-muted mb-0">Consultation en lecture seule de l'appel fait par les enseignants, toutes classes confondues.</p>
        </div>
        <a href="{{ route('admin.attendance.history', $selectedClassId ? ['class_id' => $selectedClassId] : []) }}" class="btn btn-outline-primary">
            <i class="fas fa-history me-2"></i>Historique par classe
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($selectedYear ?? null)
        <p class="text-muted small mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Année active : <strong>{{ $selectedYear->name }}</strong> — changez-la dans la barre en haut.
        </p>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Sélection</h5>
        </div>
        <div class="card-body">
            @if($classes->isEmpty())
                <div class="alert alert-warning mb-0">Aucune classe pour cette année scolaire.</div>
            @else
                <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-3">
                    <div class="col-md-5">
                        <label for="class_id" class="form-label">Classe</label>
                        <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Sélectionner une classe --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate }}" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Afficher
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if($selectedClassId)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Appel du {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                    — {{ $classes->firstWhere('id', $selectedClassId)->name ?? '' }}
                </h5>
            </div>
            <div class="card-body">
                @if($students->count() > 0)
                    @if($attendances->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Élève</th>
                                        <th style="min-width: 180px;">Situation du jour</th>
                                        <th>Enseignant</th>
                                        <th style="min-width: 200px;">Remarque</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        @php
                                            $currentAttendance = $attendances->get((int) $student->id);
                                            $currentStatus = $currentAttendance?->status;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                                                         style="width: 35px; height: 35px; font-size: 0.85rem; background-color: #f59e0b;">
                                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                                    </div>
                                                    <span class="fw-medium">{{ $student->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($currentStatus)
                                                    <span class="badge {{ $statusBadge[$currentStatus] ?? 'bg-secondary' }}">
                                                        {{ $statusLabels[$currentStatus] ?? $currentStatus }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted border">Non renseigné</span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $currentAttendance?->teacher?->name ?? '—' }}</td>
                                            <td>
                                                @if($currentAttendance?->reason)
                                                    <span class="text-muted small">{{ $currentAttendance->reason }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Aucun appel enregistré pour cette classe à cette date.</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Aucun élève dans cette classe</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Sélectionnez une classe</h4>
                <p class="text-muted">Choisissez une classe et une date, puis cliquez sur « Afficher ».</p>
            </div>
        </div>
    @endif
</div>
@endsection
