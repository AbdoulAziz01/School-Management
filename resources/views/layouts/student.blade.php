<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            min-height: 100vh;
            height: 100vh;
            background: linear-gradient(180deg, #1c1917 0%, #292524 50%, #1c1917 100%);
            color: #fef3c7;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1050;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.3);
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
            margin: 15px;
            padding: 15px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            border: 1px solid rgba(251, 191, 36, 0.2);
            transition: all 0.4s ease;
        }
        
        .sidebar-user:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.25) 0%, rgba(217, 119, 6, 0.15) 100%);
            border-color: rgba(251, 191, 36, 0.4);
        }
        
        .user-avatar {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        
        .sidebar-user .user-info {
            flex: 1;
        }
        
        .sidebar-user h6 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #fef3c7;
            font-size: 0.95rem;
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
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: white;
            padding: 15px 25px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid #fde68a;
        }
        
        .top-navbar .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #92400e;
            margin: 0;
        }
        
        .top-navbar .navbar-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 8px;
            transition: background 0.2s;
            color: #92400e;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            background: #fef3c7;
        }
        
        .user-dropdown .user-avatar-sm {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Content Area */
        .content-area {
            padding: 25px;
            padding-bottom: 50px;
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
        
        /* Responsive */
        @media (max-width: 991.98px) {
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
            }
            
            .top-navbar {
                display: none;
            }
            
            .content-area {
                padding: 15px;
                padding-top: 80px;
                padding-bottom: 50px;
            }
            
            .sidebar-toggle {
                display: block !important;
            }
        }
        
        @media (max-width: 576px) {
            .content-area {
                padding: 10px;
                padding-top: 75px;
                padding-bottom: 40px;
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
        
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #92400e;
            cursor: pointer;
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
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <button class="menu-toggle" id="sidebarToggleMobile">
            <i class="fas fa-bars"></i>
        </button>
        <h5><i class="fas fa-graduation-cap me-2"></i>Espace Élève</h5>
        <div style="width: 24px;"></div>
    </div>
    
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
            
            <div class="sidebar-user">
                <div class="user-avatar">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="user-info">
                    <h6>{{ Auth::user()->name ?? 'Utilisateur' }}</h6>
                    <small><i class="fas fa-user-graduate"></i> Élève</small>
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
            <!-- Top Navbar -->
            <div class="top-navbar">
                <div class="d-flex align-items-center">
                    <button class="sidebar-toggle me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('title', 'Tableau de bord')</h1>
                </div>
                
                <div class="navbar-actions">
                    <div class="dropdown user-dropdown">
                        <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar-sm">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Utilisateur' }}</span>
                            <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('student.profile.index') }}"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a href="{{ route('logout.get') }}" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area">
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
        var sidebarToggle = document.getElementById('sidebarToggle');
        var sidebarToggleMobile = document.getElementById('sidebarToggleMobile');
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }
        
        if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
        if (sidebarToggleMobile) sidebarToggleMobile.addEventListener('click', toggleSidebar);
        
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
    
    @include('partials.botpress-webchat')
    @stack('scripts')
</body>
</html>
