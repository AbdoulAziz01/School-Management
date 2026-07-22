@extends('admin.layouts.app')

@section('title', !empty($isFormationSchool) && $isFormationSchool ? 'Gestion des modules' : 'Gestion des matières')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="mb-0">{{ !empty($isFormationSchool) && $isFormationSchool ? 'Liste des modules' : 'Liste des matières' }}</h5>
                        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            {{ !empty($isFormationSchool) && $isFormationSchool ? 'Ajouter un module' : 'Ajouter une matière' }}
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <form action="{{ route('admin.subjects.index') }}" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" aria-label="Rechercher une matière" class="form-control form-control-sm" placeholder="Rechercher..." value="{{ request('search') }}" style="min-width: 200px;">
                            <select name="status" aria-label="Filtrer par statut" class="form-select form-select-sm" style="min-width: 120px;" onchange="this.form.submit()">
                                <option value="">Statut : tous</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actives</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactives</option>
                            </select>
                            <select name="cycle" aria-label="Filtrer par niveau" class="form-select form-select-sm" style="min-width: 140px;" onchange="this.form.submit()">
                                <option value="">Niveau : tous</option>
                                <option value="primaire" {{ request('cycle') == 'primaire' ? 'selected' : '' }}>Primaire</option>
                                <option value="college" {{ request('cycle') == 'college' ? 'selected' : '' }}>Collège</option>
                                <option value="lycee" {{ request('cycle') == 'lycee' ? 'selected' : '' }}>Lycée</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search') || request('status') || request('cycle'))
                                <a href="{{ route('admin.subjects.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </form>
                        <span class="text-muted small">{{ $subjects->total() }} {{ !empty($isFormationSchool) && $isFormationSchool ? 'module(s)' : 'matière(s)' }}</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(!empty($isFormationSchool) && $isFormationSchool)
                        <p class="text-muted small mb-3">
                            Chaque module a sa propre pondération CC / Examen (fiche module).
                            Le menu « Modèle LMD par défaut » sert uniquement aux nouveaux modules.
                        </p>
                    @else
                        <!-- <p class="text-muted small mb-3">
                            Le catalogue standard (Français, Maths, SVT, etc.) est installé automatiquement pour votre établissement.
                            Ajustez le coefficient et les niveaux sur chaque matière ; utilisez « Ajouter » seulement pour une matière optionnelle.
                        </p> -->
                    @endif
                    
                    @if($subjects->isEmpty())
                        <div class="alert alert-info">
                            @if(request('search') || request('status') || request('cycle'))
                                Aucun{{ !empty($isFormationSchool) && $isFormationSchool ? ' module' : 'e matière' }} ne correspond à votre recherche.
                            @else
                                Aucun{{ !empty($isFormationSchool) && $isFormationSchool ? ' module' : 'e matière' }} n'a été créé{{ !empty($isFormationSchool) && $isFormationSchool ? '' : 'e' }} pour le moment.
                            @endif
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Nom</th>
                                        @if(!empty($usesFormationLmd) && $usesFormationLmd)
                                            <th>CC / Examen</th>
                                        @endif
                                        <th>Professeurs</th>
                                        <th style="min-width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjects as $subject)
                                        <tr>
                                            <td><code>{{ $subject->code }}</code></td>
                                            <td>{{ $subject->name }}</td>
                                            @if(!empty($usesFormationLmd) && $usesFormationLmd)
                                                <td>
                                                    @php $lmd = \App\Support\FormationLmdSettings::fromSubject($subject); @endphp
                                                    <span class="badge bg-light text-dark border">{{ $lmd->shortLabel() }}</span>
                                                </td>
                                            @endif
                                            <td>
                                                <span class="badge" style="background-color: #fd7e14;">{{ $subject->teachers->count() }} professeur(s)</span>
                                                @if($subject->teachers->count() > 0)
                                                    <div class="dropdown d-inline-block">
                                                        <a href="#" class="small ms-1" role="button" id="teachersDropdown{{ $subject->id }}"
                                                           data-bs-toggle="dropdown" data-bs-strategy="fixed" data-bs-boundary="viewport" aria-expanded="false">Voir tout</a>
                                                        <ul class="dropdown-menu dropdown-menu-end p-2" aria-labelledby="teachersDropdown{{ $subject->id }}" style="min-width: 220px;">
                                                            <li class="dropdown-header px-2">Professeurs — {{ $subject->name }}</li>
                                                            @foreach($subject->teachers as $teacher)
                                                                <li class="px-2 py-1"><i class="fas fa-user-tie me-2 text-muted"></i>{{ $teacher->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-sm btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" title="Supprimer"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $subject->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <!-- Modal de confirmation -->
                                                <div class="modal fade" id="deleteModal{{ $subject->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Êtes-vous sûr de vouloir supprimer {{ !empty($isFormationSchool) && $isFormationSchool ? 'le module' : 'la matière' }} <strong>{{ $subject->name }}</strong> ?</p>
                                                                <p class="text-danger"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" class="d-inline">
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
                                {{ $subjects->withQueryString()->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
