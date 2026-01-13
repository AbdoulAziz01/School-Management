@extends('admin.layouts.app')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* Style Select2 */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
        padding-left: 0;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.75rem);
    }
    
    /* Limiter la hauteur du dropdown */
    .select2-dropdown {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .select2-results__options {
        max-height: 250px;
        overflow-y: auto;
    }
    
    .select2-results__option {
        padding: 8px 12px;
    }
    
    .select2-results__option--highlighted {
        background-color: #0d6efd !important;
        color: white;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>
@endpush

@section('content')
    <div class="flex-wrap pt-3 pb-2 mb-4 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
        <h1 class="mb-0 h3">Affectation des Élèves aux Classes</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Élèves non affectés -->
    <div class="mb-4 shadow-sm card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="fas fa-user-clock"></i> Élèves non affectés
                <span class="badge bg-dark float-end">{{ $unassignedStudents->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($unassignedStudents->isEmpty())
                <div class="py-5 text-center text-muted">
                    <i class="mb-3 fas fa-user-check fa-4x text-success"></i>
                    <p class="h5">Tous les élèves sont affectés à une classe</p>
                    <p class="text-muted">Aucune affectation en attente</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="12%">Identifiant</th>
                                <th width="25%">Nom complet</th>
                                <th width="25%">Email</th>
                                <th width="25%">Classe</th>
                                <th width="13%" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unassignedStudents as $student)
                            <tr>
                                <td><strong class="text-primary">{{ $student->identifier }}</strong></td>
                                <td>{{ $student->name }}</td>
                                <td><small>{{ $student->email }}</small></td>
                                <td>
                                    <form action="{{ route('admin.students.assign.store') }}" method="POST" class="assign-form">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                        <select name="class_id" class="form-select form-select-sm class-select" required>
                                            <option value="">Sélectionner une classe</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">
                                                    {{ $class->name }} - {{ $class->level->name ?? 'N/A' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-primary btn-sm btn-assign" data-student-id="{{ $student->id }}">
                                        <i class="fas fa-user-plus"></i> Affecter
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Élèves affectés -->
    <div class="shadow-sm card">
        <div class="text-white card-header bg-success">
            <h5 class="mb-0">
                <i class="fas fa-user-graduate"></i> Élèves affectés
                <span class="badge bg-light text-dark float-end">{{ $assignedStudents->total() }}</span>
            </h5>
        </div>
        <div class="card-body">
            @if($assignedStudents->isEmpty())
                <div class="py-5 text-center text-muted">
                    <i class="mb-3 fas fa-user-graduate fa-4x"></i>
                    <p class="h5">Aucun élève affecté pour le moment</p>
                    <p class="text-muted">Les élèves affectés apparaîtront ici</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="12%">Identifiant</th>
                                <th width="25%">Nom complet</th>
                                <th width="25%">Email</th>
                                <th width="25%">Classe</th>
                                <th width="13%" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignedStudents as $student)
                            <tr>
                                <td><strong class="text-success">{{ $student->identifier }}</strong></td>
                                <td>{{ $student->name }}</td>
                                <td><small>{{ $student->email }}</small></td>
                                <td>
                                    <span class="badge bg-primary fs-6">
                                        {{ $student->class->name ?? 'Non affecté' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('admin.students.unassign', $student) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir retirer {{ $student->name }} de sa classe ?')">
                                            <i class="fas fa-user-minus"></i> Retirer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3 d-flex justify-content-center">
                    {{ $assignedStudents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<!-- jQuery (requis pour Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialiser Select2 sur tous les selects de classe
    $('.class-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Sélectionner une classe',
        allowClear: true,
        width: '100%',
        dropdownAutoWidth: true,
        language: {
            noResults: function() {
                return "Aucune classe trouvée";
            },
            searching: function() {
                return "Recherche en cours...";
            }
        }
    });
    
    // Trier les options par nom de classe
    $('.class-select').each(function() {
        const select = $(this);
        const options = select.find('option:not(:first)').sort(function(a, b) {
            return $(a).text().localeCompare($(b).text());
        });
        
        select.find('option:not(:first)').remove();
        select.append(options);
        
        // Réinitialiser Select2 après le tri
        select.trigger('change.select2');
    });
    
    // Gérer le clic sur le bouton Affecter
    $('.btn-assign').on('click', function() {
        const studentId = $(this).data('student-id');
        const form = $(this).closest('tr').find('.assign-form');
        const classId = form.find('select[name="class_id"]').val();
        
        if (!classId) {
            alert('Veuillez sélectionner une classe avant d\'affecter l\'élève.');
            return;
        }
        
        // Soumettre le formulaire
        form.submit();
    });
    
    // Animation de chargement lors de la soumission
    $('form').on('submit', function() {
        const btn = $(this).closest('tr').find('.btn-assign');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin"></i> Affectation...');
    });
});
</script>
@endpush
