@extends('admin.layouts.app')

@section('title', $class->exists
    ? (!empty($isFormationSchool) && $isFormationSchool ? 'Modifier la promotion' : 'Modifier la classe')
    : (!empty($isFormationSchool) && $isFormationSchool ? 'Nouvelle promotion' : 'Créer une nouvelle classe'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.academic-years.index') }}">Années scolaires</a>
                    </li>
                    @if($class->exists)
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.academic-years.show', $class->academic_year_id) }}">
                                {{ $class->academicYear->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.classes.show', $class) }}">{{ $class->name }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Modifier</li>
                    @elseif($selectedAcademicYear ?? null)
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.academic-years.show', $selectedAcademicYear) }}">
                                {{ $selectedAcademicYear->name }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Nouvelle classe</li>
                    @endif
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    @if(!empty($isFormationSchool) && $isFormationSchool)
                        {{ $class->exists ? 'Modifier le groupe : ' . $class->name : 'Nouvelle promotion' }}
                    @else
                        {{ $class->exists ? 'Modifier la classe : ' . $class->name : 'Créer une nouvelle classe' }}
                    @endif
                </h1>
                <a href="{{ $class->exists ? route('admin.classes.show', $class) : route('admin.classes.index') }}" 
                   class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ $class->exists ? route('admin.classes.update', $class) : route('admin.classes.store') }}" 
                          method="POST"
                          class="needs-validation" 
                          novalidate>
                        @csrf
                        @if($class->exists)
                            @method('PUT')
                        @endif

                        @if(!empty($isFormationSchool) && $isFormationSchool)
                            @if(!$class->exists)
                                <p class="text-muted small mb-3">
                                    Définissez la promotion : diplôme (BT, BTS, Licence…), filière, année de formation et les groupes.
                                </p>
                            @endif

                            @php
                                $diplomaOptions = $formationDiplomas ?? \App\Support\SenegalFormationDiplomas::types();
                                $selectedDiploma = old('diploma_type', $class->diploma_type);
                                $departments = $formationDepartments ?? collect();
                                $selectedDepartmentId = old('formation_department_id', $class->formation_department_id);
                            @endphp

                            <div class="row">
                                <div class="col-12 mb-4">
                                    <div class="border rounded p-3 bg-light">
                                        <label class="form-label fw-semibold mb-2">
                                            <i class="fas fa-building me-1"></i> Département *
                                        </label>
                                        <div class="row g-3 align-items-start">
                                            <div class="col-md-6">
                                                <label for="formation_department_id" class="form-label small text-muted">Département existant</label>
                                                <select id="formation_department_id" name="formation_department_id"
                                                        class="form-select @error('formation_department_id') is-invalid @enderror">
                                                    <option value="" {{ ! $selectedDepartmentId ? 'selected' : '' }}>— Nouveau département —</option>
                                                    @foreach($departments as $department)
                                                        <option value="{{ $department->id }}"
                                                            {{ (string) $selectedDepartmentId === (string) $department->id ? 'selected' : '' }}>
                                                            {{ $department->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formation_department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="col-md-6" id="new-department-wrap">
                                                <label for="department_name" class="form-label small text-muted">Nouveau département</label>
                                                <input type="text" id="department_name" name="department_name"
                                                       class="form-control @error('department_name') is-invalid @enderror"
                                                       value="{{ old('department_name') }}"
                                                       placeholder="Ex. : Informatique, Gestion, Commerce…">
                                                <div class="form-text">Saisissez un nom : il sera enregistré et réutilisable pour les prochaines promotions.</div>
                                                @error('department_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label d-block">Diplôme visé *</label>
                                    <div class="row g-2" id="diploma-options">
                                        @foreach($diplomaOptions as $code => $label)
                                            <div class="col-md-6 col-lg-4">
                                                <label class="diploma-option w-100 border rounded p-2 h-100 d-flex align-items-start gap-2 mb-0 {{ $selectedDiploma === $code ? 'border-primary bg-light' : '' }}"
                                                       style="cursor: pointer;">
                                                    <input class="form-check-input mt-1 flex-shrink-0" type="radio"
                                                           name="diploma_type" id="diploma_{{ $code }}"
                                                           value="{{ $code }}"
                                                           {{ $selectedDiploma === $code ? 'checked' : '' }}
                                                           @if($loop->first) required @endif>
                                                    <span>{{ $label }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('diploma_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="promotion_name" class="form-label">Nom de la promotion *</label>
                                    <input type="text" id="promotion_name" name="promotion_name"
                                           class="form-control @error('promotion_name') is-invalid @enderror"
                                           value="{{ old('promotion_name', $class->promotion_name) }}"
                                           placeholder="Master Informatique de Gestion 2026" required>
                                    @error('promotion_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="academic_year_id" class="form-label">Année scolaire *</label>
                                    <select class="form-select @error('academic_year_id') is-invalid @enderror"
                                            id="academic_year_id" name="academic_year_id" required
                                            {{ $class->exists ? 'disabled' : '' }}>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}"
                                                {{ (old('academic_year_id', $class->academic_year_id ?? $selectedAcademicYear?->id) == $year->id) ? 'selected' : '' }}>
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($class->exists)
                                        <input type="hidden" name="academic_year_id" value="{{ $class->academic_year_id }}">
                                    @endif
                                    @error('academic_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="filiere" class="form-label">Filière / spécialité *</label>
                                    <input type="text" id="filiere" name="filiere"
                                           class="form-control @error('filiere') is-invalid @enderror"
                                           value="{{ old('filiere', $class->filiere) }}"
                                           placeholder="Licence Informatique de Gestion" required>
                                    <div class="form-text">Rempli automatiquement à partir du nom de la promotion (sans l'année).</div>
                                    @error('filiere')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="formation_year" class="form-label">Année de formation *</label>
                                    <select id="formation_year" name="formation_year"
                                            class="form-select @error('formation_year') is-invalid @enderror"
                                            data-current="{{ old('formation_year', $class->formation_year) }}"
                                            required disabled>
                                        <option value="">— Sélectionnez d'abord un diplôme —</option>
                                    </select>
                                    @error('formation_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                @if($class->exists)
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Nom du groupe / classe *</label>
                                        <input type="text" id="name" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name', $class->getRawOriginal('name') ?? $class->name) }}"
                                               placeholder="Ex. : LIG1-1" required>
                                        <div class="form-text">Format : diminutif filière + année + numéro (ex. LIG1-1, MC1-1).</div>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @else
                                    <div class="col-12 mb-3">
                                        <label for="groups" class="form-label">Groupes / classes de la promotion *</label>
                                        <textarea id="groups" name="groups" rows="2"
                                                  class="form-control @error('groups') is-invalid @enderror"
                                                  placeholder="—" required>{{ old('groups') }}</textarea>
                                        <div class="form-text" id="groups-hint">Ajoutez d'autres groupes sur des lignes suivantes si besoin.</div>
                                        @error('groups')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                @endif

                                <div class="col-md-4 mb-3">
                                    <label for="capacity" class="form-label">Capacité par groupe</label>
                                    <input type="number" id="capacity" name="capacity" min="1" max="100"
                                           class="form-control @error('capacity') is-invalid @enderror"
                                           value="{{ old('capacity', $class->capacity) }}"
                                           placeholder="60">
                                    @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="room_number" class="form-label">Salle</label>
                                    <input type="text" id="room_number" name="room_number"
                                           class="form-control @error('room_number') is-invalid @enderror"
                                           value="{{ old('room_number', $class->room_number) }}"
                                           placeholder="Ex. : Salle Daniel Brothier, Bâtiment B">
                                    @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label">Description / notes</label>
                                    <textarea id="description" name="description" rows="2"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="Objectifs, durée, horaires, prérequis…">{{ old('description', $class->description) }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @else
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    {{ !empty($isFormationSchool) && $isFormationSchool ? 'Nom de la promotion *' : 'Nom de la classe *' }}
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $class->name) }}" 
                                       placeholder="{{ !empty($isFormationSchool) && $isFormationSchool ? 'Ex. : Promotion A — Année 1' : '' }}"
                                       required>
                                <div class="invalid-feedback">
                                    Veuillez saisir un nom de classe.
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="level_id" class="form-label">
                                    {{ !empty($isFormationSchool) && $isFormationSchool ? 'Cycle *' : 'Niveau *' }}
                                </label>
                                @if($levels->isEmpty())
                                    <div class="alert alert-warning py-2 small mb-2">
                                        @if(!empty($isFormationSchool) && $isFormationSchool)
                                            Aucun cycle défini.
                                            <a href="{{ route('admin.cycles.create') }}">Créez d'abord un cycle</a>
                                            (Année 1, Année 2…).
                                        @else
                                            Aucun niveau disponible. Rechargez la page ou contactez l'administrateur plateforme.
                                        @endif
                                    </div>
                                @endif
                                <select class="form-select @error('level_id') is-invalid @enderror" 
                                        id="level_id" 
                                        name="level_id" 
                                        required
                                        @if($levels->isEmpty()) disabled @endif>
                                    <option value="" disabled {{ !$class->exists && !old('level_id') ? 'selected' : '' }}>
                                        {{ !empty($isFormationSchool) && $isFormationSchool ? 'Sélectionner un cycle' : 'Sélectionner un niveau' }}
                                    </option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level->id }}" 
                                                {{ (old('level_id', $class->level_id) == $level->id) ? 'selected' : '' }}>
                                            {{ $level->name }}@if($level->serie) — {{ $level->serie }}@endif
                                            @if(!$level->isFormationCycle())
                                                ({{ $level->cycleLabel() }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    Veuillez sélectionner un niveau.
                                </div>
                                @error('level_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="academic_year_id" class="form-label">Année scolaire *</label>
                                <select class="form-select @error('academic_year_id') is-invalid @enderror" 
                                        id="academic_year_id" 
                                        name="academic_year_id" 
                                        required
                                        {{ $class->exists ? 'disabled' : '' }}>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" 
                                                {{ (old('academic_year_id', $class->academic_year_id ?? $selectedAcademicYear?->id) == $year->id) ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($class->exists)
                                    <input type="hidden" name="academic_year_id" value="{{ $class->academic_year_id }}">
                                @endif
                                <div class="invalid-feedback">
                                    Veuillez sélectionner une année scolaire.
                                </div>
                                @error('academic_year_id')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="room_number" class="form-label">Salle</label>
                                <input type="text" 
                                       class="form-control @error('room_number') is-invalid @enderror" 
                                       id="room_number" 
                                       name="room_number" 
                                       value="{{ old('room_number', $class->room_number) }}">
                                @error('room_number')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="capacity" class="form-label">Capacité maximale</label>
                                <input type="number" 
                                       class="form-control @error('capacity') is-invalid @enderror" 
                                       id="capacity" 
                                       name="capacity" 
                                       min="1" 
                                       max="100"
                                       value="{{ old('capacity', $class->capacity) }}">
                                <div class="form-text">
                                    Laisser vide pour une capacité illimitée.
                                </div>
                                @error('capacity')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                {{ $class->exists ? 'Mettre à jour' : (!empty($isFormationSchool) && $isFormationSchool ? 'Créer la promotion' : 'Créer la classe') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const formationYearsByDiploma = @json(\App\Support\SenegalFormationDiplomas::formationYearsByDiploma());
    const formationDiplomaCodePrefixes = @json(\App\Support\FormationGroupNaming::diplomaCodePrefixes());
    const formationStopWords = ['de', 'du', 'des', 'd', 'et', 'la', 'le', 'les', 'en', 'au', 'aux', 'à', 'a', 'l'];

    function usesDiplomaCodePrefix(diplomaCode) {
        return Object.prototype.hasOwnProperty.call(formationDiplomaCodePrefixes, diplomaCode);
    }

    function prefixAbbrev(filiere, diplomaCode) {
        if (diplomaCode && usesDiplomaCodePrefix(diplomaCode)) {
            return formationDiplomaCodePrefixes[diplomaCode];
        }

        if (filiere && filiere.trim()) {
            return filiere.trim().split(/\s+/).filter(function (word) {
                return word && formationStopWords.indexOf(word.toLowerCase()) === -1;
            }).map(function (word) {
                return word.charAt(0).toUpperCase();
            }).join('') || 'CLS';
        }

        if (diplomaCode) {
            return diplomaCode.replace(/_/g, '').toUpperCase();
        }

        return 'CLS';
    }

    function canSuggestGroups(filiere, diplomaCode, yearLabel) {
        if (!diplomaCode || !yearLabel) return false;
        if (usesDiplomaCodePrefix(diplomaCode)) return true;
        return !!(filiere && filiere.trim());
    }

    function formationYearDigit(diplomaCode, yearLabel) {
        const match = String(yearLabel || '').match(/(\d+)/);
        if (match) return match[1];

        const years = formationYearsByDiploma[diplomaCode] || {};
        for (const [num, label] of Object.entries(years)) {
            if (label === yearLabel) return num;
        }

        return '1';
    }

    function suggestFormationGroupNames(filiere, diplomaCode, yearLabel, count) {
        count = count || 3;
        const abbr = prefixAbbrev(filiere, diplomaCode);
        const year = formationYearDigit(diplomaCode, yearLabel);
        return Array.from({ length: count }, function (_, index) {
            return abbr + year + '-' + (index + 1);
        });
    }

    function updateGroupsHint() {
        const groupsEl = document.getElementById('groups');
        const hintEl = document.getElementById('groups-hint');
        if (!groupsEl || !hintEl) return;

        const firstLine = (groupsEl.value.trim() || groupsEl.placeholder || '').split(/\r?\n/)[0].trim();
        if (!firstLine || firstLine === '—') {
            hintEl.textContent = 'Un nom sera proposé ici. Autres groupes : une ligne par nom.';
            return;
        }

        const match = firstLine.match(/^(.+-)(\d+)$/);
        const nextNames = match
            ? [match[1] + (parseInt(match[2], 10) + 1), match[1] + (parseInt(match[2], 10) + 2)].join(', ')
            : 'même format';

        hintEl.textContent = 'Autres groupes : ' + nextNames + '… (une ligne par nom).';
    }

    function updateGroupsSuggestion() {
        const groupsEl = document.getElementById('groups');
        const filiereEl = document.getElementById('filiere');
        const yearEl = document.getElementById('formation_year');
        if (!groupsEl) return;

        const filiere = filiereEl ? filiereEl.value.trim() : '';
        const yearLabel = yearEl ? yearEl.value : '';
        const diploma = getSelectedDiploma();

        if (!canSuggestGroups(filiere, diploma, yearLabel)) {
            updateGroupsHint();
            return;
        }

        const suggestion = suggestFormationGroupNames(filiere, diploma, yearLabel, 1);
        groupsEl.placeholder = suggestion[0] || '—';

        if (groupsEl.dataset.userEdited !== 'true') {
            groupsEl.value = suggestion.join('\n');
            groupsEl.dataset.autoSuggested = 'true';
        }

        updateGroupsHint();
    }

    function filiereFromPromotionName(name) {
        if (!name || !name.trim()) return '';

        return name.trim()
            .replace(/\s+20\d{2}(\s*[\/\-]\s*20\d{2})?\s*$/u, '')
            .replace(/\s+\d{4}\s*$/u, '')
            .trim();
    }

    function updateFiliereFromPromotion() {
        const promotionEl = document.getElementById('promotion_name');
        const filiereEl = document.getElementById('filiere');
        if (!promotionEl || !filiereEl) return;

        const derived = filiereFromPromotionName(promotionEl.value);
        if (!derived) return;

        if (filiereEl.dataset.userEdited !== 'true' || filiereEl.dataset.autoFilled === 'true' || !filiereEl.value.trim()) {
            filiereEl.value = derived;
            filiereEl.dataset.autoFilled = 'true';
            updateGroupsSuggestion();
        }
    }

    function initPromotionFiliereSync() {
        const promotionEl = document.getElementById('promotion_name');
        const filiereEl = document.getElementById('filiere');
        if (!promotionEl || !filiereEl) return;

        promotionEl.addEventListener('input', updateFiliereFromPromotion);

        filiereEl.addEventListener('input', function () {
            if (this.dataset.autoFilled === 'true') {
                this.dataset.autoFilled = 'false';
            }
            this.dataset.userEdited = 'true';
            updateGroupsSuggestion();
        });
    }

    function initFormationGroupNaming() {
        const groupsEl = document.getElementById('groups');
        const yearEl = document.getElementById('formation_year');

        if (!groupsEl) return;

        groupsEl.addEventListener('input', function () {
            if (this.dataset.autoSuggested === 'true') {
                this.dataset.autoSuggested = 'false';
            }
            this.dataset.userEdited = 'true';
            updateGroupsHint();
        });

        const scheduleSuggestion = function () {
            updateGroupsSuggestion();
        };

        if (yearEl) {
            yearEl.addEventListener('change', scheduleSuggestion);
        }

        document.querySelectorAll('#diploma-options input[type="radio"]').forEach(function (radio) {
            radio.addEventListener('change', scheduleSuggestion);
        });
    }

    function initFormationDepartmentField() {
        const select = document.getElementById('formation_department_id');
        const nameInput = document.getElementById('department_name');
        const wrap = document.getElementById('new-department-wrap');
        if (!select || !nameInput) return;

        function syncDepartmentField() {
            const hasExisting = !!select.value;
            nameInput.disabled = hasExisting;
            nameInput.required = !hasExisting;
            if (wrap) {
                wrap.classList.toggle('opacity-50', hasExisting);
            }
            if (hasExisting) {
                nameInput.value = '';
            }
        }

        select.addEventListener('change', syncDepartmentField);
        syncDepartmentField();
    }

    function initFormationPromotionHelpers() {
        initFormationDepartmentField();
        initPromotionFiliereSync();
        initFormationGroupNaming();
    }

    function updateFormationYearOptions(diplomaCode, keepValue) {
        const select = document.getElementById('formation_year');
        if (!select) return;

        const current = keepValue ?? select.dataset.current ?? '';
        const years = formationYearsByDiploma[diplomaCode] || {};

        select.innerHTML = '';

        if (!diplomaCode || Object.keys(years).length === 0) {
            select.disabled = true;
            select.innerHTML = '<option value="">— Sélectionnez d\'abord un diplôme —</option>';
            return;
        }

        select.disabled = false;
        select.appendChild(new Option('— Choisir l\'année —', '', false, !current));

        let matched = false;
        Object.entries(years).forEach(function ([num, label]) {
            const isSelected = current === label || current === num;
            if (isSelected) matched = true;
            select.appendChild(new Option(label, label, false, isSelected));
        });

        if (current && !matched) {
            select.appendChild(new Option(current + ' (existant)', current, false, true));
        }

        select.dataset.current = select.value;
        updateGroupsSuggestion();
    }

    function getSelectedDiploma() {
        const checked = document.querySelector('#diploma-options input[type="radio"]:checked');
        return checked ? checked.value : null;
    }

    document.querySelectorAll('#diploma-options input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('#diploma-options .diploma-option').forEach(function (card) {
                card.classList.remove('border-primary', 'bg-light');
            });
            if (radio.checked) {
                radio.closest('.diploma-option').classList.add('border-primary', 'bg-light');
                updateFormationYearOptions(radio.value, '');
                updateGroupsSuggestion();
            }
        });
    });

    function applyFormationDraftData(data) {
        const form = document.querySelector('form.needs-validation');
        if (!form || !data) return;

        if (data.diploma_type) {
            const radio = form.querySelector('[name="diploma_type"][value="' + data.diploma_type + '"]');
            if (radio) {
                radio.checked = true;
                document.querySelectorAll('#diploma-options .diploma-option').forEach(function (card) {
                    card.classList.remove('border-primary', 'bg-light');
                });
                radio.closest('.diploma-option')?.classList.add('border-primary', 'bg-light');
                updateFormationYearOptions(data.diploma_type, data.formation_year || '');
            }
        }

        if (data.formation_year && document.getElementById('formation_year')) {
            document.getElementById('formation_year').value = data.formation_year;
            document.getElementById('formation_year').dataset.current = data.formation_year;
        }

        updateFiliereFromPromotion();
        updateGroupsSuggestion();
    }

    document.addEventListener('form-draft-restored', function (e) {
        if (!document.getElementById('formation_year')) return;
        applyFormationDraftData(e.detail?.data);
    });

    document.addEventListener('form-draft-cleared', function (e) {
        if (!document.getElementById('formation_year')) return;
        updateFormationYearOptions(null);
        document.querySelectorAll('#diploma-options .diploma-option').forEach(function (c) {
            c.classList.remove('border-primary', 'bg-light');
        });
        updateGroupsHint();
    });

    document.addEventListener('DOMContentLoaded', function () {
        initFormationPromotionHelpers();
        @if(!$class->exists && !$errors->any())
        if (!window.FormDraftAutosave?.wasRestored()) {
            updateFormationYearOptions(getSelectedDiploma());
        }
        @else
        updateFormationYearOptions(getSelectedDiploma());
        @endif
        updateFiliereFromPromotion();
        updateGroupsSuggestion();
    });

    // Désactiver la soumission du formulaire si des champs invalides
    (function () {
        'use strict'
        
        // Récupérer tous les formulaires auxquels nous voulons appliquer des styles de validation Bootstrap
        var forms = document.querySelectorAll('.needs-validation')
        
        // Boucler dessus et empêcher la soumission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>
@endpush

@endsection
