<nav id="sidebar" class="sidebar">
    <!-- Header -->
    <div class="sidebar-header px-3 py-3 text-center border-bottom border-light border-opacity-25">
        <h5 class="mb-0 text-white">
            <i class="fas fa-school me-2"></i>Gestion Scolaire
        </h5>
    </div>
    
    <!-- User Info -->
    <div class="px-3 py-3 text-center border-bottom border-light border-opacity-25">
        <div class="rounded-circle bg-white bg-opacity-25 text-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem;">
            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
        </div>
        <h6 class="mb-0 text-white">{{ Auth::user()->name }}</h6>
        <small class="text-white-50">Administrateur</small>
    </div>
    
    <ul class="nav flex-column mt-3">
        <!-- Tableau de bord -->
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                Tableau de bord
            </a>
        </li>
        
        <!-- Inscriptions en attente -->
        <li class="nav-item">
            <a href="{{ route('admin.registrations.pending') }}" class="nav-link {{ request()->routeIs('admin.registrations.pending') ? 'active' : '' }}">
                <i class="fas fa-user-clock"></i>
                Inscriptions en attente
                @php $pendingCount = \App\Models\User::where('status', 'pending')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>
        
        <!-- Gestion des élèves -->
        <li class="nav-item">
            <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i>
                Gestion des élèves
            </a>
        </li>
        
        <!-- Gestion des enseignants -->
        <li class="nav-item">
            <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i>
                Gestion des enseignants
            </a>
        </li>
        
        <!-- Classes -->
        <li class="nav-item">
            <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard"></i>
                Classes
            </a>
        </li>
        
        <!-- Matières -->
        <li class="nav-item">
            <a href="{{ route('admin.subjects.index') }}" class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                <i class="fas fa-book"></i>
                Matières
            </a>
        </li>
        
        <!-- Années scolaires -->
        <li class="nav-item">
            <a href="{{ route('admin.academic-years.index') }}" class="nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i>
                Années scolaires
            </a>
        </li>
    </ul>
    
    <hr class="mx-3 my-3 border-light border-opacity-25">
    
    <ul class="nav flex-column">
        <!-- Profil -->
        <li class="nav-item">
            <a href="{{ route('admin.profile.edit') }}" class="nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i>
                Mon Profil
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
