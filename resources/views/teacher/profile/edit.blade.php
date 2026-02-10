@extends('teacher.layouts.app')

@section('title', 'Modifier le profil - Enseignant')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('teacher.dashboard') }}">Tableau de bord</a></li>
            <li class="breadcrumb-item"><a href="{{ route('teacher.profile.index') }}">Profil</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>
    
    <h1 class="mb-0 h3">Modifier le profil</h1>
    <p class="text-muted">Mettez à jour vos informations personnelles</p>
</div>

<div class="row g-4">
    <div class="col-md-8">
        {{-- Informations personnelles --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations personnelles</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('teacher.profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $teacher->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="email" class="form-label">Adresse email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $teacher->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                   value="{{ old('phone', $teacher->phone) }}" placeholder="+33 6 12 34 56 78">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date de naissance</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   value="{{ old('date_of_birth', $teacher->date_of_birth ? \Carbon\Carbon::parse($teacher->date_of_birth)->format('Y-m-d') : '') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" 
                                      rows="2" placeholder="Votre adresse complète...">{{ old('address', $teacher->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('teacher.profile.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        {{-- Changer le mot de passe --}}
        <div class="card mt-4" id="password">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Changer le mot de passe</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('teacher.profile.update-password') }}">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" id="current_password" 
                                   class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" 
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 caractères</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-2"></i>Changer le mot de passe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Photo de profil --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-camera me-2"></i>Photo de profil</h5>
            </div>
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 150px; height: 150px; font-size: 4rem;">
                    @if($teacher->profile_photo_path)
                        <img src="{{ asset('storage/' . $teacher->profile_photo_path) }}" alt="Photo de profil" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        {{ strtoupper(substr($teacher->name, 0, 2)) }}
                    @endif
                </div>
                
                <form method="POST" action="{{ route('teacher.profile.update-photo') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="photo" class="form-label">Changer la photo</label>
                        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" 
                               accept="image/jpeg,image/png,image/jpg,image/gif">
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">JPG, PNG ou GIF. Max 2 Mo.</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-upload me-2"></i>Télécharger
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
