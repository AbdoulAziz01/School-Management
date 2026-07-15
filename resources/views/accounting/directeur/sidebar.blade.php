<nav id="sidebar" class="sidebar">
    @include('admin.components.sidebar-styles')

    @include('admin.components.sidebar-logo')

    <div class="menu-section">
        <i class="fas fa-th-large me-1"></i> Comptabilité
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.dashboard') }}" class="nav-link {{ request()->routeIs('directeur.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.fee-types.index') }}" class="nav-link {{ request()->routeIs('directeur.fee-types.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Frais scolaires</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index') }}" class="nav-link {{ request()->routeIs('directeur.salaries.*') ? 'active' : '' }}">
                <i class="fas fa-money-check-alt"></i>
                <span>Salaires</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>

    <div class="menu-section">
        <i class="fas fa-cog me-1"></i> Paramètres
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.profile.edit') }}" class="nav-link {{ request()->routeIs('directeur.profile.*') ? 'active' : '' }}">
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
