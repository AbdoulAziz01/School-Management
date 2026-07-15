@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus"></i> Ajouter un enseignant
                    </h5>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.teachers.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <h5 class="mb-3">Informations personnelles</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-admin.form-field name="name" label="Nom complet" required autofocus />
                                </div>
                                <div class="col-md-6">
                                    <x-admin.form-field type="email" name="email" label="Email" required />
                                </div>
                                <div class="col-md-6">
                                    <x-admin.form-field type="date" name="date_of_birth" label="Date de naissance" />
                                </div>
                                <div class="col-md-6">
                                    <x-admin.form-field name="phone" label="Téléphone" />
                                </div>
                                <div class="col-12">
                                    <x-admin.form-field type="textarea" name="address" label="Adresse" />
                                </div>
                            </div>
                        </div>

                        @include('admin.teachers._teaching-fields', [
                            'requireTeaching' => true,
                            'selectedSubjectIds' => array_map('intval', old('subjects', [])),
                            'selectedClassIds' => array_map('intval', old('classes', [])),
                        ])

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer l'enseignant
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
