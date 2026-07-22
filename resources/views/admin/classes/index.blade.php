@extends('admin.layouts.app')

@section('title', !empty($isFormationSchool) && $isFormationSchool ? 'Gestion des promotions' : 'Gestion des classes')

@section('content')
<div class="container-fluid">
    <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
        <i class="fas fa-arrow-left me-2"></i>Tableau de bord
    </a>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h1 class="h3 mb-0">{{ !empty($isFormationSchool) && $isFormationSchool ? 'Gestion des promotions' : 'Gestion des classes' }}</h1>
                <div class="d-flex flex-wrap gap-2">
                    @if(!empty($canProcessPromotions) && $promotionYear)
                        <form method="POST" action="{{ route('admin.classes.process-all-promotions') }}"
                              onsubmit="return confirm('Appliquer le passage en classe supérieure pour TOUTES les classes de {{ $promotionYear->name }} ?\n\nSeuls les élèves admis (moyenne ≥ {{ config('school.passing_grade_min', 10) }}/20) seront promus.');">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $promotionYear->id }}">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-level-up-alt me-1"></i>
                                Passages toutes classes ({{ $promotionYear->name }})
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> {{ !empty($isFormationSchool) && $isFormationSchool ? 'Nouvelle promotion' : 'Nouvelle classe' }}
                    </a>
                </div>
            </div>

            @if(!empty($isFormationSchool) && $isFormationSchool)
                <p class="text-muted mb-4">
                    Créez une promotion en une fois : nom, filière, année de formation, année scolaire et tous les groupes/classes.
                </p>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ !empty($isFormationSchool) && $isFormationSchool ? 'Liste des promotions' : 'Liste des classes' }}</h5>
                    <div class="text-muted small">
                        {{ $classes->total() }} {{ !empty($isFormationSchool) && $isFormationSchool ? 'groupe(s)' : 'classe(s)' }}
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($classes->isEmpty())
                        <div class="alert alert-info m-3">
                            {{ !empty($isFormationSchool) && $isFormationSchool ? 'Aucune promotion n\'a été créée pour le moment.' : 'Aucune classe n\'a été créée pour le moment.' }}
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        @if(!empty($isFormationSchool) && $isFormationSchool)
                                            <th>Département</th>
                                            <th>Promotion</th>
                                            <th>Groupe</th>
                                            <th>Diplôme</th>
                                            <th>Filière</th>
                                            <th>Année</th>
                                        @else
                                            <th>Nom</th>
                                            <th>Niveau</th>
                                        @endif
                                        <th>Année scolaire</th>
                                        <th class="text-nowrap">Effectif</th>
                                        <th>Professeurs affectés</th>
                                        <th class="text-nowrap text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $class)
                                        @php
                                            $isLocked = $class->academicYear?->isReadOnly();
                                            $displayCount = $isLocked
                                                ? ($cohortCounts[$class->id] ?? $class->students_count)
                                                : $class->students_count;
                                        @endphp
                                        <tr class="{{ $isLocked ? 'table-secondary' : '' }}">
                                            @if(!empty($isFormationSchool) && $isFormationSchool)
                                                <td>{{ $class->formationPromotion?->formationDepartment?->name ?? $class->formationDepartment?->name ?? '—' }}</td>
                                                <td>{{ $class->formationPromotion?->name ?? $class->promotion_name ?? '—' }}</td>
                                                <td><strong>{{ $class->name }}</strong></td>
                                                <td>{{ \App\Support\SenegalFormationDiplomas::label($class->formationPromotion?->diploma_type ?? $class->diploma_type) ?? '—' }}</td>
                                                <td>{{ $class->formationPromotion?->filiere ?? $class->filiere ?? '—' }}</td>
                                                <td>{{ $class->formationPromotion?->formation_year ?? $class->formation_year ?? '—' }}</td>
                                            @else
                                                <td class="text-truncate" style="max-width: 150px;" title="{{ $class->name }}">
                                                    {{ $class->name }}
                                                </td>
                                                <td class="text-muted small">
                                                    @if($class->diploma_type)
                                                        {{ \App\Support\SenegalFormationDiplomas::label($class->diploma_type) }}
                                                        @if($class->formation_year)
                                                            — {{ $class->formation_year }}
                                                        @endif
                                                    @else
                                                        {{ $class->level->name ?? 'Non défini' }}
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="text-nowrap">
                                                <span class="text-muted">{{ $class->academicYear->name }}</span>
                                                @if($isLocked)
                                                    <span class="badge bg-secondary ms-1">Archivée</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <span class="badge" style="{{ $displayCount > 0 ? 'background-color: #fd7e14;' : 'background-color: #f8f9fa; color: #212529;' }}">
                                                    {{ $displayCount }} élève(s)
                                                </span>
                                                @if($isLocked && $displayCount !== $class->students_count)
                                                    <br><small class="text-muted">({{ $class->students_count }} actuellement)</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    // Titulaire primaire (class_teacher) + professeurs par
                                                    // matière (TeacherAssignment, collège/lycée) : les deux
                                                    // voies d'affectation possibles pour une classe.
                                                    $classTeachers = $class->teachers
                                                        ->concat($class->teacherAssignments->pluck('teacher')->filter())
                                                        ->unique('id');
                                                @endphp
                                                @if($classTeachers->count() > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($classTeachers->take(3) as $teacher)
                                                            <span class="badge bg-info" title="{{ $teacher->name }}{{ $teacher->subjects->isNotEmpty() ? ' - ' . $teacher->subjects->pluck('name')->join(', ') : '' }}">
                                                                {{ Str::limit($teacher->name, 12) }}
                                                            </span>
                                                        @endforeach
                                                        @if($classTeachers->count() > 3)
                                                            <span class="badge bg-secondary">+{{ $classTeachers->count() - 3 }}</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $classTeachers->count() }} professeur(s)</small>
                                                @else
                                                    <span class="text-muted small">Aucun</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.classes.show', $class) }}" 
                                                       class="btn btn-sm" style="color: #fd7e14; border-color: #fd7e14;" 
                                                       title="Voir les détails"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @unless($isLocked)
                                                    <a href="{{ route('admin.classes.edit', $class) }}" 
                                                       class="btn btn-sm btn-outline-secondary" 
                                                       title="Modifier"
                                                       data-bs-toggle="tooltip">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="{{ $class->students_count > 0 ? 'Impossible de supprimer - classe non vide' : 'Supprimer' }}"
                                                            {{ $class->students_count > 0 ? 'disabled' : '' }}
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $class->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @else
                                                    <span class="btn btn-sm btn-outline-secondary disabled" title="Année terminée — consultation seule">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                    @endunless
                                                </div>
                                                
                                                <!-- Modal de confirmation de suppression -->
                                                @unless($isLocked)
                                                <div class="modal fade" id="deleteModal{{ $class->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <p>Êtes-vous sûr de vouloir supprimer la classe <strong>{{ $class->name }}</strong> ?</p>
                                                                <p class="text-danger"><i class="fas fa-warning me-1"></i>Cette action est irréversible.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <form action="{{ route('admin.classes.destroy', $class) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="fas fa-trash me-1"></i>Supprimer
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endunless
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            {{ $classes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -1rem;
        padding: 0 1rem;
    }
    
    .table {
        min-width: 100%;
        width: max-content;
        margin-bottom: 0;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
    }
    
    .table th {
        white-space: nowrap;
        font-weight: 600;
    }
    
    .btn-group-sm > .btn, .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .card-header h5 {
            margin-bottom: 0.5rem;
        }
        
        .table-responsive {
            margin: 0 -0.5rem;
            padding: 0 0.5rem;
        }
        
        .table > :not(caption) > * > * {
            padding: 0.5rem 0.25rem;
        }
    }
    
    @media (max-width: 576px) {
        .btn-group .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
        
        .badge {
            font-size: 0.7em;
            padding: 0.25em 0.5em;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Initialisation des tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection
