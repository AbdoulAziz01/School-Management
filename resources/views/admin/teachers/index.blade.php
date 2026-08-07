@extends('admin.layouts.app')

@section('title', 'Gestion des enseignants')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0">Liste des enseignants</h5>
                        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> Ajouter un enseignant
                        </a>
                        <a href="{{ route('admin.teachers.import') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-file-import me-1"></i> Importer
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <form action="{{ route('admin.teachers.index') }}" method="GET" class="d-flex gap-2 align-items-start">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}" style="min-width: 200px;">
                            <select name="status" class="form-select form-select-sm" style="min-width: 120px;">
                                <option value="">Tous les statuts</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>

                            {{-- Checklist de classes : filtre les enseignants affectés à une ou plusieurs classes cochées --}}
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"
                                        data-bs-auto-close="outside" aria-expanded="false" style="min-width: 140px;">
                                    <i class="fas fa-chalkboard me-1"></i>
                                    Classe{{ count($selectedClassIds ?? []) > 1 ? 's' : '' }}
                                    @if(!empty($selectedClassIds))
                                        <span class="badge bg-primary ms-1">{{ count($selectedClassIds) }}</span>
                                    @endif
                                </button>
                                <div class="dropdown-menu p-2" style="max-height: 320px; overflow-y: auto; min-width: 240px;">
                                    @if($filterClasses->isEmpty())
                                        <div class="text-muted small px-2">Aucune classe pour l'année courante.</div>
                                    @else
                                        @foreach($filterClasses as $class)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="class_ids[]" value="{{ $class->id }}"
                                                       id="filter_class_{{ $class->id }}"
                                                       {{ in_array($class->id, $selectedClassIds ?? []) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="filter_class_{{ $class->id }}">
                                                    {{ $class->name }}
                                                    <span class="text-muted">{{ $class->level->name ?? '' }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                        <hr class="my-2">
                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                            <i class="fas fa-filter me-1"></i> Appliquer
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search') || request('status') || !empty($selectedClassIds))
                                <a href="{{ route('admin.teachers.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </form>
                        <span class="text-muted small">{{ $teachers->total() }} enseignant(s)</span>
                    </div>
                </div>
                <div class="card-body">
                    @include('admin.teachers._credentials-alert')

                    @if($teachers->isEmpty())
                        <div class="alert alert-info">
                            @if(request('search') || request('status'))
                                Aucun enseignant ne correspond à votre recherche.
                            @else
                                Aucun enseignant n'a été enregistré pour le moment.
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Enseignant</th>
                                        <th>Classe(s)</th>
                                        <th>Statut</th>
                                        <th class="text-end" style="width: 70px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teachers as $teacher)
                                        <tr>
                                            <td>
                                                <div>{{ $teacher->name }}</div>
                                                <div class="small text-muted">
                                                    <code>{{ $teacher->identifier ?? '-' }}</code> · {{ $teacher->email }}
                                                </div>
                                            </td>
                                            <td style="max-width: 160px;">
                                                @php
                                                    // Primaire (titulaire, class_teacher) + collège/lycée
                                                    // (TeacherAssignment par classe+matière) : un enseignant
                                                    // peut apparaître dans l'un, l'autre, ou aucun des deux.
                                                    $teacherClasses = $teacher->assignedClasses
                                                        ->concat($teacher->teacherAssignments->pluck('schoolClass')->filter())
                                                        ->unique('id')
                                                        ->values();
                                                @endphp
                                                @if($teacherClasses->isEmpty())
                                                    <span class="text-muted small">Aucune</span>
                                                @else
                                                    <div class="dropdown d-inline-block">
                                                        <a href="#" class="badge bg-light text-dark border" role="button" id="classesDropdown{{ $teacher->id }}"
                                                           data-bs-toggle="dropdown" data-bs-strategy="fixed" data-bs-boundary="viewport" aria-expanded="false">
                                                            {{ $teacherClasses->count() }} classe(s)
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="classesDropdown{{ $teacher->id }}" style="min-width: 160px;">
                                                            @foreach($teacherClasses as $class)
                                                                <li class="px-2 py-1">{{ $class->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($teacher->status == 'approved')
                                                    <span class="badge bg-success">Approuvé</span>
                                                @elseif($teacher->status == 'pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                @else
                                                    <span class="badge bg-danger">Rejeté</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                {{-- Menu compact (au lieu de 4 boutons côte à côte) : la colonne
                                                     Actions ne pousse plus le tableau hors de l'écran. --}}
                                                <div class="dropdown">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"
                                                            data-bs-strategy="fixed" data-bs-boundary="viewport" aria-expanded="false" title="Actions">
                                                        <i class="fas fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.teachers.show', $teacher) }}">
                                                                <i class="fas fa-eye me-2 text-info"></i>Voir
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.teachers.edit', $teacher) }}">
                                                                <i class="fas fa-edit me-2 text-warning"></i>Modifier
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.teachers.classes.edit', $teacher) }}">
                                                                <i class="fas fa-chalkboard me-2 text-primary"></i>Affectations
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button type="button" class="dropdown-item text-danger"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteTeacherModal{{ $teacher->id }}">
                                                                <i class="fas fa-trash me-2"></i>Supprimer
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <!-- Modal de confirmation de suppression -->
                                                <div class="modal fade" id="deleteTeacherModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <p>Êtes-vous sûr de vouloir supprimer l'enseignant <strong>{{ $teacher->name }}</strong> ?</p>
                                                                <p class="text-danger"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <form action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="fas fa-trash me-1"></i>Supprimer
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $teachers->withQueryString()->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection