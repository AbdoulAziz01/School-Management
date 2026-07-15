@extends('admin.layouts.app')

@section('title', 'Modifier l\'enseignant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 card-title">Modifier l'enseignant: {{ $teacher->name }}</h4>
                    <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <x-admin.form-field name="name" label="Nom complet" :value="$teacher->name" required />

                                <div class="mb-3">
                                    <label class="form-label">Identifiant de connexion</label>
                                    <input type="text" class="form-control bg-light" value="{{ $teacher->identifier }}" readonly disabled>
                                    <div class="form-text">Généré automatiquement à la création (connexion avec cet identifiant ou l'email).</div>
                                </div>

                                <x-admin.form-field type="email" name="email" label="Email" :value="$teacher->email" required />

                                <x-admin.form-field type="date" name="date_of_birth" label="Date de naissance"
                                    :value="$teacher->date_of_birth ? $teacher->date_of_birth->format('Y-m-d') : ''" />
                            </div>

                            <div class="col-md-6">
                                <x-admin.form-field name="phone" label="Téléphone" :value="$teacher->phone" />

                                <x-admin.form-field type="select" name="status" label="Statut" required>
                                    <option value="pending" {{ old('status', $teacher->status) === 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="approved" {{ old('status', $teacher->status) === 'approved' ? 'selected' : '' }}>Approuvé</option>
                                    <option value="rejected" {{ old('status', $teacher->status) === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                </x-admin.form-field>

                                <x-admin.form-field type="textarea" name="address" label="Adresse" :value="$teacher->address" :rows="3" />

                                <div class="mb-3">
                                    <label class="form-label">Connexion</label>
                                    @include('admin.teachers._credentials-panel')
                                </div>
                            </div>
                        </div>

                        @include('admin.teachers._teaching-fields')

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?')) { document.getElementById('delete-form').submit(); }" class="btn btn-danger">
                                <i class="fas fa-trash me-1"></i> Supprimer
                            </button>
                            
                            <div>
                                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('admin.teachers.destroy', $teacher) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete() {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?')) {
            document.getElementById('delete-form').submit();
        }
    }
</script>
@endpush
