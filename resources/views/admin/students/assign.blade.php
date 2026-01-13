@extends('admin.layouts.app')

@push('styles')
<style>
    /* Styles pour le menu déroulant des classes */
    .class-select-wrapper {
        position: relative;
        min-width: 250px;
        max-width: 100%;
    }
    
    .class-select-wrapper select {
        width: 100%;
        max-width: 100%;
    }
    
    /* Style pour le conteneur des options du select */
    select.form-select option {
        white-space: normal;
        padding: 8px 12px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Style pour le menu déroulant */
    select.form-select {
        max-height: 200px;
        overflow-y: auto;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
        padding-right: 2.5rem;
    }
    
    /* Style pour le conteneur du select avec défilement personnalisé */
    .select-container {
        position: relative;
        width: 100%;
    }
    
    /* Style pour le menu déroulant ouvert */
    select.form-select:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    /* Style pour les options au survol */
    select.form-select option:hover {
        background-color: #f8f9fa;
        color: #000;
    }
    
    /* Style pour l'option sélectionnée */
    select.form-select option:checked {
        background-color: #0d6efd;
        color: #fff;
        font-weight: 500;
    }
    
    /* Style pour la flèche du menu déroulant */
    .class-select-wrapper::after {
        content: '';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        border-left: 5px solid transparent;
        border-right: 5px solid transparent;
        border-top: 5px solid #6c757d;
    }
    
    /* Ajustement pour les petits écrans */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .class-select-wrapper {
            min-width: 100%;
            max-width: 100%;
        }
        
        select.form-select {
            width: 100%;
            max-width: 100%;
        }
    }
</style>
@endpush

@section('content')
    <div class="flex-wrap pt-3 pb-2 mb-4 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
        <h1 class="mb-0 h3">Affectation des Élèves aux Classes</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-4 card">
        <div class="card-header">
            <h5 class="mb-0">Élèves non affectés</h5>
        </div>
        <div class="card-body">
            @if($unassignedStudents->isEmpty())
                <div class="py-4 text-center text-muted">
                    <i class="mb-3 fas fa-user-check fa-3x"></i>
                    <p class="h5">Tous les élèves sont affectés à une classe</p>
                </div>
            @else
                <div class="table-responsive">
                    <form action="{{ route('admin.students.assign.store') }}" method="POST">
                        @csrf
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Identifiant</th>
                                    <th>Nom complet</th>
                                    <th>Email</th>
                                    <th>Classe</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unassignedStudents as $student)
                                <tr>
                                    <td><strong>{{ $student->identifier }}</strong></td>
                                    <td>{{ $student->name }}</td>
                                    <td>{{ $student->email }}</td>
                                    <td>
                                        <div class="class-select-wrapper">
                                            <div class="select-container">
                                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                <select name="class_id" class="form-select form-select-sm" style="max-width: 100%;" required>
                                                    <option value="">Sélectionner une classe</option>
                                                    @forelse($classes as $class)
                                                        @php
                                                            $levelName = $class->level ? $class->level->name : 'N/A';
                                                            $classTitle = $class->name . ' - ' . $levelName;
                                                        @endphp
                                                        <option value="{{ $class->id }}" 
                                                                data-level="{{ $levelName }}" 
                                                                title="{{ $classTitle }}">
                                                            {{ $class->name }} - {{ $levelName }}
                                                        </option>
                                                    @empty
                                                        <option value="" disabled>Aucune classe disponible</option>
                                                    @endforelse
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-user-plus"></i> Affecter
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Élèves affectés</h5>
        </div>
        <div class="card-body">
            @if($assignedStudents->isEmpty())
                <div class="py-4 text-center text-muted">
                    <i class="mb-3 fas fa-user-graduate fa-3x"></i>
                    <p class="h5">Aucun élève affecté pour le moment</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Identifiant</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Classe</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedStudents as $student)
                            <tr>
                                <td><strong>{{ $student->identifier }}</strong></td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $student->class->name ?? 'Non affecté' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('admin.students.unassign', $student) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir retirer cet élève de sa classe ?')">
                                            <i class="fas fa-user-minus"></i> Retirer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $assignedStudents->links() }}
                </div>
            @endif
        </div>
    </div>
@push('scripts')
<script>
    // Script pour améliorer l'expérience utilisateur du menu déroulant
    document.addEventListener('DOMContentLoaded', function() {
        // Trier les options par niveau et par nom de classe
        const selects = document.querySelectorAll('select[name="class_id"]');
        
        selects.forEach(select => {
            // Stocker les options dans un tableau
            const options = Array.from(select.options);
            
            // Supprimer l'option vide et l'option de sélection
            const firstOption = options.shift();
            const selectOption = options.shift();
            
            // Trier les options par niveau puis par nom de classe
            options.sort((a, b) => {
                const levelA = a.getAttribute('data-level') || '';
                const levelB = b.getAttribute('data-level') || '';
                
                if (levelA !== levelB) {
                    return levelA.localeCompare(levelB);
                }
                return a.text.localeCompare(b.text);
            });
            
            // Vider le select
            select.innerHTML = '';
            
            // Ajouter l'option de sélection
            if (selectOption) {
                select.add(selectOption);
            } else if (firstOption) {
                select.add(firstOption);
            }
            
            // Ajouter les options triées
            options.forEach(option => {
                select.add(option);
            });
        });
    });
</script>
@endpush

@endsection
