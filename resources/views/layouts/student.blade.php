<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.form-draft-meta')
    <title>@yield('title', 'Tableau de bord') - Espace Élève</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    
    @stack('styles')
    
    <style>
        :root {
            --primary-color: #f59e0b;
            --primary-dark: #d97706;
            --primary-light: #fef3c7;
            --sidebar-width: 280px;
            --bg-dark: #1c1917;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #fffbeb 0%, #ffffff 50%, #fef3c7 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
        }
        
        /* Wrapper */
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1c1917 0%, #292524 50%, #1c1917 100%);
            color: #fef3c7;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1050;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            box-shadow: none;
            border-right: 1px solid rgba(251, 191, 36, 0.12);
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar::-webkit-scrollbar {
            display: none;
            width: 0;
        }
        
        .sidebar-header {
            padding: 25px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
            border-bottom: 1px solid rgba(251, 191, 36, 0.2);
        }
        
        .sidebar-header .logo-icon-box {
            width: 52px;
            height: 52px;
            min-width: 52px;
            min-height: 52px;
            flex-shrink: 0;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #1c1917;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.4);
            animation: glow-pulse 2s ease-in-out infinite;
            overflow: hidden;
            padding: 0;
        }

        .sidebar-header .logo-icon-box.has-school-logo {
            background: #ffffff;
            padding: 4px;
            animation: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .sidebar-header .logo-icon-box .school-logo-img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
        }

        .sidebar-header .logo-text {
            width: 100%;
        }
        
        .sidebar-header .logo-text h3 {
            margin: 0;
            font-size: 1.05rem;
            line-height: 1.35;
            font-weight: 700;
            color: #fbbf24;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        
        .sidebar-header .logo-text small {
            color: rgba(251, 191, 36, 0.6);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
            margin-top: 4px;
        }

        @keyframes glow-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(245, 158, 11, 0.4); }
            50% { box-shadow: 0 0 35px rgba(245, 158, 11, 0.6); }
        }
        
        .sidebar-user {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(217, 119, 6, 0.1) 100%);
            margin: 10px 12px;
            padding: 10px 12px;
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid rgba(251, 191, 36, 0.2);
            transition: all 0.4s ease;
        }
        
        .sidebar-user:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.25) 0%, rgba(217, 119, 6, 0.15) 100%);
            border-color: rgba(251, 191, 36, 0.4);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
        }
        
        .sidebar-user .user-info {
            flex: 1;
            min-width: 0;
        }
        
        .sidebar-user h6 {
            margin: 0 0 3px 0;
            font-weight: 600;
            color: #fef3c7;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
        }
        
        .sidebar-user small.role-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #57534e 0%, #44403c 100%);
            color: #fbbf24;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sidebar-user-class-tag {
            font-size: 0.72rem;
            font-weight: 600;
            color: #fde68a;
            line-height: 1.2;
        }

        .sidebar-user-year {
            display: block;
            margin-top: 2px;
            font-size: 0.62rem;
            color: rgba(254, 243, 199, 0.55);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sidebar-user small {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #57534e 0%, #44403c 100%);
            color: #fbbf24;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .menu-section {
            padding: 20px 20px 10px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(251, 191, 36, 0.5);
        }
        
        .sidebar-nav {
            padding: 0;
        }
        
        .sidebar-nav .nav-link {
            color: #a8a29e;
            padding: 12px 16px;
            margin: 3px 12px;
            display: flex;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            text-decoration: none;
            position: relative;
            font-size: 0.9rem;
        }
        
        .sidebar-nav .nav-link:hover {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
            padding-left: 22px;
        }
        
        .sidebar-nav .nav-link.active {
            background: linear-gradient(90deg, rgba(251, 191, 36, 0.2) 0%, transparent 100%);
            color: #fbbf24;
            font-weight: 600;
        }
        
        .sidebar-nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 0 4px 4px 0;
        }
        
        .sidebar-nav .nav-link i {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(251, 191, 36, 0.1);
            border-radius: 10px;
            margin-right: 12px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .sidebar-nav .nav-link:hover i,
        .sidebar-nav .nav-link.active i {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
        }
        
        .sidebar-nav .nav-link.logout-link {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }
        
        .sidebar-nav .nav-link.logout-link i {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }
        
        .sidebar-nav .nav-link.logout-link:hover {
            background: rgba(239, 68, 68, 0.2);
        }
        
        .sidebar-nav .nav-link.logout-link:hover i {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .menu-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(251, 191, 36, 0.2) 50%, transparent 100%);
            margin: 15px 20px;
            border: none;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-width: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .portal-page-body {
            padding: 1.5rem 1.5rem 2rem;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.08);
            background: white;
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
            padding: 15px 20px;
            font-weight: 600;
            color: #92400e;
            border-radius: 16px 16px 0 0 !important;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Alerts */
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
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: #1c1917;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #1c1917;
        }
        
        /* Tables */
        .table thead th {
            background: linear-gradient(90deg, #fffbeb 0%, #fef3c7 100%);
            color: #92400e;
            font-weight: 600;
            border-bottom: 2px solid #fde68a;
        }
        
        .table tbody tr:hover {
            background-color: #fffbeb;
        }
        
        /* Headings */
        h1, h2, h3, h4, h5, h6 {
            color: #92400e;
        }
        
        a {
            color: #d97706;
        }
        
        a:hover {
            color: #b45309;
        }
        
        /* Responsive */
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
                padding-top: 0;
            }

            .portal-page-body {
                padding: 1rem 0.85rem 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .portal-page-body {
                padding: 0.75rem 0.65rem 1.25rem;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            h1, .h1 { font-size: 1.5rem; }
            h2, .h2 { font-size: 1.3rem; }
            h3, .h3 { font-size: 1.15rem; }
        }
        
        /* Overlay for mobile */
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
    </style>
    @include('partials.portal-top-navbar-styles')
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-icon-box {{ !empty($schoolLogoDataUri) ? 'has-school-logo' : '' }}">
                    @if(!empty($schoolLogoDataUri))
                        <img src="{{ $schoolLogoDataUri }}" alt="Logo" class="school-logo-img">
                    @else
                        <i class="fas fa-graduation-cap"></i>
                    @endif
                </div>
                <div class="logo-text">
                    <h3>{{ $schoolDisplayName ?? 'Mon établissement' }}</h3>
                    <small>via {{ $platformName }} · Élève</small>
                </div>
            </div>
            
            <div class="menu-section">
                <i class="fas fa-th-large me-1"></i> Navigation
            </div>
            
            <div class="sidebar-nav">
                <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Tableau de bord</span>
                </a>
                <a href="{{ route('student.grades') }}" class="nav-link {{ request()->routeIs('student.grades') ? 'active' : '' }}">
                    <i class="fas fa-star"></i>
                    <span>Mes notes</span>
                </a>
                <a href="{{ route('student.schedule') }}" class="nav-link {{ request()->routeIs('student.schedule') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Emploi du temps</span>
                </a>
                <a href="{{ route('student.attendance') }}" class="nav-link {{ request()->routeIs('student.attendance') ? 'active' : '' }}">
                    <i class="fas fa-user-check"></i>
                    <span>Mes présences</span>
                </a>

                <a href="{{ route('student.lms.index') }}" class="nav-link {{ request()->routeIs('student.lms.*') || request()->routeIs('student.quiz.*') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>E-Learning</span>
                </a>

                <a href="{{ route('student.virtual-class.index') }}" class="nav-link {{ request()->routeIs('student.virtual-class.*') ? 'active' : '' }}">
                    <i class="fas fa-video"></i>
                    <span>Classes Virtuelles</span>
                </a>

                <a href="{{ route('student.card.show') }}" class="nav-link {{ request()->routeIs('student.card.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card"></i>
                    <span>Ma Carte Scolaire</span>
                </a>

                @if(\App\Support\SchoolModules::isEnabled($currentSchool ?? null, \App\Support\SchoolModules::ACCOUNTING))
                    <a href="{{ route('student.payments.index') }}" class="nav-link {{ request()->routeIs('student.payments.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Mes paiements</span>
                    </a>
                @endif

                <div class="menu-separator"></div>
                
                <div class="menu-section">
                    <i class="fas fa-cog me-1"></i> Compte
                </div>
                
                <a href="{{ route('student.profile.index') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Mon profil</span>
                </a>
                
                <a href="{{ route('logout.get') }}" class="nav-link logout-link">
                    <i class="fas fa-power-off"></i>
                    <span>Déconnexion</span>
                </a>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            @include('partials.portal-top-navbar', [
                'portalScope' => 'student',
                'portalProfileRoute' => route('student.profile.index'),
                'portalRoleLabel' => 'Élève',
                'portalShowMenuToggle' => true,
            ])

            <div class="portal-page-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <strong>Erreur !</strong> Veuillez corriger les erreurs ci-dessous.
                        <ul class="mt-2 mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle for Mobile
        var portalSidebarToggle = document.getElementById('portalSidebarToggle');

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        if (portalSidebarToggle) portalSidebarToggle.addEventListener('click', toggleSidebar);
        
        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            this.classList.remove('active');
        });
        
        // Close sidebar when clicking a link on mobile
        document.querySelectorAll('.sidebar .nav-link, .sidebar-nav .nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('sidebarOverlay').classList.remove('active');
                }
            });
        });
    </script>
    <script>
        document.querySelectorAll('.alert.alert-dismissible').forEach(function (alertEl) {
            setTimeout(function () {
                bootstrap.Alert.getOrCreateInstance(alertEl).close();
            }, 5000);
        });
    </script>
    
    @include('partials.ai-agent-widget')
    <script src="{{ asset('js/form-draft-autosave.js') }}"></script>
    @stack('scripts')
</body>
</html>
