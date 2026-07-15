<nav id="sidebar" class="sidebar">
    @include('admin.components.sidebar-styles')

    @include('admin.components.sidebar-logo')

    <div class="menu-section">
        <i class="fas fa-th-large me-1"></i> Pilotage financier
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.dashboard') }}" class="nav-link {{ request()->routeIs('directeur.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Vue d'ensemble</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.ledger.index') }}" class="nav-link {{ request()->routeIs('directeur.ledger.*') ? 'active' : '' }}">
                <i class="fas fa-scroll"></i>
                <span>Journal des opérations</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-sliders-h me-1"></i> Paramètres financiers
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.fee-types.index') }}" class="nav-link {{ request()->routeIs('directeur.fee-types.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Frais scolaires</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index') }}" class="nav-link {{ request()->routeIs('directeur.salaries.*') ? 'active' : '' }}">
                <i class="fas fa-money-check-alt"></i>
                <span>Salaires du personnel</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-user-graduate me-1"></i> Élèves
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.students.debtors') }}" class="nav-link {{ request()->routeIs('directeur.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-clock"></i>
                <span>Élèves débiteurs</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-file-export me-1"></i> Rapports
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.ledger.export') }}" class="nav-link">
                <i class="fas fa-file-csv"></i>
                <span>Export grand livre (CSV)</span>
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
