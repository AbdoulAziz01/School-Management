<nav id="sidebar" class="sidebar">
    <!-- Header -->
    <div class="sidebar-header px-3 py-3 text-center border-bottom border-light border-opacity-25">
        <h5 class="mb-0 text-white">
            <i class="fas fa-chalkboard-teacher me-2"></i>Espace Enseignant
        </h5>
    </div>
    
    <!-- User Info -->
    <div class="px-3 py-3 text-center border-bottom border-light border-opacity-25">
        <div class="rounded-circle bg-white bg-opacity-25 text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem;">
            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
        </div>
        <h6 class="mb-0 text-white">{{ Auth::user()->name }}</h6>
        <small class="text-white-50">Enseignant</small>
    </div>
    
    <ul class="nav flex-column mt-3">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}" href="{{ route('teacher.dashboard') }}">
                <i class="fas fa-home"></i> Tableau de bord
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.classes.*') ? 'active' : '' }}" href="{{ route('teacher.classes.index') }}">
                <i class="fas fa-users"></i> Mes Classes
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.grades.*') ? 'active' : '' }}" href="{{ route('teacher.grades.index') }}">
                <i class="fas fa-star"></i> Notes
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}" href="{{ route('teacher.attendance.index') }}">
                <i class="fas fa-clipboard-check"></i> Présences
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.schedule') ? 'active' : '' }}" href="{{ route('teacher.schedule') }}">
                <i class="fas fa-calendar-alt"></i> Emploi du temps
            </a>
        </li>
    </ul>
    
    <hr class="mx-3 my-3 border-light border-opacity-25">
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('teacher.profile.*') ? 'active' : '' }}" href="{{ route('teacher.profile.index') }}">
                <i class="fas fa-user-cog"></i> Mon Profil
            </a>
        </li>
        
        <!-- Déconnexion -->
        <li class="nav-item">
            <a href="{{ route('logout.get') }}" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </a>
        </li>
    </ul>
</nav>
