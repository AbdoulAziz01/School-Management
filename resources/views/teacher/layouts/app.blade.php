<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.form-draft-meta')
    <title>@yield('title', 'Tableau de bord - Enseignant')</title>

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
        
        html { margin: 0; overflow-x: hidden; }

        body {
            margin: 0;
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 50%, #fef3c7 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            padding: 0;
            overflow-x: hidden;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
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
        
        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            transform: scale(1.1);
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.4);
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

        .portal-page-body {
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

        .stat-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid #fde68a;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(245, 158, 11, 0.2);
        }
        
        .stat-label {
            color: #92400e;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: #92400e;
            margin: 0;
        }
        
        .table th {
            font-weight: 600;
            color: #92400e;
            border-top: none;
            background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%);
        }
        
        .table tbody tr:hover {
            background-color: #fffbeb;
        }
        
        .badge-present { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #1c1917; }
        .badge-absent { background: linear-gradient(135deg, #57534e 0%, #44403c 100%); color: #fef3c7; }
        .badge-late { background: linear-gradient(135deg, #fcd34d 0%, #fbbf24 100%); color: #1c1917; }
        .badge-excused { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #fef3c7; }
        
        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
            color: #1c1917;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            color: #1c1917;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border: none;
            color: #1c1917;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #57534e 0%, #44403c 100%);
            border: none;
            color: #fef3c7;
        }
        
        /* Heading styles */
        h1, h2, h3, h4, h5, h6 {
            color: #92400e;
        }
        
        a {
            color: #d97706;
        }
        
        a:hover {
            color: #b45309;
        }
        
        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                padding: 0;
                padding-bottom: 50px;
            }

            .portal-page-body {
                padding: 1rem 0.85rem 0;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 0;
                padding-bottom: 40px;
            }

            .portal-page-body {
                padding: 0.75rem 0.65rem 0;
            }

            .card-body {
                padding: 12px;
            }

            /* Card-header : empile les éléments sur xs */
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

            .table-responsive {
                font-size: 0.8rem;
            }
            td .btn-sm {
                padding: 0.2rem 0.35rem;
                font-size: 0.72rem;
            }

            .stat-value {
                font-size: 1.4rem;
            }

            h1, .h1 { font-size: 1.4rem; }
            h2, .h2 { font-size: 1.25rem; }
            h3, .h3 { font-size: 1.1rem; }
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
    @include('partials.portal-top-navbar-styles')
    @stack('styles')
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        @include('teacher.components.sidebar')
        <script>
            (function () {
                var saved = sessionStorage.getItem('teacherSidebarScrollTop');
                var sidebar = document.getElementById('sidebar');
                if (!sidebar || saved === null) return;
                sidebar.scrollTop = parseInt(saved, 10) || 0;
            })();
        </script>

        <!-- Main content -->
        <main class="main-content">
            @include('partials.portal-top-navbar', [
                'portalScope' => 'teacher',
                'portalProfileRoute' => route('teacher.profile.index'),
                'portalRoleLabel' => 'Enseignant',
                'portalShowMenuToggle' => true,
            ])

            <div class="portal-page-body">
            @if(session('success') || session('error') || session('warning'))
                <div class="toast-notifications-container">
                    @if(session('success'))
                        <div class="toast-notification toast-notification-success" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('success') }}</span>
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
    
    <script>
        function toggleTeacherSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        document.getElementById('portalSidebarToggle')?.addEventListener('click', toggleTeacherSidebar);
        
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

        // Conserver la position de scroll du sidebar entre les pages
        (function () {
            var storageKey = 'teacherSidebarScrollTop';
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

        document.querySelectorAll('.alert.alert-dismissible').forEach(function (alertEl) {
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
