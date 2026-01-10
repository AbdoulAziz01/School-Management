<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="mb-0 navbar-brand h1">
            EduSchool Computer Science
        </span>

        <div class="d-flex align-items-center">
            <span class="text-white small me-3">
                {{ auth()->user()->name ?? '' }} ({{ auth()->user()->role ?? '' }})
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
            <nav class="nav flex-column">
                <span class="px-3 mb-2 text-muted text-uppercase small">Navigation</span>

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
