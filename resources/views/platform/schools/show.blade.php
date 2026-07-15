@extends('platform.layouts.app')

@section('title', $school->name . ' — ' . $platformName)

@push('styles')
<style>
    /* ── Stat cliquable santé ── */
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
        background-color: rgba(234, 88, 12, 0.06);
        color: inherit;
    }

    /* ── En-tête école mobile ── */
    @media (max-width: 575.98px) {
        .school-header-logo {
            width: 48px !important;
            height: 48px !important;
        }
        .school-header-logo i { font-size: 1.25rem !important; }
        .school-header-name { font-size: 1.1rem !important; }
        .school-header-meta { font-size: 0.7rem !important; }
    }

    /* ── Cartes staff mobile ── */
    .staff-mobile-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.875rem;
        padding: 0.875rem 1rem;
        margin-bottom: 0.625rem;
    }
    .staff-mobile-card .staff-id   { font-size: 0.75rem; color: #475569; }
    .staff-mobile-card .staff-name { font-weight: 700; font-size: 0.9rem; color: #0f172a; }
    .staff-mobile-card .staff-email{ font-size: 0.72rem; color: #64748b; word-break: break-all; }
    .staff-mobile-pwd {
        background: #fff7ed;
        border: 1px solid rgba(234,88,12,.2);
        border-radius: 0.5rem;
        padding: 0.35rem 0.625rem;
        margin-top: 0.5rem;
        font-size: 0.78rem;
    }
    .staff-mobile-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }
    .staff-mobile-actions .btn { flex: 1; font-size: 0.75rem; padding: 0.35rem 0.5rem; border-radius: 0.5rem; }
</style>
@endpush

@section('content')
@php $schoolLogo = \App\Support\SchoolLogoStorage::dataUri($school); @endphp

@include('platform.schools._staff-credentials-alert')
@include('platform.schools._school-login-credentials', ['loginCredentials' => $loginCredentials ?? null, 'school' => $school])

{{-- ── Fil d'ariane ── --}}
<p class="text-muted small mb-3">
    <a href="{{ route('platform.schools.index') }}" class="text-decoration-none">
        <i class="fas fa-arrow-left me-1"></i> Retour aux établissements
    </a>
</p>

{{-- ══════════════════  EN-TÊTE ÉCOLE  ══════════════════ --}}
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
    {{-- Logo + Infos --}}
    <div class="d-flex align-items-start gap-3 min-w-0">
        <div class="border rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0 school-header-logo"
             style="width:64px;height:64px;overflow:hidden;">
            @if($schoolLogo)
                <img src="{{ $schoolLogo }}" alt="Logo" class="img-fluid" style="max-height:60px;">
            @else
                <i class="fas fa-school fa-2x text-muted"></i>
            @endif
        </div>
        <div class="min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h1 class="h4 mb-0 school-header-name" style="color:#0f172a;">{{ $school->name }}</h1>
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
                    Renseignez le type via
                    <a href="{{ route('platform.schools.edit', $school) }}">Modifier</a>.
                </p>
            @endif
            <p class="text-muted mb-0 school-header-meta" style="font-size:.8rem;">
                ID <code>{{ $school->id }}</code> · Slug <code>{{ $school->slug }}</code>
                · Créé {{ $school->created_at?->format('d/m/Y à H:i') }}
                @if($school->updated_at)
                    · Modifié {{ $school->updated_at->diffForHumans() }}
                @endif
            </p>
        </div>
    </div>
    {{-- Boutons action --}}
    <div class="d-flex flex-wrap gap-2 flex-shrink-0">
        <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Retour</span>
        </a>
        <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-pen me-1"></i> Modifier
        </a>
        <form method="POST" action="{{ route('platform.schools.toggle-active', $school) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $school->is_active ? 'btn-warning' : 'btn-success' }}">
                {{ $school->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
        @php $accountingEnabled = \App\Support\SchoolModules::isEnabled($school, \App\Support\SchoolModules::ACCOUNTING); @endphp
        @if($accountingEnabled || $school->isPrivateEstablishment())
            <form method="POST" action="{{ route('platform.schools.toggle-accounting-module', $school) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm {{ $accountingEnabled ? 'btn-outline-warning' : 'btn-outline-success' }}">
                    <i class="fas fa-coins me-1"></i>{{ $accountingEnabled ? 'Désactiver' : 'Activer' }} Comptabilité
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Alertes de santé --}}
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

{{-- ══════════  ÉLÈVES SANS CLASSE  ══════════ --}}
@if($unassignedStudents->isNotEmpty())
    <div id="unassigned-students" class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fs-6">
                <i class="fas fa-user-slash text-warning me-1"></i>
                Élèves sans classe ({{ $unassignedStudents->count() }})
            </h5>
            @if($currentAcademicYear)
                <small class="text-muted">
                    Année <span class="badge bg-{{ $currentAcademicYear->statusBadgeClass() }}">{{ $currentAcademicYear->statusLabel() }}</span>
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
                        <th class="d-none d-md-table-cell">Email</th>
                        <th>Statut</th>
                        <th>Affecter</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unassignedStudents as $student)
                        <tr>
                            <td><code>{{ $student->identifier ?? $student->user_id ?? '—' }}</code></td>
                            <td class="fw-semibold">{{ $student->name }}</td>
                            <td class="d-none d-md-table-cell"><small>{{ $student->email ?? '—' }}</small></td>
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
                                        <span class="text-warning small">Aucune année courante.</span>
                                    @elseif($currentAcademicYear->isClosed())
                                        <span class="text-warning small">Année terminée.</span>
                                    @else
                                        <span class="text-warning small">Aucune classe disponible.</span>
                                    @endif
                                @else
                                    <form method="POST"
                                          action="{{ route('platform.schools.students.assign-class', [$school, $student]) }}"
                                          class="d-flex flex-wrap gap-2 align-items-center">
                                        @csrf
                                        <select name="class_id" class="form-select form-select-sm" style="min-width:140px;max-width:220px;" required>
                                            <option value="">Choisir…</option>
                                            @foreach($assignableClasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->display_name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="fas fa-check"></i>
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
            Structure : <strong>Promotions → Modules</strong>.
            @if($school->usesLmdGrading())
                Calcul : <strong>LMD</strong> (CC + examen).
            @else
                Calcul : <strong>classique</strong> (devoirs + composition).
            @endif
        </p>
    </div>
@endif

{{-- ══════════  ANNÉES SCOLAIRES  ══════════ --}}
@if($academicYears->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fas fa-calendar-alt me-1 text-muted"></i> Années scolaires</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Année</th>
                    <th>Statut</th>
                    <th class="d-none d-sm-table-cell">Période</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($academicYears as $year)
                    <tr>
                        <td class="fw-semibold">{{ $year->name }}</td>
                        <td><span class="badge bg-{{ $year->statusBadgeClass() }}">{{ $year->statusLabel() }}</span></td>
                        <td class="small text-muted d-none d-sm-table-cell">{{ $year->periodLabel() }}</td>
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

{{-- ══════════  CARTES IDENTITÉ / COORDONNÉES / PARAMÈTRES  ══════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Identité & direction</h6></div>
            <div class="card-body small">
                <dl class="mb-0">
                    <dt class="text-muted">Type d'établissement</dt>
                    <dd class="mb-2">
                        @if($school->hasEstablishmentType())
                            <span class="badge {{ $school->establishmentTypeBadgeClass() }}">{{ $school->establishmentTypeLabel() }}</span>
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
                        <dd class="mb-2">{{ $school->establishmentTypeDescription() }}</dd>
                    @endif
                    @if($school->motto)
                        <dt class="text-muted">Devise</dt>
                        <dd class="fst-italic">« {{ $school->motto }} »</dd>
                    @endif
                    @if($school->authorization_number)
                        <dt class="text-muted">N° autorisation</dt>
                        <dd>{{ $school->authorization_number }}</dd>
                    @endif
                    <dt class="text-muted">Directeur</dt>
                    <dd>{{ $school->director_name ?? '—' }}</dd>
                    <dt class="text-muted">Adjoint</dt>
                    <dd class="mb-0">{{ $school->deputy_director_name ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Coordonnées</h6></div>
            <div class="card-body small">
                <dl class="mb-0">
                    <dt class="text-muted">Email</dt>
                    <dd style="word-break:break-all;">{{ $school->email ?? '—' }}</dd>
                    <dt class="text-muted">Secrétariat</dt>
                    <dd style="word-break:break-all;">{{ $school->secretariat_email ?? '—' }}</dd>
                    <dt class="text-muted">Téléphone</dt>
                    <dd>{{ $school->phone ?? '—' }}</dd>
                    @if($school->whatsapp)
                        <dt class="text-muted">WhatsApp</dt>
                        <dd>{{ $school->whatsapp }}</dd>
                    @endif
                    @if($school->website)
                        <dt class="text-muted">Site web</dt>
                        <dd><a href="{{ $school->website }}" target="_blank" rel="noopener" style="word-break:break-all;">{{ $school->website }}</a></dd>
                    @endif
                    <dt class="text-muted">Adresse</dt>
                    <dd class="mb-0">{{ collect([$school->address, $school->district, $school->city, $school->department, $school->region])->filter()->implode(', ') ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><h6 class="mb-0">Paramètres</h6></div>
            <div class="card-body small">
                <dl class="mb-0">
                    <dt class="text-muted">Fuseau horaire</dt>
                    <dd>{{ \App\Models\School::TIMEZONES[$school->timezone] ?? $school->timezone ?? '—' }}</dd>
                    <dt class="text-muted">Langue</dt>
                    <dd>{{ \App\Models\School::LOCALES[$school->locale] ?? $school->locale ?? '—' }}</dd>
                    <dt class="text-muted">Code inscription</dt>
                    <dd class="mb-0"><code>{{ $school->code }}</code></dd>
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

{{-- ══════════  SANTÉ DE L'ÉTABLISSEMENT  ══════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0">Santé de l'établissement</h6></div>
    <div class="card-body">
        @php
            $healthStats = [
                ['key' => 'students', 'value' => $school->students_count,  'label' => 'Élèves',     'class' => ''],
                ['key' => 'teachers', 'value' => $school->teachers_count,  'label' => 'Profs',      'class' => ''],
                ['key' => 'staff',    'value' => $school->staff_count,     'label' => 'Staff',      'class' => ''],
                ['key' => 'classes',  'value' => $school->classes_count,   'label' => 'Classes',    'class' => ''],
                ['key' => 'subjects', 'value' => $subjectsCount,           'label' => 'Matières',   'class' => ''],
                ['key' => 'pending',  'value' => $school->pending_count,   'label' => 'En attente', 'class' => $school->pending_count ? 'text-danger' : ''],
            ];
        @endphp
        <div class="row g-2 text-center mb-3">
            @foreach($healthStats as $stat)
                <div class="col-4 col-md-2">
                    <a href="{{ route('platform.schools.inspection', ['school' => $school, 'section' => $stat['key']]) }}"
                       class="platform-school-stat d-block py-2 px-1 rounded">
                        <div class="h4 mb-0 {{ $stat['class'] }}">{{ $stat['value'] }}</div>
                        <small class="text-muted">{{ $stat['label'] }}</small>
                    </a>
                </div>
            @endforeach
        </div>
        <hr class="my-2">
        <div class="row g-2 small">
            <div class="col-12 col-md-6">
                <strong>Année courante :</strong>
                @if($currentAcademicYear)
                    <span class="badge bg-{{ $currentAcademicYear->statusBadgeClass() }}">{{ $currentAcademicYear->statusLabel() }}</span>
                    {{ $currentAcademicYear->name }}
                    <span class="text-muted">({{ $currentAcademicYear->periodLabel() }})</span>
                @else
                    <span class="text-warning">Non configurée</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <strong>Administrateur :</strong>
                @if($school->admins_count > 0)
                    <span class="text-success">{{ $school->admins_count }} compte(s)</span>
                    @if($school->surveillants_count > 0) · {{ $school->surveillants_count }} surveillant(s) @endif
                @else
                    <span class="text-danger">Aucun — à créer ci-dessous</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <strong>Élèves sans classe :</strong>
                @if($school->unassigned_students_count > 0)
                    <a href="#unassigned-students" class="text-warning">{{ $school->unassigned_students_count }}</a>
                @else
                    <span class="text-muted">0</span>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <strong>Connexion admin :</strong>
                <span class="text-muted">Page <code>/login</code> avec identifiant ADM…</span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════  LOGO / CODE / STATS  ══════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted mb-2">Logo (stocké en BDD)</h6>
                <form method="POST" action="{{ route('platform.schools.update', $school) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $school->name }}">
                    <input type="hidden" name="is_active" value="{{ $school->is_active ? '1' : '0' }}">
                    <input type="file" name="logo" class="form-control form-control-sm mb-2" accept="image/*">
                    @if($school->logo_data)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                            <label class="form-check-label small" for="remove_logo">Supprimer le logo</label>
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
                <h6 class="text-muted mb-2">Code d'inscription</h6>
                <p class="h4"><code>{{ $school->code }}</code></p>
                <form method="POST" action="{{ route('platform.schools.regenerate-code', $school) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Régénérer le code ?')">Régénérer</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="h2 mb-1">{{ $school->users_count }}</div>
                <p class="text-muted mb-2 small">utilisateurs au total</p>
                <span class="badge {{ $school->is_active ? 'bg-success' : 'bg-secondary' }}">
                    Établissement {{ $school->is_active ? 'actif' : 'inactif' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════  ADMIN & SURVEILLANTS  ══════════ --}}
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fs-6">Admin & surveillants</h5>
                <span class="badge bg-secondary">{{ $staffMembers->count() }} compte(s)</span>
            </div>

            {{-- ── Mobile : cartes (caché sur md+) ── --}}
            <div class="d-md-none p-3">
                @forelse($staffMembers as $member)
                @php
                    $knownPassword = collect(($loginCredentials ?? [])['staff'] ?? [])->firstWhere('email', $member->email)['password'] ?? null;
                @endphp
                <div class="staff-mobile-card">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                        @if($member->role === 'admin')
                            <span class="badge bg-primary">Admin</span>
                        @else
                            <span class="badge bg-info text-dark">Surveillant</span>
                        @endif
                        <code class="staff-id user-select-all">{{ $member->identifier }}</code>
                    </div>
                    <p class="staff-name mb-0">{{ $member->name }}</p>
                    <p class="staff-email mb-2">{{ $member->email }}</p>
                    @if($knownPassword)
                        <div class="staff-mobile-pwd">
                            <span class="text-muted" style="font-size:.7rem;font-weight:600;text-transform:uppercase;">Mot de passe</span>
                            <code class="fw-bold text-primary d-block user-select-all fs-6">{{ $knownPassword }}</code>
                        </div>
                    @endif
                    <div class="staff-mobile-actions">
                        <form method="POST"
                              action="{{ route('platform.schools.admins.reset-password', [$school, $member]) }}"
                              onsubmit="return confirm('Générer un nouveau mot de passe pour {{ $member->name }} ?');"
                              class="flex-grow-1">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-outline-warning w-100" style="font-size:.75rem;padding:.35rem .5rem;border-radius:.5rem;">
                                <i class="fas fa-sync-alt me-1"></i> Régénérer le mot de passe
                            </button>
                        </form>
                    </div>
                    @if($member->invitation_email_sent_at)
                        <div class="text-muted mt-1" style="font-size:.68rem;">
                            Dernier envoi : {{ $member->invitation_email_sent_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
                @empty
                    <p class="text-muted text-center py-3 small">Aucun compte.</p>
                @endforelse
            </div>

            {{-- ── Desktop : tableau (caché sur xs/sm) ── --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Identifiant</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Mot de passe</th>
                            <th class="text-end" style="min-width:200px;">Actions</th>
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
                                <td>
                                    @php
                                        $knownPassword = collect(($loginCredentials ?? [])['staff'] ?? [])->firstWhere('email', $member->email)['password'] ?? null;
                                    @endphp
                                    @if($knownPassword)
                                        <code class="user-select-all text-primary fw-semibold">{{ $knownPassword }}</code>
                                        <a href="#school-login-credentials" class="small d-block text-muted">Voir détail</a>
                                    @else
                                        <span class="text-muted small">—</span>
                                        <span class="d-block text-muted" style="font-size:.7rem;">Régénérer pour afficher</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST"
                                          action="{{ route('platform.schools.admins.reset-password', [$school, $member]) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Générer un nouveau mot de passe pour {{ $member->name }} ?\n\nL\'ancien mot de passe ne fonctionnera plus.');">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Nouveau mot de passe">
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
                            <tr><td colspan="6" class="text-muted text-center py-3">Aucun compte.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($staffMembers->isNotEmpty())
                <div class="card-footer bg-white small text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    « Régénérer » affiche le nouveau code en haut de page et envoie un email si la messagerie est configurée.
                </div>
            @endif
        </div>
    </div>

    {{-- Formulaire ajout admin / surveillant --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h5 class="mb-0 fs-6">Ajouter admin ou surveillant</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.schools.admins.store', $school) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Rôle</label>
                        <select name="staff_role" class="form-select form-select-sm" required>
                            <option value="admin">Administrateur (ADM…)</option>
                            <option value="surveillant">Surveillant (SUR…)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nom</label>
                        <input type="text" name="admin_name" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="admin_email" class="form-control form-control-sm" required>
                        <div class="form-text small">L'identifiant et le mot de passe s'afficheront ici après la création.</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Créer le compte</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
