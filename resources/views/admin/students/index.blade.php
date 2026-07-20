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

    .student-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        max-height: 320px;
        overflow-y: auto;
        margin-top: 0.25rem;
        border-radius: 0.375rem;
    }

    .student-search-dropdown .list-group-item {
        cursor: pointer;
        border-left: none;
        border-right: none;
    }

    .student-search-dropdown .list-group-item:first-child {
        border-top: none;
    }

    .student-search-dropdown .list-group-item:hover,
    .student-search-dropdown .list-group-item.active {
        background-color: #fff3e6;
        color: #212529;
    }

    .student-suggestion-chip {
        cursor: pointer;
        transition: background-color 0.15s;
    }

    .student-suggestion-chip:hover {
        background-color: #fff3e6 !important;
        border-color: #fd7e14 !important;
        color: #212529 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center text-decoration-none mb-3 small text-muted">
        <i class="fas fa-arrow-left me-2"></i>Tableau de bord
    </a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h2 class="h4 mb-0">Gestion des élèves</h2>
        <div class="d-flex gap-2 flex-wrap">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-file-download me-1"></i> Exporter
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.students.export', ['format' => 'xlsx']) }}">
                            <i class="fas fa-file-excel me-2 text-success"></i> Excel (.xlsx)
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.students.export', ['format' => 'csv']) }}">
                            <i class="fas fa-file-csv me-2 text-warning"></i> CSV (.csv)
                        </a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('admin.students.import') }}" class="btn btn-outline-primary">
                <i class="fas fa-file-import me-1"></i> Importer
            </a>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Nouvel élève
            </a>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body py-3">
            <form action="{{ route('admin.students.index') }}" method="GET" class="row g-2 align-items-end" id="student-search-form">
                <div class="col-md-8 col-lg-9 position-relative">
                    <label for="student-search-input" class="form-label mb-1 fw-semibold">
                        <i class="fas fa-search me-1 text-warning"></i> Rechercher un élève
                    </label>
                    <input
                        type="search"
                        name="search"
                        id="student-search-input"
                        class="form-control"
                        placeholder="Nom, prénom, identifiant ou email…"
                        value="{{ $search ?? '' }}"
                        autocomplete="off"
                        role="combobox"
                        aria-expanded="false"
                        aria-controls="student-search-suggestions"
                        aria-autocomplete="list"
                    >
                    <div id="student-search-suggestions" class="student-search-dropdown list-group shadow-sm d-none" role="listbox"></div>
                </div>
                <div class="col-md-4 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-warning flex-grow-1 text-dark fw-semibold">
                        <i class="fas fa-search me-1"></i> Rechercher
                    </button>
                    @if(!empty($search))
                        <a href="{{ route('admin.students.index', request()->only('tab')) }}" class="btn btn-outline-secondary" title="Effacer la recherche">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
            @if(!empty($search))
                <p class="text-muted small mb-0 mt-2">
                    Résultats pour « <strong>{{ $search }}</strong> » — cliquez sur
                    <i class="fas fa-eye text-warning"></i> pour voir toutes les informations de l'élève.
                </p>
            @endif
        </div>
    </div>

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
            @include('admin.students._list', [
                'students' => $students,
                'search' => $search ?? '',
                'searchSuggestions' => $searchSuggestions ?? collect(),
            ])
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
                'classes' => $classes,
                'total' => $unassignedStudentsTotal ?? $unassignedStudents->count(),
                'truncated' => $unassignedStudentsTruncated ?? false,
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
        
        // Focus sur le champ de recherche si une recherche est active
        const searchInput = document.getElementById('student-search-input');
        const suggestionsBox = document.getElementById('student-search-suggestions');
        const searchForm = document.getElementById('student-search-form');
        let suggestTimer = null;
        let activeSuggestionIndex = -1;

        function hideSuggestions() {
            if (!suggestionsBox) return;
            suggestionsBox.classList.add('d-none');
            suggestionsBox.innerHTML = '';
            activeSuggestionIndex = -1;
            if (searchInput) {
                searchInput.setAttribute('aria-expanded', 'false');
            }
        }

        function renderSuggestions(items) {
            if (!suggestionsBox || !searchInput) return;

            if (!items.length) {
                hideSuggestions();
                return;
            }

            suggestionsBox.innerHTML = '';

            items.forEach(function (item, index) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action text-start';
                btn.setAttribute('role', 'option');
                btn.dataset.index = index;
                if (item.search_url) btn.dataset.searchUrl = item.search_url;
                if (item.url) btn.dataset.url = item.url;

                const strong = document.createElement('strong');
                strong.textContent = item.name || '';
                btn.appendChild(strong);

                if (item.class) {
                    const cls = document.createElement('small');
                    cls.className = 'text-muted ms-2';
                    cls.textContent = item.class;
                    btn.appendChild(cls);
                }

                if (item.identifier) {
                    btn.appendChild(document.createElement('br'));
                    const id = document.createElement('small');
                    id.className = 'text-muted';
                    id.textContent = item.identifier;
                    btn.appendChild(id);
                }

                suggestionsBox.appendChild(btn);
            });

            suggestionsBox.classList.remove('d-none');
            searchInput.setAttribute('aria-expanded', 'true');

            suggestionsBox.querySelectorAll('[role="option"]').forEach(function (btn) {
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    window.location.href = btn.dataset.searchUrl || btn.dataset.url;
                });
            });
        }

        function fetchSuggestions(query) {
            if (query.length < 2) {
                hideSuggestions();
                return;
            }

            fetch('{{ route('admin.students.search-suggestions') }}?q=' + encodeURIComponent(query), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(renderSuggestions)
                .catch(function () { hideSuggestions(); });
        }

        if (searchInput && suggestionsBox) {
            searchInput.addEventListener('input', function () {
                clearTimeout(suggestTimer);
                const query = this.value.trim();
                suggestTimer = setTimeout(function () { fetchSuggestions(query); }, 250);
            });

            searchInput.addEventListener('keydown', function (e) {
                const options = suggestionsBox.querySelectorAll('[role="option"]');
                if (!options.length || suggestionsBox.classList.contains('d-none')) {
                    return;
                }

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeSuggestionIndex = Math.min(activeSuggestionIndex + 1, options.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeSuggestionIndex = Math.max(activeSuggestionIndex - 1, 0);
                } else if (e.key === 'Escape') {
                    hideSuggestions();
                    return;
                } else if (e.key === 'Enter' && activeSuggestionIndex >= 0) {
                    e.preventDefault();
                    options[activeSuggestionIndex].dispatchEvent(new MouseEvent('mousedown'));
                    return;
                } else {
                    return;
                }

                options.forEach(function (opt, i) {
                    opt.classList.toggle('active', i === activeSuggestionIndex);
                });
            });

            searchInput.addEventListener('blur', function () {
                setTimeout(hideSuggestions, 150);
            });

            document.addEventListener('click', function (e) {
                if (!searchForm.contains(e.target)) {
                    hideSuggestions();
                }
            });
        }

        if (searchInput && searchInput.value.trim() !== '') {
            searchInput.focus();
            searchInput.select();
        }

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
