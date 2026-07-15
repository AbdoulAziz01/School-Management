<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Caisse — Guichet')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #f59e0b;
            --primary-dark: #d97706;
            --primary-light: #fef3c7;
            --bg-dark: #1c1917;
        }

        body {
            margin: 0;
            background: linear-gradient(135deg, #fefce8 0%, #fffbeb 50%, #fef3c7 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
        }

        /* Guichet : pas de sidebar, un seul flux d'écran centré, pensé pour un usage rapide au quotidien */
        .caisse-shell {
            max-width: 960px;
            margin: 0 auto;
            padding: 1.5rem 1.25rem 3rem;
        }

        .card {
            border: 1px solid #fde68a;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.08);
            margin-bottom: 20px;
        }

        .card-header {
            background: linear-gradient(90deg, #fffbeb 0%, #ffffff 100%);
            border-bottom: 2px solid #fde68a;
            border-radius: 16px 16px 0 0 !important;
            padding: 1rem 1.25rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border: none;
            color: #1c1917;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #1c1917;
        }

        .alert { border-radius: 12px; border: none; }
        .alert-success { background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #f59e0b; color: #92400e; }
        .alert-danger { background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #dc2626; color: #92400e; }
        .alert-warning { background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #d97706; color: #92400e; }
        .alert-info { background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%); border-left: 4px solid #fbbf24; color: #92400e; }

        h1, h2, h3, h4, h5, h6 { color: #92400e; }
    </style>
    @include('partials.portal-top-navbar-styles')
    @stack('styles')
</head>
<body>
    @include('partials.portal-top-navbar', [
        'portalScope' => 'caisse',
        'portalProfileRoute' => route('caisse.profile.edit'),
        'portalRoleLabel' => 'Caissier',
    ])

    <div class="caisse-shell">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
