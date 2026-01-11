<x-app-layout>
    <x-slot name="header">
        <h1 class="mb-0 h3 fw-bold">Tableau de Bord Administratif</h1>
    </x-slot>

    {{-- Section Aperçu - 4 Cards statistiques --}}
    <div class="mb-4">
        <h5 class="mb-3 text-muted">Aperçu</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="{{ route('admin.pending') }}" class="text-decoration-none">
                    <div class="border-0 shadow-sm card h-100">
                        <div class="card-body">
                            <div class="mb-2 d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-warning me-2" viewBox="0 0 16 16">
                                    <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985.299L10 2.5a7 7 0 0 1 1.356-1.087zm1.37.71a7 7 0 0 0-.439-.27l.5-1.94c.195.1.388.207.57.333zM8 2.748l-.096.315a7 7 0 0 0-.5-.07V2a8 8 0 0 1 .596.022zM6.5 2.5a7 7 0 0 0-.5.07v.573a7 7 0 0 1 .5.07V2.5zm-1.354.646a7 7 0 0 0-.44.27l-.57-1.93c.182-.126.375-.233.57-.333l.44 1.993zM5.5 3.5a7 7 0 0 0-.985.299L4 2.5a8 8 0 0 1 1.356-1.087L5.5 3.5zM4.5 4.5a7 7 0 0 0-.299.985L2.5 4a8 8 0 0 1 1.087-1.356L4.5 4.5zM4 5.5a7 7 0 0 0-.07.5h.573a7 7 0 0 1 .07-.5V5.5zm-1.94 1.5a7 7 0 0 0 .27.439l-1.93.57c-.1-.195-.207-.388-.333-.57l1.993-.44zM2.5 8a7 7 0 0 0 .299.985L2 9.5a8 8 0 0 1 0-3l.5.5zm.5.5a7 7 0 0 0 .5.07v-.573a7 7 0 0 1-.5.07V8.5zm.5.5a7 7 0 0 0 .07.5h-.573a7 7 0 0 1 .07-.5V9zm.5.5a7 7 0 0 0 .985.299L4.5 11.5a8 8 0 0 1-1.356-1.087L4 9.5zm.5.5a7 7 0 0 0 .439.27l-.57 1.93c-.182-.126-.375-.233-.57-.333L4.5 10zm1.5.5a7 7 0 0 0 .5.07v-.573a7 7 0 0 1-.5.07V10.5zm.5-.5a7 7 0 0 0 .5-.07v.573a7 7 0 0 1-.5-.07V10zm.5-.5a7 7 0 0 0 .299-.985L7.5 8.5a8 8 0 0 1-1.087 1.356L6.5 9.5zM8 9.5a7 7 0 0 0 .5.5v-1a7 7 0 0 1-.5-.5h.5z"/>
                                </svg>
                                <span class="text-muted small">Inscriptions en attente</span>
                            </div>
                            <h2 class="mb-1">{{ $pendingCount ?? 0 }}</h2>
                            <small class="text-warning">En attente de validation</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary me-2" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                            </svg>
                            <span class="text-muted small">Total Élèves</span>
                        </div>
                        <h2 class="mb-1">1250</h2>
                        <small class="text-success">+5% de plus de 30</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary me-2" viewBox="0 0 16 16">
                                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                            </svg>
                            <span class="text-muted small">Total Professeurs</span>
                        </div>
                        <h2 class="mb-1">95</h2>
                        <small class="text-success">+ nouveaux embauchés</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary me-2" viewBox="0 0 16 16">
                                <path d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1m3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z"/>
                            </svg>
                            <span class="text-muted small">Taux de Présence Global</span>
                        </div>
                        <h2 class="mb-1">93.5%</h2>
                        <small class="text-muted">Études en trimestre</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertes Récentes --}}
    <div class="mb-4 border-0 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 card-title">Alertes Récentes</h5>
            <div class="list-group list-group-flush">
                <div class="px-0 border-0 list-group-item d-flex align-items-start">
                    <span class="mt-1 badge bg-danger me-3">!</span>
                    <div class="flex-grow-1">
                        <strong>Alerte: Serveur de base de données en surcharge, intervention requise.</strong>
                        <div class="text-muted small">Il y a 5 min</div>
                    </div>
                </div>
                <div class="px-0 border-0 list-group-item d-flex align-items-start">
                    <span class="mt-1 badge bg-info me-3">i</span>
                    <div class="flex-grow-1">
                        <strong>Nouveau rapport d'audit de sécurité disponible.</strong>
                        <div class="text-muted small">Il y a 2 heures</div>
                    </div>
                </div>
                <div class="px-0 border-0 list-group-item d-flex align-items-start">
                    <span class="mt-1 badge bg-warning me-3">⚠</span>
                    <div class="flex-grow-1">
                        <strong>Le compte de l'élève "Sophie Dupont" a été mis à jour.</strong>
                        <div class="text-muted small">Hier</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gestion utilisateurs + Rapports --}}
    <div class="mb-4 row g-3">
        <div class="col-md-6">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <h5 class="mb-3 card-title">Gestion des Utilisateurs</h5>
                    <div class="gap-2 d-grid">
                        <a href="#" class="btn btn-primary">Gérer Élèves</a>
                        <a href="#" class="btn btn-outline-primary">Gérer Professeurs</a>
                        <a href="#" class="btn btn-outline-secondary">Gérer Administrateurs</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <h5 class="mb-3 card-title">Accès Rapide aux Rapports</h5>
                    <div class="gap-2 d-grid">
                        <a href="#" class="btn btn-success">Générer un rapport IA</a>
                        <a href="#" class="btn btn-outline-success">Consulter les fiches et reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphique Taux de Présence --}}
    <div class="border-0 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 card-title">Taux de Présence Mensuel</h5>
            <p class="text-muted small">Taux moyen de présence des élèves au fil des 6 derniers mois.</p>
            
            {{-- Graphique simple avec des barres CSS --}}
            <div class="d-flex align-items-end justify-content-between" style="height: 200px; gap: 10px;">
                <div class="rounded bg-primary" style="width: 5%; height: 10%;"></div>
                <div class="rounded bg-primary" style="width: 5%; height: 40%;"></div>
                <div class="rounded bg-primary" style="width: 5%; height: 60%;"></div>
                <div class="rounded bg-primary" style="width: 5%; height: 80%;"></div>
                <div class="rounded bg-primary" style="width: 5%; height: 90%;"></div>
                <div class="rounded bg-primary" style="width: 5%; height: 100%;"></div>
            </div>
            <div class="mt-2 d-flex justify-content-between small text-muted">
                <span>Jan</span>
                <span>Fév</span>
                <span>Mar</span>
                <span>Avr</span>
                <span>Mai</span>
                <span>Juin</span>
            </div>
        </div>
    </div>
</x-app-layout>
