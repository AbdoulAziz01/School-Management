# 4. Architecture Logicielle — EduManager

## 4.1 Architecture globale

EduManager repose sur une architecture **MVC 3-tiers** classique orchestrée par le framework **Laravel 12** :

```
┌──────────────────────────────────────────────┐
│  COUCHE PRÉSENTATION (Client)                │
│  Navigateur — HTML/CSS/JS — Blade rendu      │
└──────────────────────────────────────────────┘
                     │ HTTP(S)
┌──────────────────────────────────────────────┐
│  COUCHE APPLICATION (Laravel)                │
│  ┌────────────────────────────────────────┐  │
│  │ Routing (routes/web.php, auth.php)     │  │
│  │ Middleware (auth, role, Student,       │  │
│  │   Teacher, VerifyCsrfToken, …)         │  │
│  │ Controllers (Admin/*, Teacher/*,       │  │
│  │   Student/*, Auth/*)                   │  │
│  │ FormRequests (LoginRequest, …)         │  │
│  │ Policies (TeacherAssignment, …)        │  │
│  │ Blade Views (resources/views/*)        │  │
│  │ Service externe : ChatController       │  │
│  │   proxy HTTP vers micro-service IA     │  │
│  └────────────────────────────────────────┘  │
└──────────────────────────────────────────────┘
                     │ Eloquent ORM
┌──────────────────────────────────────────────┐
│  COUCHE PERSISTANCE (MySQL/MariaDB)          │
│  Tables métier + tables système + pivots     │
└──────────────────────────────────────────────┘
```

---

## 4.2 Justification du pattern MVC

Le **MVC** est le pattern architectural imposé par Laravel. Son adoption se justifie par :

- **Séparation des préoccupations (SoC)** : chaque couche a une responsabilité unique.
  - *Models* (`app/Models/*.php`, 13 classes Eloquent) encapsulent les données et les règles de persistance (relations `belongsTo`, `hasMany`, `belongsToMany`, casts, mutateurs comme `SchoolClass::getNameAttribute` qui transforme `6eme 1` en `6ème A`).
  - *Views* (`resources/views/*.blade.php`) utilisent le moteur Blade pour composer le HTML de manière déclarative et réutilisable (layouts, composants `x-app-layout`, directives `@auth`, `@role`).
  - *Controllers* (26 contrôleurs répartis en espaces de noms `Admin/`, `Teacher/`, `Student/`, `Auth/`) orchestrent la logique métier et les interactions entre modèles et vues.
- **Testabilité** : les contrôleurs et modèles sont aisément isolables (PHPUnit 11).
- **Maintenabilité** : l'arborescence par rôle (`Admin/`, `Teacher/`, `Student/`) reflète l'organisation fonctionnelle et limite le couplage entre acteurs.
- **Productivité** : Laravel fournit Eloquent, le système de validation, le routing, l'ACL (middleware), l'authentification (Breeze), la protection CSRF, la gestion des sessions — autant de briques qui auraient dû être réécrites en Core PHP.

---

## 4.3 Architecture de sécurité multicouche

La sécurité est implémentée **en défense en profondeur** :

1. **Routing-level** : les groupes de routes `prefix('admin')`, `prefix('teacher')`, `prefix('student')` sont encapsulés dans des `middleware(['auth', …])`.
2. **Middleware d'authentification** (`auth`) : refuse les requêtes sans session active.
3. **Middleware de rôle** (`RoleMiddleware`, `StudentMiddleware`, `TeacherMiddleware`) : redirige automatiquement l'utilisateur vers son propre tableau de bord en cas de tentative d'accès à une zone non autorisée.
4. **Contrôleur** : vérifications métier supplémentaires — par ex. `TeacherGradesController::store` vérifie via `$teacher->assignedClasses()->where('classes.id', $request->class_id)->exists()` que l'enseignant possède bien la classe ciblée.
5. **Policy** (`authorize`) : utilisée dans `TeacherAssignmentController` via `$this->authorize('viewAny', TeacherAssignment::class)`.
6. **FormRequest** (`LoginRequest`) : validation + rate-limiting (5 tentatives par IP+identifiant) + vérification du `status` (pending/rejected bloqués).
7. **Hashage des mots de passe** : trait `password` casté en `hashed` dans `User::$casts` (bcrypt).
8. **CSRF** : protection automatique sur tous les POST/PUT/PATCH/DELETE.

