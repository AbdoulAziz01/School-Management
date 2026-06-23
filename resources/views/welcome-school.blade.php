<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduManager — Système de Gestion Scolaire</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#f59e0b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="EduManager">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        *, body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #d97706 0%, #c2410c 45%, #92400e 100%);
            background-attachment: fixed;
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
        .deco-circle { position: absolute; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.07); }

        .navbar-custom {
            position: relative; z-index: 10;
            padding: 22px 32px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .brand-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .brand-name { color: #fff; font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; }
        .nav-subtitle { color: rgba(254,243,199,0.8); font-size: 0.8rem; }
        .btn-nav {
            padding: 8px 20px; border-radius: 50px;
            background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.28);
            color: #fff; font-size: 0.85rem; font-weight: 500; text-decoration: none; transition: background 0.2s;
        }
        .btn-nav:hover { background: rgba(255,255,255,0.24); color: #fff; }

        .main-wrapper {
            position: relative; z-index: 10;
            max-width: 1200px; margin: 0 auto; padding: 0 24px 80px;
        }

        .hero { text-align: center; padding: 48px 16px 80px; position: relative; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 20px; border-radius: 50px;
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.22);
            color: rgba(254,243,199,0.9); font-size: 0.82rem; font-weight: 500; margin-bottom: 36px;
        }
        .dot-green {
            width: 8px; height: 8px; border-radius: 50%; background: #4ade80;
            flex-shrink: 0; animation: blink 2.2s ease-in-out infinite;
        }

        .hero-title {
            font-size: clamp(2.2rem, 4.5vw, 4.4rem); font-weight: 900; color: #fff;
            line-height: 1.12; letter-spacing: -0.03em; margin-bottom: 24px;
            text-shadow: 0 4px 28px rgba(0,0,0,0.22);
        }
        .hero-title .accent {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 60%, #fbbf24 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        .hero-subtitle {
            color: rgba(254,243,199,0.88); font-size: 1.12rem; font-weight: 300;
            line-height: 1.75; max-width: 600px; margin: 0 auto 52px;
        }

        .cta-group {
            display: flex; align-items: center; justify-content: center;
            flex-wrap: wrap; gap: 16px; margin-bottom: 72px;
        }
        .btn-login {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 44px; border-radius: 50px;
            background: linear-gradient(135deg, #ffffff 0%, #fef3c7 100%);
            color: #92400e; font-size: 1.05rem; font-weight: 700; text-decoration: none;
            box-shadow: 0 16px 40px rgba(0,0,0,0.25);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #fef9c3 0%, #fde68a 100%); color: #92400e;
            transform: translateY(-3px); box-shadow: 0 24px 50px rgba(0,0,0,0.32);
        }
        .btn-discover {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 15px 36px; border-radius: 50px;
            background: rgba(255,255,255,0.11); border: 1px solid rgba(255,255,255,0.26);
            color: #fff; font-size: 1rem; font-weight: 500; text-decoration: none;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-discover:hover { background: rgba(255,255,255,0.2); color: #fff; transform: translateY(-2px); }

        .float-badge {
            position: absolute;
            background: rgba(255,255,255,0.14); backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.26);
            border-radius: 18px; padding: 12px 16px;
            display: flex; align-items: center; gap: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        }
        .float-badge .ficon {
            width: 38px; height: 38px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .float-badge .flabel { color: rgba(255,255,255,0.72); font-size: 0.72rem; font-weight: 500; display: block; }
        .float-badge .fval { color: #fff; font-size: 1rem; font-weight: 700; display: block; line-height: 1.2; }

        .badge-tl { top: 60px; left: 0; animation: floatA 7s ease-in-out infinite; }
        .badge-tr { top: 40px; right: 0; animation: floatB 9s ease-in-out 1s infinite; }
        .badge-ml { top: 220px; left: -10px; animation: floatC 5.5s ease-in-out 2s infinite; }
        .badge-mr { top: 210px; right: -10px; animation: floatD 8s ease-in-out 0.5s infinite; }

        .hero-sep { display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 10px; }
        .hero-sep span { height: 1px; width: 48px; background: rgba(255,255,255,0.22); display: block; }
        .hero-sep i { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.36); display: block; }
        .hero-tagline { color: rgba(254,243,199,0.78); font-size: 0.85rem; }

        /* ── BANDES RÔLES ── */
        .roles-list {
            display: flex; flex-direction: column;
            border-radius: 24px; overflow: hidden;
            box-shadow: 0 24px 60px rgba(0,0,0,0.22);
        }

        .role-row {
            display: grid;
            grid-template-columns: auto 1fr auto auto;
            align-items: center; gap: 24px;
            padding: 28px 36px;
            background: rgba(255,255,255,0.97);
            text-decoration: none;
            border-left: 5px solid transparent;
            transition: background 0.25s, border-color 0.25s, padding-left 0.25s;
        }
        .role-row + .role-row { border-top: 1px solid rgba(0,0,0,0.06); }
        .role-row:hover { background: #fff; padding-left: 44px; }
        .role-row.amber:hover  { border-left-color: #d97706; }
        .role-row.orange:hover { border-left-color: #c2410c; }
        .role-row.indigo:hover { border-left-color: #4f46e5; }

        .role-icon {
            width: 58px; height: 58px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .role-info { min-width: 0; }
        .role-name { font-size: 1.15rem; font-weight: 800; line-height: 1.2; margin-bottom: 4px; }
        .role-desc { font-size: 0.85rem; color: #6b7280; line-height: 1.5; }

        .role-tags { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .role-tag {
            display: flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 50px;
            font-size: 0.78rem; font-weight: 500; white-space: nowrap;
        }
        .tag-amber { background: #fef9c3; color: #92400e; }
        .tag-orange { background: #ffedd5; color: #c2410c; }
        .tag-indigo { background: #e0e7ff; color: #4338ca; }

        .role-cta {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px; border-radius: 50px;
            font-size: 0.88rem; font-weight: 700; color: #fff;
            white-space: nowrap; flex-shrink: 0;
            transition: transform 0.2s;
        }
        .role-row:hover .role-cta { transform: translateX(4px); }
        .cta-amber  { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 6px 18px rgba(217,119,6,0.35); }
        .cta-orange { background: linear-gradient(135deg, #ea580c, #c2410c); box-shadow: 0 6px 18px rgba(194,65,12,0.35); }
        .cta-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 6px 18px rgba(79,70,229,0.35); }

        @media (max-width: 900px) {
            .role-row { grid-template-columns: auto 1fr auto; gap: 16px; padding: 20px 24px; }
            .role-tags { display: none; }
        }
        @media (max-width: 640px) {
            .role-row { grid-template-columns: auto 1fr; }
            .role-cta { display: none; }
        }

        /* ── Stats ── */
        .stats-bar {
            margin-top: 64px;
            background: rgba(255,255,255,0.09); border: 1px solid rgba(255,255,255,0.14);
            border-radius: 20px; padding: 32px 40px;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; text-align: center;
        }
        @media (max-width: 640px) { .stats-bar { grid-template-columns: repeat(2, 1fr); } }
        .stat-num { color: #fff; font-size: 2rem; font-weight: 900; line-height: 1; }
        .stat-label { color: rgba(254,243,199,0.78); font-size: 0.82rem; margin-top: 6px; }
        .stat-divider { border-left: 1px solid rgba(255,255,255,0.1); }

        .footer-text { text-align: center; margin-top: 40px; color: rgba(254,243,199,0.5); font-size: 0.78rem; }

        /* ── Bouton PWA Install ── */
        .btn-pwa-install {
            display: none; /* affiché par JS quand disponible */
            align-items: center; gap: 10px;
            padding: 15px 32px; border-radius: 50px;
            background: rgba(255,255,255,0.13);
            border: 1.5px solid rgba(255,255,255,0.38);
            color: #fff; font-size: 1rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-pwa-install:hover {
            background: rgba(255,255,255,0.22);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
        }
        .btn-pwa-install.visible { display: inline-flex; }
        .pwa-install-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        /* Toast iOS */
        .pwa-ios-toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
            background: #1c1917; color: #fef3c7;
            border: 1px solid rgba(251,191,36,0.3);
            border-radius: 16px; padding: 14px 20px;
            font-size: 0.82rem; font-weight: 500; line-height: 1.5;
            max-width: 320px; width: calc(100% - 40px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.45);
            z-index: 9999; display: none;
            text-align: center;
        }
        .pwa-ios-toast.show { display: block; animation: slideUp .3s ease; }
        .pwa-ios-toast strong { color: #fbbf24; }
        .pwa-ios-close {
            margin-top: 10px; background: rgba(255,255,255,0.1);
            border: none; color: #fef3c7; border-radius: 8px;
            padding: 5px 16px; font-size: 0.78rem; cursor: pointer; width: 100%;
        }
        @keyframes slideUp { from { transform: translateX(-50%) translateY(20px); opacity:0; } to { transform: translateX(-50%) translateY(0); opacity:1; } }

        @keyframes blink { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.4; transform:scale(0.75); } }
        @keyframes floatA { 0%,100% { transform:translateY(0) rotate(0deg); } 50% { transform:translateY(-16px) rotate(2deg); } }
        @keyframes floatB { 0%,100% { transform:translateY(0) rotate(0deg); } 50% { transform:translateY(-22px) rotate(-2deg); } }
        @keyframes floatC { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-12px); } }
        @keyframes floatD { 0%,100% { transform:translateY(0) rotate(0deg); } 50% { transform:translateY(-18px) rotate(2deg); } }

        @media (max-width: 1100px) { .float-badge { display: none !important; } }
    </style>
</head>
<body>

    <div class="deco-circles">
        <div class="deco-circle" style="width:600px;height:600px;top:-220px;left:-220px;"></div>
        <div class="deco-circle" style="width:420px;height:420px;top:-130px;left:-130px;border-color:rgba(255,255,255,0.05);"></div>
        <div class="deco-circle" style="width:700px;height:700px;bottom:-260px;right:-260px;"></div>
        <div class="deco-circle" style="width:490px;height:490px;bottom:-155px;right:-155px;border-color:rgba(255,255,255,0.04);"></div>
        <div style="position:absolute;width:400px;height:400px;top:8%;right:4%;border-radius:50%;background:radial-gradient(circle,rgba(251,191,36,0.32) 0%,transparent 70%);opacity:0.22;pointer-events:none;"></div>
        <div style="position:absolute;width:340px;height:340px;bottom:12%;left:4%;border-radius:50%;background:radial-gradient(circle,rgba(254,243,199,0.26) 0%,transparent 70%);opacity:0.18;pointer-events:none;"></div>
    </div>

    <nav class="navbar-custom" style="max-width:1200px;margin:0 auto;padding:22px 24px;">
        <a href="/" class="brand">
            <div class="brand-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3L22 8.5V15.5L12 21L2 15.5V8.5L12 3Z" stroke="white" stroke-width="2.2" stroke-linejoin="round"/>
                    <path d="M12 3V21M2 8.5L12 14L22 8.5" stroke="white" stroke-width="2.2" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="brand-name">EduManager</span>
        </a>
        <div style="display:flex;align-items:center;gap:18px;">
            <span class="nav-subtitle d-none d-sm-block">Système de Gestion Scolaire</span>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-nav" style="background:rgba(255,255,255,0.9);color:#92400e;font-weight:700;">
                    Mon Tableau de Bord
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-nav">Se connecter</a>
            @endauth
        </div>
    </nav>

    <div class="main-wrapper">

        <section class="hero">

            <div class="status-badge">
                <span class="dot-green"></span>
                Plateforme Éducatif 100% Sénégalais
            </div>

            <h1 class="hero-title">
                Bienvenue sur le système de gestion scolaire<br>
                <span class="accent">EduManager</span>
            </h1>

            <p class="hero-subtitle">
                Une plateforme intuitive et sécurisée pour booster la performance de votre école.
                Tout ce dont vous avez besoin, en un seul endroit.
            </p>

            <div class="cta-group">
                <a href="{{ route('login') }}" class="btn-login">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M15 3H19C19.53 3 20.04 3.21 20.41 3.59C20.79 3.96 21 4.47 21 5V19C21 19.53 20.79 20.04 20.41 20.41C20.04 20.79 19.53 21 19 21H15" stroke="#92400e" stroke-width="2.5" stroke-linecap="round"/>
                        <path d="M10 17L15 12L10 7" stroke="#92400e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 12H3" stroke="#92400e" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    Se connecter
                </a>

                {{-- Bouton installation PWA (affiché par JS si supporté) --}}
                <button id="pwaInstallBtn" class="btn-pwa-install" type="button">
                    <div class="pwa-install-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M12 16L7 11M12 16L17 11M12 16V4" stroke="#1c1917" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3 20H21" stroke="#1c1917" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    Installer l'application
                </button>

                <a href="#espaces" class="btn-discover">
                    Découvrir les espaces
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M12 5V19M5 12L12 19L19 12" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>

            {{-- Toast iOS --}}
            <div class="pwa-ios-toast" id="pwaIosToast">
                <strong>📲 Installer EduManager</strong><br>
                Appuyez sur <strong>Partager</strong>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;margin:0 3px;"><path d="M8 12H16M12 8V16" stroke="#fbbf24" stroke-width="2" stroke-linecap="round"/><rect x="3" y="3" width="18" height="18" rx="3" stroke="#fbbf24" stroke-width="2"/></svg>
                puis <strong>« Sur l'écran d'accueil »</strong>
                <button class="pwa-ios-close" onclick="document.getElementById('pwaIosToast').classList.remove('show')">Compris</button>
            </div>

            <div class="float-badge badge-tl">
                <div class="ficon" style="background:rgba(74,222,128,0.18);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M17 21V19C17 16.79 15.21 15 13 15H5C2.79 15 1 16.79 1 19V21" stroke="#4ade80" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="9" cy="7" r="4" stroke="#4ade80" stroke-width="2"/>
                        <path d="M23 21V19C22.99 17.41 21.94 16.04 20.48 15.56" stroke="#4ade80" stroke-width="2" stroke-linecap="round"/>
                        <path d="M16 3.13C17.47 3.64 18.5 5.02 18.5 6.63C18.5 8.23 17.47 9.61 16 10.12" stroke="#4ade80" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <span class="flabel">Élèves inscrits</span>
                    <span class="fval">1 250</span>
                </div>
            </div>

            <div class="float-badge badge-tr">
                <div class="ficon" style="background:rgba(251,191,36,0.18);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M12 20H21" stroke="#fbbf24" stroke-width="2" stroke-linecap="round"/>
                        <path d="M16.5 3.5C17.33 2.67 18.67 2.67 19.5 3.5C20.33 4.33 20.33 5.67 19.5 6.5L7 19L3 20L4 16L16.5 3.5Z" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <span class="flabel">Moyenne Générale</span>
                    <span class="fval">17.5 / 20</span>
                </div>
            </div>

            <div class="float-badge badge-ml">
                <div class="ficon" style="background:rgba(129,140,248,0.18);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke="#818cf8" stroke-width="2"/>
                        <line x1="16" y1="2" x2="16" y2="6" stroke="#818cf8" stroke-width="2" stroke-linecap="round"/>
                        <line x1="8" y1="2" x2="8" y2="6" stroke="#818cf8" stroke-width="2" stroke-linecap="round"/>
                        <line x1="3" y1="10" x2="21" y2="10" stroke="#818cf8" stroke-width="2"/>
                    </svg>
                </div>
                <div>
                    <span class="flabel">Taux de présence</span>
                    <span class="fval">97.2 %</span>
                </div>
            </div>

            <div class="float-badge badge-mr">
                <div class="ficon" style="background:rgba(251,113,133,0.18);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" stroke="#fb7185" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <span class="flabel">Enseignants actifs</span>
                    <span class="fval">48</span>
                </div>
            </div>

            <div class="hero-sep">
                <span></span><i></i><span></span>
            </div>
            <p class="hero-tagline">Trois espaces d'accès dédiés</p>
        </section>

        {{-- ===== BANDES RÔLES ===== --}}
        <section id="espaces" class="roles-list">

            <a href="{{ route('login') }}" class="role-row amber">
                <div class="role-icon" style="background:linear-gradient(135deg,#fef9c3,#fde68a);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#d97706" stroke-width="2.2" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="role-info">
                    <div class="role-name" style="color:#78350f;">Espace Élève</div>
                    <div class="role-desc">Notes, emploi du temps, absences — tout votre parcours en un coup d'œil.</div>
                </div>
                <div class="role-tags">
                    <span class="role-tag tag-amber">Notes & bulletins</span>
                    <span class="role-tag tag-amber">Emploi du temps</span>
                    <span class="role-tag tag-amber">Présences</span>
                </div>
                <span class="role-cta cta-amber">
                    Accéder
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </a>

            <a href="{{ route('login') }}" class="role-row orange">
                <div class="role-icon" style="background:linear-gradient(135deg,#fed7aa,#fdba74);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                        <rect x="2" y="3" width="20" height="14" rx="2" stroke="#c2410c" stroke-width="2.2"/>
                        <line x1="8" y1="21" x2="16" y2="21" stroke="#c2410c" stroke-width="2.2" stroke-linecap="round"/>
                        <line x1="12" y1="17" x2="12" y2="21" stroke="#c2410c" stroke-width="2.2" stroke-linecap="round"/>
                        <path d="M7 10l3 3 7-7" stroke="#c2410c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="role-info">
                    <div class="role-name" style="color:#7c2d12;">Espace Enseignant</div>
                    <div class="role-desc">Gérez vos classes, saisissez les notes et suivez les présences en temps réel.</div>
                </div>
                <div class="role-tags">
                    <span class="role-tag tag-orange">Classes & matières</span>
                    <span class="role-tag tag-orange">Saisie des notes</span>
                    <span class="role-tag tag-orange">Tableau de bord</span>
                </div>
                <span class="role-cta cta-orange">
                    Accéder
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </a>

            <a href="{{ route('login') }}" class="role-row indigo">
                <div class="role-icon" style="background:linear-gradient(135deg,#e0e7ff,#c7d2fe);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#4f46e5" stroke-width="2.2" stroke-linejoin="round"/>
                        <path d="M9 12l2 2 4-4" stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="role-info">
                    <div class="role-name" style="color:#312e81;">Espace Administrateur</div>
                    <div class="role-desc">Pilotez l'établissement, gérez les ressources et consultez les statistiques globales.</div>
                </div>
                <div class="role-tags">
                    <span class="role-tag tag-indigo">Gestion globale</span>
                    <span class="role-tag tag-indigo">Statistiques</span>
                    <span class="role-tag tag-indigo">Inscriptions</span>
                </div>
                <span class="role-cta cta-indigo">
                    Accéder
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </a>

        </section>

        <div class="stats-bar">
            <div>
                <div class="stat-num">1 250+</div>
                <div class="stat-label">Élèves inscrits</div>
            </div>
            <div class="stat-divider">
                <div class="stat-num">48</div>
                <div class="stat-label">Enseignants</div>
            </div>
            <div class="stat-divider">
                <div class="stat-num">97.2%</div>
                <div class="stat-label">Taux de présence</div>
            </div>
            <div class="stat-divider">
                <div class="stat-num">17.5</div>
                <div class="stat-label">Moyenne générale</div>
            </div>
        </div>

        <p class="footer-text">© 2025–2026 EduManager — Système de Gestion Scolaire — Tous droits réservés</p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js').catch(() => {}); }</script>
</body>
</html>
