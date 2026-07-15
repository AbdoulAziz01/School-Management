{{-- Champs fiche établissement (super administrateur) --}}
@props(['school', 'academicYears' => collect()])

<div class="col-md-8">
    <label for="name" class="form-label">Nom de l'établissement *</label>
    <input type="text" id="name" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror"
           value="{{ old('name', $school->name) }}" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="col-md-4">
    <label for="establishment_type" class="form-label">Type d'établissement *</label>
    <select id="establishment_type" name="establishment_type" class="form-select @error('establishment_type') is-invalid @enderror" required>
        <option value="" disabled @selected(!old('establishment_type', $school->establishment_type))>— Choisir —</option>
        @foreach(\App\Models\School::ESTABLISHMENT_TYPES as $value => $label)
            <option value="{{ $value }}" @selected(old('establishment_type', $school->establishment_type) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('establishment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text" id="establishment-type-levels-hint">
        {{ \App\Support\SchoolLevelProvisioner::defaultLevelsHintForType(old('establishment_type', $school->establishment_type)) }}
    </div>
</div>
<div class="col-md-4">
    <label for="establishment_category" class="form-label">Statut *</label>
    <select id="establishment_category" name="establishment_category" class="form-select @error('establishment_category') is-invalid @enderror" required>
        <option value="" disabled @selected(!old('establishment_category', $school->establishment_category))>— Choisir —</option>
        @foreach(\App\Models\School::ESTABLISHMENT_CATEGORIES as $value => $label)
            <option value="{{ $value }}" @selected(old('establishment_category', $school->establishment_category) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('establishment_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">Axe indépendant du type pédagogique — conditionne l'accès au module Comptabilité (privé uniquement).</div>
</div>
<div class="col-md-8" id="formation-lmd-option" style="{{ old('establishment_type', $school->establishment_type) === \App\Models\School::TYPE_FORMATION ? '' : 'display:none' }}">
    <div class="form-check form-switch mt-4">
        <input type="hidden" name="formation_use_lmd" value="0">
        <input class="form-check-input" type="checkbox" role="switch" id="formation_use_lmd" name="formation_use_lmd" value="1"
               @checked(old('formation_use_lmd', $school->formation_use_lmd ?? true))>
        <label class="form-check-label" for="formation_use_lmd">
            Utiliser le système LMD (CC + examen par module)
        </label>
    </div>
    <p class="text-muted small mb-0">
        Décochez pour une formation professionnelle avec notes type école classique (devoirs + composition) et vos propres modules, sans LMD.
    </p>
</div>
@push('scripts')
<script>
(function () {
    const levelHints = @json(\App\Support\SchoolLevelProvisioner::defaultLevelsHintsByType());
    const formationType = @json(\App\Models\School::TYPE_FORMATION);
    const defaultHint = @json(\App\Support\SchoolLevelProvisioner::defaultLevelsHintForType(null));

    function onEstablishmentTypeChange() {
        const select = document.getElementById('establishment_type');
        if (!select) return;

        const block = document.getElementById('formation-lmd-option');
        if (block) {
            block.style.display = select.value === formationType ? '' : 'none';
        }

        const hint = document.getElementById('establishment-type-levels-hint');
        if (hint) {
            hint.textContent = levelHints[select.value] || defaultHint;
        }
    }

    document.getElementById('establishment_type')?.addEventListener('change', onEstablishmentTypeChange);
})();
</script>
@endpush
<div class="col-md-6">
    <label for="motto" class="form-label">Devise / slogan</label>
    <input type="text" id="motto" name="motto" class="form-control" value="{{ old('motto', $school->motto) }}">
</div>
<div class="col-md-6">
    <label for="authorization_number" class="form-label">N° d'autorisation / enregistrement</label>
    <input type="text" id="authorization_number" name="authorization_number" class="form-control"
           value="{{ old('authorization_number', $school->authorization_number) }}">
</div>
<div class="col-md-6">
    <label for="director_name" class="form-label">Proviseur / Directeur</label>
    <input type="text" id="director_name" name="director_name" class="form-control" value="{{ old('director_name', $school->director_name) }}">
</div>
<div class="col-md-6">
    <label for="deputy_director_name" class="form-label">Censeur / Adjoint</label>
    <input type="text" id="deputy_director_name" name="deputy_director_name" class="form-control"
           value="{{ old('deputy_director_name', $school->deputy_director_name) }}">
</div>

<hr class="my-2">

<div class="col-md-6">
    <label for="email" class="form-label">Email de l'établissement</label>
    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $school->email) }}">
</div>
<div class="col-md-6">
    <label for="secretariat_email" class="form-label">Email secrétariat</label>
    <input type="email" id="secretariat_email" name="secretariat_email" class="form-control"
           value="{{ old('secretariat_email', $school->secretariat_email) }}">
</div>
<div class="col-md-4">
    <label for="phone" class="form-label">Téléphone principal</label>
    <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $school->phone) }}">
