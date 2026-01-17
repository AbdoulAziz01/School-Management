@extends('admin.layouts.app')

@section('title', 'Détails de l\'étudiant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Détails de l'étudiant</h4>
                    <div>
                        <a href="{{ route('admin.students.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                        <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <div class="avatar avatar-xxl mx-auto">
                                    <span class="text-white avatar-text rounded-circle bg-primary" style="font-size: 2.5rem; width: 100px; height: 100px; line-height: 100px;">
                                        {{ substr($student->name, 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <h4>{{ $student->name }}</h4>
                            <p class="text-muted">
                                <span class="badge bg-{{ $student->status === 'approved' ? 'success' : ($student->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ $student->status === 'approved' ? 'Approuvé' : ($student->status === 'pending' ? 'En attente' : 'Rejeté') }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6>Informations personnelles</h6>
                                        <hr class="mt-1 mb-3">
                                        <p><strong>Email :</strong> {{ $student->email }}</p>
                                        <p><strong>Date de naissance :</strong> {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/Y') : 'Non spécifiée' }}</p>
                                        <p><strong>Téléphone :</strong> {{ $student->phone ?? 'Non spécifié' }}</p>
                                        <p><strong>Adresse :</strong> {{ $student->address ?? 'Non spécifiée' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <h6>Informations scolaires</h6>
                                        <hr class="mt-1 mb-3">
                                        <p><strong>Classe :</strong> {{ $student->class ? $student->class->name : 'Non affecté' }}</p>
                                        @if($student->class && $student->class->level)
                                            <p><strong>Niveau :</strong> {{ $student->class->level->name }}</p>
                                        @endif
                                        @if($student->class && $student->class->academicYear)
                                            <p><strong>Année scolaire :</strong> {{ $student->class->academicYear->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Créé le {{ $student->created_at->format('d/m/Y') }}
                                @if($student->created_at != $student->updated_at)
                                    <br>Dernière modification le {{ $student->updated_at->format('d/m/Y') }}
                                @endif
                            </small>
                        </div>
                        <div>
                            @if($student->status === 'pending')
                                <form action="{{ route('admin.students.approve', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir approuver cet étudiant ?')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check me-1"></i> Approuver
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
