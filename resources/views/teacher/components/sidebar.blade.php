<nav id="sidebar" class="sidebar">
    <style>
        /* ===== DESIGN SIDEBAR DARK AMBER - ENSEIGNANT ===== */
        #sidebar {
            background: linear-gradient(180deg, #1c1917 0%, #292524 50%, #1c1917 100%) !important;
            border-right: none !important;
            box-shadow: none !important;
            border-right: 1px solid rgba(251, 191, 36, 0.12) !important;
        }
        
        .sidebar-logo {
            background: transparent;
            padding: 25px 20px;
            margin: 0;
            position: relative;
            border-bottom: 1px solid rgba(251, 191, 36, 0.2);
        }
        
        .sidebar-logo .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
        }
        
        .sidebar-logo .logo-icon-box {
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

        .sidebar-logo .logo-icon-box.has-school-logo {
            background: #ffffff;
            padding: 4px;
            animation: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .sidebar-logo .logo-icon-box .school-logo-img {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            object-position: center;
            display: block;
        }

        .sidebar-logo .logo-text {
            width: 100%;
        }
        
        @keyframes glow-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(245, 158, 11, 0.4); }
            50% { box-shadow: 0 0 35px rgba(245, 158, 11, 0.6); }
        }
        
        .sidebar-logo .logo-text h5 {
            color: #fbbf24;
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.35;
            margin: 0;
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        
        .sidebar-logo .logo-text small {
            color: rgba(251, 191, 36, 0.6);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: block;
            margin-top: 4px;
        }
        
        .user-card {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.15) 0%, rgba(217, 119, 6, 0.1) 100%);
            margin: 15px;
            padding: 15px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            border: 1px solid rgba(251, 191, 36, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .user-card:hover {
            background: linear-gradient(135deg, rgba(251, 191, 36, 0.25) 0%, rgba(217, 119, 6, 0.15) 100%);
            border-color: rgba(251, 191, 36, 0.4);
        }
        
        .user-avatar {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1c1917;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        
        .user-info h6 {
            color: #fef3c7;
            font-weight: 600;
            margin: 0 0 4px 0;
            font-size: 0.95rem;
        }
        
        .user-info .badge-role {
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
        
        #sidebar .nav-link {
            color: #a8a29e !important;
            background: transparent;
            margin: 3px 12px;
            padding: 12px 16px;
            border-radius: 12px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        #sidebar .nav-link:hover {
            background: rgba(251, 191, 36, 0.1) !important;
            color: #fbbf24 !important;
            padding-left: 22px;
        }
        
        #sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(251, 191, 36, 0.2) 0%, transparent 100%) !important;
            color: #fbbf24 !important;
            font-weight: 600;
        }
        
        #sidebar .nav-link.active::before {
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
        
        #sidebar .nav-link i {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(251, 191, 36, 0.1);
            border-radius: 10px;
            margin-right: 12px;
            font-size: 0.95rem;
            color: #a8a29e;
            transition: all 0.3s ease;
        }
        
        #sidebar .nav-link:hover i,
        #sidebar .nav-link.active i {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
        }
        
        .menu-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(251, 191, 36, 0.2) 50%, transparent 100%);
            margin: 15px 20px;
            border: none;
        }
        
        #sidebar .nav-link.logout-link {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5 !important;
        }
        
        #sidebar .nav-link.logout-link i {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }
        
        #sidebar .nav-link.logout-link:hover {
            background: rgba(239, 68, 68, 0.2) !important;
        }
        
        #sidebar .nav-link.logout-link:hover i {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
    </style>

    <!-- Logo Header -->
    <div class="sidebar-logo">
        <div class="logo-container">
            <div class="logo-icon-box {{ !empty($schoolLogoDataUri) ? 'has-school-logo' : '' }}">
                @if(!empty($schoolLogoDataUri))
                    <img src="{{ $schoolLogoDataUri }}" alt="Logo établissement" class="school-logo-img">
                @else
                    <i class="fas fa-chalkboard-teacher"></i>
                @endif
            </div>
            <div class="logo-text">
                <h5>{{ $schoolDisplayName ?? 'Mon établissement' }}</h5>
                <small>via {{ $platformName }} · Enseignant</small>
            </div>
        </div>
    </div>
    
    <div class="menu-section">
        <i class="fas fa-th-large me-1"></i> Navigation
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.classes.*') ? 'active' : '' }}" href="{{ route('teacher.classes.index') }}">
                <i class="fas fa-users"></i>
                <span>Mes Classes</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.grades.*') ? 'active' : '' }}" href="{{ route('teacher.grades.index') }}">
                <i class="fas fa-star"></i>
                <span>Notes</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}" href="{{ route('teacher.attendance.index') }}">
                <i class="fas fa-clipboard-check"></i>
                <span>Présences</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.schedule') ? 'active' : '' }}" href="{{ route('teacher.schedule') }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Emploi du temps</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.lms.*') ? 'active' : '' }}" href="{{ route('teacher.lms.index') }}">
                <i class="fas fa-graduation-cap"></i>
                <span>E-Learning</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.virtual-class.*') ? 'active' : '' }}" href="{{ route('teacher.virtual-class.index') }}">
                <i class="fas fa-video"></i>
                <span>Classes Virtuelles</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    
    <div class="menu-section">
        <i class="fas fa-cog me-1"></i> Compte
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.profile.*') ? 'active' : '' }}" href="{{ route('teacher.profile.index') }}">
                <i class="fas fa-user-circle"></i>
                <span>Mon Profil</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="{{ route('logout.get') }}" class="nav-link logout-link">
                <i class="fas fa-power-off"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</nav>