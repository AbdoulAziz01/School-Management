<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Laravel - Administration')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background: #1e3a8a;
            color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            z-index: 1000;
            position: sticky;
            top: 56px;
            width: 280px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 10px 15px;
            margin: 2px 10px;
            border-radius: 5px;
            transition: all 0.2s;
            font-size: 0.95rem;
        }
        
        .sidebar .nav-link:hover {
            background-color: white;
            color: red;
            font-weight: 500;
        }
        
        .sidebar .nav-link.active {
            background-color: white;
            color: #1e3a8a;
            font-weight: 500;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 300px;
            padding: 40px 60px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        /* Espacement pour les conteneurs */
        .main-content .container-fluid {
            max-width: 100%;
            padding-left: 0;
            padding-right: 0;
        }
        
        /* Espacement du header */
        .main-content h1,
        .main-content .h3 {
            margin-bottom: 1.5rem;
        }
        
        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .avatar-sm {
            width: 40px;
            height: 40px;
        }
        
        .avatar-title {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        /* Boutons */
        .btn {
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Tables */
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="px-3 mb-4">
            <h4 class="mb-0 text-center text-white">
                <i class="fas fa-graduation-cap me-2"></i>
                Admin Panel
            </h4>
        </div>
        
        <ul class="mb-auto nav nav-pills flex-column">
            <!-- Tableau de bord -->
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active bg-white text-primary' : 'text-white' }}">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Tableau de bord
                </a>
            </li>
            
            <!-- Inscriptions en attente -->
            <li class="nav-item">
                <a href="{{ route('admin.registrations.pending') }}" class="nav-link text-white {{ request()->routeIs('admin.registrations.pending') ? 'active bg-white text-primary' : 'text-white' }}">
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
                <a href="{{ route('admin.students.index') }}" class="nav-link text-white {{ request()->routeIs('admin.students.*') ? 'active bg-white text-primary' : 'text-white' }}">
                    <i class="fas fa-user-graduate me-2"></i>
                    Gestion des élèves
                </a>
            </li>
            
            <!-- Gestion des enseignants -->
            <li class="nav-item">
                <a href="{{ route('admin.teachers.index') }}" class="nav-link text-white {{ request()->routeIs('admin.teachers.*') ? 'active bg-white text-primary' : 'text-white' }}">
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Gestion des enseignants
                </a>
            </li>
            
            <!-- Classes -->
            <li class="nav-item">
                <a href="{{ route('admin.classes.index') }}" class="nav-link text-white {{ request()->routeIs('admin.classes.*') ? 'active bg-white text-primary' : 'text-white' }}">
                    <i class="fas fa-chalkboard me-2"></i>
                    Classes
                </a>
            </li>
            
            <!-- Années scolaires -->
            <li class="nav-item">
                <a href="{{ route('admin.academic-years.index') }}" class="nav-link text-white {{ request()->routeIs('admin.academic-years.*') ? 'active bg-white text-primary' : 'text-white' }}">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Années scolaires
                </a>
            </li>
        </ul>
        
        <hr class="mx-3 my-4 bg-white opacity-25">
        
        <!-- Paramètres -->
        <div class="px-3 dropdown">
            <a href="#" class="text-white d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-2"></i>
                <strong>Paramètres</strong>
            </a>
            <ul class="shadow dropdown-menu dropdown-menu-dark">
                <li><a class="dropdown-item text-dark" href="#">Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-dark">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>
