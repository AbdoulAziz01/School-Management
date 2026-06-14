@extends('platform.layouts.app')

@section('title', 'Tableau de bord — ' . $platformName)

@push('styles')
<style>
    /* ════════════════════════════════════════════════
       HEADER
    ════════════════════════════════════════════════ */
    .dash-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    .dash-title   { font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem; }
    .dash-sub     { font-size: 0.875rem; color: #94a3b8; margin: 0; }
    .btn-new-school {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.65rem 1.25rem;
        border-radius: 0.875rem;
        background: linear-gradient(135deg, #ea580c 0%, #f59e0b 100%);
        box-shadow: 0 6px 22px rgba(234,88,12,.30);
        color: #fff !important;
        font-size: 0.875rem; font-weight: 700;
        text-decoration: none !important;
        transition: transform .2s ease, box-shadow .2s ease;
        flex-shrink: 0;
    }
    .btn-new-school:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 10px 30px rgba(234,88,12,.40);
    }

    /* ════════════════════════════════════════════════
       KPI GRID
    ════════════════════════════════════════════════ */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 1199px) { .kpi-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 767px)  { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

    .kpi-card {
        display: block !important;
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 1rem;
        padding: 1.375rem 1.25rem;
        text-decoration: none !important;
        color: inherit !important;
        box-shadow: 0 1px 4px rgba(15,23,42,.07);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 36px rgba(15,23,42,.10) !important;
        color: inherit !important;
    }
    .kpi-card:link, .kpi-card:visited { color: inherit !important; }
    .kpi-icon-wrap {
        width: 2.5rem; height: 2.5rem; border-radius: 0.75rem;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1rem;
        flex-shrink: 0;
    }
    .kpi-value { font-size: 1.9rem; font-weight: 800; color: #0f172a; margin: 0 0 0.3rem; line-height: 1; }
    .kpi-label { font-size: 0.8rem; font-weight: 600; color: #475569; margin: 0; }
    .kpi-header { margin-bottom: 1rem; }

    /* ════════════════════════════════════════════════
       ALERT BANNERS
    ════════════════════════════════════════════════ */
    .alerts-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 767px) { .alerts-grid { grid-template-columns: 1fr; } }

    .alert-banner {
        display: flex !important; align-items: center; gap: 0.875rem;
        padding: 0.875rem 1rem;
        border-radius: 0.875rem;
        border-width: 1px; border-style: solid;
        text-decoration: none !important;
        color: inherit !important;
        transition: transform .2s ease;
    }
    .alert-banner:hover { transform: scale(1.01); }
    .alert-banner.danger { background: rgba(239,68,68,.07);  border-color: rgba(239,68,68,.2); }
    .alert-banner.amber  { background: rgba(245,158,11,.07); border-color: rgba(245,158,11,.22); }
    .alert-banner.sky    { background: rgba(14,165,233,.07); border-color: rgba(14,165,233,.22); }
    .alert-icon-wrap {
        width: 2.25rem; height: 2.25rem; border-radius: 0.75rem;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .alert-banner.danger .alert-icon-wrap { background: rgba(239,68,68,.12); }
    .alert-banner.amber  .alert-icon-wrap { background: rgba(245,158,11,.12); }
    .alert-banner.sky    .alert-icon-wrap { background: rgba(14,165,233,.12); }
    .alert-banner.danger .alert-title { color: #b91c1c; }
    .alert-banner.amber  .alert-title { color: #92400e; }
    .alert-banner.sky    .alert-title { color: #0369a1; }
    .alert-banner.danger .alert-sub   { color: #ef4444; }
    .alert-banner.amber  .alert-sub   { color: #d97706; }
    .alert-banner.sky    .alert-sub   { color: #0ea5e9; }
    .alert-banner.danger i.fa-exclamation-triangle { color: #ef4444; }
    .alert-banner.amber  i.fa-calendar-times       { color: #f59e0b; }
    .alert-banner.sky    i.fa-user-slash           { color: #0ea5e9; }
    .alert-banner.danger .alert-arrow { color: rgba(239,68,68,.4); }
    .alert-banner.amber  .alert-arrow { color: rgba(245,158,11,.4); }
    .alert-banner.sky    .alert-arrow { color: rgba(14,165,233,.4); }
    .alert-title { font-weight: 700; font-size: 0.8rem; margin: 0 0 2px; }
    .alert-sub   { font-size: 0.7rem; margin: 0; }

    /* ════════════════════════════════════════════════
       CHARTS
    ════════════════════════════════════════════════ */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 991px) { .charts-grid { grid-template-columns: 1fr; } }

    .chart-card {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 1.125rem;
        padding: 1.5rem;
        box-shadow: 0 1px 4px rgba(15,23,42,.07);
        overflow: visible;
        position: relative;
    }
    .chart-card-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; }
    .chart-card-title  { font-size: 0.9rem; font-weight: 700; color: #0f172a; margin: 0 0 0.2rem; }
    .chart-card-sub    { font-size: 0.7rem; color: #94a3b8; margin: 0; }
    .chart-tag {
        display: inline-flex; align-items: center; gap: 0.375rem;
        padding: 0.3rem 0.625rem;
        border-radius: 0.5rem;
        font-size: 0.7rem; font-weight: 700;
        flex-shrink: 0;
    }
    .chart-tag.orange { background: rgba(249,115,22,.10); color: #ea580c; }
    .chart-tag.violet { background: rgba(139,92,246,.10); color: #7c3aed; }

    .donut-wrap {
        display: flex; align-items: center; gap: 1.5rem;
    }
    .donut-legend { flex: 1; }
    .legend-row {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    .legend-row:last-child { margin-bottom: 0; }
    .legend-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; color: #64748b; }
    .legend-dot { width: 0.6rem; height: 0.6rem; border-radius: 9999px; flex-shrink: 0; }
    .legend-val { font-size: 0.875rem; font-weight: 700; color: #0f172a; }
    .legend-divider { border: none; border-top: 1px solid #f1f5f9; margin: 0.75rem 0; }
    .legend-total { display: flex; align-items: center; justify-content: space-between; }
    .legend-total-label { font-size: 0.72rem; color: #94a3b8; font-weight: 600; }
    .legend-total-val   { font-size: 0.9rem; font-weight: 800; color: #0f172a; }

    /* ════════════════════════════════════════════════
       TABLES
    ════════════════════════════════════════════════ */
    .tables-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    @media (max-width: 991px) { .tables-grid { grid-template-columns: 1fr; } }

    .table-card {
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 1.125rem;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(15,23,42,.07);
    }
    .table-card-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1.125rem 1.5rem;
        border-bottom: 1px solid #f8fafc;
    }
    .table-card-title { font-size: 0.9rem; font-weight: 700; color: #0f172a; margin: 0; }
    .table-card-link  {
        font-size: 0.75rem; font-weight: 700; color: #f59e0b !important;
        text-decoration: none !important; transition: color .15s;
    }
    .table-card-link:hover { color: #d97706 !important; }
    .table-card-badge-icon {
        width: 1.75rem; height: 1.75rem; border-radius: 0.5rem;
        background: rgba(239,68,68,.08);
        display: flex; align-items: center; justify-content: center;
    }
    .table-card-badge-icon i { color: #ef4444; font-size: 0.7rem; }

    /* Rows */
    .dash-row {
        display: flex !important; align-items: center; gap: 1rem;
        padding: 0.875rem 1.5rem;
        border-bottom: 1px solid #f8fafc;
        text-decoration: none !important;
        color: inherit !important;
        transition: background .15s ease;
        cursor: pointer;
    }
    .dash-row:link, .dash-row:visited { color: inherit !important; }
    .dash-row:hover { background: rgba(245,158,11,.04); }
    .dash-row:last-child { border-bottom: none; }
    .row-avatar {
        width: 2.25rem; height: 2.25rem; border-radius: 0.75rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; color: #fff; font-size: 0.875rem;
    }
    .row-avatar.gradient-orange { background: linear-gradient(135deg, #ea580c 0%, #f59e0b 100%); }
    .row-avatar.gradient-red    { background: linear-gradient(135deg, #ef4444 0%, #f97316 100%); }
    .row-name   { font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0 0 2px; }
    .row-meta   { font-size: 0.7rem; color: #94a3b8; margin: 0; }
    .row-info   { flex: 1; min-width: 0; overflow: hidden; }
    .row-name, .row-meta { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
    .row-chevron { color: #e2e8f0; font-size: 0.7rem; flex-shrink: 0; transition: color .15s; }
    .dash-row:hover .row-chevron { color: #94a3b8; }

    /* Empty state */
    .empty-state {
        padding: 3rem 1.5rem; text-align: center;
    }
    .empty-icon-wrap {
        width: 3rem; height: 3rem; border-radius: 0.875rem;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 0.875rem;
    }
    .empty-text { font-size: 0.875rem; color: #94a3b8; margin: 0; }

    /* Issues badges container */
    .issues-wrap { display: flex; flex-wrap: wrap; gap: 0.25rem; margin-top: 0.3rem; }

    /* ════════════════════════════════════════════════
       GLASS BADGES
    ════════════════════════════════════════════════ */
    .badge-glass {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.25rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.68rem; font-weight: 700;
        border-width: 1px; border-style: solid;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .badge-green  { background: rgba(16,185,129,.12); color: #059669; border-color: rgba(16,185,129,.25); }
    .badge-red    { background: rgba(239,68,68,.12);  color: #dc2626; border-color: rgba(239,68,68,.25); }
    .badge-amber  { background: rgba(245,158,11,.12); color: #b45309; border-color: rgba(245,158,11,.28); }
    .badge-slate  { background: rgba(100,116,139,.12); color: #475569; border-color: rgba(100,116,139,.22); }
    .badge-orange { background: rgba(249,115,22,.10); color: #ea580c; border-color: rgba(249,115,22,.22); }
    .badge-dot    { width: 0.4rem; height: 0.4rem; border-radius: 9999px; }

    /* Action button watchlist */
    .btn-manage {
        display: inline-flex; align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.72rem; font-weight: 700;
        text-decoration: none !important;
        color: #ea580c !important;
        background: rgba(249,115,22,.08);
        border: 1px solid rgba(249,115,22,.22);
        transition: transform .15s ease;
        flex-shrink: 0;
    }
    .btn-manage:hover { transform: scale(1.05); background: rgba(249,115,22,.15); }
</style>
@endpush

@section('content')

{{-- ──────────────────────  HEADER  ────────────────────── --}}
<div class="dash-header">
    <div>
        <h1 class="dash-title">Tableau de bord</h1>
        <p class="dash-sub">Vue d'ensemble de la plateforme · {{ $platformName }}</p>
    </div>
    <a href="{{ route('platform.schools.create') }}" class="btn-new-school">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 4v16m8-8H4"/>
        </svg>
        Nouvel établissement
    </a>
</div>

{{-- ──────────────────────  KPI CARDS  ────────────────────── --}}
@php
$kpiCards = [
    [
        'label'   => 'Établissements',
        'value'   => $stats['schools_total'],
        'url'     => route('platform.schools.index'),
        'bg'      => 'background:rgba(124,58,237,.1)',
        'color'   => 'color:#7c3aed',
        'dot'     => '#7c3aed',
        'icon'    => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z',
    ],
    [
        'label'   => 'Actifs',
        'value'   => $stats['schools_active'],
        'url'     => route('platform.schools.index', ['status' => 'active']),
        'bg'      => 'background:rgba(16,185,129,.1)',
        'color'   => 'color:#059669',
        'dot'     => '#10b981',
        'icon'    => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z',
    ],
    [
        'label'   => 'Inactifs',
        'value'   => $stats['schools_inactive'],
        'url'     => route('platform.schools.index', ['status' => 'inactive']),
        'bg'      => 'background:rgba(100,116,139,.1)',
        'color'   => 'color:#64748b',
        'dot'     => '#94a3b8',
        'icon'    => 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636',
    ],
    [
        'label'   => 'Utilisateurs',
        'value'   => $stats['users_total'],
        'url'     => route('platform.schools.index'),
        'bg'      => 'background:rgba(37,99,235,.1)',
        'color'   => 'color:#2563eb',
        'dot'     => '#3b82f6',
        'icon'    => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
    ],
    [
        'label'   => 'Élèves',
        'value'   => $stats['students_total'],
        'url'     => route('platform.schools.index'),
        'bg'      => 'background:rgba(249,115,22,.1)',
        'color'   => 'color:#f97316',
        'dot'     => '#f97316',
        'icon'    => 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
    ],
    [
        'label'   => 'Enseignants',
        'value'   => $stats['teachers_total'],
        'url'     => route('platform.schools.index'),
        'bg'      => 'background:rgba(236,72,153,.1)',
        'color'   => 'color:#db2777',
        'dot'     => '#ec4899',
        'icon'    => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
    ],
    [
        'label'   => 'Staff',
        'value'   => $stats['staff_total'],
        'url'     => route('platform.schools.index'),
        'bg'      => 'background:rgba(139,92,246,.1)',
        'color'   => 'color:#8b5cf6',
        'dot'     => '#8b5cf6',
        'icon'    => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
    ],
    [
        'label'   => 'En attente',
        'value'   => $stats['pending_registrations'],
        'url'     => route('platform.schools.index', ['status' => 'pending']),
        'bg'      => 'background:rgba(239,68,68,.1)',
        'color'   => 'color:#ef4444',
        'dot'     => '#ef4444',
        'icon'    => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    ],
];
@endphp

<div class="kpi-grid">
    @foreach($kpiCards as $card)
    <a href="{{ $card['url'] }}" class="kpi-card">
        <div class="kpi-header">
            <div class="kpi-icon-wrap" style="{{ $card['bg'] }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" style="{{ $card['color'] }}">
                    <path d="{{ $card['icon'] }}"/>
                </svg>
            </div>
        </div>
        <p class="kpi-value">{{ number_format($card['value']) }}</p>
        <p class="kpi-label">{{ $card['label'] }}</p>
    </a>
    @endforeach
</div>

{{-- ──────────────────────  ALERTES  ────────────────────── --}}
@if($stats['schools_without_admin'] || $stats['schools_without_current_year'] || $stats['unassigned_students'])
<div class="alerts-grid">

    @if($stats['schools_without_admin'] > 0)
    <a href="{{ route('platform.schools.index', ['status' => 'no_admin']) }}" class="alert-banner danger">
        <div class="alert-icon-wrap">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <p class="alert-title">{{ $stats['schools_without_admin'] }} sans administrateur</p>
            <p class="alert-sub">Établissement(s) à configurer</p>
        </div>
        <i class="fas fa-arrow-right alert-arrow" style="flex-shrink:0;font-size:.7rem;"></i>
    </a>
    @endif

    @if($stats['schools_without_current_year'] > 0)
    <a href="{{ route('platform.schools.index', ['status' => 'no_current_year']) }}" class="alert-banner amber">
        <div class="alert-icon-wrap">
            <i class="fas fa-calendar-times"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <p class="alert-title">{{ $stats['schools_without_current_year'] }} sans année courante</p>
            <p class="alert-sub">Établissements actifs concernés</p>
        </div>
        <i class="fas fa-arrow-right alert-arrow" style="flex-shrink:0;font-size:.7rem;"></i>
    </a>
    @endif

    @if($stats['unassigned_students'] > 0)
    <a href="{{ route('platform.students.unassigned') }}" class="alert-banner sky">
        <div class="alert-icon-wrap">
            <i class="fas fa-user-slash"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <p class="alert-title">{{ $stats['unassigned_students'] }} élève(s) sans classe</p>
            <p class="alert-sub">Sur toutes les écoles</p>
        </div>
        <i class="fas fa-arrow-right alert-arrow" style="flex-shrink:0;font-size:.7rem;"></i>
    </a>
    @endif

</div>
@endif

{{-- ──────────────────────  CHARTS  ────────────────────── --}}
<div class="charts-grid">

    {{-- Courbe d'évolution --}}
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <p class="chart-card-title">Croissance du réseau</p>
                <p class="chart-card-sub">Établissements partenaires · Janv – Juin {{ date('Y') }}</p>
            </div>
            <span class="chart-tag orange">
                <i class="fas fa-arrow-trend-up" style="font-size:.65rem;"></i>En hausse
            </span>
        </div>
        <canvas id="enrollmentChart" height="220"></canvas>
    </div>

    {{-- Répartition par rôle --}}
    <div class="chart-card">
        <div class="chart-card-header">
            <div>
                <p class="chart-card-title">Statut et répartition des établissements</p>
                <p class="chart-card-sub">Vue d'ensemble de la plateforme SaaS</p>
            </div>
            <span class="chart-tag violet">
                <i class="fas fa-chart-pie" style="font-size:.65rem;"></i>Par statut
            </span>
        </div>
        <div class="donut-wrap">
            <div style="flex-shrink:0;">
                <canvas id="rolesChart" width="170" height="170"></canvas>
            </div>
            <div class="donut-legend">
                <div class="legend-row">
                    <span class="legend-label">
                        <span class="legend-dot" style="background:#10b981;"></span>Établissements Actifs
                    </span>
                    <span class="legend-val">{{ $stats['schools_active'] }}</span>
                </div>
                <div class="legend-row">
                    <span class="legend-label">
                        <span class="legend-dot" style="background:#f59e0b;"></span>Sans admin (en attente)
                    </span>
                    <span class="legend-val">{{ $stats['schools_without_admin'] }}</span>
                </div>
                <div class="legend-row">
                    <span class="legend-label">
                        <span class="legend-dot" style="background:#ef4444;"></span>Inactifs / Suspendus
                    </span>
                    <span class="legend-val">{{ $stats['schools_inactive'] }}</span>
                </div>
                <hr class="legend-divider">
                <div class="legend-total">
                    <span class="legend-total-label">Total établissements</span>
                    <span class="legend-total-val">{{ $stats['schools_total'] }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ──────────────────────  TABLES  ────────────────────── --}}
<div class="tables-grid">

    {{-- Derniers établissements --}}
    <div class="table-card">
        <div class="table-card-header">
            <p class="table-card-title">Derniers établissements</p>
            <a href="{{ route('platform.schools.index') }}" class="table-card-link">Voir tout →</a>
        </div>

        @forelse($recentSchools as $school)
        <a href="{{ route('platform.schools.show', $school) }}" class="dash-row">
            <div class="row-avatar gradient-orange">{{ strtoupper(mb_substr($school->name, 0, 1)) }}</div>
            <div class="row-info">
                <span class="row-name">{{ $school->name }}</span>
                <span class="row-meta">{{ $school->students_count }} élèves · {{ $school->teachers_count }} profs</span>
            </div>
            @if($school->pending_count > 0)
                <span class="badge-glass badge-red">
                    <i class="fas fa-clock" style="font-size:.5rem;"></i>{{ $school->pending_count }}
                </span>
            @endif
            @if($school->is_active)
                <span class="badge-glass badge-green">
                    <span class="badge-dot" style="background:#10b981;"></span>Actif
                </span>
            @else
                <span class="badge-glass badge-slate">
                    <span class="badge-dot" style="background:#94a3b8;"></span>Inactif
                </span>
            @endif
            <i class="fas fa-chevron-right row-chevron"></i>
        </a>
        @empty
        <div class="empty-state">
            <div class="empty-icon-wrap" style="background:#f8fafc;">
                <i class="fas fa-school" style="color:#cbd5e1;font-size:1.25rem;"></i>
            </div>
            <p class="empty-text">Aucun établissement</p>
        </div>
        @endforelse
    </div>

    {{-- À surveiller --}}
    <div class="table-card">
        <div class="table-card-header">
            <p class="table-card-title">Établissements à surveiller</p>
            <div class="table-card-badge-icon">
                <i class="fas fa-exclamation"></i>
            </div>
        </div>

        @forelse($watchlist['schools'] as $school)
        @php
            $issues = [];
            if ($school->admins_count < 1)  $issues[] = ['label' => 'Sans admin',   'cls' => 'badge-red'];
            if ($school->pending_count > 0) $issues[] = ['label' => $school->pending_count . ' en attente', 'cls' => 'badge-amber'];
            if (!$school->is_active)        $issues[] = ['label' => 'Inactif',       'cls' => 'badge-slate'];
            if ($school->is_active && empty($watchlist['years'][$school->id] ?? null))
                                            $issues[] = ['label' => 'Sans année',    'cls' => 'badge-amber'];
        @endphp
        <div class="dash-row"
             onclick="window.location.href='{{ route('platform.schools.show', $school) }}'"
             tabindex="0" role="link"
             data-href="{{ route('platform.schools.show', $school) }}">
            <div class="row-avatar gradient-red">{{ strtoupper(mb_substr($school->name, 0, 1)) }}</div>
            <div class="row-info">
                <span class="row-name">{{ $school->name }}</span>
                <div class="issues-wrap">
                    @foreach($issues as $issue)
                        <span class="badge-glass {{ $issue['cls'] }}">{{ $issue['label'] }}</span>
                    @endforeach
                </div>
            </div>
            <a href="{{ route('platform.schools.show', $school) }}"
               class="btn-manage"
               onclick="event.stopPropagation()">
                Gérer
            </a>
        </div>
        @empty
        <div class="empty-state">
            <div class="empty-icon-wrap" style="background:#f0fdf4;">
                <i class="fas fa-check-circle" style="color:#4ade80;font-size:1.25rem;"></i>
            </div>
            <p class="empty-text">Aucune alerte · Tout va bien</p>
        </div>
        @endforelse
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ══ Line Chart : Croissance du réseau ══════════════════════ */
    var enrollCtx = document.getElementById('enrollmentChart').getContext('2d');

    var grad = enrollCtx.createLinearGradient(0, 0, 0, 240);
    grad.addColorStop(0,   'rgba(249,115,22,0.22)');
    grad.addColorStop(0.7, 'rgba(249,115,22,0.04)');
    grad.addColorStop(1,   'rgba(249,115,22,0)');

    var growthLabels = @json($schoolGrowth['labels']);
    var growthData   = @json($schoolGrowth['data']);

    new Chart(enrollCtx, {
        type: 'line',
        data: {
            labels: growthLabels,
            datasets: [{
                label: 'Établissements',
                data: growthData,
                borderColor: '#f97316',
                backgroundColor: grad,
                borderWidth: 2.5,
                pointBackgroundColor: '#f97316',
                pointBorderColor: '#fff',
                pointBorderWidth: 2.5,
                pointRadius: 5,
                pointHoverRadius: 7,
                fill: true,
                tension: 0.42,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#f8fafc',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    padding: 14,
                    cornerRadius: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(ctx) {
                            var n = ctx.parsed.y;
                            return '  ' + n + ' établissement' + (n > 1 ? 's' : '');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(148,163,184,0.08)', drawTicks: false },
                    border: { display: false, dash: [4,4] },
                    ticks: { color: '#94a3b8', font: { size: 11 }, padding: 10, stepSize: 2 },
                    beginAtZero: true,
                    suggestedMax: 10,
                }
            }
        }
    });

    /* ══ Donut Chart : Statut des établissements ════════════════ */
    var rolesCtx = document.getElementById('rolesChart').getContext('2d');
    var donutUrls = [
        '{{ route("platform.schools.index", ["status" => "active"]) }}',
        '{{ route("platform.schools.index", ["status" => "no_admin"]) }}',
        '{{ route("platform.schools.index", ["status" => "inactive"]) }}'
    ];

    var dActive   = {{ (int) $stats['schools_active'] }};
    var dPending  = {{ (int) $stats['schools_without_admin'] }};
    var dInactive = {{ (int) $stats['schools_inactive'] }};
    var hasDonut  = (dActive + dPending + dInactive) > 0;

    new Chart(rolesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Établissements Actifs', 'Sans admin (en attente)', 'Inactifs / Suspendus'],
            datasets: [{
                data: hasDonut ? [dActive, dPending, dInactive] : [1],
                backgroundColor: hasDonut
                    ? ['rgba(16,185,129,.85)', 'rgba(245,158,11,.85)', 'rgba(239,68,68,.85)']
                    : ['rgba(226,232,240,.4)'],
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: false,
            cutout: '72%',
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    window.location.href = donutUrls[elements[0].index];
                }
            },
            onHover: function(event, elements) {
                event.native.target.style.cursor = elements.length > 0 ? 'pointer' : 'default';
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#ffffff',
                    titleColor: '#0f172a',
                    bodyColor: '#475569',
                    borderColor: '#e2e8f0',
                    borderWidth: 1.5,
                    padding: 12,
                    cornerRadius: 10,
                    displayColors: true,
                    boxWidth: 8, boxHeight: 8,
                    boxPadding: 5,
                    titleFont: { size: 12, weight: '700' },
                    bodyFont: { size: 11 },
                    callbacks: {
                        title: function(items) {
                            var labels = ['Actifs', 'Sans admin', 'Inactifs'];
                            return labels[items[0].dataIndex] ?? items[0].label;
                        },
                        label: function(ctx) {
                            var n = ctx.parsed;
                            return '  ' + n + ' établissement' + (n > 1 ? 's' : '') + ' · Cliquer pour voir';
                        }
                    }
                }
            }
        }
    });

    /* ══ Navigation clavier pour les lignes watchlist ═══════════ */
    document.querySelectorAll('.dash-row[role="link"]').forEach(function (row) {
        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                var href = row.getAttribute('data-href');
                if (href) window.location.href = href;
            }
        });
    });

});
</script>
@endpush
