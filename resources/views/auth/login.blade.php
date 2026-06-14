<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — {{ $platformName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        *, body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #d97706 0%, #c2410c 45%, #92400e 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.045) 1px, transparent 1px);
            background-size: 55px 55px;
            pointer-events: none;
            z-index: 0;
        }

        .deco-circles { position: fixed; inset: 0; pointer-events: none; z-index: 0; overflow: hidden; }
        .deco-circle  { position: absolute; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.07); }

        .auth-wrapper { position: relative; z-index: 10; width: 100%; padding: 24px; }

        .auth-card {
            background: rgba(255,255,255,0.98);
            border-radius: 24px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.28);
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
            padding: 2.8rem 2.5rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.92rem;
        }
        .form-control:focus {
            border-color: #d97706;
            box-shadow: 0 0 0 3px rgba(217,119,6,0.18);
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #c2410c 100%);
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.01em;
            padding: 13px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #d97706 0%, #92400e 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.22);
        }

        .input-group .btn-outline-secondary {
            border-radius: 0 12px 12px 0;
            border-color: #dee2e6;
            color: #6b7280;
        }
        .input-group .btn-outline-secondary:hover { background: #f9fafb; color: #374151; }
    </style>
</head>
<body>

    <div class="deco-circles">
        <div class="deco-circle" style="width:600px;height:600px;top:-220px;left:-220px;"></div>
        <div class="deco-circle" style="width:420px;height:420px;top:-130px;left:-130px;border-color:rgba(255,255,255,0.05);"></div>
        <div class="deco-circle" style="width:700px;height:700px;bottom:-260px;right:-260px;"></div>
        <div class="deco-circle" style="width:490px;height:490px;bottom:-155px;right:-155px;border-color:rgba(255,255,255,0.04);"></div>
        <div style="position:absolute;width:400px;height:400px;top:5%;right:5%;border-radius:50%;background:radial-gradient(circle,rgba(251,191,36,0.32) 0%,transparent 70%);opacity:0.22;pointer-events:none;"></div>
        <div style="position:absolute;width:340px;height:340px;bottom:5%;left:5%;border-radius:50%;background:radial-gradient(circle,rgba(254,243,199,0.26) 0%,transparent 70%);opacity:0.18;pointer-events:none;"></div>
    </div>

    <div class="auth-wrapper">
        <div class="auth-card">
                    <div class="mb-4 text-center">
                        <i class="mb-3 fas fa-graduation-cap fa-3x" style="color: #f59e0b;"></i>
                        <h2 class="mb-3 h3 font-weight-normal">Connexion à {{ $platformName }}</h2>
                        <p class="text-muted">Accédez à votre espace personnel</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(!empty($loggedInUser))
                        <div class="alert alert-warning">
                            <strong>Vous êtes déjà connecté</strong> en tant que
                            {{ $loggedInUser->name }} ({{ $loggedInUser->role }}).
                            <br>Déconnectez-vous pour vous connecter avec un autre compte (ex. admin d'école).
                            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-dark">Se déconnecter</button>
                            </form>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="identifier" class="form-label">Identifiant</label>
                            <input type="text" 
                                   class="form-control @error('identifier') is-invalid @enderror" 
                                   id="identifier" 
                                   name="identifier" 
                                   placeholder="Email ou identifiant (ex: E2026001)"
                                   value="{{ old('identifier') }}" 
                                   required 
                                   autofocus>
                            @error('identifier')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="••••••••"
                                       required
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Se souvenir de moi
                            </label>
                        </div>

                        <div class="gap-2 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Se connecter
                            </button>
                        </div>
                    </form>

                    <div class="mt-3 text-center">
                        @if (Route::has('register'))
                            <div class="mt-2">
                                Pas encore de compte ? 
                                <a href="{{ route('register') }}" class="text-decoration-none">
                                    S'inscrire
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3 text-center">
                        <a href="{{ url('/') }}" class="text-decoration-none">
                            ← Retour à l'accueil
                        </a>
                    </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>
