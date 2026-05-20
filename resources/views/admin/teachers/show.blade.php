@extends('admin.layouts.app')

@section('title', 'Détails de l\'enseignant')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">
            <i class="fas fa-chalkboard-teacher me-2"></i>
            Détails de l'enseignant
        </h2>
        <div>
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations personnelles -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations personnelles</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($teacher->profile_photo_path)
                            <img src="{{ asset('storage/' . $teacher->profile_photo_path) }}" 
                                 alt="Photo de profil" 
                                 class="rounded-circle" 
                                 style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 120px; height: 120px; font-size: 3rem; background-color: #fd7e14;">
                                {{ strtoupper(substr($teacher->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="mb-1">{{ $teacher->name }}</h4>
                    <p class="text-muted mb-3">
                        <code>{{ $teacher->identifier ?? 'N/A' }}</code>
                    </p>
                    
                    <div class="mb-3">
                        @if($teacher->status == 'approved')
                            <span class="badge bg-success">Approuvé</span>
                        @elseif($teacher->status == 'pending')
                            <span class="badge bg-warning text-dark">En attente</span>
                        @else
                            <span class="badge bg-danger">Rejeté</span>
                        @endif
                    </div>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="fas fa-envelope me-2"></i>Email</span>
                        <span>{{ $teacher->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="fas fa-phone me-2"></i>Téléphone</span>
                        <span>{{ $teacher->phone ?? 'Non renseigné' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Adresse</span>
                        <span>{{ $teacher->address ?? 'Non renseignée' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="fas fa-birthday-cake me-2"></i>Date de naissance</span>
                        <span>{{ $teacher->date_of_birth ? $teacher->date_of_birth->format('d/m/Y') : 'Non renseignée' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted"><i class="fas fa-calendar me-2"></i>Inscrit le</span>
                        <span>{{ $teacher->created_at->format('d/m/Y') }}</span>
                    </li>
                    <li class="list-group-item">
                        <span class="text-muted d-block mb-2"><i class="fas fa-key me-2"></i>Connexion</span>
                        @if($teacher->invitation_email_sent_at)
                            <p class="small text-muted mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                Dernière tentative le {{ $teacher->invitation_email_sent_at->format('d/m/Y à H:i') }} (vérifiez les spams).
                            </p>
                        @else
                            <p class="small text-muted mb-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Aucun email d'invitation enregistré pour l'instant.
                            </p>
                        @endif
                        @if($teacher->email)
                            <form action="{{ route('admin.teachers.send-invitation', $teacher) }}" method="POST"
                                  onsubmit="return confirm('Envoyer identifiant et nouveau mot de passe temporaire à {{ $teacher->email }} ?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-envelope me-1"></i>
                                    {{ $teacher->invitation_email_sent_at ? 'Renvoyer les identifiants' : 'Envoyer les identifiants' }}
                                </button>
                            </form>
                        @endif
                    </li>
                </ul>
                <div class="card-footer">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-warning btn-sm flex-fill">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                        <a href="{{ route('admin.teachers.classes.edit', $teacher) }}" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-chalkboard me-1"></i> Affectations
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Classes affectées -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chalkboard me-2"></i>Classes affectées
                    </h5>
                    <span class="badge" style="background-color: #fd7e14;">{{ $teacher->assignedClasses->count() }} classe(s)</span>
                </div>
                <div class="card-body">
                    @if($teacher->assignedClasses->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-school fa-3x mb-3"></i>
                            <p class="mb-0">Aucune classe affectée pour le moment.</p>
                            <a href="{{ route('admin.teachers.classes.edit', $teacher) }}" class="btn btn-primary btn-sm mt-3">
                                <i class="fas fa-plus me-1"></i> Affecter des classes
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Classe</th>
                                        <th>Niveau</th>
                                        <th>Année scolaire</th>
                                        <th>Effectif</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teacher->assignedClasses as $class)
                                        <tr>
                                            <td><strong>{{ $class->name }}</strong></td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $class->level->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ $class->academicYear->name ?? 'N/A' }}</td>
                                            <td>{{ $class->students_count ?? $class->students->count() }} élève(s)</td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Matières enseignées -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2"></i>Matières enseignées
                    </h5>
                    <span class="badge bg-info">{{ $teacher->subjects->count() }} matière(s)</span>
                </div>
                <div class="card-body">
                    @if($teacher->subjects->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-book-open fa-3x mb-3"></i>
                            <p class="mb-0">Aucune matière assignée pour le moment.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($teacher->subjects as $subject)
                                <div class="col-md-6 col-lg-4 mb-2">
                                    <div class="border rounded p-2 d-flex align-items-center">
                                        <i class="fas fa-book me-2" style="color: #fd7e14;"></i>
                                        <span>{{ $subject->name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Êtes-vous sûr de vouloir supprimer l'enseignant <strong>{{ $teacher->name }}</strong> ?</p>
                <p class="text-danger mt-2"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
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
@endsection
