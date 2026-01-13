<div class="sidebar">
    <ul class="nav flex-column">
        <!-- Tableau de bord -->
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt me-2"></i>
                Tableau de bord
            </a>
        </li>
        
        <!-- Inscriptions en attente -->
        <li class="nav-item">
            <a href="{{ route('admin.registrations.pending') }}" class="nav-link {{ request()->routeIs('admin.registrations.pending') ? 'active' : '' }}">
                <i class="fas fa-user-clock me-2"></i>
                Inscriptions en attente
                @php $pendingCount = \App\Models\User::where('status', 'pending')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-danger rounded-pill ms-2">{{ $pendingCount }}</span>
                @endif
            </a>
        </li>
        
        <!-- Gestion des élèves -->
        <li class="nav-item">
            <a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate me-2"></i>
                Gestion des élèves
            </a>
        </li>
        
        <!-- Gestion des enseignants -->
        <li class="nav-item">
            <a href="{{ route('admin.teachers.index') }}" class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Gestion des enseignants
            </a>
        </li>
        
        <!-- Classes -->
        <li class="nav-item">
            <a href="{{ route('admin.classes.index') }}" class="nav-link {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard me-2"></i>
                Classes
            </a>
        </li>
        
        <!-- Années scolaires -->
        <li class="nav-item">
            <a href="{{ route('admin.academic-years.index') }}" class="nav-link {{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt me-2"></i>
                Années scolaires
            </a>
        </li>
    </ul>
    
    <hr class="mx-3 my-4">
    
    <!-- Paramètres -->
    <div class="px-3">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-2"></i>
                <strong>Paramètres</strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
