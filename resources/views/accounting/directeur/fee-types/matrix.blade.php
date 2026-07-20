@extends('admin.layouts.app', ['sidebarView' => 'accounting.directeur.sidebar', 'navbarView' => 'accounting.directeur.navbar'])

@section('title', 'Frais scolaires')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-table me-2"></i>Frais scolaires</h1>
        <p class="text-muted mb-0">
            Grille tarifaire par niveau{{ $academicYear ? ' — année '.$academicYear->name : '' }}.
            Combien paie un élève de cette classe ?
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('directeur.fee-types.types') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-cog me-1"></i> Gérer les types de frais
        </a>
        <a href="{{ route('directeur.fee-types.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nouveau type de frais
        </a>
    </div>
</div>

@if(! $academicYear)
    <div class="alert alert-warning mb-0">
        <i class="fas fa-exclamation-triangle me-1"></i>
        Aucune année scolaire courante — impossible d'afficher ou de modifier la grille tarifaire.
    </div>
@elseif($feeTypes->isEmpty())
    <div class="alert alert-info mb-0">
        Aucun type de frais configuré.
        <a href="{{ route('directeur.fee-types.create') }}">Commencez par ajouter l'inscription et la mensualité</a>.
    </div>
@elseif($levels->isEmpty())
    <div class="alert alert-info mb-0">Aucun niveau configuré pour cet établissement.</div>
@else

{{-- Barre d'outils : recherche + filtre cycle (JS simple, purement cosmétique) --}}
<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <div style="display:flex;align-items:center;gap:.5rem;padding:.4rem .75rem;background:#ffffff;border:1px solid #fde68a;border-radius:.5rem;">
        <label for="fee-grid-search" class="visually-hidden">Rechercher une classe</label>
        <i class="fas fa-search" style="color:#d97706;font-size:.85rem;"></i>
        <input id="fee-grid-search" type="text" placeholder="Rechercher une classe..." autocomplete="off"
               style="background:transparent;border:none;outline:none;min-width:220px;color:#44403c;">
    </div>
    <div>
        <label for="fee-grid-cycle" class="visually-hidden">Filtrer par cycle</label>
        <select id="fee-grid-cycle" style="padding:.5rem .75rem;background:#ffffff;border:1px solid #fde68a;border-radius:.5rem;color:#44403c;">
            <option value="">Tous les cycles</option>
            @foreach($levels->pluck('cycle')->unique()->filter() as $cycle)
                <option value="{{ $cycle }}">{{ $levels->firstWhere('cycle', $cycle)->cycleLabel() }}</option>
            @endforeach
        </select>
    </div>
    <span class="ms-auto small text-muted"><span id="fee-grid-count">{{ $levels->count() }}</span> classe(s)</span>
</div>

{{-- Tableau croisé — couleurs écrites en style inline pour être certain qu'elles s'appliquent --}}
<div style="background:#ffffff;border:1px solid #fde68a;border-radius:.75rem;overflow:hidden;box-shadow:0 1px 3px rgba(217,119,6,.08);">
    <div style="max-height:65vh;overflow-y:auto;">
        <table id="fee-grid-table" style="width:100%;border-collapse:collapse;color:#44403c;">
            <thead>
                <tr>
                    <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">Classe</th>
                    @foreach($feeTypes as $feeType)
                        <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">{{ $feeType->name }}</th>
                    @endforeach
                    <th style="position:sticky;top:0;background:#fef3c7;text-align:left;font-weight:700;font-size:.95rem;color:#92400e;padding:1.1rem 1.25rem;border-bottom:2px solid #fde68a;white-space:nowrap;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($levels as $level)
                    <tr data-name="{{ mb_strtolower($level->name) }}" data-cycle="{{ $level->cycle }}" style="background:#ffffff;">
                        <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;font-weight:600;color:#1c1917;">{{ $level->name }}</td>
                        @foreach($feeTypes as $feeType)
                            <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;color:#57534e;">{{ $displayGrid[$level->id][$feeType->id] !== null ? number_format($displayGrid[$level->id][$feeType->id], 0, ',', ' ').' FCFA' : '—' }}</td>
                        @endforeach
                        <td style="padding:1rem 1.25rem;border-bottom:1px solid #fef3c7;white-space:nowrap;">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#feeModal{{ $level->id }}" style="color:#d97706;text-decoration:none;font-weight:600;margin-right:1rem;">Modifier</a>
                            <a href="{{ route('directeur.fee-types.levels.history', $level) }}" style="color:#d97706;text-decoration:none;font-weight:600;">Historique</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Un modal par niveau, entièrement rendu par Blade (aucune dépendance JS pour la soumission) --}}
@foreach($levels as $level)
    <div class="modal fade" id="feeModal{{ $level->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('directeur.fee-types.levels.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="level_id" value="{{ $level->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-coins me-2"></i>Frais — {{ $level->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">
                            Laissez un champ vide pour désactiver ce frais pour ce niveau (il reprendra
                            automatiquement le montant « Tous niveaux » s'il existe, sinon il ne sera pas facturé).
                        </p>
                        @foreach($feeTypes as $feeType)
                            <div class="mb-3 row align-items-center">
                                <label class="col-6 col-form-label" for="fee-amount-{{ $level->id }}-{{ $feeType->id }}">
                                    {{ $feeType->name }}
                                </label>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="1" min="0" class="form-control"
                                               id="fee-amount-{{ $level->id }}-{{ $feeType->id }}"
                                               name="amounts[{{ $feeType->id }}]"
                                               value="{{ $modalValues[$level->id][$feeType->id] !== null ? (int) $modalValues[$level->id][$feeType->id] : '' }}"
                                               placeholder="Montant">
                                        <span class="input-group-text">FCFA</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@endif

@push('scripts')
<script>
(function () {
    var searchInput = document.getElementById('fee-grid-search');
    var cycleSelect = document.getElementById('fee-grid-cycle');
    var countEl = document.getElementById('fee-grid-count');
    var rows = document.querySelectorAll('#fee-grid-table tbody tr');

    function applyFilters() {
        var search = (searchInput.value || '').toLowerCase().trim();
        var cycle = cycleSelect.value;
        var visible = 0;

        rows.forEach(function (row) {
            var matchesSearch = !search || row.dataset.name.indexOf(search) !== -1;
            var matchesCycle = !cycle || row.dataset.cycle === cycle;
            var show = matchesSearch && matchesCycle;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (countEl) countEl.textContent = visible;
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (cycleSelect) cycleSelect.addEventListener('change', applyFilters);
})();
</script>
@endpush
@endsection
