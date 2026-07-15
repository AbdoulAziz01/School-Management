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
                        <x-admin.form-field name="first_name" label="Prénom" required />
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-field name="last_name" label="Nom" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-admin.form-field type="email" name="email" label="Email (optionnel)"
                            placeholder="Connexion possible avec l'identifiant" />
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-field type="date" name="date_of_birth" label="Date de naissance" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-admin.form-field type="select" name="class_id" label="Classe (optionnel)">
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
                        </x-admin.form-field>
                    </div>
                    <div class="col-md-6">
                        <x-admin.form-field type="select" name="status" label="Statut" required>
                            <option value="{{ App\Models\User::STATUS_PENDING }}" {{ old('status', 'approved') == App\Models\User::STATUS_PENDING ? 'selected' : '' }}>
                                En attente
                            </option>
                            <option value="{{ App\Models\User::STATUS_APPROVED }}" {{ old('status', 'approved') == App\Models\User::STATUS_APPROVED ? 'selected' : '' }}>
                                Approuvé
                            </option>
                            <option value="{{ App\Models\User::STATUS_REJECTED }}" {{ old('status', 'approved') == App\Models\User::STATUS_REJECTED ? 'selected' : '' }}>
                                Rejeté
                            </option>
                        </x-admin.form-field>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Documents & Photo --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">Documents &amp; Photo <span class="text-muted fw-normal">(optionnels)</span></h6>
                <div class="row">
                    <div class="col-md-4">
                        <x-admin.form-field type="file" name="photo" label="Photo de l'élève"
                            accept="image/*" help="JPG, PNG, WEBP — max 4 Mo" />
                    </div>
                    <div class="col-md-4">
                        <x-admin.form-field type="file" name="birth_certificate" accept=".pdf"
                            help="Fichier PDF uniquement — max 8 Mo">
                            <x-slot:label>Extrait de naissance <span class="badge bg-secondary fw-normal" style="font-size:.7rem;">PDF</span></x-slot:label>
                        </x-admin.form-field>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Contact Parent / Tuteur --}}
                <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">
                    <i class="fab fa-whatsapp me-1 text-success"></i> Contact Parent / Tuteur
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <x-admin.form-field name="parent_name" label="Nom du parent/tuteur" placeholder="Ex : Ibrahima Fall" />
                    </div>
                    <div class="col-md-4">
                        <x-admin.form-field name="parent_whatsapp" label="Numéro WhatsApp parent"
                            placeholder="Ex : 221781234567"
                            help="Format international sans + ni espaces (ex : 221781234567)" />
                    </div>
                    <div class="col-md-4">
                        <x-admin.form-field type="select" name="parent_lang" label="Langue de notification">
                            <option value="fr_text" {{ old('parent_lang', 'fr_text') === 'fr_text' ? 'selected' : '' }}>Français (texte)</option>
                            <option value="wo_audio" {{ old('parent_lang') === 'wo_audio' ? 'selected' : '' }}>Wolof (audio)</option>
                            <option value="pu_audio" {{ old('parent_lang') === 'pu_audio' ? 'selected' : '' }}>Pulaar (audio)</option>
                        </x-admin.form-field>
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
