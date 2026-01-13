<div class="sidebar d-none d-lg-block position-fixed h-100">
    <div class="d-flex flex-column h-100">
        <!-- Logo -->
        <div class="p-3 text-center border-bottom">
            <a href="{{ route('student.dashboard') }}" class="text-decoration-none">
                <span class="fs-4 fw-bold text-primary">Espace Étudiant</span>
            </a>
        </div>
        
        <!-- Navigation -->
        <div class="nav flex-column flex-grow-1 p-3">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-home me-2"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('student.timetable') }}" class="nav-link {{ request()->routeIs('student.timetable*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Emploi du temps
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('student.assignments') }}" class="nav-link {{ request()->routeIs('student.assignments*') ? 'active' : '' }}">
                        <i class="fas fa-tasks me-2"></i>
                        Devoirs
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('student.grades') }}" class="nav-link {{ request()->routeIs('student.grades*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line me-2"></i>
                        Notes
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('student.attendance') }}" class="nav-link {{ request()->routeIs('student.attendance*') ? 'active' : '' }}">
                        <i class="fas fa-user-check me-2"></i>
                        Absences
                    </a>
                </li>
            </ul>
            
            <hr>
            
            <!-- Paramètres et Déconnexion -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" alt="" width="32" height="32" class="rounded-circle me-2">
                    <strong>{{ Auth::user()->name }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                    <li><a class="dropdown-item" href="#">Paramètres</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="d-block d-lg-none fixed-bottom bg-white border-top">
    <div class="row text-center">
        <div class="col-3 p-2">
            <a href="{{ route('student.dashboard') }}" class="text-decoration-none {{ request()->routeIs('student.dashboard') ? 'text-primary' : 'text-muted' }}">
                <i class="fas fa-home d-block"></i>
                <small>Accueil</small>
            </a>
        </div>
        <div class="col-3 p-2">
            <a href="{{ route('student.timetable') }}" class="text-decoration-none {{ request()->routeIs('student.timetable*') ? 'text-primary' : 'text-muted' }}">
                <i class="fas fa-calendar-alt d-block"></i>
                <small>EDT</small>
            </a>
        </div>
        <div class="col-3 p-2">
            <a href="{{ route('student.assignments') }}" class="text-decoration-none {{ request()->routeIs('student.assignments*') ? 'text-primary' : 'text-muted' }}">
                <i class="fas fa-tasks d-block"></i>
                <small>Devoirs</small>
            </a>
        </div>
        <div class="col-3 p-2">
            <a href="#" class="text-decoration-none text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-h d-block"></i>
                <small>Plus</small>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('student.grades') }}">Notes</a></li>
                <li><a class="dropdown-item" href="{{ route('student.attendance') }}">Absences</a></li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Déconnexion</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Activer les tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Gérer le menu déroulant mobile
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
</script>
@endpush
