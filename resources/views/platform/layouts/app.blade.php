<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.form-draft-meta')
    <title>@yield('title', $platformName . ' — Plateforme')</title>
    <link rel="icon" type="image/png" href="{{ $platformLogoIcon }}">

    {{-- Bootstrap CDN — conservé pour les autres vues de la plateforme --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ════════════════════════════════════════════════
           VARIABLES + BOOTSTRAP BLUE → BRAND ORANGE
        ════════════════════════════════════════════════ */
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #020617;
            --brand-from: #ea580c;
            --brand-to: #f59e0b;
            --text-muted: #94a3b8;
            --text-light: #64748b;

            /* Remplace le bleu Bootstrap par l'orange de la charte */
            --bs-primary:               #ea580c;
            --bs-primary-rgb:           234, 88, 12;
            --bs-link-color:            #ea580c;
            --bs-link-color-rgb:        234, 88, 12;
            --bs-link-hover-color:      #c2410c;
            --bs-link-hover-color-rgb:  194, 65, 12;
            --bs-focus-ring-color:      rgba(234, 88, 12, 0.25);
        }

        /* ── btn-primary → gradient orange/ambre ─── */
        .btn-primary {
            --bs-btn-bg:                    #ea580c;
            --bs-btn-border-color:          #ea580c;
            --bs-btn-hover-bg:              #c2410c;
            --bs-btn-hover-border-color:    #c2410c;
            --bs-btn-active-bg:             #c2410c;
            --bs-btn-active-border-color:   #c2410c;
            --bs-btn-color:                 #fff;
            --bs-btn-hover-color:           #fff;
            --bs-btn-focus-shadow-rgb:      234, 88, 12;
            background: linear-gradient(135deg, #ea580c 0%, #f59e0b 100%) !important;
            border-color: transparent !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(234,88,12,.28);
            font-weight: 600;
        }
        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background: linear-gradient(135deg, #c2410c 0%, #d97706 100%) !important;
            box-shadow: 0 6px 20px rgba(234,88,12,.38) !important;
            color: #fff !important;
        }

        /* ── btn-outline-primary → contour orange ─── */
        .btn-outline-primary {
            --bs-btn-color:                 #ea580c;
            --bs-btn-border-color:          #ea580c;
            --bs-btn-hover-bg:              #ea580c;
            --bs-btn-hover-border-color:    #ea580c;
            --bs-btn-hover-color:           #fff;
            --bs-btn-active-bg:             #c2410c;
            --bs-btn-focus-shadow-rgb:      234, 88, 12;
            color: #ea580c !important;
            border-color: rgba(234,88,12,.5) !important;
            font-weight: 600;
        }
        .btn-outline-primary:hover, .btn-outline-primary:active, .btn-outline-primary.active {
            background: linear-gradient(135deg, #ea580c 0%, #f59e0b 100%) !important;
            border-color: transparent !important;
            color: #fff !important;
        }

        /* ── Liens dans le contenu principal ─── */
        .plf-content a:not(.btn):not(.kpi-card):not(.dash-row):not(.alert-banner):not(.btn-manage):not(.table-card-link) {
            color: #ea580c;
        }
        .plf-content a:not(.btn):not(.kpi-card):not(.dash-row):not(.alert-banner):not(.btn-manage):not(.table-card-link):hover {
            color: #c2410c;
        }

        /* ── Badges primary ─── */
        .badge.bg-primary  { background: linear-gradient(135deg, #ea580c, #f59e0b) !important; color: #fff !important; }
        .badge.bg-success  { background-color: #10b981 !important; }
        .badge.bg-danger   { background-color: #ef4444 !important; }
        .badge.bg-warning  { background-color: #f59e0b !important; color: #fff !important; }
        .badge.bg-info     { background-color: #0ea5e9 !important; color: #fff !important; }
        .badge.bg-secondary{ background-color: #64748b !important; }

        /* ── Text / border / bg primary ─── */
        .text-primary   { color: #ea580c !important; }
        .border-primary { border-color: #ea580c !important; }
        .bg-primary     { background: linear-gradient(135deg, #ea580c, #f59e0b) !important; }

        /* ── Focus ring inputs ─── */
        .form-control:focus, .form-select:focus {
            border-color: rgba(234,88,12,.5) !important;
            box-shadow: 0 0 0 0.25rem rgba(234,88,12,.15) !important;
        }

        /* ── Pagination ─── */
        .page-link { color: #ea580c !important; }
        .page-link:hover { background: rgba(234,88,12,.08) !important; color: #c2410c !important; border-color: rgba(234,88,12,.2) !important; }
        .page-item.active .page-link {
            background: linear-gradient(135deg, #ea580c, #f59e0b) !important;
            border-color: transparent !important;
            color: #fff !important;
        }

        /* ── Alertes Bootstrap ─── */
        .alert-primary { background: rgba(234,88,12,.08) !important; border-color: rgba(234,88,12,.22) !important; color: #9a3412 !important; }

        /* ── Nav tabs / pills ─── */
        .nav-tabs .nav-link.active   { color: #ea580c !important; border-color: #dee2e6 #dee2e6 #fff !important; }
        .nav-tabs .nav-link:hover    { color: #c2410c !important; }
        .nav-pills .nav-link.active  { background: linear-gradient(135deg, #ea580c, #f59e0b) !important; color: #fff !important; }

        /* ── Liens dans la topbar ─── */
        .plf-topbar a:not(.plf-topbar-user) { color: #ea580c !important; }

        /* ── Couleurs monospace / code dans les tableaux (slugs, codes) ─── */
        code { color: #ea580c !important; background: rgba(234,88,12,.07) !important; padding: 0.15em 0.35em; border-radius: 4px; }

        /* ════════════════════════════════════════════════
           BASE
        ════════════════════════════════════════════════ */
        body { background: #f8fafc; min-height: 100vh; font-family: 'Inter','Segoe UI',system-ui,sans-serif; }

        /* ════════════════════════════════════════════════
           SIDEBAR
        ════════════════════════════════════════════════ */
        .plf-sidebar {
            background-color: var(--sidebar-bg) !important;
            position: fixed;
            left: 0; top: 0; bottom: 0;   /* top+bottom = robuste sur tous les mobiles */
            width: var(--sidebar-w);
            display: flex;
            flex-direction: column;
            z-index: 50;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .plf-sidebar::-webkit-scrollbar { display: none; }

        /* Tous les liens/boutons dans la sidebar */
        .plf-sidebar a,
        .plf-sidebar a:link,
        .plf-sidebar a:visited { color: var(--text-muted) !important; text-decoration: none !important; }
        .plf-sidebar a:hover   { color: #fff !important; }
        .plf-sidebar button    { color: var(--text-muted) !important; }

        /* ── Brand ── */
        .plf-brand {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 1.5rem 1.25rem 1.25rem;
        }
        .plf-brand-icon {
            width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; flex-shrink: 0;
            background: linear-gradient(135deg, var(--brand-from) 0%, var(--brand-to) 100%);
            box-shadow: 0 4px 16px rgba(234,88,12,.35);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem;
        }
        .plf-brand-name { color: #fff !important; font-weight: 700; font-size: 0.9rem; margin: 0; line-height: 1.3; }
        .plf-brand-sub  { color: var(--text-light) !important; font-size: 0.68rem; margin: 0; line-height: 1.3; }

        /* ── User Card ── */
        .plf-user-card {
            display: flex; align-items: center; gap: 0.75rem;
            margin: 0 1rem 1.25rem;
            padding: 0.75rem;
            border-radius: 0.875rem;
            background: rgba(255,255,255,.05) !important;
            border: 1px solid rgba(255,255,255,.1);
            text-decoration: none !important;
            color: inherit !important;
            transition: all .2s ease;
        }
        .plf-user-card:link, .plf-user-card:visited { color: inherit !important; }
        .plf-user-card:hover {
            background: rgba(255,255,255,.10) !important;
            border-color: rgba(255,255,255,.2);
            color: #fff !important;
        }
        .plf-user-card.is-active {
            background: rgba(234,88,12,.18) !important;
            border-color: rgba(234,88,12,.32) !important;
        }
        .plf-user-avatar {
            width: 2.5rem; height: 2.5rem; border-radius: 0.625rem; flex-shrink: 0;
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #1c1917; font-size: 0.875rem;
        }
        .plf-user-name  { color: #fff !important; font-weight: 600; font-size: 0.875rem; margin: 0; line-height: 1.3; }
        .plf-user-email { color: var(--text-light) !important; font-size: 0.68rem; margin: 0; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .plf-user-role  { color: #fbbf24 !important; font-size: 0.625rem; font-weight: 600; letter-spacing: .01em; display: block; margin-top: 2px; }

        /* ── Navigation ── */
        .plf-nav { flex: 1; padding: 0 0.75rem; }
        .plf-nav-label {
            display: block;
            color: var(--text-light) !important;
            font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em;
            padding: 0 1rem; margin-bottom: 0.5rem;
        }
        .plf-nav-link {
            display: flex !important; align-items: center; gap: 0.75rem;
            padding: 0.65rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem; font-weight: 500; line-height: 1;
            color: var(--text-muted) !important;
            text-decoration: none !important;
            margin-bottom: 0.2rem;
            transition: all .2s ease;
        }
        .plf-nav-link:link, .plf-nav-link:visited { color: var(--text-muted) !important; }
        .plf-nav-link:hover {
            background: rgba(255,255,255,.08) !important;
            color: #fff !important;
        }
        .plf-nav-link.is-active {
            background: linear-gradient(135deg, var(--brand-from) 0%, var(--brand-to) 100%) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(234,88,12,.32);
        }
        .plf-nav-link.is-active:link,
        .plf-nav-link.is-active:visited,
        .plf-nav-link.is-active:hover { color: #fff !important; }
        .plf-nav-icon { width: 1rem; text-align: center; flex-shrink: 0; }

        /* ── Déconnexion ── */
        .plf-logout-wrap {
            padding: 0 0.75rem;
            margin-top: auto; padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .plf-logout-btn {
            display: flex !important; align-items: center; gap: 0.75rem;
            width: 100%; padding: 0.65rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.875rem; font-weight: 500; line-height: 1;
            color: var(--text-muted) !important;
            background: none !important; border: none; cursor: pointer; text-align: left;
            transition: all .2s ease;
        }
        .plf-logout-btn:hover {
            color: #f87171 !important;
            background: rgba(239,68,68,.1) !important;
        }

        /* ════════════════════════════════════════════════
           MAIN
        ════════════════════════════════════════════════ */
        .plf-main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        /* ════════════════════════════════════════════════
           TOPBAR
        ════════════════════════════════════════════════ */
        .plf-topbar {
            display: flex !important; align-items: center !important; justify-content: space-between !important;
            padding: 0.875rem 2rem;
            background: rgba(248,250,252,.9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid #e2e8f0;
            position: sticky; top: 0; z-index: 40;
        }
        .plf-topbar-platform { font-size: 0.7rem; color: #94a3b8; margin: 0 0 2px; }
        .plf-topbar-greeting { font-size: 0.875rem; color: #475569; margin: 0; }
        .plf-topbar-greeting strong { color: #0f172a; font-weight: 700; }
        .plf-topbar-right { display: flex !important; align-items: center; gap: 1rem; }
        .plf-topbar-date { display: flex; align-items: center; gap: 0.375rem; font-size: 0.72rem; color: #94a3b8; }
        .plf-topbar-date .icon { color: #fbbf24; }
        .plf-topbar-user {
            display: flex !important; align-items: center; gap: 0.5rem;
            color: #374151 !important; text-decoration: none !important;
        }
        .plf-topbar-user:hover { color: #111827 !important; }
        .plf-topbar-avatar {
            width: 2.25rem; height: 2.25rem; border-radius: 0.625rem; flex-shrink: 0;
            background: linear-gradient(135deg, #fbbf24 0%, #f97316 100%);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #1c1917; font-size: 0.75rem;
            box-shadow: 0 1px 4px rgba(0,0,0,.1);
        }
        .plf-topbar-uname { font-size: 0.875rem; font-weight: 600; color: #374151 !important; }

        /* ════════════════════════════════════════════════
           FLASH MESSAGES
        ════════════════════════════════════════════════ */
        .plf-flash {
            display: flex; align-items: center; gap: 0.75rem;
            margin: 1rem 2rem 0;
            padding: 0.875rem 1rem;
            border-radius: 0.875rem;
            font-size: 0.875rem;
            border-width: 1px; border-style: solid;
        }
        .plf-flash-ok  { background: rgba(16,185,129,.08); border-color: rgba(16,185,129,.22); color: #065f46; }
        .plf-flash-err { background: rgba(239,68,68,.08);  border-color: rgba(239,68,68,.22);  color: #7f1d1d; }
        .plf-flash-close {
            margin-left: auto; opacity: .5; cursor: pointer;
            background: none; border: none; color: inherit; font-size: 0.875rem; line-height: 1; padding: 0;
        }
        .plf-flash-close:hover { opacity: 1; }

        /* ════════════════════════════════════════════════
           CONTENU
        ════════════════════════════════════════════════ */
        .plf-content { padding: 1.75rem 2rem 3rem; flex: 1; }

        /* ════════════════════════════════════════════════
           RESPONSIVE
        ════════════════════════════════════════════════ */

        /* Overlay sidebar mobile */
        .plf-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 49;
            cursor: pointer;
        }
        .plf-sidebar-overlay.is-open { display: block; }

        /* Bouton hamburger dans la topbar */
        .plf-topbar-toggle {
            display: none;
            background: none;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 1rem;
            padding: 0.35rem 0.55rem;
            border-radius: 0.5rem;
            cursor: pointer;
            flex-shrink: 0;
            line-height: 1;
        }
        .plf-topbar-toggle:hover { background: #f1f5f9; }

        @media (max-width: 991.98px) {
            /* Sidebar : off-canvas à gauche, pleine hauteur via top+bottom */
            .plf-sidebar {
                position: fixed;
                left: 0; top: 0; bottom: 0;
                width: min(var(--sidebar-w), 82vw);
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 50;
            }
            .plf-sidebar.is-open {
                transform: translateX(0);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.35);
            }

            .plf-main { margin-left: 0; }

            /* Topbar */
            .plf-topbar {
                padding: 0.65rem 1rem;
                position: sticky;
                top: 0;
                z-index: 40;
                gap: 0.75rem;
            }
            .plf-topbar-toggle { display: inline-flex; align-items: center; }

            /* Contenu */
            .plf-content { padding: 1.25rem 1rem 2rem; }
            .plf-flash   { margin: 0.75rem 1rem 0; }
        }

        @media (max-width: 575.98px) {
            .plf-topbar          { padding: 0.5rem 0.75rem; }
            .plf-content         { padding: 1rem 0.75rem 2rem; }
            .plf-topbar-platform { display: none; }
            .plf-topbar-greeting { font-size: 0.8rem; }
        }

        /* Modales ancrées à droite (jamais centrées) — voir
           admin/layouts/app.blade.php pour l'explication complète. */
        .modal .modal-dialog {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: auto !important;
            margin: 0 !important;
            max-width: 480px !important;
            width: 100% !important;
            height: 100% !important;
            min-height: 100% !important;
            display: block !important;
        }
        .modal .modal-content {
            height: 100%;
            border-radius: 0;
            border: none;
            display: flex;
            flex-direction: column;
        }
        .modal .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
        }
        .modal.fade .modal-dialog {
            transform: translateX(100%);
            transition: transform 0.25s ease-out;
        }
        .modal.fade.show .modal-dialog {
            transform: translateX(0);
        }
        @media (max-width: 576px) {
            .modal .modal-dialog {
                max-width: 100% !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- Overlay sidebar mobile --}}
<div class="plf-sidebar-overlay" id="plfSidebarOverlay"></div>

{{-- ═══════════════════════  SIDEBAR  ═══════════════════════ --}}
<aside class="plf-sidebar" id="plfSidebar">

    {{-- Brand --}}
    <div class="plf-brand">
        <div class="plf-brand-icon" style="background: #fff; padding: 5px;">
            <img src="{{ $platformLogoIcon }}" alt="{{ $platformName }}" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <div class="overflow-hidden">
            <p class="plf-brand-name">{{ $platformName }}</p>
            <p class="plf-brand-sub">Plateforme · Super Administration</p>
        </div>
    </div>

    {{-- User Card --}}
    <a href="{{ route('platform.profile.edit') }}"
       class="plf-user-card {{ request()->routeIs('platform.profile.*') ? 'is-active' : '' }}">
        <div class="plf-user-avatar">{{ $platformUserInitials }}</div>
        <div class="overflow-hidden flex-1 min-w-0">
            <p class="plf-user-name">{{ $platformUserName }}</p>
            <p class="plf-user-email">{{ $platformUserEmail }}</p>
            <span class="plf-user-role"><i class="fas fa-user-shield me-1"></i>Super administrateur</span>
        </div>
    </a>

    {{-- Navigation --}}
    <nav class="plf-nav">
        <span class="plf-nav-label">Menu</span>

        <a href="{{ route('platform.dashboard') }}"
           class="plf-nav-link {{ request()->routeIs('platform.dashboard') ? 'is-active' : '' }}">
            <i class="fas fa-chart-pie plf-nav-icon"></i>
            Tableau de bord
        </a>

        <a href="{{ route('platform.schools.index') }}"
           class="plf-nav-link {{ request()->routeIs('platform.schools.*') ? 'is-active' : '' }}">
            <i class="fas fa-school plf-nav-icon"></i>
            Établissements
        </a>

        <a href="{{ route('platform.profile.edit') }}"
           class="plf-nav-link {{ request()->routeIs('platform.profile.*') ? 'is-active' : '' }}">
            <i class="fas fa-user-circle plf-nav-icon"></i>
            Mon profil
        </a>
    </nav>

    {{-- Déconnexion --}}
    <div class="plf-logout-wrap" style="padding-bottom: 1.25rem;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="plf-logout-btn">
                <i class="fas fa-sign-out-alt plf-nav-icon"></i>
                Déconnexion
            </button>
        </form>
    </div>
</aside>

{{-- ═══════════════════════  MAIN  ═══════════════════════ --}}
<main class="plf-main">

    {{-- Topbar --}}
    <div class="plf-topbar">
        {{-- Hamburger (visible uniquement sur mobile) --}}
        <button class="plf-topbar-toggle" id="plfSidebarToggle" aria-label="Ouvrir le menu">
            <i class="fas fa-bars"></i>
        </button>
        <div>
            <p class="plf-topbar-platform">{{ $platformName }}</p>
            <p class="plf-topbar-greeting">
                Bienvenue, <strong>{{ $platformUserName }}</strong>
            </p>
        </div>
        <div class="plf-topbar-right">
            <div class="plf-topbar-date d-none d-lg-flex">
                <i class="fas fa-calendar-alt icon"></i>
                <span id="plf-date"></span>
            </div>
            <a href="{{ route('platform.profile.edit') }}" class="plf-topbar-user">
                <div class="plf-topbar-avatar">{{ $platformUserInitials }}</div>
                <span class="plf-topbar-uname d-none d-md-inline">{{ $platformUserName }}</span>
            </a>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="plf-flash plf-flash-ok" id="flash-ok">
            <i class="fas fa-check-circle flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button class="plf-flash-close" onclick="document.getElementById('flash-ok').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="plf-flash plf-flash-err" id="flash-err">
            <i class="fas fa-exclamation-circle flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
            <button class="plf-flash-close" onclick="document.getElementById('flash-err').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- Contenu de la page --}}
    <div class="plf-content">
        @yield('content')
    </div>

</main>

{{-- Bootstrap JS (compatibilité autres vues) --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Date dans la topbar
    (function () {
        var el = document.getElementById('plf-date');
        if (el) el.textContent = new Date().toLocaleDateString('fr-FR', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    })();

    // Toggle sidebar mobile (super-admin)
    (function () {
        var toggle  = document.getElementById('plfSidebarToggle');
        var sidebar = document.getElementById('plfSidebar');
        var overlay = document.getElementById('plfSidebarOverlay');
        if (!toggle || !sidebar || !overlay) return;

        function openSidebar()  { sidebar.classList.add('is-open');    overlay.classList.add('is-open'); }
        function closeSidebar() { sidebar.classList.remove('is-open'); overlay.classList.remove('is-open'); }

        toggle.addEventListener('click', function () {
            sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
        });
        overlay.addEventListener('click', closeSidebar);

        sidebar.querySelectorAll('.plf-nav-link, .plf-logout-btn, .plf-user-card').forEach(function (el) {
            el.addEventListener('click', function () {
                if (window.innerWidth < 992) closeSidebar();
            });
        });
    })();

    // Auto-fermeture des flashs après 5s
    ['flash-ok', 'flash-err'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        setTimeout(function () {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 5000);
    });
</script>
<script src="{{ asset('js/form-draft-autosave.js') }}"></script>
@include('partials.ai-agent-widget')
@stack('scripts')
</body>
</html>
