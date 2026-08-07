<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.form-draft-meta')
    <title>@yield('title', 'Tableau de bord - Administration')</title>

    {{-- PWA --}}
    <link rel="icon" type="image/png" href="{{ $platformLogoIcon }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#f59e0b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $platformName }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #f59e0b;
            --primary-dark: #d97706;
            --primary-light: #fef3c7;
            --sidebar-width: 280px;
            --bg-dark: #1c1917;
        }
        
        html {
            margin: 0;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            background: linear-gradient(135deg, #fefce8 0%, #fffbeb 50%, #fef3c7 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            padding: 0;
            overflow-x: hidden;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #1c1917 0%, #292524 50%, #1c1917 100%);
            color: #fef3c7;
            box-shadow: none;
            z-index: 1050;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;             /* pleine hauteur robuste sur tous les mobiles */
            border-right: 1px solid rgba(251, 191, 36, 0.12);
            padding: 0;
            width: var(--sidebar-width);
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar::-webkit-scrollbar {
            display: none;
            width: 0;
        }
        
        .sidebar .nav-link {
            color: #a8a29e;
            padding: 12px 16px;
            margin: 3px 12px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-left: none;
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(251, 191, 36, 0.2) 0%, transparent 100%);
            color: #fbbf24 !important;
            font-weight: 600;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
            text-decoration: none;
            padding-left: 22px;
        }
        
        .sidebar .nav-link i {
            margin-right: 12px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(251, 191, 36, 0.1);
            border-radius: 10px;
            font-size: 0.95rem;
            color: #a8a29e;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover i {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
        }
        
        /* Mobile Header */
        .mobile-header {
            display: none;
            background: linear-gradient(135deg, #1c1917 0%, #292524 100%);
            color: #fbbf24;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .mobile-header .menu-toggle {
            background: none;
            border: none;
            color: #fbbf24;
            font-size: 1.5rem;
            padding: 0;
            cursor: pointer;
        }
        
        .mobile-header h5 {
            margin: 0;
            font-size: 1.1rem;
            color: #fbbf24;
        }
        
        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1045;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        .main-content {
            padding: 0;
            padding-bottom: 50px;
            flex: 1;
            min-width: 0;
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 50%, #fef3c7 100%);
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            overflow-x: hidden;
            overflow-y: visible;
        }

        .admin-page-body {
            padding: 1.5rem 1.5rem 0;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.08);
            margin-bottom: 20px;
            border: 1px solid #fde68a;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.15);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: linear-gradient(90deg, #fffbeb 0%, #ffffff 100%);
            border-bottom: 2px solid #fde68a;
            border-radius: 16px 16px 0 0 !important;
            padding: 1rem 1.25rem;
        }
        
        .user-dropdown .dropdown-menu {
            left: auto;
            right: 0;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-success {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-danger {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #dc2626;
            color: #92400e;
        }
        
        .alert-warning {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #d97706;
            color: #92400e;
        }
        
        .alert-info {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #fbbf24;
            color: #92400e;
        }

        /* Notifications flash — toast discret en bas à droite, disparaît seul */
        .toast-notifications-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2000;
            display: flex;
            flex-direction: column-reverse;
            gap: 10px;
            max-width: min(360px, calc(100vw - 40px));
        }
        .toast-notification {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            border-radius: 12px;
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.18);
            font-size: 14px;
            line-height: 1.4;
            border-left: 4px solid #f59e0b;
            animation: toast-notification-in 0.25s ease-out;
        }
        .toast-notification.toast-notification-success { border-left-color: #16a34a; }
        .toast-notification.toast-notification-error { border-left-color: #dc2626; }
        .toast-notification.toast-notification-warning { border-left-color: #d97706; }
        .toast-notification.toast-notification-info { border-left-color: #fbbf24; }
        .toast-notification i { margin-top: 2px; }
        .toast-notification span { flex: 1; }
        .toast-notification .toast-notification-close {
            background: none;
            border: none;
            font-size: 16px;
            line-height: 1;
            color: #92400e;
            opacity: 0.6;
            cursor: pointer;
            padding: 0;
        }
        .toast-notification .toast-notification-close:hover { opacity: 1; }
        .toast-notification.toast-notification-hide {
            animation: toast-notification-out 0.2s ease-in forwards;
        }
        @keyframes toast-notification-in {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes toast-notification-out {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(12px); }
        }

        /* Button Styles - Palette Unifiée */
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            transition: all 0.3s ease;
            color: #1c1917;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
            color: #1c1917;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border: none;
            color: #1c1917;
            font-weight: 600;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #57534e 0%, #44403c 100%);
            border: none;
            color: #fef3c7;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #44403c 0%, #292524 100%);
            color: #fef3c7;
        }
        
        .btn-outline-primary {
            border: 2px solid #f59e0b;
            color: #d97706;
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
        }
        
        /* Pagination Styles */
        .pagination {
            margin-bottom: 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .pagination .page-link {
            color: var(--primary-color);
            border-radius: 10px;
            margin: 2px;
            padding: 0.5rem 0.75rem;
            border: 1px solid #fde68a;
            transition: all 0.3s ease;
        }
        
        .pagination .page-link:hover {
            background: #fef3c7;
            color: #92400e;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-color: #f59e0b;
            color: #1c1917;
        }
        
        .pagination .page-link svg {
            width: 1rem;
            height: 1rem;
        }
        
        /* Responsive Styles */
        /* ── Bouton toggle desktop ───────────────────────────────────── */
        @media (min-width: 992px) {
            .sidebar-toggle-btn {
                position: fixed;
                top: 50%;
                transform: translateY(-50%);
                left: calc(var(--sidebar-width) - 14px);
                z-index: 1060;
                width: 28px; height: 28px;
                border-radius: 50%;
                background: #f59e0b;
                color: #1c1917;
                border: 2.5px solid #fff;
                cursor: pointer;
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 2px 10px rgba(0,0,0,.30);
                font-size: 10px; font-weight: 800;
                transition: left 0.4s cubic-bezier(0.4,0,0.2,1), background .18s;
                padding: 0;
            }
            .sidebar-toggle-btn:hover { background: #d97706; }

            /* État réduit — sidebar cachée */
            body.sidebar-hidden .sidebar {
                transform: translateX(-100%);
            }
            body.sidebar-hidden .main-content {
                margin-left: 0 !important;
                width:     100vw !important;
                max-width: 100vw !important;
            }
            body.sidebar-hidden .sidebar-toggle-btn {
                left: 6px;
            }

            /* Transition douce sur le contenu */
            .main-content {
                transition: margin-left 0.4s cubic-bezier(0.4,0,0.2,1),
                            width       0.4s cubic-bezier(0.4,0,0.2,1),
                            max-width   0.4s cubic-bezier(0.4,0,0.2,1);
            }
        }

        @media (max-width: 991.98px) {
            .sidebar-toggle-btn { display: none !important; }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .mobile-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                padding: 0;
                padding-top: 56px;
                padding-bottom: 50px;
            }

            /* Le portal-top-navbar doit se coller SOUS le mobile-header fixe (56px) */
            .portal-top-navbar {
                top: 56px !important;
            }

            .admin-page-body {
                padding: 1rem 0.85rem 0;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0;
                padding-top: 56px;
                padding-bottom: 40px;
            }

            .admin-page-body {
                padding: 0.75rem 0.65rem 0;
            }

            .card-body {
                padding: 12px;
            }

            /* Card-header : empile les éléments verticalement sur xs */
            .card-header {
                padding: 0.65rem 0.75rem;
            }
            .card-header > .d-flex {
                flex-wrap: wrap !important;
                gap: 0.5rem !important;
            }
            .card-header form.d-flex {
                flex-wrap: wrap !important;
                width: 100%;
            }
            .card-header .form-control,
            .card-header .form-select {
                min-width: 0 !important;
                flex: 1 1 120px;
            }

            /* Tables sur mobile */
            .table-responsive {
                font-size: 0.8rem;
            }
            td .btn-sm {
                padding: 0.2rem 0.35rem;
                font-size: 0.72rem;
            }

            .btn-group {
                flex-direction: column;
            }
            .btn-group .btn {
                border-radius: 4px !important;
                margin-bottom: 2px;
            }

            h1, .h1 { font-size: 1.4rem; }
            h2, .h2 { font-size: 1.25rem; }
            h3, .h3 { font-size: 1.1rem; }
        }
        
        /* Table Styles */
        .table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%);
            color: #92400e;
            font-weight: 600;
            border-bottom: 2px solid #fde68a;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #fffbeb;
        }
        
        /* Badge variants - Palette unifiée */
        .badge.bg-success {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
            color: #1c1917 !important;
        }
        
        .badge.bg-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #1c1917 !important;
        }
        
        .badge.bg-info {
            background: linear-gradient(135deg, #fde68a 0%, #fcd34d 100%) !important;
            color: #92400e !important;
        }
        
        .badge.bg-warning {
            background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%) !important;
            color: #1c1917 !important;
        }
        
        .badge.bg-secondary {
            background: linear-gradient(135deg, #57534e 0%, #44403c 100%) !important;
            color: #fef3c7 !important;
        }
        
        /* Text colors - Orange theme */
        .text-primary {
            color: #fd7e14 !important;
        }
        
        .bg-primary {
            background-color: #fd7e14 !important;
        }
        
        .bg-primary.bg-opacity-10 {
            background-color: rgba(253, 126, 20, 0.1) !important;
        }
        
        /* Avatar styles */
        .avatar-title.text-primary {
            color: #fd7e14 !important;
        }
        
        .avatar-title.bg-primary {
            background-color: #fd7e14 !important;
        }
        
        /* Icon colors */
        .fas.text-primary, .far.text-primary, .fa.text-primary {
            color: #fd7e14 !important;
        }
        
        /* Border colors */
        .border-primary {
            border-color: #fd7e14 !important;
        }
        
        /* Heading styles */
        h1, h2, h3, h4, h5, h6 {
            color: #92400e;
        }
        
        /* Links */
        a {
            color: #d97706;
            transition: color 0.2s ease;
        }
        
        a:hover {
            color: #b45309;
        }

        /* ── Modales ancrées à droite ──────────────────────────────
           Préférence utilisateur : plus aucune modale centrée à
           l'écran. Règle globale (aucune modale, présente ou future,
           n'a besoin d'être modifiée individuellement) : toute modale
           Bootstrap standard glisse depuis la droite et occupe toute
           la hauteur, quelle que soit sa taille (modal-lg, modal-xl,
           modal-dialog-centered…). */
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
    @include('partials.portal-top-navbar-styles')
    @stack('styles')
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <button class="menu-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h5><i class="fas fa-graduation-cap me-2"></i>{{ $schoolDisplayName ?? 'Mon établissement' }}</h5>
        <div style="width: 24px;"></div>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Bouton toggle sidebar desktop -->
    <button id="desktopSidebarToggle" class="sidebar-toggle-btn" title="Réduire/Afficher le menu">
        <i class="fas fa-chevron-left" id="deskToggleIcon"></i>
    </button>
    
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        @include($sidebarView ?? 'admin.components.sidebar')
        <script>
            (function () {
                var saved = sessionStorage.getItem('adminSidebarScrollTop');
                var sidebar = document.getElementById('sidebar');
                if (!sidebar || saved === null) return;
                sidebar.scrollTop = parseInt(saved, 10) || 0;
            })();
        </script>

        <!-- Main content -->
        <main class="main-content">
            @include($navbarView ?? 'admin.components.navbar')

            <div class="admin-page-body">
            @if(session('success') || session('info') || session('error') || session('warning'))
                <div class="toast-notifications-container">
                    @if(session('success'))
                        <div class="toast-notification toast-notification-success" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('success') }}</span>
                            <button type="button" class="toast-notification-close" aria-label="Fermer">&times;</button>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="toast-notification toast-notification-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <span>{{ session('info') }}</span>
                            <button type="button" class="toast-notification-close" aria-label="Fermer">&times;</button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="toast-notification toast-notification-error" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ session('error') }}</span>
                            <button type="button" class="toast-notification-close" aria-label="Fermer">&times;</button>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div class="toast-notification toast-notification-warning" role="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ session('warning') }}</span>
                            <button type="button" class="toast-notification-close" aria-label="Fermer">&times;</button>
                        </div>
                    @endif
                </div>
            @endif

            @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script pour le sidebar toggle mobile et le sidebar overlay pour le mobile -->
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        });
        
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            this.classList.remove('active');
        });
        
        // Close sidebar when clicking a link on mobile
        document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('sidebarOverlay').classList.remove('active');
                }
            });
        });

        // ── Toggle sidebar desktop ───────────────────────────────────
        (function () {
            var btn  = document.getElementById('desktopSidebarToggle');
            var icon = document.getElementById('deskToggleIcon');
            if (!btn) return;

            var hidden = localStorage.getItem('adminSidebarHidden') === '1';

            function applyState(animate) {
                if (hidden) {
                    document.body.classList.add('sidebar-hidden');
                    icon.className = 'fas fa-chevron-right';
                    btn.title = 'Afficher le menu';
                } else {
                    document.body.classList.remove('sidebar-hidden');
                    icon.className = 'fas fa-chevron-left';
                    btn.title = 'Réduire le menu';
                }
            }

            applyState(); // restaurer l'état au chargement

            btn.addEventListener('click', function () {
                hidden = !hidden;
                localStorage.setItem('adminSidebarHidden', hidden ? '1' : '0');
                applyState();
            });
        })();

        // Conserver la position de scroll du sidebar entre les pages
        (function () {
            var storageKey = 'adminSidebarScrollTop';
            var sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            function saveScroll() {
                sessionStorage.setItem(storageKey, String(sidebar.scrollTop));
            }

            sidebar.addEventListener('scroll', saveScroll, { passive: true });

            sidebar.querySelectorAll('.nav-link[href]').forEach(function (link) {
                link.addEventListener('click', saveScroll);
            });

            window.addEventListener('beforeunload', saveScroll);

            if (sessionStorage.getItem(storageKey) === null) {
                var activeLink = sidebar.querySelector('.nav-link.active');
                if (activeLink) {
                    activeLink.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                    saveScroll();
                }
            }
        })();

        document.querySelectorAll('.alert.alert-dismissible:not(.alert-no-autoclose)').forEach(function (alertEl) {
            setTimeout(function () {
                bootstrap.Alert.getOrCreateInstance(alertEl).close();
            }, 5000);
        });

        // Notifications flash (succès/erreur/etc.) : toast en bas à droite,
        // disparaît seul après 10s ou au clic sur la croix.
        document.querySelectorAll('.toast-notification').forEach(function (toastEl) {
            var dismiss = function () {
                toastEl.classList.add('toast-notification-hide');
                setTimeout(function () { toastEl.remove(); }, 200);
            };
            var closeBtn = toastEl.querySelector('.toast-notification-close');
            if (closeBtn) closeBtn.addEventListener('click', dismiss);
            setTimeout(dismiss, 10000);
        });
    </script>
    
    @include('partials.ai-agent-widget')
    <script src="{{ asset('js/form-draft-autosave.js') }}"></script>
    @stack('scripts')

    {{-- PWA Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
