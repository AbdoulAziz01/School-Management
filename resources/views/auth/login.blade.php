<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — {{ $platformName }}</title>
    <link rel="icon" type="image/png" href="{{ $platformLogoIcon }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        /* ── Design tokens ──────────────────────────────────────────── */
        :root {
            --ink-950: #17140f;
            --ink-900: #1c1917;
            --ink-800: #292524;
            --ink-700: #44403c;
            --amber-300: #fcd34d;
            --amber-400: #fbbf24;
            --amber-500: #f59e0b;
            --amber-600: #d97706;
            --amber-700: #b45309;
            --cream-50: #fefdfb;
            --cream-100: #fffbeb;
            --cream-200: #fef3c7;
            --ease: cubic-bezier(0.16, 1, 0.3, 1);
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--ink-950);
            overflow-x: hidden;
        }

        /* ── Layout : deux volets asymétriques ──────────────────────── */
        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        }

        /* ── Volet gauche : identité ─────────────────────────────────── */
        .brand-pane {
            position: relative;
            background: linear-gradient(160deg, var(--ink-900) 0%, var(--ink-800) 55%, var(--ink-950) 100%);
            color: var(--cream-100);
            padding: 4rem 4.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .brand-pane-bg { position: absolute; inset: 0; pointer-events: none; z-index: 0; }

        .brand-grid {
            position: absolute; inset: -10%;
            background-image: radial-gradient(rgba(251, 191, 36, 0.16) 1.2px, transparent 1.2px);
            background-size: 34px 34px;
            mask-image: radial-gradient(ellipse 70% 60% at 50% 40%, #000 40%, transparent 85%);
        }

        .brand-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.5;
        }
        .brand-orb--1 { width: 420px; height: 420px; top: -120px; right: -100px; background: radial-gradient(circle, rgba(245,158,11,0.5) 0%, transparent 70%); }
        .brand-orb--2 { width: 380px; height: 380px; bottom: -140px; left: -80px; background: radial-gradient(circle, rgba(217,119,6,0.4) 0%, transparent 70%); }

        .brand-content { position: relative; z-index: 1; }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }
        .brand-mark img { width: 40px; height: 40px; object-fit: contain; }
        .brand-mark span {
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -0.01em;
            color: #fff;
        }

        .brand-headline {
            margin: 2.75rem 0 0.9rem;
            font-size: clamp(1.9rem, 2.6vw, 2.5rem);
            font-weight: 800;
            line-height: 1.18;
            letter-spacing: -0.02em;
            color: #fff;
            max-width: 480px;
        }
        .brand-headline .accent {
            background: linear-gradient(90deg, var(--amber-400), var(--amber-500));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .brand-sub {
            color: rgba(254, 243, 199, 0.72);
            font-size: 1rem;
            max-width: 420px;
            line-height: 1.6;
            margin: 0;
        }

        .brand-features {
            list-style: none;
            margin: 2.75rem 0 0;
            padding: 0;
            display: grid;
            gap: 0.9rem;
        }
        .brand-features li {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.85rem 1rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            backdrop-filter: blur(6px);
        }
        .brand-features i {
            width: 34px; height: 34px;
            flex: none;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px;
            background: rgba(245, 158, 11, 0.16);
            color: var(--amber-400);
            font-size: 0.85rem;
        }
        .brand-features span { font-size: 0.9rem; color: rgba(255,255,255,0.88); font-weight: 500; }

        .brand-footer {
            position: relative; z-index: 1;
            font-size: 0.78rem;
            color: rgba(254, 243, 199, 0.45);
        }

        /* ── Volet droit : formulaire ────────────────────────────────── */
        .form-pane {
            background: var(--cream-50);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            position: relative;
        }

        .form-pane-inner { width: 100%; max-width: 400px; }

        .form-mark {
            display: none;
        }

        .form-heading h1 {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--ink-900);
            letter-spacing: -0.01em;
            margin: 0 0 0.4rem;
        }
        .form-heading p {
            color: #78716c;
            font-size: 0.92rem;
            margin: 0 0 2rem;
        }

        /* ── Alerts ──────────────────────────────────────────────────── */
        .auth-alert {
            border: none;
            border-radius: 14px;
            padding: 0.85rem 1rem;
            font-size: 0.85rem;
            margin-bottom: 1.1rem;
            display: flex;
            gap: 0.6rem;
            align-items: flex-start;
        }
        .auth-alert i { margin-top: 0.15rem; }
        .auth-alert--success { background: #ecfdf5; color: #065f46; }
        .auth-alert--danger { background: #fef2f2; color: #991b1b; }
        .auth-alert--warning { background: var(--cream-200); color: #92400e; }
        .auth-alert ul { margin: 0.2rem 0 0; padding-left: 1.1rem; }

        /* ── Champs premium ──────────────────────────────────────────── */
        .field { margin-bottom: 1.15rem; }
        .field-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ink-700);
            margin-bottom: 0.4rem;
        }
        .field-shell {
            position: relative;
            display: flex;
            align-items: center;
            background: #fff;
            border: 1.5px solid #e7e2d9;
            border-radius: 14px;
            transition: border-color 0.2s var(--ease), box-shadow 0.2s var(--ease), transform 0.15s var(--ease);
        }
        .field-shell:focus-within {
            border-color: var(--amber-500);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.14);
            transform: translateY(-1px);
        }
        .field-shell.is-invalid { border-color: #dc2626; }
        .field-shell.is-invalid:focus-within { box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.12); }

        .field-icon {
            flex: none;
            width: 44px;
            display: flex; align-items: center; justify-content: center;
            color: #a8a29e;
            font-size: 0.9rem;
            transition: color 0.2s var(--ease);
        }
        .field-shell:focus-within .field-icon { color: var(--amber-600); }

        .field-shell input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            padding: 0.85rem 0.9rem 0.85rem 0;
            font-size: 0.92rem;
            font-family: inherit;
            color: var(--ink-900);
            min-width: 0;
        }
        .field-shell input::placeholder { color: #c7c2b8; }

        .field-action {
            flex: none;
            width: 44px;
            height: 44px;
            display: flex; align-items: center; justify-content: center;
            background: none;
            border: none;
            color: #a8a29e;
            cursor: pointer;
            transition: color 0.2s var(--ease);
            border-radius: 0 14px 14px 0;
        }
        .field-action:hover { color: var(--ink-700); }
        .field-action:focus-visible { outline: 2px solid var(--amber-500); outline-offset: -2px; }

        .field-feedback {
            font-size: 0.78rem;
            color: #dc2626;
            margin-top: 0.35rem;
        }

        .field-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.4rem;
        }
        .remember-toggle { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; user-select: none; }
        .remember-toggle input {
            width: 17px; height: 17px;
            accent-color: var(--amber-600);
            cursor: pointer;
        }
        .remember-toggle span { font-size: 0.85rem; color: var(--ink-700); }
        .link-subtle {
            font-size: 0.83rem;
            color: var(--amber-700);
            text-decoration: none;
            font-weight: 600;
        }
        .link-subtle:hover { text-decoration: underline; }

        /* ── Bouton spectaculaire ────────────────────────────────────── */
        .btn-submit {
            position: relative;
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 0.95rem;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #1c1917;
            background: linear-gradient(135deg, var(--amber-400) 0%, var(--amber-600) 100%);
            box-shadow: 0 10px 24px rgba(217, 119, 6, 0.28);
            cursor: pointer;
            overflow: hidden;
            transition: transform 0.18s var(--ease), box-shadow 0.18s var(--ease);
            display: flex; align-items: center; justify-content: center; gap: 0.55rem;
        }
        .btn-submit::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.45) 50%, transparent 70%);
            transform: translateX(-120%);
            transition: transform 0.6s var(--ease);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(217, 119, 6, 0.36); }
        .btn-submit:hover::before { transform: translateX(120%); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:focus-visible { outline: 2px solid var(--ink-900); outline-offset: 2px; }
        .btn-submit:disabled { cursor: default; opacity: 0.92; }

        .btn-spinner {
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 2.5px solid rgba(28,25,23,0.25);
            border-top-color: #1c1917;
            display: none;
            animation: spin 0.7s linear infinite;
        }
        .btn-submit.is-loading .btn-spinner { display: inline-block; }
        .btn-submit.is-loading .btn-label::after { content: 'Connexion en cours…'; }
        .btn-submit.is-loading .btn-label { font-size: 0; }
        .btn-submit.is-loading .btn-label::after { font-size: 0.95rem; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Pied de formulaire ──────────────────────────────────────── */
        .form-footer { margin-top: 1.6rem; text-align: center; font-size: 0.87rem; color: #78716c; }
        .form-footer a { color: var(--amber-700); font-weight: 600; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }
        .form-footer-back { margin-top: 0.9rem; }
        .form-footer-back a { color: #a8a29e; font-weight: 500; font-size: 0.82rem; }

        /* ── Animations d'entrée ─────────────────────────────────────── */
        @media (prefers-reduced-motion: no-preference) {
            .brand-orb { animation: drift 22s ease-in-out infinite alternate; }
            .brand-orb--2 { animation-duration: 26s; animation-delay: -6s; }
            @keyframes drift {
                0%   { transform: translate(0, 0) scale(1); }
                100% { transform: translate(30px, -24px) scale(1.08); }
            }

            .reveal { opacity: 0; animation: reveal 0.7s var(--ease) forwards; }
            .reveal-1 { animation-delay: 0.05s; }
            .reveal-2 { animation-delay: 0.15s; }
            .reveal-3 { animation-delay: 0.25s; }
            .reveal-4 { animation-delay: 0.35s; }
            @keyframes reveal {
                from { opacity: 0; transform: translateY(14px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            .brand-features li { opacity: 0; animation: reveal 0.6s var(--ease) forwards; }
            .brand-features li:nth-child(1) { animation-delay: 0.4s; }
            .brand-features li:nth-child(2) { animation-delay: 0.48s; }
            .brand-features li:nth-child(3) { animation-delay: 0.56s; }
            .brand-features li:nth-child(4) { animation-delay: 0.64s; }

            .has-error .form-pane-inner { animation: shake 0.5s var(--ease); }
            @keyframes shake {
                10%, 90% { transform: translateX(-1px); }
                20%, 80% { transform: translateX(3px); }
                30%, 50%, 70% { transform: translateX(-6px); }
                40%, 60% { transform: translateX(6px); }
            }
        }

        /* ── Focus visible global (clavier) ─────────────────────────── */
        a:focus-visible, input:focus-visible, button:focus-visible {
            outline: 2px solid var(--amber-500);
            outline-offset: 2px;
        }

        /* ── Responsive ──────────────────────────────────────────────── */
        @media (max-width: 992px) {
            .auth-shell { grid-template-columns: 1fr; }
            .brand-pane {
                padding: 2.75rem 2rem 2.25rem;
                min-height: 0;
            }
            .brand-headline { font-size: 1.5rem; max-width: none; }
            .brand-sub { max-width: none; }
            .brand-features { display: none; }
            .brand-footer { display: none; }
        }
        @media (max-width: 576px) {
            .brand-pane { padding: 2.25rem 1.5rem; }
            .form-pane { padding: 2rem 1.25rem 3rem; }
        }
    </style>
</head>
<body>

    <div class="auth-shell">
        {{-- ── Volet identité ─────────────────────────────────────── --}}
        <aside class="brand-pane">
            <div class="brand-pane-bg">
                <div class="brand-grid"></div>
                <div class="brand-orb brand-orb--1"></div>
                <div class="brand-orb brand-orb--2"></div>
            </div>

            <div class="brand-content">
                <div class="brand-mark reveal reveal-1">
                    <img src="{{ $platformLogoIcon }}" alt="">
                    <span>{{ $platformName }}</span>
                </div>

                <h1 class="brand-headline reveal reveal-2">
                    La plateforme qui pilote <span class="accent">toute votre école.</span>
                </h1>
                <p class="brand-sub reveal reveal-3">
                    Élèves, enseignants, notes, présences et finances — un seul espace, pensé pour aller à l'essentiel.
                </p>

                <ul class="brand-features">
                    <li><i class="fas fa-chart-line"></i><span>Pilotage complet de l'établissement</span></li>
                    <li><i class="fas fa-file-invoice"></i><span>Bulletins et notes en temps réel</span></li>
                    <li><i class="fas fa-user-check"></i><span>Suivi des présences au quotidien</span></li>
                    <li><i class="fas fa-wallet"></i><span>Comptabilité et paiements centralisés</span></li>
                </ul>
            </div>

            <p class="brand-footer">© {{ now()->year }} {{ $platformName }} — Système de gestion scolaire</p>
        </aside>

        {{-- ── Volet formulaire ──────────────────────────────────────── --}}
        <main class="form-pane">
            <div class="form-pane-inner {{ $errors->any() ? 'has-error' : '' }}">

                <div class="form-heading reveal reveal-1">
                    <h1>Bon retour</h1>
                    <p>Connectez-vous à votre espace personnel</p>
                </div>

                <div aria-live="polite">
                    @if(session('success'))
                        <div class="auth-alert auth-alert--success"><i class="fas fa-circle-check"></i><div>{{ session('success') }}</div></div>
                    @endif

                    @if(!empty($loggedInUser))
                        <div class="auth-alert auth-alert--warning">
                            <i class="fas fa-triangle-exclamation"></i>
                            <div>
                                <strong>Vous êtes déjà connecté</strong> en tant que {{ $loggedInUser->name }} ({{ $loggedInUser->role }}).
                                Déconnectez-vous pour utiliser un autre compte.
                                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Se déconnecter</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="auth-alert auth-alert--danger">
                            <i class="fas fa-circle-exclamation"></i>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="auth-alert auth-alert--danger"><i class="fas fa-circle-exclamation"></i><div>{{ session('error') }}</div></div>
                    @endif

                    @if(session('status'))
                        <div class="auth-alert auth-alert--success"><i class="fas fa-circle-check"></i><div>{{ session('status') }}</div></div>
                    @endif
                </div>

                <form method="POST" action="{{ route('login') }}" class="login-form reveal reveal-2" novalidate>
                    @csrf

                    <div class="field">
                        <label for="identifier" class="field-label">Identifiant</label>
                        <div class="field-shell {{ $errors->has('identifier') ? 'is-invalid' : '' }}">
                            <span class="field-icon"><i class="fas fa-user"></i></span>
                            <input type="text"
                                   id="identifier"
                                   name="identifier"
                                   placeholder="Email ou identifiant (ex : E2026001)"
                                   value="{{ old('identifier') }}"
                                   required
                                   autofocus
                                   autocomplete="username">
                        </div>
                        @error('identifier')
                            <div class="field-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password" class="field-label">Mot de passe</label>
                        <div class="field-shell {{ $errors->has('password') ? 'is-invalid' : '' }}">
                            <span class="field-icon"><i class="fas fa-lock"></i></span>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   placeholder="••••••••"
                                   required
                                   autocomplete="current-password">
                            <button class="field-action toggle-password" type="button" aria-label="Afficher le mot de passe" aria-pressed="false">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field-row">
                        <label class="remember-toggle">
                            <input type="checkbox" id="remember" name="remember">
                            <span>Se souvenir de moi</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="link-subtle" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-submit">
                        <span class="btn-spinner" aria-hidden="true"></span>
                        <span class="btn-label">Se connecter</span>
                    </button>
                </form>

                <div class="form-footer reveal reveal-3">
                    @if (Route::has('register'))
                        Pas encore de compte ? <a href="{{ route('register') }}">S'inscrire</a>
                    @endif
                    <div class="form-footer-back">
                        <a href="{{ url('/') }}"><i class="fas fa-arrow-left me-1"></i>Retour à l'accueil</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Afficher/masquer le mot de passe
        document.querySelectorAll('.toggle-password').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = this.closest('.field-shell').querySelector('input');
                const isHidden = input.getAttribute('type') === 'password';
                input.setAttribute('type', isHidden ? 'text' : 'password');
                this.setAttribute('aria-pressed', String(isHidden));
                this.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });

        // État de chargement du bouton — la navigation réelle prend le relais,
        // ce state reste visible jusqu'au rendu de la page suivante.
        const loginForm = document.querySelector('.login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function () {
                if (!loginForm.checkValidity()) { return; }
                const submitBtn = loginForm.querySelector('.btn-submit');
                submitBtn.classList.add('is-loading');
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>
