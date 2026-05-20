<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Plateforme — EduManager')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --platform-primary: #4f46e5; --platform-dark: #312e81; }
        body { background: #f8fafc; min-height: 100vh; }
        .platform-sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #312e81 0%, #1e1b4b 100%);
            color: #e0e7ff;
            width: 260px;
            position: fixed;
            left: 0; top: 0;
        }
        .platform-sidebar .nav-link {
            color: #c7d2fe;
            border-radius: 8px;
            margin: 2px 12px;
            padding: 10px 14px;
        }
        .platform-sidebar .nav-link:hover, .platform-sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .platform-main { margin-left: 260px; padding: 2rem; }
        @media (max-width: 991px) {
            .platform-sidebar { position: relative; width: 100%; min-height: auto; }
            .platform-main { margin-left: 0; }
        }
        .badge-platform { background: var(--platform-primary); }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="platform-sidebar py-4">
        <div class="px-4 mb-4">
            <h5 class="text-white mb-0"><i class="fas fa-shield-halved me-2"></i>Plateforme</h5>
            <small class="text-white-50">Super administration</small>
        </div>
        <nav class="nav flex-column">
            <a href="{{ route('platform.dashboard') }}" class="nav-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie me-2"></i> Tableau de bord
            </a>
            <a href="{{ route('platform.schools.index') }}" class="nav-link {{ request()->routeIs('platform.schools.*') ? 'active' : '' }}">
                <i class="fas fa-school me-2"></i> Établissements
            </a>
            <hr class="border-secondary mx-3">
            <form method="POST" action="{{ route('logout') }}" class="px-3">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                </button>
            </form>
        </nav>
    </aside>

    <main class="platform-main">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
