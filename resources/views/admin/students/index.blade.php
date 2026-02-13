@extends('admin.layouts.app')

@section('title', 'Gestion des élèves')

@push('styles')
<style>
    .nav-tabs {
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 1.5rem;
    }
    
    .nav-tabs .nav-link {
        color: #6c757d;
        font-weight: 500;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s;
        margin-right: 0.5rem;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #fd7e14;
    }
    
    .nav-tabs .nav-link.active {
        color: #fd7e14;
        background-color: transparent;
        border-color: transparent;
        border-bottom-color: #fd7e14;
        font-weight: 600;
    }
    
    .tab-content {
        padding: 1rem 0;
        padding-bottom: 2rem;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    /* Responsive table */
    @media (max-width: 768px) {
        .nav-tabs .nav-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        
        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }
    }
    
    /* Accordion styles for classes */
    .accordion-item {
        border: 1px solid rgba(0, 0, 0, 0.125);
        margin-bottom: 0.5rem;
        border-radius: 0.375rem !important;
        overflow: hidden;
    }
    
    .accordion-button {
        padding: 1rem 1.25rem;
        font-weight: 500;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #fff3e6;
        color: #fd7e14;
    }
    
    .accordion-body {
        padding: 0;
    }
    
    .accordion-body .table {
        margin-bottom: 0;
    }
    
    .accordion-body .table thead {
        position: sticky;
        top: 0;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Gestion des élèves</h2>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Nouvel élève
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Onglets -->
    <ul class="nav nav-tabs mb-3" id="studentsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active_tab === 'list' ? 'active' : '' }}" 
                    id="list-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#list-tab-pane" 
                    type="button" 
                    role="tab" 
                    aria-controls="list-tab-pane" 
                    aria-selected="{{ $active_tab === 'list' ? 'true' : 'false' }}">
                <i class="fas fa-list me-2"></i>Liste complète
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active_tab === 'byclass' ? 'active' : '' }}" 
                    id="byclass-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#byclass-tab-pane" 
                    type="button" 
                    role="tab" 
                    aria-controls="byclass-tab-pane" 
                    aria-selected="{{ $active_tab === 'byclass' ? 'true' : 'false' }}">
                <i class="fas fa-th-list me-2"></i>Par classe
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $active_tab === 'assign' ? 'active' : '' }}" 
                    id="assign-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#assign-tab-pane" 
                    type="button" 
                    role="tab" 
                    aria-controls="assign-tab-pane" 
                    aria-selected="{{ $active_tab === 'assign' ? 'true' : 'false' }}">
                <i class="fas fa-user-plus me-2"></i>Affectation aux classes
            </button>
        </li>
    </ul>

    <!-- Contenu des onglets -->
    <div class="tab-content" id="studentsTabsContent">
        <!-- Onglet Liste complète -->
        <div class="tab-pane fade {{ $active_tab === 'list' ? 'show active' : '' }}" 
             id="list-tab-pane" 
             role="tabpanel" 
             aria-labelledby="list-tab" 
             tabindex="0">
            @include('admin.students._list', ['students' => $students])
        </div>
        
        <!-- Onglet Par classe -->
        <div class="tab-pane fade {{ $active_tab === 'byclass' ? 'show active' : '' }}" 
             id="byclass-tab-pane" 
             role="tabpanel" 
             aria-labelledby="byclass-tab" 
             tabindex="0">
            @include('admin.students._by_class', [
                'classes' => $classes,
                'studentsByClass' => $studentsByClass
            ])
        </div>
        
        <!-- Onglet Affectation aux classes -->
        <div class="tab-pane fade {{ $active_tab === 'assign' ? 'show active' : '' }}" 
             id="assign-tab-pane" 
             role="tabpanel" 
             aria-labelledby="assign-tab" 
             tabindex="0">
            @include('admin.students._assign', [
                'students' => $unassignedStudents,
                'classes' => $classes
            ])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Activer les tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Gérer la sélection/désélection de tous les élèves
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function(e) {
                const checkboxes = document.querySelectorAll('.student-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            });
        }
        
        // Gérer la désélection de "Sélectionner tout" si une case est décochée
        const studentCheckboxes = document.querySelectorAll('.student-checkbox');
        studentCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked && selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                } else if (selectAllCheckbox) {
                    // Vérifier si toutes les cases sont cochées
                    const allChecked = Array.from(studentCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
        
        // Gérer le changement d'onglet avec mise à jour de l'URL
        const tabElms = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElms.forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                const target = event.target;
                const tabId = target.getAttribute('data-bs-target');
                
                // Mettre à jour l'URL sans recharger la page
                const tabName = target.getAttribute('aria-controls');
                let newUrl = window.location.href.split('?')[0];
                
                if (tabName === 'assign-tab-pane') {
                    newUrl += '?tab=assign';
                    window.history.pushState({}, '', newUrl);
                } else if (window.location.search.includes('tab=assign')) {
                    window.history.pushState({}, '', newUrl);
                }
            });
        });
        
        // Gérer le bouton précédent/suivant du navigateur
        window.addEventListener('popstate', function() {
            const activeTab = document.querySelector('#studentsTabs .nav-link.active');
            const currentPath = window.location.pathname;
            
            if (currentPath.includes('/assign')) {
                // Afficher l'onglet d'affectation
                const assignTab = document.querySelector('#assign-tab');
                if (activeTab !== assignTab) {
                    new bootstrap.Tab(assignTab).show();
                }
            } else {
                // Afficher l'onglet de liste
                const listTab = document.querySelector('#list-tab');
                if (activeTab !== listTab) {
                    new bootstrap.Tab(listTab).show();
                }
            }
        });
    });
</script>
@endpush
