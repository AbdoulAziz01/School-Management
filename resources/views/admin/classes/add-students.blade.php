@extends('admin.layouts.app')

@section('title', 'Gérer les élèves - ' . $class->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('academic-years.index') }}">Années scolaires</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('academic-years.show', $class->academic_year_id) }}">
                            {{ $class->academicYear->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('classes.show', $class) }}">{{ $class->name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Gérer les élèves</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Gérer les élèves - {{ $class->name }}</h1>
                <a href="{{ route('classes.show', $class) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la classe
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sélectionner les élèves pour la classe</h5>
                </div>
                <form action="{{ route('classes.add-students', $class) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($availableStudents->isEmpty())
                            <div class="alert alert-info mb-0">
                                Tous les élèves sont déjà affectés à une classe pour cette année scolaire.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                                </div>
                                            </th>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Classe actuelle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($availableStudents as $student)
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input student-checkbox" 
                                                               type="checkbox" 
                                                               name="students[]" 
                                                               value="{{ $student->id }}"
                                                               {{ $student->class_id == $class->id ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>{{ $student->name }}</td>
                                                <td>{{ $student->email }}</td>
                                                <td>
                                                    @if($student->class && $student->class_id != $class->id)
                                                        <span class="badge bg-warning text-dark">
                                                            {{ $student->class->name }}
                                                        </span>
                                                    @elseif($student->class_id == $class->id)
                                                        <span class="badge bg-success">
                                                            Actuellement dans cette classe
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Non affecté</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('classes.show', $class) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Annuler
                            </a>
                            @if(!$availableStudents->isEmpty())
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sélectionner/désélectionner tous les élèves
        const selectAllCheckbox = document.getElementById('selectAll');
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                studentCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            });
            
            // Mettre à jour la case "Tout sélectionner" si toutes les cases sont cochées
            studentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(studentCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                });
            });
        }
    });
</script>
@endpush

@endsection
