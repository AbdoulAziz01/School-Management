@extends('platform.layouts.app')

@section('title', $school->name . ' — ' . $platformName)

@push('styles')
<style>
    .platform-school-stat {
        display: block;
        text-decoration: none;
        color: inherit;
        height: 100%;
        border-radius: 0.5rem;
        transition: transform 0.15s ease, background-color 0.15s ease;
    }
    .platform-school-stat:hover {
        transform: translateY(-2px);
        background-color: rgba(79, 70, 229, 0.06);
        color: inherit;
    }
</style>
@endpush

@section('content')
@php $schoolLogo = \App\Support\SchoolLogoStorage::dataUri($school); @endphp

@include('platform.schools._staff-credentials-alert')

<p class="text-muted small mb-3">
    <a href="{{ route('platform.schools.index') }}" class="text-decoration-none">
        <i class="fas fa-arrow-left me-1"></i> Retour aux établissements
    </a>
</p>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <div class="border rounded bg-light d-flex align-items-center justify-content-center" style="width:64px;height:64px;overflow:hidden;">
            @if($schoolLogo)
                <img src="{{ $schoolLogo }}" alt="Logo" class="img-fluid" style="max-height:60px;">
            @else
                <i class="fas fa-school fa-2x text-muted"></i>
            @endif
        </div>
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h1 class="h3 mb-0">{{ $school->name }}</h1>
                @if($school->hasEstablishmentType())
                    <span class="badge {{ $school->establishmentTypeBadgeClass() }}">
                        {{ $school->establishmentTypeLabel() }}
                    </span>
                    @if($school->isFormation() && ! $school->usesLmdGrading())
                        <span class="badge bg-light text-dark border">Sans LMD</span>
                    @endif
                @else
                    <span class="badge bg-danger">Type non défini</span>
                @endif
            </div>
            @if($school->establishmentTypeDescription())
                <p class="text-muted small mb-1">{{ $school->establishmentTypeDescription() }}</p>
            @elseif(! $school->hasEstablishmentType())
                <p class="text-warning small mb-1">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Renseignez le type d'établissement via
                    <a href="{{ route('platform.schools.edit', $school) }}">Modifier</a>
                    pour activer les bons niveaux et classes par défaut.
                </p>
            @endif
            <p class="text-muted mb-0 small">
                ID <code>{{ $school->id }}</code> · Slug <code>{{ $school->slug }}</code>
                · Créé le {{ $school->created_at?->format('d/m/Y à H:i') }}
                @if($school->updated_at)
                    · Modifié {{ $school->updated_at->diffForHumans() }}
                @endif
            </p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
        <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-pen me-1"></i> Modifier
        </a>
        <form method="POST" action="{{ route('platform.schools.toggle-active', $school) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $school->is_active ? 'btn-warning' : 'btn-success' }}">
                {{ $school->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
    </div>
</div>

@if(!empty($healthAlerts))
    <div class="mb-4">
        @foreach($healthAlerts as $alert)
            @if(!empty($alert['href']))
                <a href="{{ $alert['href'] }}" class="alert alert-{{ $alert['type'] }} py-2 mb-2 d-flex align-items-center gap-2 text-decoration-none">
                    <span>{{ $alert['message'] }}</span>
                    <i class="fas fa-arrow-down ms-auto small opacity-75"></i>
                </a>
            @else
                <div class="alert alert-{{ $alert['type'] }} py-2 mb-2">{{ $alert['message'] }}</div>
            @endif
        @endforeach
    </div>
@endif

@if($unassignedStudents->isNotEmpty())
    <div id="unassigned-students" class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="fas fa-user-slash text-warning me-1"></i>
                Élèves sans classe ({{ $unassignedStudents->count() }})
            </h5>
            @if($currentAcademicYear)
                <small class="text-muted">
                    Affectation autorisée — année
                    <span class="badge bg-{{ $currentAcademicYear->statusBadgeClass() }}">{{ $currentAcademicYear->statusLabel() }}</span>
                    {{ $currentAcademicYear->name }}
                </small>
            @else
                <small class="text-warning">Aucune année courante — affectation désactivée.</small>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Identifiant</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th style="min-width: 280px;">Affecter à une classe</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unassignedStudents as $student)
                        <tr>
                            <td><code>{{ $student->identifier ?? $student->user_id ?? '—' }}</code></td>
                            <td class="fw-semibold">{{ $student->name }}</td>
                            <td><small>{{ $student->email ?? '—' }}</small></td>
                            <td>
                                @if($student->status === 'approved')
                                    <span class="badge bg-success">Approuvé</span>
                                @elseif($student->status === 'pending')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @else
                                    <span class="badge bg-secondary">{{ $student->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($assignableClasses->isEmpty())
                                    @if(! $currentAcademicYear)
                                        <span class="text-warning small">Aucune année scolaire courante — affectation impossible.</span>
                                    @elseif($currentAcademicYear->isClosed())
                                        <span class="text-warning small">L'année « {{ $currentAcademicYear->name }} » est terminée — affectation impossible.</span>
                                    @else
                                        <span class="text-warning small">Aucune classe pour l'année courante « {{ $currentAcademicYear->name }} ».</span>
                                    @endif
                                @else
                                    <form method="POST"
                                          action="{{ route('platform.schools.students.assign-class', [$school, $student]) }}"
                                          class="d-flex flex-wrap gap-2 align-items-center">
                                        @csrf
                                        <select name="class_id" class="form-select form-select-sm" style="min-width: 200px;" required>
                                            <option value="">Classe ({{ $currentAcademicYear?->name }})…</option>
                                            @foreach($assignableClasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                                            <i class="fas fa-check me-1"></i> Affecter
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($school->isFormation())
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <strong><i class="fas fa-graduation-cap me-1"></i> École de formation professionnelle</strong>
        <p class="mb-0 small">
            Structure : <strong>Promotions</strong> (filière, année, groupes) → <strong>Modules</strong>.
            @if($school->usesLmdGrading())
                Calcul des moyennes : <strong>LMD</strong> (CC + examen).
            @else
                Calcul des moyennes : <strong>classique</strong> (devoirs + composition), sans LMD.
            @endif
        </p>
    </div>
@endif

@if($academicYears->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fas fa-calendar-alt me-1"></i> Années scolaires</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Année</th>
                    <th>Statut</th>
                    <th>Période</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($academicYears as $year)
                    <tr>
                        <td class="fw-semibold">{{ $year->name }}</td>
                        <td><span class="badge bg-{{ $year->statusBadgeClass() }}">{{ $year->statusLabel() }}</span></td>
                        <td class="small text-muted">{{ $year->periodLabel() }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => 'classes', 'academic_year_id' => $year->id]) }}"
                               class="btn btn-sm btn-outline-primary">Classes</a>
                            <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => 'students', 'academic_year_id' => $year->id]) }}"
                               class="btn btn-sm btn-outline-secondary">Élèves</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Identité & direction</h6></div>
            <div class="card-body small">
                <dl class="mb-0">
                    <dt class="text-muted">Type d'établissement</dt>
                    <dd class="mb-2">
                        @if($school->hasEstablishmentType())
                            <span class="badge {{ $school->establishmentTypeBadgeClass() }}">
                                {{ $school->establishmentTypeLabel() }}
                            </span>
                            @if($school->isFormation() && ! $school->usesLmdGrading())
                                <span class="badge bg-light text-dark border ms-1">Formation sans LMD</span>
                            @endif
                        @else
                            <span class="text-danger">Non défini</span>
                            <a href="{{ route('platform.schools.edit', $school) }}" class="small ms-1">Définir</a>
                        @endif
                    </dd>
                    @if($school->establishmentTypeDescription())
                        <dt class="text-muted">Parcours scolaire</dt>
                        <dd class="mb-0">{{ $school->establishmentTypeDescription() }}</dd>
                    @endif
                    @if($school->motto)
                        <dt class="text-muted">Devise</dt>
                        <dd class="fst-italic">« {{ $school->motto }} »</dd>
                    @endif
                    @if($school->authorization_number)
                        <dt class="text-muted">N° autorisation</dt>
                        <dd>{{ $school->authorization_number }}</dd>
                    @endif
                    <dt class="text-muted">Proviseur / Directeur</dt>
                    <dd>{{ $school->director_name ?? '—' }}</dd>
                    <dt class="text-muted">Censeur / Adjoint</dt>
                    <dd>{{ $school->deputy_director_name ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Coordonnées</h6></div>
            <div class="card-body small">
                <dl class="mb-0">
                    <dt class="text-muted">Email</dt>
                    <dd>{{ $school->email ?? '—' }}</dd>
                    <dt class="text-muted">Secrétariat</dt>
                    <dd>{{ $school->secretariat_email ?? '—' }}</dd>
                    <dt class="text-muted">Téléphone</dt>
                    <dd>{{ $school->phone ?? '—' }}</dd>
                    @if($school->whatsapp)
                        <dt class="text-muted">WhatsApp</dt>
                        <dd>{{ $school->whatsapp }}</dd>
                    @endif
                    @if($school->website)
                        <dt class="text-muted">Site web</dt>
                        <dd><a href="{{ $school->website }}" target="_blank" rel="noopener">{{ $school->website }}</a></dd>
                    @endif
                    <dt class="text-muted">Adresse</dt>
                    <dd>{{ collect([$school->address, $school->district, $school->city, $school->department, $school->region])->filter()->implode(', ') ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Paramètres</h6></div>
            <div class="card-body small">
                <dl class="mb-0">
                    <dt class="text-muted">Fuseau horaire</dt>
                    <dd>{{ \App\Models\School::TIMEZONES[$school->timezone] ?? $school->timezone ?? '—' }}</dd>
                    <dt class="text-muted">Langue</dt>
                    <dd>{{ \App\Models\School::LOCALES[$school->locale] ?? $school->locale ?? '—' }}</dd>
                    <dt class="text-muted">Code inscription</dt>
                    <dd><code>{{ $school->code }}</code></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@if($school->description || $school->secretariat_hours)
<div class="row g-3 mb-4">
    @if($school->description)
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Présentation</h6></div>
                <div class="card-body small">{{ $school->description }}</div>
            </div>
        </div>
    @endif
    @if($school->secretariat_hours)
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Horaires secrétariat</h6></div>
                <div class="card-body small">{{ $school->secretariat_hours }}</div>
            </div>
        </div>
    @endif
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Santé de l'établissement</h6></div>
            <div class="card-body">
                @php
                    $healthStats = [
                        ['key' => 'students', 'value' => $school->students_count, 'label' => 'Élèves', 'class' => ''],
                        ['key' => 'teachers', 'value' => $school->teachers_count, 'label' => 'Profs', 'class' => ''],
                        ['key' => 'staff', 'value' => $school->staff_count, 'label' => 'Staff', 'class' => ''],
                        ['key' => 'classes', 'value' => $school->classes_count, 'label' => 'Classes', 'class' => ''],
                        ['key' => 'subjects', 'value' => $subjectsCount, 'label' => 'Matières', 'class' => ''],
                        ['key' => 'pending', 'value' => $school->pending_count, 'label' => 'En attente', 'class' => $school->pending_count ? 'text-danger' : ''],
                    ];
                @endphp
                <div class="row g-3 text-center">
                    @foreach($healthStats as $stat)
                        <div class="col-4 col-md-2">
                            <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => $stat['key']]) }}"
                               class="platform-school-stat d-block py-2 px-1">
                                <div class="h4 mb-0 {{ $stat['class'] }}">{{ $stat['value'] }}</div>
                                <small class="text-muted">{{ $stat['label'] }}</small>
                            </a>
                        </div>
                    @endforeach
                </div>
                <hr>
                <div class="row small">
                    <div class="col-md-6">
                        <strong>Année scolaire courante :</strong>
                        @if($currentAcademicYear)
                            <span class="badge bg-{{ $currentAcademicYear->statusBadgeClass() }}">{{ $currentAcademicYear->statusLabel() }}</span>
                            {{ $currentAcademicYear->name }}
                            <span class="text-muted">({{ $currentAcademicYear->periodLabel() }})</span>
                        @else
                            <span class="text-warning">Non configurée</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <strong>Administrateur :</strong>
                        @if($school->admins_count > 0)
                            <span class="text-success">{{ $school->admins_count }} compte(s)</span>
                            @if($school->surveillants_count > 0)
                                · {{ $school->surveillants_count }} surveillant(s)
                            @endif
                        @else
                            <span class="text-danger">Aucun — à créer ci-dessous</span>
                        @endif
                    </div>
                    <div class="col-md-6 mt-2">
                        <strong>Élèves sans classe :</strong>
                        @if($school->unassigned_students_count > 0)
                            <a href="#unassigned-students" class="text-warning">{{ $school->unassigned_students_count }}</a>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </div>
                    <div class="col-md-6 mt-2">
                        <strong>Connexion admin établissement :</strong>
                        <span class="text-muted">Page <code>/login</code> avec identifiant ADM…</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Logo (stocké en BDD)</h6>
                <form method="POST" action="{{ route('platform.schools.update', $school) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $school->name }}">
                    <input type="hidden" name="is_active" value="{{ $school->is_active ? '1' : '0' }}">
                    <input type="file" name="logo" class="form-control form-control-sm mb-2" accept="image/*">
                    @if($school->logo_data)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                            <label class="form-check-label" for="remove_logo">Supprimer le logo</label>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Mettre à jour le logo</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Code d'inscription</h6>
                <p class="h4"><code>{{ $school->code }}</code></p>
                <form method="POST" action="{{ route('platform.schools.regenerate-code', $school) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Régénérer le code ?')">Régénérer</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="h2 mb-1">{{ $school->users_count }}</div>
                <p class="text-muted mb-2">utilisateurs au total</p>
                @if($school->is_active)
                    <span class="badge bg-success">Établissement actif</span>
                @else
                    <span class="badge bg-secondary">Établissement inactif</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Admin & surveillants (table users)</h5>
                <span class="badge bg-secondary">{{ $staffMembers->count() }} compte(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th class="text-end" style="min-width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffMembers as $member)
                            <tr>
                                <td><code class="user-select-all">{{ $member->identifier }}</code></td>
                                <td>{{ $member->name }}</td>
                                <td><small>{{ $member->email }}</small></td>
                                <td>
                                    @if($member->role === 'admin')
                                        <span class="badge bg-primary">Admin</span>
                                    @else
                                        <span class="badge bg-info text-dark">Surveillant</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST"
                                          action="{{ route('platform.schools.admins.reset-password', [$school, $member]) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Générer un nouveau mot de passe pour {{ $member->name }} ?\n\nL\'ancien mot de passe ne fonctionnera plus.');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Nouveau mot de passe à 6 chiffres">
                                            <i class="fas fa-sync-alt me-1"></i> Régénérer le mot de passe
                                        </button>
                                    </form>
                                    @if($member->invitation_email_sent_at)
                                        <div class="small text-muted mt-1">
                                            Dernier envoi : {{ $member->invitation_email_sent_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center py-3">Aucun compte.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($staffMembers->isNotEmpty())
                <div class="card-footer bg-white small text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    « Régénérer le mot de passe » affiche le nouveau code en haut de page pour le transmettre à l'admin ou au surveillant (email envoyé si la messagerie est configurée).
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0">Ajouter admin ou surveillant</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.schools.admins.store', $school) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Rôle</label>
                        <select name="staff_role" class="form-select" required>
                            <option value="admin">Administrateur (ADM…)</option>
                            <option value="surveillant">Surveillant (SUR…)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nom</label>
                        <input type="text" name="admin_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="admin_email" class="form-control" required>
                        <div class="form-text">L’identifiant et le mot de passe s’afficheront ici après la création du compte.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Créer le compte</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