---

## 4.4 Intégration d'un micro-service IA

L'architecture est enrichie par un **service hétérogène** : un micro-service Python (`ai_service/app_ai_final.py`, Flask) exposé sur le port 5000. Le `ChatController` agit comme **proxy HTTP** (`Http::timeout(30)->post($this->aiServiceUrl . '/chat', …)`), découplant la logique LLM (Python, plus adapté à l'écosystème IA) de l'application Laravel (PHP). Ce découplage permet :

- un déploiement indépendant des deux services,
- un redémarrage du chatbot sans impacter la session utilisateur,
- une évolution technologique autonome (le service IA pourrait être réécrit sans toucher à Laravel).

---

## 4.5 Remarque sur la couche réactive (IMPORTANT POUR LE JURY)

Le mémoire mentionne Livewire, mais l'analyse du code révèle que **Livewire n'est pas utilisé**. Aucune dépendance `livewire/livewire` dans `composer.json` (seul `laravel/breeze` est présent), aucun composant dans `app/Livewire/`. Les formulaires sont des formulaires HTML classiques qui déclenchent des requêtes HTTP POST → contrôleur → rechargement complet de la page (SSR).

Ce choix, typique du scaffolding **Breeze Blade**, est parfaitement défendable :

- simplicité d'apprentissage,
- meilleure prévisibilité du rendu,
- réduction de la surface d'attaque (pas de WebSockets),
- pas de dépendance JavaScript lourde côté client.

Si vous souhaitez malgré tout valoriser la "réactivité", vous pouvez mentionner l'utilisation ponctuelle d'**AJAX** dans `StudentController::index` (`if (request()->ajax())`) pour le rafraîchissement des onglets sans rechargement, et l'appel asynchrone au chatbot IA dans `resources/views/chat.blade.php`.

---

## 4.6 Synthèse des choix technologiques

| Couche | Technologie | Version | Justification |
|---|---|---|---|
| Serveur HTTP | Apache (via XAMPP) | 2.4+ | Environnement de développement Windows |
| Langage backend | PHP | 8.2 | Typage strict, enums, readonly — productivité moderne |
| Framework | Laravel | 12.0 | Écosystème mature, ORM Eloquent, Artisan CLI, sécurité prête à l'emploi |
| Auth scaffolding | Laravel Breeze | 2.3 | Auth + Blade minimaliste, adapté au périmètre du projet |
| ACL | Spatie Laravel-Permission | 6.24 | Gestion des rôles/permissions granulaire |
| SGBD | MySQL / MariaDB | 8.x / 10.x | Intégration native XAMPP, compatibilité Eloquent |
| Front | Blade + Tailwind (Breeze) | — | Templates serveur, classes utilitaires |
| Service IA | Python + Flask | 3.x | Bibliothèques LLM (langchain, openai) disponibles |
| Qualité | PHPUnit 11, Pint | — | Tests et formatage automatique |

---

## Conclusion

Cette conception documente fidèlement le code existant d'EduManager. Les diagrammes PlantUML fournis sont **copiables-collables** sur [plantuml.com](https://plantuml.com) ou dans un plugin IDE (IntelliJ/VS Code) pour être générés en SVG/PNG et insérés dans votre mémoire. Le dictionnaire de données suit l'ordre des migrations et peut servir de schéma entité-relation directement.

Pour la soutenance, je vous recommande d'être particulièrement préparé·e sur :

- la **cohabitation des deux modèles de rôles** (`ROLE_TEACHER = 'teacher'` constante vs `'professeur'` dans certains middlewares, `'student'` vs `'eleve'`) — c'est une dette technique héritée d'une migration de refactoring ;
- la **clarification Livewire vs Blade classique** si vous aviez annoncé Livewire dans votre soutenance ;
- la **règle RG-15** sur le calcul pondéré de la moyenne générale, qui est votre règle métier la plus technique.
