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
        <i class="fas fa-credit-card me-1"></i> Recettes
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.payments.index') }}" class="nav-link {{ request()->routeIs('comptable.payments.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>
                <span>Encaissements</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('comptable.receipts.search') }}" class="nav-link {{ request()->routeIs('comptable.receipts.*') ? 'active' : '' }}">
                <i class="fas fa-search"></i>
                <span>Reçus</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-money-bill-wave me-1"></i> Dépenses
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.expenses.create') }}" class="nav-link {{ request()->routeIs('comptable.expenses.create') ? 'active' : '' }}">
                <i class="fas fa-plus"></i>
                <span>Nouvelle dépense</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('comptable.expenses.index') }}" class="nav-link {{ request()->routeIs('comptable.expenses.index') ? 'active' : '' }}">
                <i class="fas fa-list"></i>
                <span>Historique</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-money-check-alt me-1"></i> Salaires
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.salaries.index', ['role_group' => 'teachers']) }}" class="nav-link {{ request()->routeIs('comptable.salaries.*') && request('role_group') === 'teachers' ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Enseignants</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('comptable.salaries.index', ['role_group' => 'surveillants']) }}" class="nav-link {{ request()->routeIs('comptable.salaries.*') && request('role_group') === 'surveillants' ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Surveillants</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('comptable.salaries.index', ['role_group' => 'admin']) }}" class="nav-link {{ request()->routeIs('comptable.salaries.*') && request('role_group') === 'admin' ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i>
                <span>Administration</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('comptable.salaries.index') }}" class="nav-link {{ request()->routeIs('comptable.salaries.*') && ! request()->has('role_group') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i>
                <span>Paiements du mois</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-vault me-1"></i> Trésorerie
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.dashboard') }}" class="nav-link">
                <i class="fas fa-coins"></i>
                <span>Solde</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('comptable.ledger.index') }}" class="nav-link {{ request()->routeIs('comptable.ledger.*') ? 'active' : '' }}">
                <i class="fas fa-right-left"></i>
                <span>Mouvements</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-user-graduate me-1"></i> Élèves
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.students.debtors') }}" class="nav-link {{ request()->routeIs('comptable.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-clock"></i>
                <span>Élèves débiteurs</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('comptable.payments.index') }}" class="nav-link">
                <i class="fas fa-receipt"></i>
                <span>Paiements</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-book me-1"></i> Journal
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('comptable.ledger.index') }}" class="nav-link {{ request()->routeIs('comptable.ledger.*') ? 'active' : '' }}">
                <i class="fas fa-scroll"></i>
                <span>Journal des opérations</span>
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
