<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.form-draft-meta')
    <title>@yield('title', $platformName . ' — Plateforme')</title>
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
            display: flex;
            flex-direction: column;
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
        .platform-brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.15rem;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.45);
        }
        .platform-user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0 12px 16px;
            padding: 14px;
            border-radius: 12px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            color: inherit;
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s;
        }
        .platform-user-card:hover {
            background: rgba(255,255,255,0.14);
            border-color: rgba(255,255,255,0.22);
            color: #fff;
        }
        .platform-user-card.active-profile {
            background: rgba(99, 102, 241, 0.35);
            border-color: rgba(165, 180, 252, 0.5);
        }
        .platform-user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e1b4b;
            flex-shrink: 0;
        }
        .platform-user-card h6 {
            margin: 0 0 2px;
            font-size: 0.9rem;
            color: #fff;
            line-height: 1.3;
        }
        .platform-user-card small {
            display: block;
            color: #a5b4fc;
            font-size: 0.72rem;
            line-height: 1.3;
            word-break: break-word;
        }
        .platform-user-card .role-badge {
            display: inline-block;
            margin-top: 4px;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #c7d2fe;
        }
        .platform-sidebar-nav { flex: 1; }
        .platform-main { margin-left: 260px; padding: 2rem; }
        .platform-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .platform-topbar-greeting strong { color: #1e293b; }
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
        <div class="px-4 mb-3 d-flex align-items-center gap-3">
            <div class="platform-brand-icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div>
                <h5 class="text-white mb-0">{{ $platformName }}</h5>
                <small class="text-white-50">Plateforme · Super administration</small>
            </div>
        </div>

        <a href="{{ route('platform.profile.edit') }}"
           class="platform-user-card {{ request()->routeIs('platform.profile.*') ? 'active-profile' : '' }}">
            <div class="platform-user-avatar">{{ $platformUserInitials }}</div>
            <div class="overflow-hidden">
                <h6>{{ $platformUserName }}</h6>
                <small>{{ $platformUserEmail }}</small>
                <span class="role-badge"><i class="fas fa-user-shield me-1"></i>Super administrateur</span>
            </div>
        </a>

        <nav class="nav flex-column platform-sidebar-nav">
            <a href="{{ route('platform.dashboard') }}" class="nav-link {{ request()->routeIs('platform.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie me-2"></i> Tableau de bord
            </a>
            <a href="{{ route('platform.schools.index') }}" class="nav-link {{ request()->routeIs('platform.schools.*') ? 'active' : '' }}">
                <i class="fas fa-school me-2"></i> Établissements
            </a>
            <a href="{{ route('platform.profile.edit') }}" class="nav-link {{ request()->routeIs('platform.profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-circle me-2"></i> Mon profil
            </a>
        </nav>

        <div class="mt-auto">
            <hr class="border-secondary mx-3">
            <form method="POST" action="{{ route('logout') }}" class="px-3 pb-2">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <main class="platform-main">
        <div class="platform-topbar">
            <div>
                <p class="text-muted small mb-0">{{ $platformName }}</p>
                <p class="platform-topbar-greeting mb-0 small">
                    Connecté en tant que <strong>{{ $platformUserName }}</strong>
                </p>
            </div>
            <a href="{{ route('platform.profile.edit') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
                <div class="platform-user-avatar" style="width:36px;height:36px;font-size:0.75rem;">{{ $platformUserInitials }}</div>
                <span class="d-none d-md-inline small fw-semibold">{{ $platformUserName }}</span>
            </a>
        </div>

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
    <script>
        document.querySelectorAll('.alert.alert-dismissible').forEach(function (alertEl) {
            setTimeout(function () {
                bootstrap.Alert.getOrCreateInstance(alertEl).close();
            }, 5000);
        });
    </script>
    <script src="{{ asset('js/form-draft-autosave.js') }}"></script>
    @stack('scripts')
</body>
</html>
