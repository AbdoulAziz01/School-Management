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
                        <form action="{{ route('admin.teachers.index') }}" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}" style="min-width: 200px;">
                            <select name="status" class="form-select form-select-sm" style="min-width: 120px;">
                                <option value="">Tous les statuts</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approuvé</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search') || request('status'))
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
                                        <th>Identifiant</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Téléphone</th>
                                        <th>Classe(s)</th>
                                        <th>Statut</th>
                                        <th>Date d'inscription</th>
                                        <th class="text-end" style="width: 70px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teachers as $teacher)
                                        <tr>
                                            <td><code>{{ $teacher->identifier ?? '-' }}</code></td>
                                            <td>{{ $teacher->name }}</td>
                                            <td>{{ $teacher->email }}</td>
                                            <td>{{ $teacher->phone ?? 'Non renseigné' }}</td>
                                            <td>
                                                @forelse($teacher->assignedClasses as $class)
                                                    <span class="badge bg-light text-dark border">{{ $class->name }}</span>
                                                @empty
                                                    <span class="text-muted small">Aucune</span>
                                                @endforelse
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
                                            <td>{{ $teacher->created_at->format('d/m/Y') }}</td>
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