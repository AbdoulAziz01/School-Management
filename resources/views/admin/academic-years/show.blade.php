<!-- resources/views/admin/academic-years/show.blade.php -->
@extends('admin.layouts.app')

@section('content')
<div class="flex-wrap pt-3 pb-2 mb-3 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
    <h1 class="h2">Détails de l'année scolaire</h1>
    <div class="mb-2 btn-toolbar mb-md-0">
        <a href="{{ route('academic-years.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
        <a href="{{ route('academic-years.edit', $academicYear) }}" class="btn btn-sm btn-outline-primary me-2">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
        <form action="{{ route('academic-years.destroy', $academicYear) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger" 
                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette année scolaire ?')">
                <i class="fas fa-trash me-1"></i> Supprimer
            </button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-4 card">
            <div class="card-header">
                <h5 class="mb-0">Informations générales</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th>Nom :</th>
                        <td>{{ $academicYear->name }}</td>
                    </tr>
                    <tr>
                        <th>Période :</th>
                        <td>
                            Du {{ $academicYear->start_date->format('d/m/Y') }} 
                            au {{ $academicYear->end_date->format('d/m/Y') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Statut :</th>
                        <td>
                            @if($academicYear->is_current)
                                <span class="badge bg-success">Année en cours</span>
                            @else
                                <form action="{{ route('academic-years.set-current', $academicYear) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        Définir comme année courante
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Date de création :</th>
                        <td>{{ $academicYear->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Dernière mise à jour :</th>
                        <td>{{ $academicYear->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Statistiques -->
        <div class="mb-4 card">
            <div class="card-header">
                <h5 class="mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="text-center row">
                    <div class="mb-3 col-6">
                        <div class="p-3 border rounded bg-light">
                            <h3 class="mb-1">{{ $academicYear->classes_count ?? 0 }}</h3>
                            <small class="text-muted">Classes</small>
                        </div>
                    </div>
                    <div class="mb-3 col-6">
                        <div class="p-3 border rounded bg-light">
                            <h3 class="mb-1">{{ $academicYear->teacher_assignments_count ?? 0 }}</h3>
                            <small class="text-muted">Affectations de professeurs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des classes de l'année -->
<div class="mb-4 card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Classes de cette année scolaire</h5>
        <a href="{{ route('classes.create', ['academic_year_id' => $academicYear->id]) }}" 
           class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Ajouter une classe
        </a>
    </div>
    <div class="p-0 card-body">
        @if($academicYear->classes->isEmpty())
            <div class="p-3 text-center text-muted">
                Aucune classe n'a été créée pour cette année scolaire.
            </div>
        @else
            <div class="table-responsive">
                <table class="table mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nom de la classe</th>
                            <th>Niveau</th>
                            <th>Effectif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($academicYear->classes as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->level->name ?? 'Non défini' }}</td>
                                <td>{{ $class->students_count ?? 0 }} élève(s)</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('classes.show', $class) }}" 
                                           class="btn btn-outline-primary" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('classes.edit', $class) }}" 
                                           class="btn btn-outline-secondary" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Liste des affectations de professeurs -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Affectations des professeurs</h5>
    </div>
    <div class="p-0 card-body">
        @if($academicYear->teacherAssignments->isEmpty())
            <div class="p-3 text-center text-muted">
                Aucune affectation de professeur pour cette année scolaire.
            </div>
        @else
            <div class="table-responsive">
                <table class="table mb-0 table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Professeur</th>
                            <th>Matière</th>
                            <th>Classe</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($academicYear->teacherAssignments as $assignment)
                            <tr>
                                <td>{{ $assignment->teacher->name }}</td>
                                <td>{{ $assignment->subject->name }}</td>
                                <td>{{ $assignment->schoolClass->name }}</td>
                                <td>
                                    <form action="{{ route('admin.teachers.unassign', $assignment) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette affectation ?')">
                                            <i class="fas fa-times"></i> Retirer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection