<x-app-layout>
    <x-slot name="header">
        <h1 class="mb-0 h3 fw-bold">Tableau de Bord Élève</h1>
    </x-slot>

    {{-- Section Aperçu --}}
    <div class="mb-4">
        <h5 class="mb-3 text-muted">Aperçu</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-white border-0 shadow-sm card bg-primary">
                    <div class="text-center card-body">
                        <span class="small">Ma Classe</span>
                        <h2 class="mb-0">6ème A</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="text-white border-0 shadow-sm card bg-success">
                    <div class="text-center card-body">
                        <span class="small">Moyenne Générale</span>
                        <h2 class="mb-0">14.5/20</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="text-white border-0 shadow-sm card bg-info">
                    <div class="text-center card-body">
                        <span class="small">Matières</span>
                        <h2 class="mb-0">8</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="text-white border-0 shadow-sm card bg-warning">
                    <div class="text-center card-body">
                        <span class="small">Prochain Cours</span>
                        <h2 class="mb-0">Maths 8h</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mes Matières et Résultats --}}
    <div class="mb-4 border-0 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 card-title">Mes Matières et Résultats</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Matière</th>
                            <th>Professeur</th>
                            <th>Moyenne</th>
                            <th>Appréciation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Mathématiques</strong></td>
                            <td>M. Diallo</td>
                            <td><span class="badge bg-success">16/20</span></td>
                            <td><small class="text-success">Très bien</small></td>
                        </tr>
                        <tr>
                            <td><strong>Français</strong></td>
                            <td>Mme. Fall</td>
                            <td><span class="badge bg-success">14/20</span></td>
                            <td><small class="text-success">Bien</small></td>
                        </tr>
                        <tr>
                            <td><strong>Anglais</strong></td>
                            <td>M. Ndiaye</td>
                            <td><span class="badge bg-warning">12/20</span></td>
                            <td><small class="text-warning">Assez bien</small></td>
                        </tr>
                        <tr>
                            <td><strong>Physique-Chimie</strong></td>
                            <td>Mme. Sow</td>
                            <td><span class="badge bg-success">15/20</span></td>
                            <td><small class="text-success">Bien</small></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mon Emploi du Temps + Devoirs --}}
    <div class="mb-4 row g-3">
        <div class="col-md-6">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <h5 class="mb-3 card-title">Emploi du Temps (Aujourd'hui)</h5>
                    <div class="list-group list-group-flush">
                        <div class="px-0 border-0 list-group-item">
                            <strong>8h00 - 9h00</strong>
                            <p class="mb-0 text-muted">Mathématiques - Salle 12</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <strong>9h00 - 10h00</strong>
                            <p class="mb-0 text-muted">Français - Salle 8</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <strong>10h30 - 11h30</strong>
                            <p class="mb-0 text-muted">Anglais - Salle 5</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <strong>14h00 - 15h00</strong>
                            <p class="mb-0 text-muted">Sport - Gymnase</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <h5 class="mb-3 card-title">Devoirs à Rendre</h5>
                    <div class="list-group list-group-flush">
                        <div class="px-0 border-0 list-group-item">
                            <span class="badge bg-danger me-2">Urgent</span>
                            <strong>Maths - Exercices chapitre 5</strong>
                            <p class="mb-0 text-muted small">À rendre : Demain</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <span class="badge bg-warning me-2">Bientôt</span>
                            <strong>Français - Rédaction</strong>
                            <p class="mb-0 text-muted small">À rendre : Vendredi</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <span class="badge bg-info me-2">OK</span>
                            <strong>Anglais - Vocabulaire</strong>
                            <p class="mb-0 text-muted small">À rendre : Lundi prochain</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Progression Trimestrielle --}}
    <div class="border-0 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 card-title">Ma Progression Trimestrielle</h5>
            <p class="text-muted small">Évolution de ta moyenne générale sur les 4 derniers mois.</p>
            
            <div class="d-flex align-items-end justify-content-between" style="height: 180px; gap: 15px;">
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-info" style="width: 60px; height: 60%;"></div>
                    <small class="mt-2 d-block">Sept</small>
                    <strong>12/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-info" style="width: 60px; height: 65%;"></div>
                    <small class="mt-2 d-block">Oct</small>
                    <strong>13/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-success" style="width: 60px; height: 70%;"></div>
                    <small class="mt-2 d-block">Nov</small>
                    <strong>14/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="mx-auto rounded bg-success" style="width: 60px; height: 73%;"></div>
                    <small class="mt-2 d-block">Déc</small>
                    <strong>14.5/20</strong>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
