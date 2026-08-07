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
            <a href="{{ route('directeur.academic-year.edit') }}" class="nav-link {{ request()->routeIs('directeur.academic-year.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Année scolaire</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-money-check-alt me-1"></i> Gestion des salaires
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index', ['role_group' => 'teachers']) }}" class="nav-link {{ request()->routeIs('directeur.salaries.index') && request('role_group') === 'teachers' ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Enseignants</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index', ['role_group' => 'surveillants']) }}" class="nav-link {{ request()->routeIs('directeur.salaries.index') && request('role_group') === 'surveillants' ? 'active' : '' }}">
                <i class="fas fa-user-shield"></i>
                <span>Surveillants</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index', ['role_group' => 'admin']) }}" class="nav-link {{ request()->routeIs('directeur.salaries.index') && request('role_group') === 'admin' ? 'active' : '' }}">
                <i class="fas fa-user-tie"></i>
                <span>Personnel administratif</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index', ['role_group' => 'accounting']) }}" class="nav-link {{ request()->routeIs('directeur.salaries.index') && request('role_group') === 'accounting' ? 'active' : '' }}">
                <i class="fas fa-cash-register"></i>
                <span>Comptables & Caissiers</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index', ['role_group' => 'direction']) }}" class="nav-link {{ request()->routeIs('directeur.salaries.index') && request('role_group') === 'direction' ? 'active' : '' }}">
                <i class="fas fa-crown"></i>
                <span>Direction</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.index') }}" class="nav-link {{ request()->routeIs('directeur.salaries.index') && ! request()->has('role_group') ? 'active' : '' }}">
                <i class="fas fa-list"></i>
                <span>Tout le personnel</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.salaries.checklist') }}" class="nav-link {{ request()->routeIs('directeur.salaries.checklist') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check"></i>
                <span>Suivi des paiements</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-user-graduate me-1"></i> Élèves
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.payments.index') }}" class="nav-link {{ request()->routeIs('directeur.payments.*') ? 'active' : '' }}">
                <i class="fas fa-receipt"></i>
                <span>Paiements</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.students.debtors') }}" class="nav-link {{ request()->routeIs('directeur.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-clock"></i>
                <span>Élèves débiteurs</span>
            </a>
        </li>
    </ul>

    <div class="menu-separator"></div>
    <div class="menu-section">
        <i class="fas fa-users me-1"></i> Établissement
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('directeur.school.classes.index') }}" class="nav-link {{ request()->routeIs('directeur.school.classes.*') || request()->routeIs('directeur.school.subjects.*') ? 'active' : '' }}">
                <i class="fas fa-door-open"></i>
                <span>Classes</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.school.teachers.index') }}" class="nav-link {{ request()->routeIs('directeur.school.teachers.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Enseignants</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.school.students.index') }}" class="nav-link {{ request()->routeIs('directeur.school.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i>
                <span>Élèves</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.personnel.index') }}" class="nav-link {{ request()->routeIs('directeur.personnel.*') && ! request()->routeIs('directeur.personnel.create') ? 'active' : '' }}">
                <i class="fas fa-address-book"></i>
                <span>Personnel & Élèves</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('directeur.personnel.create') }}" class="nav-link {{ request()->routeIs('directeur.personnel.create') ? 'active' : '' }}">
                <i class="fas fa-user-plus"></i>
                <span>Comptables & Caissiers</span>
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
