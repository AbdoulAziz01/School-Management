@extends('admin.layouts.app')

@section('title', 'Gestion des enseignants')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Liste des enseignants</h5>
                    <div class="d-flex align-items-center gap-3">
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
                    @if(session('teacher_created'))
                        @php $created = session('teacher_created'); @endphp
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Enseignant créé :</strong> {{ $created['name'] }}
                            <ul class="mb-0 mt-2">
                                <li><strong>Email :</strong> {{ $created['email'] }}</li>
                                @if($created['reset_sent'])
                                    <li>Identifiant et mot de passe temporaire envoyés <strong>uniquement par email</strong> (vérifiez les spams). L'administrateur ne voit pas le mot de passe.</li>
                                @else
                                    <li class="text-warning">
                                        Identifiants non envoyés par email.
                                        @if(!empty($created['mail_error']))
                                            {{ $created['mail_error'] }}
                                        @else
                                            Configurez Brevo dans <code>.env</code>, puis renvoyez l'email depuis la fiche enseignant.
                                        @endif
                                    </li>
                                    <li><strong>Identifiant (secours) :</strong> <code>{{ $created['identifier'] }}</code> — à transmettre manuellement si l'email a échoué.</li>
                                @endif
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
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
                                        <th>Statut</th>
                                        <th>Date d'inscription</th>
                                        <th style="min-width: 200px;">Actions</th>
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
                                                @if($teacher->status == 'approved')
                                                    <span class="badge bg-success">Approuvé</span>
                                                @elseif($teacher->status == 'pending')
                                                    <span class="badge bg-warning">En attente</span>
                                                @else
                                                    <span class="badge bg-danger">Rejeté</span>
                                                @endif
                                            </td>
                                            <td>{{ $teacher->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-sm btn-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-sm btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.teachers.classes.edit', $teacher) }}" class="btn btn-sm btn-primary" title="Affectations">
                                                    <i class="fas fa-chalkboard"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" title="Supprimer"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteTeacherModal{{ $teacher->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
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
                <div class="card-footer">
                    <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Ajouter un enseignant
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection