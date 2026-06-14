@extends('admin.layouts.app')

@section('title', 'Ajouter un nouvel élève')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h2">Ajouter un nouvel élève</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Identité --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">Identité</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Prénom *</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                   id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nom *</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                   id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-muted fw-normal">(optionnel)</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" placeholder="Connexion possible avec l'identifiant">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date de naissance *</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Classe <span class="text-muted fw-normal">(optionnel)</span></label>
                            <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                                <option value="">Sélectionner une classe</option>
                                @foreach($classes as $year => $yearClasses)
                                    <optgroup label="{{ $year }}">
                                        @foreach($yearClasses as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} - {{ $class->level->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Statut *</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="{{ App\Models\User::STATUS_PENDING }}" {{ old('status', 'pending') == App\Models\User::STATUS_PENDING ? 'selected' : '' }}>
                                    En attente
                                </option>
                                <option value="{{ App\Models\User::STATUS_APPROVED }}" {{ old('status') == App\Models\User::STATUS_APPROVED ? 'selected' : '' }}>
                                    Approuvé
                                </option>
                                <option value="{{ App\Models\User::STATUS_REJECTED }}" {{ old('status') == App\Models\User::STATUS_REJECTED ? 'selected' : '' }}>
                                    Rejeté
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Documents & Photo --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">Documents &amp; Photo <span class="text-muted fw-normal">(optionnels)</span></h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo de l'élève</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror"
                                   id="photo" name="photo" accept="image/*">
                            <div class="form-text">JPG, PNG, WEBP — max 4 Mo</div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="birth_certificate" class="form-label">Extrait de naissance <span class="badge bg-secondary fw-normal" style="font-size:.7rem;">PDF</span></label>
                            <input type="file" class="form-control @error('birth_certificate') is-invalid @enderror"
                                   id="birth_certificate" name="birth_certificate" accept=".pdf">
                            <div class="form-text">Fichier PDF uniquement — max 8 Mo</div>
                            @error('birth_certificate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
