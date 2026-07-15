<nav id="sidebar" class="sidebar">
    @include('admin.components.sidebar-styles')

    @include('admin.components.sidebar-logo')

    <div class="menu-section">
        <i class="fas fa-th-large me-1"></i> Guichet
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('caisse.dashboard') }}" class="nav-link {{ request()->routeIs('caisse.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-credit-card me-1"></i> Encaissements
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('caisse.students.search') }}" class="nav-link {{ request()->routeIs('caisse.students.search') && ! request()->filled('intent') ? 'active' : '' }}">
                <i class="fas fa-cash-register"></i>
                <span>Nouveau paiement</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.history', ['scope' => 'today']) }}" class="nav-link {{ request()->routeIs('caisse.history') && request('scope') === 'today' ? 'active' : '' }}">
                <i class="fas fa-calendar-day"></i>
                <span>Paiements du jour</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.history') }}" class="nav-link {{ request()->routeIs('caisse.history') && ! request()->has('scope') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Historique des paiements</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.receipts.search') }}" class="nav-link {{ request()->routeIs('caisse.receipts.search') ? 'active' : '' }}">
                <i class="fas fa-print"></i>
                <span>Réimpression de reçu</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-user-graduate me-1"></i> Élèves
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('caisse.students.search') }}" class="nav-link {{ request()->routeIs('caisse.students.search') && ! request()->filled('intent') ? 'active' : '' }}">
                <i class="fas fa-search"></i>
                <span>Rechercher un élève</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.students.search', ['intent' => 'situation']) }}" class="nav-link {{ request()->routeIs('caisse.students.search') && request('intent') === 'situation' ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Situation financière</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.students.search', ['intent' => 'history']) }}" class="nav-link {{ request()->routeIs('caisse.students.search') && request('intent') === 'history' ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Historique des paiements</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-vault me-1"></i> Caisse
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('caisse.dashboard') }}" class="nav-link {{ request()->routeIs('caisse.dashboard') && ! $sidebarCashSessionOpen ? 'active' : '' }}">
                <i class="fas fa-unlock"></i>
                <span>Ouvrir la caisse</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.session.close-form') }}" class="nav-link {{ request()->routeIs('caisse.session.*') ? 'active' : '' }}">
                <i class="fas fa-lock"></i>
                <span>Clôturer la caisse</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.dashboard') }}" class="nav-link {{ request()->routeIs('caisse.dashboard') && $sidebarCashSessionOpen ? 'active' : '' }}">
                <i class="fas fa-coins"></i>
                <span>Solde actuel</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('caisse.sessions.index') }}" class="nav-link {{ request()->routeIs('caisse.sessions.*') ? 'active' : '' }}">
                <i class="fas fa-clock-rotate-left"></i>
                <span>Historique de caisse</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-receipt me-1"></i> Reçus
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('caisse.receipts.search') }}" class="nav-link {{ request()->routeIs('caisse.receipts.search') ? 'active' : '' }}">
                <i class="fas fa-magnifying-glass"></i>
                <span>Rechercher un reçu</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>

    <div class="menu-section">
        <i class="fas fa-cog me-1"></i> Paramètres
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('caisse.profile.edit') }}" class="nav-link {{ request()->routeIs('caisse.profile.*') ? 'active' : '' }}">
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
