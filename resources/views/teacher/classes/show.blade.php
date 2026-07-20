@extends('teacher.layouts.app')

@section('title', 'Classe ' . $class->name . ' - Enseignant')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Tableau de bord</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.classes.index') }}">Mes Classes</a></li>
            <li class="breadcrumb-item active">{{ $class->name }}</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0 h3">{{ $class->name }}</h1>
            <p class="text-muted">{{ $class->level->name ?? '' }} - {{ $students->count() }} élève(s)</p>
        </div>
        <div>
            <a href="{{ route('teacher.grades.create', ['class_id' => $class->id]) }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Saisir des notes
            </a>
        </div>
    </div>
</div>

{{-- Informations de la classe --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h5>
            </div>
            <div class="card-body">
                <p><strong>Classe:</strong> {{ $class->name }}</p>
                <p><strong>Niveau:</strong> {{ $class->level->name ?? 'N/A' }}</p>
                <p><strong>Capacité:</strong> {{ $class->capacity ?? 'Non définie' }}</p>
                <p class="mb-0"><strong>Description:</strong> {{ $class->description ?? 'Aucune description' }}</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8" id="matieres-classe">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Matières enseignées dans cette classe</h5>
            </div>
            <div class="card-body">
                @if($assignments->count() > 0)
                    <p class="text-muted small mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Décochez une matière que vous n'assurez pas réellement dans cette classe — elle disparaîtra de la saisie de notes et de l'appel.
                    </p>
                    <div class="row g-3">
                        @foreach($assignments as $assignment)
                            @continue(! $assignment->subject)
                            <div class="col-md-6">
                                <div class="border rounded p-3 {{ $assignment->is_active ? '' : 'bg-light' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 {{ $assignment->is_active ? '' : 'text-muted text-decoration-line-through' }}">
                                                {{ $assignment->subject->name }}
                                            </h6>
                                            <small class="text-muted">Coef: {{ $assignment->subject->coefficient ?? 1 }}</small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($assignment->is_active)
                                                <a href="{{ route('teacher.grades.index', ['class_id' => $class->id, 'subject_id' => $assignment->subject->id]) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-star"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('teacher.assignments.toggle', $assignment) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $assignment->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                                        title="{{ $assignment->is_active ? 'Désactiver cette matière' : 'Réactiver cette matière' }}">
                                                    <i class="fas {{ $assignment->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center my-4">Aucune matière assignée pour cette classe</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Liste des élèves --}}
<div class="card" id="liste-eleves">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Liste des élèves ({{ $students->count() }})</h5>
        <div>
            <a href="{{ route('teacher.attendance.index', ['class_id' => $class->id]) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-clipboard-check me-1"></i>Faire l'appel
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($students->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Identifiant</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-size: 0.85rem;">
                                            {{ strtoupper(substr($student->name, 0, 2)) }}
                                        </div>
                                        {{ $student->name }}
                                    </div>
                                </td>
                                <td>{{ $student->email }}</td>
                                <td><code>{{ $student->identifier ?? 'N/A' }}</code></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        @foreach($subjects->take(2) as $subject)
                                            <a href="{{ route('teacher.grades.index', ['class_id' => $class->id, 'subject_id' => $subject->id]) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Notes - {{ $subject->name }}">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        @endforeach
                                        <a href="{{ route('teacher.attendance.student-history', $student->id) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="Historique de présence">
                                            <i class="fas fa-calendar-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun élève inscrit dans cette classe</p>
            </div>
        @endif
    </div>
</div>
@endsection
