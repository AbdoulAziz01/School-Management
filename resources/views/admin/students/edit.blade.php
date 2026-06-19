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
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name"
                                           value="{{ old('first_name', $student->first_name ?? '') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                           id="last_name" name="last_name"
                                           value="{{ old('last_name', $student->last_name ?? '') }}" required>
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
                                           id="email" name="email" value="{{ old('email', $student->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone', $student->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date de naissance</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                           id="date_of_birth" name="date_of_birth"
                                           value="{{ old('date_of_birth', $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '') }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Classe</label>
                                    <select class="form-select @error('class_id') is-invalid @enderror"
                                            id="class_id" name="class_id">
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
                                    </select>
                                    @error('class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
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
                                <div class="mb-3">
                                    <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                            id="status" name="status" required>
                                        <option value="pending" {{ old('status', $student->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="approved" {{ old('status', $student->status) === 'approved' ? 'selected' : '' }}>Approuvé</option>
                                        <option value="rejected" {{ old('status', $student->status) === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Adresse</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="2">{{ old('address', $student->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Documents & Photo --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">Documents &amp; Photo</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Photo de l'élève</label>
                                    @if($student->profile_photo_path)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($student->profile_photo_path) }}"
                                                 alt="Photo actuelle" class="rounded"
                                                 style="width:72px;height:72px;object-fit:cover;border:1px solid #dee2e6;">
                                            <span class="ms-2 text-muted small">Photo actuelle</span>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('photo') is-invalid @enderror"
                                           id="photo" name="photo" accept="image/*">
                                    <div class="form-text">JPG, PNG, WEBP — max 4 Mo{{ $student->profile_photo_path ? ' (remplace la photo actuelle)' : '' }}</div>
                                    @error('photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="birth_certificate" class="form-label">
                                        Extrait de naissance <span class="badge bg-secondary fw-normal" style="font-size:.7rem;">PDF</span>
                                    </label>
                                    @if($student->birth_certificate_path)
                                        <div class="mb-2">
                                            <a href="{{ Storage::url($student->birth_certificate_path) }}" target="_blank"
                                               class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-pdf me-1 text-danger"></i> Voir le document actuel
                                            </a>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control @error('birth_certificate') is-invalid @enderror"
                                           id="birth_certificate" name="birth_certificate" accept=".pdf">
                                    <div class="form-text">Fichier PDF uniquement — max 8 Mo{{ $student->birth_certificate_path ? ' (remplace le document actuel)' : '' }}</div>
                                    @error('birth_certificate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Contact Parent / Tuteur --}}
                        <h6 class="text-uppercase text-muted fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.08em;">
                            <i class="fab fa-whatsapp me-1 text-success"></i> Contact Parent / Tuteur
                        </h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="parent_name" class="form-label">Nom du parent/tuteur</label>
                                    <input type="text" class="form-control @error('parent_name') is-invalid @enderror"
                                           id="parent_name" name="parent_name"
                                           value="{{ old('parent_name', $student->parent_name) }}"
                                           placeholder="Ex : Ibrahima Fall">
                                    @error('parent_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="parent_whatsapp" class="form-label">Numéro WhatsApp parent</label>
                                    <input type="text" class="form-control @error('parent_whatsapp') is-invalid @enderror"
                                           id="parent_whatsapp" name="parent_whatsapp"
                                           value="{{ old('parent_whatsapp', $student->parent_whatsapp) }}"
                                           placeholder="Ex : 221781234567">
                                    <div class="form-text">Format international sans + ni espaces (ex : 221781234567)</div>
                                    @error('parent_whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="parent_lang" class="form-label">Langue de notification</label>
                                    <select class="form-select @error('parent_lang') is-invalid @enderror" id="parent_lang" name="parent_lang">
                                        <option value="fr_text" {{ old('parent_lang', $student->parent_lang ?? 'fr_text') === 'fr_text' ? 'selected' : '' }}>Français (texte)</option>
                                        <option value="wo_audio" {{ old('parent_lang', $student->parent_lang) === 'wo_audio' ? 'selected' : '' }}>Wolof (audio)</option>
                                        <option value="pu_audio" {{ old('parent_lang', $student->parent_lang) === 'pu_audio' ? 'selected' : '' }}>Pulaar (audio)</option>
                                    </select>
                                    @error('parent_lang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
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
