{{-- Système de design partagé du portail Directeur (Centre de Commande) —
     un seul endroit pour tous les composants visuels réutilisés
     (tuiles KPI, tableaux personnes, chips, badges, pilules de filtre...),
     inclus via @include dans le @push('styles') de chaque page plutôt que
     dupliqué fichier par fichier. --}}
<style>
/* ── En-tête : badge de comptage ────────────────────────────────────── */
.count-chip {
    display: inline-flex; align-items: center;
    padding: 0.45rem 1rem; border-radius: 999px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff; font-weight: 700; font-size: 0.85rem;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.28);
}

/* ── Tuiles KPI ──────────────────────────────────────────────────────── */
.kpi-tile {
    display: flex; align-items: center; gap: 0.85rem;
    height: 100%; padding: 1.1rem 1.15rem;
    background: #fff; border: 1px solid #fde68a; border-radius: 16px;
    box-shadow: 0 2px 10px rgba(245, 158, 11, 0.06);
    text-decoration: none !important; color: inherit !important;
    transition: transform .18s ease, box-shadow .18s ease;
}
a.kpi-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(245, 158, 11, 0.16); border-color: #fbbf24; }
.kpi-tile-static { box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
.kpi-icon {
    flex: none; width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
}
.kpi-icon-amber  { background: #fef3c7; color: #b45309; }
.kpi-icon-slate  { background: #f1f5f9; color: #475569; }
.kpi-icon-green  { background: #dcfce7; color: #16a34a; }
.kpi-icon-red    { background: #fee2e2; color: #dc2626; }
.kpi-icon-orange { background: #ffedd5; color: #ea580c; }
.kpi-icon-blue   { background: #dbeafe; color: #2563eb; }
.kpi-icon-purple { background: #ede9fe; color: #7c3aed; }
.kpi-body { min-width: 0; flex: 1; }
.kpi-label { font-size: 0.78rem; color: #78716c; font-weight: 500; margin-bottom: 0.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kpi-value { font-size: 1.5rem; font-weight: 700; color: #1c1917; line-height: 1.15; }
.kpi-value-sm { font-size: 1.15rem; }
.kpi-value-suffix { font-size: 0.85rem; font-weight: 500; color: #a8a29e; }

/* ── Filtres en pilules ──────────────────────────────────────────────── */
.filter-pills { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.filter-pill {
    display: inline-flex; align-items: center;
    padding: 0.5rem 1.1rem; border-radius: 999px;
    background: #fff; border: 1.5px solid #e7e2d9;
    color: #57534e; font-size: 0.85rem; font-weight: 600;
    text-decoration: none !important; transition: all .18s ease; cursor: pointer;
}
.filter-pill:hover { border-color: #fbbf24; color: #92400e; }
.filter-pill.is-active {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-color: transparent; color: #fff;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.28);
}

/* ── Tableaux "personnes" (élèves, profs, personnel) ─────────────────── */
.people-table-card, .data-table-card { border-radius: 16px; overflow: hidden; border: 1px solid #f3e8d0; }
.people-table thead th, .data-table thead th {
    background: #fffbeb; color: #92400e; font-size: 0.75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 2px solid #fde68a;
    padding: 0.85rem 1rem;
}
.people-table td, .data-table td { padding: 0.85rem 1rem; vertical-align: middle; }

.person-cell { display: flex; align-items: center; gap: 0.65rem; text-decoration: none !important; }
.person-avatar {
    flex: none; width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff; font-weight: 700; font-size: 0.85rem;
}
.person-name { font-weight: 600; color: #1c1917; }
.person-cell:hover .person-name { color: #b45309; }

.class-chip {
    display: inline-flex; align-items: center;
    padding: 0.28rem 0.65rem; margin: 0.1rem 0.15rem 0.1rem 0;
    border-radius: 999px; background: #fef3c7; color: #92400e;
    font-size: 0.75rem; font-weight: 600; border: 1px solid #fde68a;
}
.class-chip-more { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }

.status-badge {
    display: inline-flex; align-items: center;
    padding: 0.3rem 0.7rem; border-radius: 999px;
    font-size: 0.75rem; font-weight: 700;
}
.status-badge-success { background: #dcfce7; color: #15803d; }
.status-badge-warning { background: #fef3c7; color: #b45309; }
.status-badge-danger  { background: #fee2e2; color: #b91c1c; }
.status-badge-neutral { background: #f1f5f9; color: #475569; }

.btn-view {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; border-radius: 10px;
    background: #fff7ed; color: #d97706; border: 1.5px solid #fde68a;
    transition: all 0.18s ease;
}
.btn-view:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }

/* ── Cartes "classe" (icône + initiales) ─────────────────────────────── */
.class-card-link { display: block; text-decoration: none !important; color: inherit !important; }
.class-card {
    display: flex; flex-direction: column;
    background: #fff; border: 1px solid #fde68a; border-radius: 18px;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.08);
    overflow: hidden; transition: transform .25s ease, box-shadow .25s ease;
}
.class-card-link:hover .class-card { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(245, 158, 11, 0.18); }
.class-card-top {
    display: flex; align-items: center; gap: 0.85rem;
    padding: 1.1rem 1.25rem;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-bottom: 1px solid #fde68a;
}
.class-card-avatar {
    flex: none; width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff; font-weight: 700; font-size: 0.9rem; text-transform: uppercase;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);
}
.class-card-title { flex: 1; min-width: 0; }
.class-card-title h5 { font-weight: 700; color: #1c1917; }
.class-card-level { font-size: 0.78rem; color: #92400e; font-weight: 500; }
.class-card-students { flex: none; display: flex; flex-direction: column; align-items: center; gap: 0.1rem; color: #d97706; font-weight: 700; font-size: 1rem; }
.class-card-students i { font-size: 0.85rem; opacity: 0.7; }
.class-card-body { padding: 1.1rem 1.25rem; flex: 1; display: flex; flex-direction: column; gap: 0.4rem; }
.class-card-stat { font-size: 0.85rem; color: #44403c; }

/* ── En-tête "hero" de fiche (classe, enseignant, élève...) ──────────── */
.class-hero {
    display: flex; align-items: center; gap: 1.15rem; flex-wrap: wrap;
    background: #fff; border: 1px solid #fde68a; border-radius: 18px;
    padding: 1.5rem 1.75rem; box-shadow: 0 2px 10px rgba(245, 158, 11, 0.06);
}
.class-hero-avatar {
    flex: none; width: 64px; height: 64px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff; font-size: 1.5rem; font-weight: 700;
    box-shadow: 0 6px 16px rgba(217, 119, 6, 0.32);
}

/* ── Barre de recherche/filtre ───────────────────────────────────────── */
.search-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem; }
.search-field {
    flex: 1 1 260px; display: flex; align-items: center; gap: 0.6rem;
    background: #fff; border: 1.5px solid #e7e2d9; border-radius: 12px;
    padding: 0.55rem 0.9rem;
}
.search-field i { color: #a8a29e; font-size: 0.85rem; }
.search-field input { border: none; outline: none; flex: 1; font-size: 0.9rem; background: transparent; }
.search-field:focus-within { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.14); }
.search-select {
    flex: 0 1 220px; border: 1.5px solid #e7e2d9; border-radius: 12px;
    padding: 0.55rem 0.9rem; font-size: 0.9rem; background: #fff; color: #44403c;
}

/* ── Cartes / sections génériques ─────────────────────────────────────── */
.panel-card { background: #fff; border: 1px solid #fde68a; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }
.panel-card-header {
    padding: 1rem 1.25rem; border-bottom: 1px solid #f3e8d0;
    font-weight: 700; color: #1c1917; display: flex; align-items: center; justify-content: space-between;
}
.section-eyebrow {
    font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    color: #b45309; margin-bottom: 0.85rem;
}
.section-eyebrow i { margin-right: 0.4rem; opacity: 0.8; }

/* ── État vide ───────────────────────────────────────────────────────── */
.empty-state {
    text-align: center; padding: 3.5rem 1.5rem;
    background: #fff; border: 1px dashed #e7e2d9; border-radius: 16px; color: #78716c;
}
.empty-state i { font-size: 2rem; color: #d6d3d1; margin-bottom: 0.85rem; display: block; }

/* ── Boutons pilules (actions primaires/secondaires) ─────────────────── */
.btn-pill-primary {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.55rem 1.15rem; border-radius: 999px; border: none;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff; font-weight: 700; font-size: 0.85rem;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.28);
    text-decoration: none !important; transition: transform .18s ease, box-shadow .18s ease;
}
.btn-pill-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(217, 119, 6, 0.36); color: #fff; }
.btn-pill-outline {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.55rem 1.15rem; border-radius: 999px;
    background: #fff; border: 1.5px solid #e7e2d9; color: #57534e;
    font-weight: 600; font-size: 0.85rem;
    text-decoration: none !important; transition: all .18s ease;
}
.btn-pill-outline:hover { border-color: #fbbf24; color: #92400e; }
</style>
