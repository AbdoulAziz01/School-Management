@extends('admin.layouts.app')

@section('title', 'Affectations de ' . $teacher->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.teachers.index') }}">Enseignants</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.teachers.show', $teacher) }}">{{ $teacher->name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Affectations</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Affectations de {{ $teacher->name }}</h1>
                <div>
                    <a href="{{ route('admin.teachers.assignments.create', $teacher) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Nouvelle affectation
                    </a>
                    <a href="{{ route('admin.teachers.show', $teacher) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Retour au profil
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($assignments->isEmpty())
                <div class="alert alert-info">
                    Aucune affectation trouvée pour cet enseignant.
                </div>
            @else
                @foreach($assignments as $yearId => $yearAssignments)
                    @php $academicYear = $yearAssignments->first()->academicYear; @endphp
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                {{ $academicYear->name }}
                                <span class="badge bg-primary">{{ $yearAssignments->count() }} affectation(s)</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Classe</th>
                                            <th>Matière</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($yearAssignments as $assignment)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('classes.show', $assignment->schoolClass) }}">
                                                        {{ $assignment->schoolClass->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $assignment->subject->name }}</td>
                                                <td>
                                                    <form action="{{ route('admin.teachers.assignments.destroy', $assignment) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette affectation ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
