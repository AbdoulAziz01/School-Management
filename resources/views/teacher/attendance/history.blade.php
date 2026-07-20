@extends('teacher.layouts.app')

@section('title', 'Historique des Présences - Enseignant')

@section('content')
<a href="{{ route('teacher.attendance.index') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Gestion des Présences
</a>
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0 h3">Historique des Présences</h1>
        <p class="text-muted">Retrouvez les appels déjà enregistrés, jour par jour</p>
    </div>
    <a href="{{ route('teacher.attendance.index', $selectedClassId ? ['class_id' => $selectedClassId] : []) }}" class="btn btn-outline-primary">
        <i class="fas fa-clipboard-check me-2"></i>Faire l'appel
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Sélection</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.attendance.history') }}" class="row g-3">
            <div class="col-md-6">
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
        </form>
    </div>
</div>

@if($selectedClassId && $selectedClass)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Appels de {{ $selectedClass->name }} ({{ $days->count() }})</h5>
        </div>
        <div class="card-body">
            @if($days->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-center">Présents</th>
                                <th class="text-center">Absents</th>
                                <th class="text-center">Retards</th>
                                <th class="text-center">Excusés</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($days as $day)
                                <tr>
                                    <td class="fw-medium">{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('d M Y') }}</td>
                                    <td class="text-center"><span class="badge bg-success">{{ $day['present'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-danger">{{ $day['absent'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-warning text-dark">{{ $day['late'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $day['excused'] }}</span></td>
                                    <td class="text-center text-muted">{{ $day['total'] }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('teacher.attendance.index', ['class_id' => $selectedClassId, 'date' => $day['date']]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Aucun appel enregistré pour cette classe pour le moment.</p>
                </div>
            @endif
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-history fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">Sélectionnez une classe</h4>
            <p class="text-muted">Choisissez une classe pour voir l'historique de ses appels.</p>
        </div>
    </div>
@endif
@endsection
