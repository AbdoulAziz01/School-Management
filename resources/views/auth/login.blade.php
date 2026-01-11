<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - School Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(to bottom right, #1e40af, #3b82f6, #93c5fd, #ffffff);
            background-attachment: fixed;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-5">
                <div class="p-5 auth-card">
                    <div class="mb-4 text-center">
                        <h2 class="fw-bold text-primary">Connexion à Mohamad PSL School</h2>
                        <p class="text-muted">Accédez à votre espace personnel</p>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Identifiant -->
                        <div class="mb-3">
                            <label for="login" class="form-label">Identifiant</label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('identifier') is-invalid @enderror" 
                                   id="identifier" 
                                   name="identifier" 
                                   placeholder="Ex: E2024001 ou P2024001"
                                   value="{{ old('identifier') }}" 
                                   required 
                                   autofocus>
                            @error('identifier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Votre mot de passe"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Se souvenir de moi -->
                        <div class="mb-4 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Se souvenir de moi
                            </label>
                        </div>

                        <!-- Bouton -->
                        <div class="mb-3 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Se connecter
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('register') }}" class="text-decoration-none">
                                Pas encore de compte ? <strong>S'inscrire</strong>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ url('/') }}" class="text-white text-decoration-none">
                        ← Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
