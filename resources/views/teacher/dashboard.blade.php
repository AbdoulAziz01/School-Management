<x-app-layout>
    <x-slot name="header">
        <h1 class="mb-0 h3 fw-bold">Tableau de Bord Professeur</h1>
    </x-slot>

    {{-- Section Aperçu --}}
    <div class="mb-4">
        <h5 class="mb-3 text-muted">Aperçu</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <span class="text-muted small">Mes Classes</span>
                        <h2 class="mb-1">4</h2>
                        <small class="text-success">6ème A, 5ème B, 4ème C, 3ème A</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <span class="text-muted small">Élèves Suivis</span>
                        <h2 class="mb-1">87</h2>
                        <small class="text-success">Répartis dans 4 classes</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <span class="text-muted small">Matières</span>
                        <h2 class="mb-1">2</h2>
                        <small class="text-success">Mathématiques, Physique</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <span class="text-muted small">Notes à Saisir</span>
                        <h2 class="mb-1">12</h2>
                        <small class="text-warning">Devoirs et contrôles</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mes Prochains Cours --}}
    <div class="mb-4 border-0 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 card-title">Mes Prochains Cours</h5>
            <div class="list-group list-group-flush">
                <div class="px-0 border-0 list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Mathématiques - 6ème A</strong>
                        <p class="mb-0 text-muted small">Chapitre : Les fractions</p>
                    </div>
                    <span class="badge bg-primary">Lundi 8h00</span>
                </div>
                <div class="px-0 border-0 list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Physique - 4ème C</strong>
                        <p class="mb-0 text-muted small">TP : Électricité</p>
                    </div>
                    <span class="badge bg-primary">Mardi 14h00</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions + Notifications --}}
    <div class="mb-4 row g-3">
        <div class="col-md-6">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <h5 class="mb-3 card-title">Actions Rapides</h5>
                    <div class="gap-2 d-grid">
                        <a href="#" class="btn btn-primary">Saisir des notes</a>
                        <a href="#" class="btn btn-outline-primary">Voir mes classes</a>
                        <a href="#" class="btn btn-outline-secondary">Mon emploi du temps</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <h5 class="mb-3 card-title">Notifications</h5>
                    <div class="list-group list-group-flush">
                        <div class="px-0 border-0 list-group-item">
                            <span class="badge bg-info me-2">i</span>
                            <strong>Réunion pédagogique</strong>
                            <p class="mb-0 text-muted small">Vendredi 15h - Salle des profs</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <span class="badge bg-warning me-2">!</span>
                            <strong>3 devoirs à corriger</strong>
                            <p class="mb-0 text-muted small">Classe 6ème A</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Moyenne --}}
    <div class="border-0 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 card-title">Performance Moyenne par Classe</h5>
            <p class="text-muted small">Moyennes générales sur le trimestre.</p>
            
            <div class="d-flex align-items-end justify-content-between" style="height: 180px; gap: 15px;">
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-success" style="width: 60px; height: 70%;"></div>
                    <small class="mt-2 d-block">6ème A</small>
                    <strong>14/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-success" style="width: 60px; height: 65%;"></div>
                    <small class="mt-2 d-block">5ème B</small>
                    <strong>13/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-warning" style="width: 60px; height: 55%;"></div>
                    <small class="mt-2 d-block">4ème C</small>
                    <strong>11/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-success" style="width: 60px; height: 75%;"></div>
                    <small class="mt-2 d-block">3ème A</small>
                    <strong>15/20</strong>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
 