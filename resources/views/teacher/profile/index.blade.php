@extends('teacher.layouts.app')

@section('title', 'Mon Profil - Enseignant')

@section('content')
<div class="mb-4">
    <h1 class="mb-0 h3">Mon Profil</h1>
    <p class="text-muted">Consultez et gérez vos informations personnelles</p>
</div>

<div class="row g-4">
    {{-- Informations de profil --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 120px; height: 120px; font-size: 3rem;">
                    @if($teacher->profile_photo_path)
                        <img src="{{ asset('storage/' . $teacher->profile_photo_path) }}" alt="Photo de profil" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        {{ strtoupper(substr($teacher->name, 0, 2)) }}
                    @endif
                </div>
                
                <h4 class="mb-1">{{ $teacher->name }}</h4>
                <p class="text-muted mb-3">Enseignant</p>
                
                <span class="badge bg-primary px-3 py-2">
                    <i class="fas fa-check-circle me-1"></i>Compte actif
                </span>
            </div>
            
            <div class="card-footer bg-white">
                <a href="{{ route('teacher.profile.edit') }}" class="btn btn-primary w-100">
                    <i class="fas fa-edit me-2"></i>Modifier le profil
                </a>
            </div>
        </div>
    </div>
    
    {{-- Détails du profil --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations personnelles</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Nom complet</label>
                        <p class="mb-0 fw-bold">{{ $teacher->name }}</p>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="text-muted small">Adresse email</label>
                        <p class="mb-0 fw-bold">{{ $teacher->email }}</p>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="text-muted small">Identifiant</label>
                        <p class="mb-0"><code>{{ $teacher->identifier ?? 'Non défini' }}</code></p>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="text-muted small">Téléphone</label>
                        <p class="mb-0 fw-bold">{{ $teacher->phone ?? 'Non renseigné' }}</p>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="text-muted small">Date de naissance</label>
                        <p class="mb-0 fw-bold">
                            {{ $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('d/m/Y') : 'Non renseignée' }}
                        </p>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="text-muted small">Rôle</label>
                        <p class="mb-0">
                            <span class="badge bg-primary">Enseignant</span>
                        </p>
                    </div>
                    
                    <div class="col-12">
                        <label class="text-muted small">Adresse</label>
                        <p class="mb-0 fw-bold">{{ $teacher->address ?? 'Non renseignée' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Sécurité --}}
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Sécurité</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">Mot de passe</h6>
                        <p class="text-muted mb-0 small">Dernière modification: {{ $teacher->updated_at ? $teacher->updated_at->format('d/m/Y') : 'Inconnue' }}</p>
                    </div>
                    <a href="{{ route('teacher.profile.edit') }}#password" class="btn btn-outline-warning">
                        <i class="fas fa-key me-2"></i>Changer le mot de passe
                    </a>
                </div>
            </div>
        </div>
        
        {{-- Compte créé --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6">
                        <small class="text-muted">Compte créé le</small>
                        <p class="mb-0 fw-bold">{{ $teacher->created_at ? $teacher->created_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Dernière mise à jour</small>
                        <p class="mb-0 fw-bold">{{ $teacher->updated_at ? $teacher->updated_at->format('d/m/Y à H:i') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
