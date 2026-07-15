@extends('admin.layouts.app')

@section('title', 'Modifier l\'élève')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 card-title">Modifier l'élève</h4>
                    <a href="{{ route('admin.students.show', $student) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Annuler
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.students.update', $student) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Identité --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">Identité</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <x-admin.form-field name="first_name" label="Prénom" :value="$student->first_name ?? ''" required />
                            </div>
                            <div class="col-md-6">
                                <x-admin.form-field name="last_name" label="Nom" :value="$student->last_name ?? ''" required />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <x-admin.form-field type="email" name="email" label="Email (optionnel)" :value="$student->email" />
                            </div>
                            <div class="col-md-6">
                                <x-admin.form-field type="tel" name="phone" label="Téléphone" :value="$student->phone" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <x-admin.form-field type="date" name="date_of_birth" label="Date de naissance"
                                    :value="$student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : ''" />
                            </div>
                            <div class="col-md-6">
                                <x-admin.form-field type="select" name="class_id" label="Classe">
                                    <option value="">-- Sélectionnez une classe --</option>
                                    @foreach($classes as $academicYear => $classGroup)
                                        <optgroup label="{{ $academicYear }}">
                                            @foreach($classGroup as $class)
                                                <option value="{{ $class->id }}"
                                                    {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                    @if($class->level)
                                                        - {{ $class->level->name }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </x-admin.form-field>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Connexion</label>
                                    @include('admin.students._credentials-panel')
                                </div>
                            </div>
                            <div class="col-md-6">
                                <x-admin.form-field type="select" name="status" label="Statut" required>
                                    <option value="pending" {{ old('status', $student->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="approved" {{ old('status', $student->status) === 'approved' ? 'selected' : '' }}>Approuvé</option>
                                    <option value="rejected" {{ old('status', $student->status) === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                </x-admin.form-field>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <x-admin.form-field type="textarea" name="address" label="Adresse" :value="$student->address" />
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Documents & Photo --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">Documents &amp; Photo</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <x-admin.form-field type="file" name="photo" accept="image/*"
                                    help="{{ 'JPG, PNG, WEBP — max 4 Mo'.($student->profile_photo_path ? ' (remplace la photo actuelle)' : '') }}">
                                    <x-slot:label>Photo de l'élève</x-slot:label>
                                    @if($student->profile_photo_path)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($student->profile_photo_path) }}"
                                                 alt="Photo actuelle" class="rounded"
                                                 style="width:72px;height:72px;object-fit:cover;border:1px solid #dee2e6;">
                                            <span class="ms-2 text-muted small">Photo actuelle</span>
                                        </div>
                                    @endif
                                </x-admin.form-field>
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-field type="file" name="birth_certificate" accept=".pdf"
                                    help="{{ 'Fichier PDF uniquement — max 8 Mo'.($student->birth_certificate_path ? ' (remplace le document actuel)' : '') }}">
                                    <x-slot:label>Extrait de naissance <span class="badge bg-secondary fw-normal" style="font-size:.7rem;">PDF</span></x-slot:label>
                                    @if($student->birth_certificate_path)
                                        <div class="mb-2">
                                            <a href="{{ Storage::url($student->birth_certificate_path) }}" target="_blank"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-pdf me-1 text-danger"></i> Voir le document actuel
                                            </a>
                                        </div>
                                    @endif
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
                                <x-admin.form-field name="parent_name" label="Nom du parent/tuteur"
                                    :value="$student->parent_name" placeholder="Ex : Ibrahima Fall" />
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-field name="parent_whatsapp" label="Numéro WhatsApp parent"
                                    :value="$student->parent_whatsapp" placeholder="Ex : 221781234567"
                                    help="Format international sans + ni espaces (ex : 221781234567)" />
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-field type="select" name="parent_lang" label="Langue de notification">
                                    <option value="fr_text" {{ old('parent_lang', $student->parent_lang ?? 'fr_text') === 'fr_text' ? 'selected' : '' }}>Français (texte)</option>
                                    <option value="wo_audio" {{ old('parent_lang', $student->parent_lang) === 'wo_audio' ? 'selected' : '' }}>Wolof (audio)</option>
                                    <option value="pu_audio" {{ old('parent_lang', $student->parent_lang) === 'pu_audio' ? 'selected' : '' }}>Pulaar (audio)</option>
                                </x-admin.form-field>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
