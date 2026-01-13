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
    
    /* Style pour les onglets */
    .nav-tabs .nav-link {
        color: #6c757d;
        font-weight: 500;
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.75rem 1.25rem;
        transition: all 0.2s;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #0d6efd;
    }
    
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        background-color: transparent;
        border-color: transparent;
        border-bottom-color: #0d6efd;
    }
    
    .tab-content {
        padding: 1.5rem 0;
    }
</style>
@endpush

<div class="container-fluid">
    <div class="flex-wrap pt-3 pb-2 mb-3 d-flex justify-content-between flex-md-nowrap align-items-center border-bottom">
        <h1 class="mb-0 h3">Gestion des Élèves</h1>
    </div>
    
    <!-- Onglets -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('admin.students.index') ? 'active' : '' }}" 
               href="{{ route('admin.students.index') }}" 
               role="tab">
                <i class="fas fa-list me-2"></i>Liste complète
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('admin.students.assign') ? 'active' : '' }}" 
               href="{{ route('admin.students.assign') }}" 
               role="tab">
                <i class="fas fa-user-plus me-2"></i>Affectation aux classes
            </a>
        </li>
    </ul>
    
    <!-- Contenu des onglets -->
    <div class="tab-content">
        @if(request()->routeIs('admin.students.index'))
            <div class="tab-pane fade show active" role="tabpanel">
                @include('admin.students._list')
            </div>
        @elseif(request()->routeIs('admin.students.assign'))
            <div class="tab-pane fade show active" role="tabpanel">
                @include('admin.students._assign')
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Activer les tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Trier les options par niveau et par nom de classe
        const classSelect = document.getElementById('class_id');
        if (classSelect) {
            const options = Array.from(classSelect.options);
            const selectedValue = classSelect.value;
            
            // Trier les options par niveau (6ème à Terminale) et par nom
            options.sort((a, b) => {
                if (a.value === '') return -1;
                if (b.value === '') return 1;
                
                const aText = a.text.toLowerCase();
                const bText = b.text.toLowerCase();
                
                // Extraire le niveau de la classe (6e, 5e, 4e, 3e, 2nde, 1ère, Tle)
                const getLevel = (text) => {
                    if (text.includes('6e')) return 0;
                    if (text.includes('5e')) return 1;
                    if (text.includes('4e')) return 2;
                    if (text.includes('3e')) return 3;
                    if (text.includes('2nde')) return 4;
                    if (text.includes('1ère') || text.includes('1ere')) return 5;
                    if (text.includes('Tle') || text.includes('Term')) return 6;
                    return 7; // Autres niveaux à la fin
                };
                
                const aLevel = getLevel(aText);
                const bLevel = getLevel(bText);
                
                if (aLevel !== bLevel) {
                    return aLevel - bLevel;
                }
                
                // Si même niveau, trier par nom de classe
                return aText.localeCompare(bText);
            });
            
            // Vider et réinsérer les options triées
            while (classSelect.options.length > 0) {
                classSelect.remove(0);
            }
            
            options.forEach(option => {
                classSelect.add(option);
            });
            
            // Restaurer la valeur sélectionnée
            classSelect.value = selectedValue;
        }
    });
</script>
@endpush
