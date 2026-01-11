<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(to bottom right, #1e40af, #3b82f6, #93c5fd, #f0f9ff);
            background-attachment: fixed;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        aside {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .nav-link {
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(59, 130, 246, 0.1);
            transform: translateX(5px);
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-light">

{{-- Navbar --}}
<nav class="bg-white navbar navbar-light sticky-top">
    <div class="px-4 container-fluid">
        <span class="mb-0 navbar-brand h1 fw-bold text-primary">
            🎓 EduSchool Computer Science
        </span>
        
        <div class="d-flex align-items-center">
            <span class="text-dark small me-3">
                <strong>{{ auth()->user()->name ?? '' }}</strong> 
                <span class="badge bg-primary">{{ auth()->user()->role ?? '' }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Déconnexion
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <aside class="py-4 bg-white col-md-3 col-lg-2 border-end min-vh-100">
            <nav class="px-2 nav flex-column">
                <span class="px-3 mb-3 text-muted text-uppercase small fw-bold">
                    Navigation
                </span>
                
                {{-- Lien Dashboard --}}
                <a href="{{ route('dashboard') }}" class="px-3 nav-link">
                    Dashboard
                </a>
                
                {{-- Liens selon le rôle --}}
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.pending') }}" class="px-3 nav-link">
                            Inscriptions en attente
                            @php
                                $pendingCount = \App\Models\User::where('status', 'pending')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge bg-danger">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="#" class="px-3 nav-link">Gestion des élèves</a>
                        <a href="#" class="px-3 nav-link">Gestion des profs</a>
                        <a href="#" class="px-3 nav-link">Classes & matières</a>
                        
                    @elseif(auth()->user()->role === 'teacher')
                        <a href="#" class="px-3 nav-link">Mes classes</a>
                        <a href="#" class="px-3 nav-link">Mes matières</a>
                        <a href="#" class="px-3 nav-link">Saisie des notes</a>
                        
                    @elseif(auth()->user()->role === 'student')
                        <a href="#" class="px-3 nav-link">Mes matières</a>
                        <a href="#" class="px-3 nav-link">Mon emploi du temps</a>
                        <a href="#" class="px-3 nav-link">Mes résultats</a>
                    @endif
                @endauth
            </nav>
        </aside>
        
        {{-- Contenu principal --}}
        <main class="py-4 col-md-9 col-lg-10">
            @isset($header)
                <div class="mb-3">
                    {{ $header }}
                </div>
            @endisset
            
            {{ $slot }}
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
