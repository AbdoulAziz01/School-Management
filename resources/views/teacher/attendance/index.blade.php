@extends('teacher.layouts.app')

@section('title', 'Gestion des Présences - Enseignant')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0 h3">Gestion des Présences</h1>
        <p class="text-muted">Faites l'appel de vos classes</p>
    </div>
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
                <select name="class_id" id="class_id" class="form-select">
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
                <input type="date" name="date" id="date" class="form-control" value="{{ $selectedDate }}">
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Afficher
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Formulaire de saisie des présences --}}
@if($selectedClassId)
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
        <input type="hidden" name="date" value="{{ $selectedDate }}">
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Appel du {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                    - {{ $classes->firstWhere('id', $selectedClassId)->name ?? '' }}
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="setAllStatus('present')">
                        <i class="fas fa-check me-1"></i>Tous présents
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAllStatus('absent')">
                        <i class="fas fa-times me-1"></i>Tous absents
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($students->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Élève</th>
                                    <th class="text-center">Statut</th>
                                    <th>Remarque</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                    @php
                                        $currentAttendance = $attendances->get($student->id);
                                        $currentStatus = $currentAttendance->status ?? 'present';
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <input type="hidden" name="attendances[{{ $index }}][user_id]" value="{{ $student->id }}">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.85rem;">
                                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                                </div>
                                                {{ $student->name }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <input type="radio" class="btn-check status-radio" name="attendances[{{ $index }}][status]" 
                                                       id="present_{{ $student->id }}" value="present" {{ $currentStatus == 'present' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-success btn-sm" for="present_{{ $student->id }}" title="Présent">
                                                    <i class="fas fa-check"></i>
                                                </label>
                                                
                                                <input type="radio" class="btn-check status-radio" name="attendances[{{ $index }}][status]" 
                                                       id="absent_{{ $student->id }}" value="absent" {{ $currentStatus == 'absent' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-danger btn-sm" for="absent_{{ $student->id }}" title="Absent">
                                                    <i class="fas fa-times"></i>
                                                </label>
                                                
                                                <input type="radio" class="btn-check status-radio" name="attendances[{{ $index }}][status]" 
                                                       id="late_{{ $student->id }}" value="late" {{ $currentStatus == 'late' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-warning btn-sm" for="late_{{ $student->id }}" title="En retard">
                                                    <i class="fas fa-clock"></i>
                                                </label>
                                                
                                                <input type="radio" class="btn-check status-radio" name="attendances[{{ $index }}][status]" 
                                                       id="excused_{{ $student->id }}" value="excused" {{ $currentStatus == 'excused' ? 'checked' : '' }}>
                                                <label class="btn btn-outline-info btn-sm" for="excused_{{ $student->id }}" title="Excusé">
                                                    <i class="fas fa-file-medical"></i>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="attendances[{{ $index }}][notes]" 
                                                   class="form-control form-control-sm" 
                                                   placeholder="Remarque..."
                                                   value="{{ $currentAttendance->notes ?? '' }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <span class="badge bg-success me-2">Présent</span>
                            <span class="badge bg-danger me-2">Absent</span>
                            <span class="badge bg-warning me-2">Retard</span>
                            <span class="badge bg-info">Excusé</span>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer l'appel
                        </button>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun élève dans cette classe</p>
                    </div>
                @endif
            </div>
        </div>
    </form>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-clipboard-list fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">Sélectionnez une classe</h4>
            <p class="text-muted">Choisissez une classe et une date pour faire l'appel.</p>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    function setAllStatus(status) {
        const radios = document.querySelectorAll('input[value="' + status + '"].status-radio');
        radios.forEach(radio => {
            radio.checked = true;
        });
    }
</script>
@endpush
