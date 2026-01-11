<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management - Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-attachment: fixed;
        }
        
        /* Alternative plus simple bleu-blanc */
        body {
            background: linear-gradient(to bottom right, #4e73df, #224abe, #1e3a8a, #ffffff);
            background-attachment: fixed;
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .btn-custom {
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <nav class="py-3 bg-transparent navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="/">
                🎓 School Management
            </a>
            <div class="ms-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm me-2">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm me-2">Se connecter</a>
                    <a href="{{ route('register') }}" class="btn btn-light btn-sm">S'inscrire</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="p-5 text-center welcome-card">
                    <h1 class="mb-4 display-4 fw-bold text-primary">
                        Bienvenue sur le système<br>de gestion scolaire
                    </h1>
                    
                    <p class="mb-5 lead text-muted">
                        Application de gestion d'école développée avec Laravel. 
                        On ajoutera bientôt les espaces admin, prof et élève.
                    </p>
                    
                    <div class="gap-3 d-grid d-md-flex justify-content-md-center">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-custom">
                            Se connecter
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary btn-custom">
                            Créer un compte
                        </a>
                    </div>
                    
                    <div class="pt-4 mt-5 border-top">
                        <p class="mb-2 text-muted small">
                            <strong>Trois espaces distincts :</strong>
                        </p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 rounded bg-light">
                                    <h5> Admin</h5>
                                    <p class="mb-0 small">Gestion complète</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded bg-light">
                                    <h5> Professeur</h5>
                                    <p class="mb-0 small">Classes & notes</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded bg-light">
                                    <h5> Élève</h5>
                                    <p class="mb-0 small">Résultats & emploi du temps</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
