<!-- resources/views/admin/academic-years/index.blade.php -->
@extends('admin.layouts.app')

@section('content')
<a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
    <i class="fas fa-arrow-left me-2"></i>Tableau de bord
</a>
<div class="flex-wrap pt-3 pb-2 mb-3 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
    <h1 class="h2">Gestion des années scolaires</h1>
    <div class="mb-2 btn-toolbar mb-md-0">
        <a href="{{ route('admin.academic-years.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle année scolaire
        </a>
    </div>
</div>

<div class="mb-4 card">
    <div class="card-header">
        <h5 class="mb-0">Liste des années scolaires</h5>
    </div>
    <div class="p-0 card-body">
        <div class="table-responsive">
            <table class="table mb-0 table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($years as $year)
                        <tr>
                            <td>{{ $year->name }}</td>
                            <td>
                                {{ $year->periodLabel() }}
                            </td>
                            <td>
                                @if($year->is_current)
                                    <span class="badge bg-success">Année en cours</span>
                                @endif
                                @if($year->is_closed)
                                    <span class="badge bg-secondary">Terminée</span>
                                @elseif(!$year->is_current)
                                    <span class="badge bg-light text-dark border">En cours d'activité</span>
                                @endif
                                @if(!$year->is_current && !$year->is_closed)
                                    <form action="{{ route('admin.academic-years.set-current', $year) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            Définir comme année courante
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.academic-years.show', $year) }}" class="btn" style="color: #fd7e14; border-color: #fd7e14;" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.academic-years.edit', $year) }}" class="btn btn-outline-secondary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" title="Supprimer"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteYearModal{{ $year->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Modal de confirmation de suppression -->
                                <div class="modal fade" id="deleteYearModal{{ $year->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <p>Êtes-vous sûr de vouloir supprimer l'année scolaire <strong>{{ $year->name }}</strong> ?</p>
                                                <p class="text-danger"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" class="d-inline">
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
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center">Aucune année scolaire enregistrée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection