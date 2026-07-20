@extends('teacher.layouts.app')

@section('title', 'Gestion des Présences - Enseignant')

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

<a href="{{ route('teacher.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0 h3">Gestion des Présences</h1>
        <p class="text-muted">Faites l'appel de vos classes</p>
    </div>
    <a href="{{ route('teacher.attendance.history', $selectedClassId ? ['class_id' => $selectedClassId] : []) }}" class="btn btn-outline-primary">
        <i class="fas fa-history me-2"></i>Historique des appels
    </a>
</div>

{{-- Filtres --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Sélection</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.attendance.index') }}" class="row g-3">
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
                    <i class="fas fa-search me-2"></i>Afficher la liste
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedClassId)
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Appel du {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                        — {{ $classes->firstWhere('id', $selectedClassId)->name ?? '' }}
                    </h5>
                    @if($attendanceLocked ?? false)
                        <span class="badge bg-secondary mt-1">
                            <i class="fas fa-lock me-1"></i>Appel enregistré — modification impossible
                        </span>
                    @endif
                </div>
                @if(!($attendanceLocked ?? false))
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="setAllStatus('present')">
                            <i class="fas fa-check me-1"></i>Marquer tout le monde présent
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAllStatus('absent')">
                            <i class="fas fa-times me-1"></i>Marquer tout le monde absent
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($students->count() > 0)
                @if($attendanceLocked ?? false)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Élève</th>
                                    <th style="min-width: 180px;">Situation du jour</th>
                                    <th style="min-width: 200px;">Remarque</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                    @php
                                        $currentAttendance = $attendances->get((int) $student->id);
                                        $currentStatus = $currentAttendance?->status ?? 'present';
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
                                            <span class="badge {{ $statusBadge[$currentStatus] ?? 'bg-secondary' }}">
                                                {{ $statusLabels[$currentStatus] ?? $currentStatus }}
                                            </span>
                                        </td>
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
                    <form method="POST" action="{{ route('teacher.attendance.store') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                        <input type="hidden" name="date" value="{{ $selectedDate }}">

                        {{-- Infos de séance --}}
                        <div class="row g-3 mb-4 pb-3 border-bottom">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Matière <span class="text-muted fw-normal">(optionnel)</span></label>
                                <select name="subject_id" class="form-select">
                                    <option value="">— Sélectionner une matière —</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Heure de début <span class="text-muted fw-normal">(optionnel)</span></label>
                                <input type="time" name="start_time" class="form-control" value="{{ date('H:i') }}">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Élève</th>
                                        <th style="min-width: 220px;">Situation du jour</th>
                                        <th style="min-width: 200px;">Remarque (facultatif)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($students as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="attendances[{{ $index }}][user_id]" value="{{ $student->id }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0"
                                                         style="width: 35px; height: 35px; font-size: 0.85rem; background-color: #f59e0b;">
                                                        {{ strtoupper(substr($student->name, 0, 2)) }}
                                                    </div>
                                                    <span class="fw-medium">{{ $student->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <select name="attendances[{{ $index }}][status]"
                                                        class="form-select attendance-status-select"
                                                        aria-label="Situation de {{ $student->name }}">
                                                    <option value="present">Présent</option>
                                                    <option value="absent">Absent</option>
                                                    <option value="late">En retard</option>
                                                    <option value="excused">Absence justifié</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="attendances[{{ $index }}][notes]"
                                                       class="form-control"
                                                       placeholder="Ex. certificat médical, voyage…">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg"
                                    onclick="return confirm('Confirmer l\'appel du jour ? Une fois enregistré, il ne pourra plus être modifié.');">
                                <i class="fas fa-save me-2"></i>Enregistrer l'appel du jour
                            </button>
                        </div>
                    </form>
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
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">Sélectionnez une classe</h4>
            <p class="text-muted">Choisissez une classe et une date, puis cliquez sur « Afficher la liste ».</p>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .attendance-status-select option[value="present"] { color: #198754; }
    .attendance-status-select option[value="absent"] { color: #dc3545; }
    .attendance-status-select option[value="late"] { color: #b45309; }
    .attendance-status-select option[value="excused"] { color: #0d6efd; }
    .attendance-status-select.status-present { border-color: #198754; background-color: #f0fdf4; }
    .attendance-status-select.status-absent { border-color: #dc3545; background-color: #fef2f2; }
    .attendance-status-select.status-late { border-color: #f59e0b; background-color: #fffbeb; }
    .attendance-status-select.status-excused { border-color: #0d6efd; background-color: #eff6ff; }
</style>
@endpush

@push('scripts')
<script>
    function setAllStatus(status) {
        document.querySelectorAll('.attendance-status-select').forEach(function (select) {
            select.value = status;
            updateSelectStyle(select);
        });
    }

    function updateSelectStyle(select) {
        select.classList.remove('status-present', 'status-absent', 'status-late', 'status-excused');
        select.classList.add('status-' + select.value);
    }

    document.querySelectorAll('.attendance-status-select').forEach(function (select) {
        updateSelectStyle(select);
        select.addEventListener('change', function () {
            updateSelectStyle(this);
        });
    });
</script>
@endpush
