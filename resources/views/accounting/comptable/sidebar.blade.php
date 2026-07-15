<nav id="sidebar" class="sidebar">
    @include('admin.components.sidebar-styles')

    @include('admin.components.sidebar-logo')

    <div class="menu-section">
        <i class="fas fa-th-large me-1"></i> Comptabilité
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.dashboard') }}" class="nav-link {{ request()->routeIs('comptable.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>

    <div class="menu-section">
        <i class="fas fa-cog me-1"></i> Paramètres
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.profile.edit') }}" class="nav-link {{ request()->routeIs('comptable.profile.*') ? 'active' : '' }}">
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
