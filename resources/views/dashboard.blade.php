<x-app-layout>
    <x-slot name="header">
        <h1 class="mb-0 h3 fw-bold">Tableau de Bord Professeur</h1>
    </x-slot>

    {{-- Section Aperçu - 4 Cards statistiques --}}
    <div class="mb-4">
        <h5 class="mb-3 text-muted">Aperçu</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary me-2" viewBox="0 0 16 16">
                                <path d="M6.5 14.5v-3.505c0-.245.25-.495.5-.495h2c.25 0 .5.25.5.5v3.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 .5-.5"/>
                            </svg>
                            <span class="text-muted small">Mes Classes</span>
                        </div>
                        <h2 class="mb-1">4</h2>
                        <small class="text-success">6ème A, 5ème B, 4ème C, 3ème A</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary me-2" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.92 10A5.5 5.5 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4"/>
                            </svg>
                            <span class="text-muted small">Élèves Suivis</span>
                        </div>
                        <h2 class="mb-1">87</h2>
                        <small class="text-success">Répartis dans 4 classes</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-primary me-2" viewBox="0 0 16 16">
                                <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783"/>
                            </svg>
                            <span class="text-muted small">Matières</span>
                        </div>
                        <h2 class="mb-1">2</h2>
                        <small class="text-success">Mathématiques, Physique</small>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <div class="mb-2 d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-warning me-2" viewBox="0 0 16 16">
                                <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/>
                                <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/>
                            </svg>
                            <span class="text-muted small">Notes à Saisir</span>
                        </div>
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
                        <strong>Mathématiques - 5ème B</strong>
                        <p class="mb-0 text-muted small">Chapitre : Équations du premier degré</p>
                    </div>
                    <span class="badge bg-primary">Lundi 10h00</span>
                </div>
                <div class="px-0 border-0 list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Physique - 4ème C</strong>
                        <p class="mb-0 text-muted small">TP : Électricité et circuits</p>
                    </div>
                    <span class="badge bg-primary">Mardi 14h00</span>
                </div>
                <div class="px-0 border-0 list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Mathématiques - 3ème A</strong>
                        <p class="mb-0 text-muted small">Chapitre : Trigonométrie</p>
                    </div>
                    <span class="badge bg-primary">Mercredi 9h00</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions Rapides + Notifications --}}
    <div class="mb-4 row g-3">
        <div class="col-md-6">
            <div class="border-0 shadow-sm card">
                <div class="card-body">
                    <h5 class="mb-3 card-title">Actions Rapides</h5>
                    <div class="gap-2 d-grid">
                        <a href="#" class="btn btn-primary">Saisir des notes</a>
                        <a href="#" class="btn btn-outline-primary">Voir mes classes</a>
                        <a href="#" class="btn btn-outline-secondary">Mon emploi du temps</a>
                        <a href="#" class="btn btn-outline-info">Générer un bulletin</a>
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
                            <p class="mb-0 text-muted small">Vendredi 15h00 - Salle des profs</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <span class="badge bg-warning me-2">!</span>
                            <strong>3 devoirs à corriger</strong>
                            <p class="mb-0 text-muted small">Classe 6ème A - Date limite : demain</p>
                        </div>
                        <div class="px-0 border-0 list-group-item">
                            <span class="badge bg-success me-2">✓</span>
                            <strong>Absence signalée</strong>
                            <p class="mb-0 text-muted small">Mamadou Diallo - 5ème B (justifiée)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Moyenne des Classes --}}
    <div class="border-0 shadow-sm card">
        <div class="card-body">
            <h5 class="mb-3 card-title">Performance Moyenne par Classe</h5>
            <p class="text-muted small">Moyennes générales de vos classes sur le trimestre en cours.</p>
            
            <div class="d-flex align-items-end justify-content-between" style="height: 200px; gap: 10px;">
                <div class="text-center" style="width: 100%;">
                    <div class="rounded bg-success" style="height: 70%; margin: 0 auto;"></div>
                    <small class="mt-2 d-block text-muted">6ème A</small>
                    <strong>14/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="rounded bg-success" style="height: 65%; margin: 0 auto;"></div>
                    <small class="mt-2 d-block text-muted">5ème B</small>
                    <strong>13/20</strong>
                </div>
                <div class="text-center" style="width: 100%;">
                    <div class="
