@extends('admin.layouts.app')

@section('title', 'Gestion des classes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Gestion des classes</h1>
                <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nouvelle classe
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des classes</h5>
                    <div class="text-muted small">
                        {{ $classes->total() }} classe(s)
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($classes->isEmpty())
                        <div class="alert alert-info m-3">
                            Aucune classe n'a été créée pour le moment.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Niveau</th>
                                        <th>Année scolaire</th>
                                        <th class="text-nowrap">Effectif</th>
                                        <th>Professeurs affectés</th>
                                        <th class="text-nowrap text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $class)
                                        <tr>
                                            <td class="text-truncate" style="max-width: 150px;" title="{{ $class->name }}">
                                                {{ $class->name }}
                                            </td>
                                            <td class="text-nowrap">
                                                <span class="badge bg-secondary">{{ $class->level->name ?? 'Non défini' }}</span>
                                            </td>
                                            <td class="text-nowrap">
                                                <span class="text-muted">{{ $class->academicYear->name }}</span>
                                            </td>
                                            <td class="text-nowrap">
                                                <span class="badge {{ $class->students_count > 0 ? 'bg-primary' : 'bg-light text-dark' }}">
                                                    {{ $class->students_count }} élève(s)
                                                </span>
                                            </td>
                                            <td>
                                                @if($class->teachers->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($class->teachers->take(3) as $teacher)
                                                            <span class="badge bg-info" title="{{ $teacher->name }}{{ $teacher->subjects->isNotEmpty() ? ' - ' . $teacher->subjects->pluck('name')->join(', ') : '' }}">
                                                                {{ Str::limit($teacher->name, 12) }}
                                                            </span>
                                                        @endforeach
                                                        @if($class->teachers->count() > 3)
                                                            <span class="badge bg-secondary">+{{ $class->teachers->count() - 3 }}</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $class->teachers->count() }} professeur(s)</small>
                                                @else
                                                    <span class="text-muted small">Aucun</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.classes.show', $class) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Voir les détails"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.classes.edit', $class) }}" 
                                                       class="btn btn-sm btn-outline-secondary" 
                                                       title="Modifier"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="{{ $class->students_count > 0 ? 'Impossible de supprimer - classe non vide' : 'Supprimer' }}"
                                                            {{ $class->students_count > 0 ? 'disabled' : '' }}
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $class->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Modal de confirmation de suppression -->
                                                <div class="modal fade" id="deleteModal{{ $class->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <p>Êtes-vous sûr de vouloir supprimer la classe <strong>{{ $class->name }}</strong> ?</p>
                                                                <p class="text-danger"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="d-inline">
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
                        </div>
                        
                        <div class="mt-3">
                            {{ $classes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -1rem;
        padding: 0 1rem;
    }
    
    .table {
        min-width: 100%;
        width: max-content;
        margin-bottom: 0;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
    }
    
    .table th {
        white-space: nowrap;
        font-weight: 600;
    }
    
    .btn-group-sm > .btn, .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .card-header h5 {
            margin-bottom: 0.5rem;
        }
        
        .table-responsive {
            margin: 0 -0.5rem;
            padding: 0 0.5rem;
        }
        
        .table > :not(caption) > * > * {
            padding: 0.5rem 0.25rem;
        }
    }
    
    @media (max-width: 576px) {
        .btn-group .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
        
        .badge {
            font-size: 0.7em;
            padding: 0.25em 0.5em;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialisation des tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection
