@extends('platform.layouts.app')

@section('title', 'Établissements — ' . $platformName)

@push('styles')
<style>
    /* ── Carte filtre ── */
    .filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.125rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }

    /* ═══════════════════════════════════════════
       CARDS MOBILE (masquées sur md+)
    ═══════════════════════════════════════════ */
    .school-mobile-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem 1.125rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 4px rgba(15,23,42,.06);
    }
    .school-mobile-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: #0f172a;
        margin: 0 0 2px;
        line-height: 1.35;
    }
    .school-mobile-date { font-size: 0.68rem; color: #94a3b8; }

    .school-mobile-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        margin-top: 0.625rem;
    }
    .smp {                                   /* school-mobile-pill */
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.22rem 0.55rem;
        white-space: nowrap;
    }
    .smp i { font-size: 0.6rem; color: #94a3b8; }
    .smp.danger { color:#dc2626; background:rgba(239,68,68,.06); border-color:rgba(239,68,68,.25); }
    .smp.danger i { color:#ef4444; }

    .school-mobile-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.875rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }
    .school-mobile-actions .btn {
        flex: 1;
        font-size: 0.78rem;
        padding: 0.4rem 0.5rem;
        border-radius: 0.625rem;
    }

    /* Vide state mobile */
    .empty-mobile {
        text-align: center;
        padding: 3rem 1rem;
        color: #94a3b8;
    }
    .empty-mobile i { font-size: 2rem; display: block; margin-bottom: 0.75rem; color: #cbd5e1; }

    /* ── Badge statut inline ── */
    .badge-actif   { background:rgba(16,185,129,.14); color:#059669; }
    .badge-inactif { background:rgba(100,116,139,.12); color:#475569; }
</style>
@endpush

@section('content')

{{-- ── Page header ─────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h4 mb-0 fw-bold" style="color:#0f172a;">Établissements</h1>
    <a href="{{ route('platform.schools.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>
        <span class="d-none d-sm-inline">Créer un établissement</span>
        <span class="d-sm-none">Créer</span>
    </a>
</div>

{{-- ── Filtres ──────────────────────────────────── --}}
<form method="GET" class="filter-card">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label small fw-semibold mb-1" style="color:#64748b;">Recherche</label>
            <input type="search" name="q" class="form-control form-control-sm"
                   placeholder="Nom, code, ville…" value="{{ request('q') }}">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small fw-semibold mb-1" style="color:#64748b;">Filtre</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Tous les établissements</option>
                <option value="active"           @selected(request('status') === 'active')>Actifs uniquement</option>
                <option value="inactive"         @selected(request('status') === 'inactive')>Inactifs uniquement</option>
                <option value="no_admin"         @selected(request('status') === 'no_admin')>Sans administrateur</option>
                <option value="pending"          @selected(request('status') === 'pending')>Avec inscriptions en attente</option>
                <option value="no_current_year"  @selected(request('status') === 'no_current_year')>Sans année scolaire courante</option>
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="fas fa-search me-1"></i>Filtrer
            </button>
            <a href="{{ route('platform.schools.index') }}" class="btn btn-outline-secondary btn-sm px-3"
               title="Réinitialiser">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </div>
</form>

{{-- ═══════════  MOBILE : cartes  (caché sur md+)  ═══════════ --}}
<div class="d-md-none">
    @forelse($schools as $school)
    <div class="school-mobile-card">

        {{-- Nom + badges statut/type --}}
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="min-w-0">
                <p class="school-mobile-name">{{ $school->name }}</p>
                <span class="school-mobile-date">Créé {{ $school->created_at?->format('d/m/Y') }}</span>
            </div>
            <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                <span class="badge {{ $school->is_active ? 'badge-actif' : 'badge-inactif' }}"
                      style="font-size:.63rem;font-weight:700;">
                    {{ $school->is_active ? 'Actif' : 'Inactif' }}
                </span>
                @if($school->hasEstablishmentType())
                    <span class="badge {{ $school->establishmentTypeBadgeClass() }}" style="font-size:.63rem;">
                        {{ $school->establishmentTypeLabel() }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Localisation + code + année --}}
        <div class="school-mobile-pills">
            @if($school->city)
                <span class="smp"><i class="fas fa-map-marker-alt"></i>{{ $school->city }}</span>
            @endif
            <span class="smp">
                <i class="fas fa-tag"></i>
                <code style="font-size:.68rem;background:none;padding:0;color:inherit;">{{ $school->code }}</code>
            </span>
            @if(!empty($currentYears[$school->id]))
                <span class="smp"><i class="fas fa-calendar-check"></i>{{ $currentYears[$school->id] }}</span>
            @endif
        </div>

        {{-- Stats --}}
        <div class="school-mobile-pills">
            <span class="smp"><i class="fas fa-user-graduate"></i>{{ $school->students_count }} élèves</span>
            <span class="smp"><i class="fas fa-chalkboard-teacher"></i>{{ $school->teachers_count }} profs</span>
            <span class="smp"><i class="fas fa-door-open"></i>{{ $school->classes_count }} classes</span>
            @if($school->admins_count === 0)
                <span class="smp danger"><i class="fas fa-exclamation-triangle"></i>Sans admin</span>
            @endif
            @if($school->pending_count > 0)
                <span class="smp danger"><i class="fas fa-clock"></i>{{ $school->pending_count }} en attente</span>
            @endif
        </div>

        {{-- Actions --}}
        <div class="school-mobile-actions">
            <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-primary">
                <i class="fas fa-eye me-1"></i>Gérer
            </a>
            <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-outline-secondary">
                <i class="fas fa-edit me-1"></i>Modifier
            </a>
        </div>
    </div>
    @empty
        <div class="empty-mobile">
            <i class="fas fa-school"></i>
            Aucun établissement trouvé.
        </div>
    @endforelse

    @if($schools->hasPages())
        <div class="mt-3">{{ $schools->links() }}</div>
    @endif
</div>

{{-- ═══════════  DESKTOP : tableau  (caché sur xs/sm)  ═══════════ --}}
<div class="card border-0 shadow-sm d-none d-md-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle table-sm">
            <thead class="table-light">
                <tr>
                    <th>Établissement</th>
                    <th>Type</th>
                    <th>Ville</th>
                    <th>Code</th>
                    <th>Élèves</th>
                    <th>Profs</th>
                    <th>Classes</th>
                    <th>En attente</th>
                    <th>Admin</th>
                    <th>Année courante</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schools as $school)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $school->name }}</div>
                            <small class="text-muted">Créé {{ $school->created_at?->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            @if($school->hasEstablishmentType())
                                <span class="badge {{ $school->establishmentTypeBadgeClass() }}">
                                    {{ $school->establishmentTypeLabel() }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $school->city ?? '—' }}</td>
                        <td><code>{{ $school->code }}</code></td>
                        <td>{{ $school->students_count }}</td>
                        <td>{{ $school->teachers_count }}</td>
                        <td>{{ $school->classes_count }}</td>
                        <td>
                            @if($school->pending_count > 0)
                                <span class="badge bg-danger">{{ $school->pending_count }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td>
                            @if($school->admins_count > 0)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-danger">Non</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($currentYears[$school->id]))
                                <small>{{ $currentYears[$school->id] }}</small>
                            @else
                                <span class="text-warning small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($school->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('platform.schools.show', $school) }}" class="btn btn-sm btn-outline-primary">Gérer</a>
                            <a href="{{ route('platform.schools.edit', $school) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-muted py-5">Aucun établissement trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($schools->hasPages())
        <div class="card-footer bg-white">{{ $schools->links() }}</div>
    @endif
</div>

@endsection
