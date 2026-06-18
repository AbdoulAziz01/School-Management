@extends('layouts.student')

@section('title', 'Mon Profil')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Mon Profil</h1>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Carte de profil -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Photo de profil</h6>
                </div>
                <div class="card-body text-center">
                    <form method="POST" action="{{ route('student.profile.update-photo') }}" enctype="multipart/form-data" id="photo-form">
                        @csrf
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/jpg"
                               style="display:none" onchange="document.getElementById('photo-form').submit()">

                        <div class="mb-3 position-relative d-inline-block">
                            @if($user->profile_photo_path)
                                <img src="{{ Storage::url($user->profile_photo_path) }}"
                                     alt="Photo de profil"
                                     class="rounded-circle border border-3"
                                     style="width:150px;height:150px;object-fit:cover;border-color:#f59e0b!important;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto"
                                     style="width:150px;height:150px;font-size:60px;color:white;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            {{-- Badge caméra cliquable --}}
                            <button type="button"
                                    onclick="document.getElementById('photo-input').click()"
                                    class="position-absolute bottom-0 end-0 btn btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:36px;height:36px;background:#f59e0b;border:2px solid #fff;"
                                    title="Changer la photo">
                                <i class="fas fa-camera" style="font-size:.75rem;color:#1c1917;"></i>
                            </button>
                        </div>

                        <h5 class="font-weight-bold mt-1">{{ $user->name }}</h5>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        <button type="button"
                                onclick="document.getElementById('photo-input').click()"
                                class="btn btn-sm"
                                style="background:#f59e0b;color:#1c1917;font-weight:700;">
                            <i class="fas fa-upload me-1"></i> Changer la photo
                        </button>
                    </form>
                </div>
            </div>

            <!-- Informations de classe -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ma Classe</h6>
                </div>
                <div class="card-body">
                    @if($user->schoolClass)
                        <h5 class="card-title">{{ $user->schoolClass->name }}</h5>
                        <p class="card-text">
                            <i class="fas fa-graduation-cap me-2"></i> {{ $user->schoolClass->level->name ?? 'N/A' }}
                        </p>
                        <p class="card-text">
                            <i class="fas fa-user-tie me-2"></i> 
                            @php $mainTeacher = $user->schoolClass->teachers->first(); @endphp
                            {{ $mainTeacher?->name ?? 'Professeur non assigné' }}
                        </p>
                    @else
                        <p class="text-muted">Aucune classe assignée</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Formulaire d'édition du profil -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations personnelles</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('student.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nom complet</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Adresse email <span class="text-muted">(optionnel)</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date de naissance</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                   id="address" name="address" value="{{ old('address', $user->address) }}">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city', $user->city) }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="country" class="form-label">Pays</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country', $user->country) }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Changement de mot de passe -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Changer le mot de passe</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('student.profile.update-password') }}" method="POST">
                        @csrf"
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