</div>
<div class="col-md-4">
    <label for="phone_secondary" class="form-label">Second téléphone</label>
    <input type="text" id="phone_secondary" name="phone_secondary" class="form-control"
           value="{{ old('phone_secondary', $school->phone_secondary) }}">
</div>
<div class="col-md-4">
    <label for="whatsapp" class="form-label">WhatsApp</label>
    <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="{{ old('whatsapp', $school->whatsapp) }}">
</div>
<div class="col-12">
    <label for="website" class="form-label">Site web</label>
    <input type="url" id="website" name="website" class="form-control @error('website') is-invalid @enderror"
           value="{{ old('website', $school->website) }}" placeholder="https://...">
    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<hr class="my-2">

<div class="col-md-3">
    <label for="region" class="form-label">Région</label>
    <input type="text" id="region" name="region" class="form-control" value="{{ old('region', $school->region) }}">
</div>
<div class="col-md-3">
    <label for="department" class="form-label">Département</label>
    <input type="text" id="department" name="department" class="form-control" value="{{ old('department', $school->department) }}">
</div>
<div class="col-md-3">
    <label for="city" class="form-label">Ville / commune</label>
    <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $school->city) }}">
</div>
<div class="col-md-3">
    <label for="district" class="form-label">Quartier / zone</label>
    <input type="text" id="district" name="district" class="form-control" value="{{ old('district', $school->district) }}">
</div>
<div class="col-12">
    <label for="address" class="form-label">Adresse complète</label>
    <textarea id="address" name="address" class="form-control" rows="2">{{ old('address', $school->address) }}</textarea>
</div>

<hr class="my-2">

<div class="col-12">
    <label for="description" class="form-label">Présentation de l'établissement</label>
    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $school->description) }}</textarea>
</div>
<div class="col-12">
    <label for="secretariat_hours" class="form-label">Horaires d'accueil du secrétariat</label>
    <textarea id="secretariat_hours" name="secretariat_hours" class="form-control" rows="2">{{ old('secretariat_hours', $school->secretariat_hours) }}</textarea>
</div>

@if($academicYears->isNotEmpty())
    <div class="col-md-4">
        <label for="default_academic_year_id" class="form-label">Année scolaire par défaut</label>
        <select id="default_academic_year_id" name="default_academic_year_id" class="form-select">
            <option value="">— Année courante —</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected(old('default_academic_year_id', $school->default_academic_year_id) == $year->id)>
                    {{ $year->name }}@if($year->is_current) (courante)@endif
                </option>
            @endforeach
        </select>
    </div>
@endif
<div class="col-md-4">
    <label for="timezone" class="form-label">Fuseau horaire</label>
    <select id="timezone" name="timezone" class="form-select">
        @foreach(\App\Models\School::TIMEZONES as $value => $label)
            <option value="{{ $value }}" @selected(old('timezone', $school->timezone ?? 'Africa/Dakar') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-4">
    <label for="locale" class="form-label">Langue d'affichage</label>
    <select id="locale" name="locale" class="form-select">
        @foreach(\App\Models\School::LOCALES as $value => $label)
            <option value="{{ $value }}" @selected(old('locale', $school->locale ?? 'fr') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
