<nav id="sidebar" class="sidebar">
    @include('admin.components.sidebar-styles')

    @include('admin.components.sidebar-logo')

    
    <div class="menu-section">
        <i class="fas fa-th-large me-1"></i> Menu Principal
    </div>
    
    <ul class="nav flex-column">
        <!-- Tableau de bord -->
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
        
        <!-- Gestion des élèves -->
        <li class="nav-item">
            <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i>
                <span>Élèves</span>
            </a>
        </li>
        
        <!-- Gestion des enseignants -->
        <li class="nav-item">
            <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Enseignants</span>
            </a>
        </li>
        
        <!-- Classes / Promotions -->
        <li class="nav-item">
            <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                <i class="fas fa-door-open"></i>
                <span>{{ !empty($isFormationSchool) && $isFormationSchool ? 'Promotions' : 'Classes' }}</span>
            </a>
        </li>

        <!-- Emplois du temps -->
        <li class="nav-item">
            <a href="{{ route('admin.schedules.index') }}" class="nav-link {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Emplois du temps</span>
            </a>
        </li>
        
        <!-- Matières / Modules -->
        <li class="nav-item">
            <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                <i class="fas fa-book-open"></i>
                <span>{{ !empty($isFormationSchool) && $isFormationSchool ? 'Modules' : 'Matières' }}</span>
            </a>
        </li>
        
        <!-- Années scolaires -->
        <li class="nav-item">
            <a href="{{ route('admin.academic-years.index') }}" class="nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i>
                <span>Années scolaires</span>
            </a>
        </li>

        <!-- Rapports et exports -->
        <li class="nav-item">
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fas fa-file-export"></i>
                <span>Rapports</span>
            </a>
        </li>

        <!-- E-Learning / LMS -->
        <li class="nav-item">
            <a href="{{ route('admin.lms.index') }}" class="nav-link {{ request()->routeIs('admin.lms.*') ? 'active' : '' }}">
                <i class="fas fa-graduation-cap"></i>
                <span>E-Learning</span>
            </a>
        </li>

        <!-- Cartes Scolaires -->
        <li class="nav-item">
            <a href="{{ route('admin.cards.index') }}" class="nav-link {{ request()->routeIs('admin.cards.*') ? 'active' : '' }}">
                <i class="fas fa-id-card"></i>
                <span>Cartes Scolaires</span>
            </a>
        </li>

        <!-- Journal d'activité -->
        @php $sidebarUnreadNotifCount = Auth::user()?->unreadNotifications()->count() ?? 0; @endphp
        <li class="nav-item">
            <a href="{{ route('admin.audit-log.index') }}" class="nav-link {{ request()->routeIs('admin.audit-log.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span>Journal d'activité</span>
                @if($sidebarUnreadNotifCount > 0)
                    <span class="badge-notif">{{ $sidebarUnreadNotifCount > 99 ? '99+' : $sidebarUnreadNotifCount }}</span>
                @endif
            </a>
        </li>

        @if(\App\Support\SchoolModules::isEnabled($currentSchool ?? null, \App\Support\SchoolModules::ACCOUNTING))
            <!-- Comptes du module Comptabilité -->
            <li class="nav-item">
                <a href="{{ route('admin.accounting-staff.index') }}" class="nav-link {{ request()->routeIs('admin.accounting-staff.*') ? 'active' : '' }}">
                    <i class="fas fa-coins"></i>
                    <span>Comptabilité</span>
                </a>
            </li>
        @endif

        <!-- Informations de l'établissement -->
        <li class="nav-item">
            <a href="{{ route('admin.school.settings.edit') }}" class="nav-link {{ request()->routeIs('admin.school.settings.*') ? 'active' : '' }}">
                <i class="fas fa-school"></i>
                <span>Mon établissement</span>
            </a>
        </li>

        @if(!empty($isFormationSchool) && $isFormationSchool && !empty($usesFormationLmd) && $usesFormationLmd)
            <li class="nav-item">
                <a href="{{ route('admin.formation.lmd-settings.edit') }}" class="nav-link {{ request()->routeIs('admin.formation.lmd-settings.*') ? 'active' : '' }}">
                    <i class="fas fa-sliders-h"></i>
                    <span>Modèle LMD</span>
                </a>
            </li>
        @endif
    </ul>
    
    <div class="menu-separator"></div>
    
    <div class="menu-section">
        <i class="fas fa-cog me-1"></i> Paramètres
    </div>
    
    <ul class="nav flex-column">
        <!-- Profil -->
        <li class="nav-item">
            <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-circle"></i>
                <span>Mon Profil</span>
            </a>
        </li>
        
        <!-- Déconnexion -->
        <li class="nav-item">
            <a href="{{ route('logout.get') }}" class="nav-link logout-link">
                <i class="fas fa-power-off"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</nav>
