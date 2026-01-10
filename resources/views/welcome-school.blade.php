<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>School Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 via CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">School Management</a>
        <div>
            <!-- le menu de navigation avec les liens de connexion et d'inscription -->
            <!-- route {login} c'est {{ route('login') }} qui est dans routes/web.php -->
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm me-2">Se connecter</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">S'inscrire</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="shadow-sm card">
                <div class="text-center card-body">
                    <h1 class="mb-3">Bienvenue sur le système de gestion scolaire</h1>
                    <p class="text-muted">
                        Application de gestion d'école développée avec Laravel. On ajoutera
                        bientôt les espaces admin, prof et élève.
                    </p>
                    <a href="{{ route('login') }}" class="btn btn-primary me-2">Se connecter</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary">Créer un compte</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
