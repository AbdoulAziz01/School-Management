# Cahier des Charges Fonctionnel et Technique — EduManager
### Version finale — Document de référence pour le mémoire de fin d'études

---

## 1. Contexte et Périmètre du projet

### 1.1 Présentation du projet

**EduManager** est une plateforme web de gestion scolaire conçue pour les établissements du secondaire (collège et lycée) opérant dans le **système éducatif sénégalais** (semestres 1 et 2, séries L / S / ES, notation sur 20, conseil de classe de fin d'année). Elle centralise l'ensemble du cycle de vie administratif et pédagogique : inscription des usagers, affectation en classes, saisie des notes et des présences, génération automatisée des bulletins semestriels et annuels, consultation des emplois du temps et assistance conversationnelle par un chatbot IA.

L'objectif principal, déduit de la couverture fonctionnelle réellement implémentée, est de **dématérialiser la chaîne pédagogique** et d'offrir à chaque acteur (administration, enseignant, élève) un espace personnalisé et sécurisé, tout en permettant à tout utilisateur authentifié d'interroger la base de données en langage naturel via un assistant IA.

### 1.2 Objectifs déduits de l'implémentation

L'analyse du code source met en évidence les objectifs opérationnels suivants :

- **Objectif 1 — Centraliser la gestion administrative** : inscriptions, validations, affectations (cf. `Admin/PendingRegistrationController`, `Admin/StudentController`, `Admin/TeacherController`).
- **Objectif 2 — Automatiser l'évaluation pédagogique** selon le modèle sénégalais (moyenne pondérée : `(D1+D2)/2 × 0,4 + Composition × 0,6` — cf. `Student/StudentBulletinController::calculateBulletinData`).
- **Objectif 3 — Tracer les absences** avec quatre états (`present`, `absent`, `late`, `excused`) et un historique par élève (cf. `Teacher/TeacherAttendanceController`).
- **Objectif 4 — Produire automatiquement les bulletins** semestriels et annuels avec calcul de rang et statistiques de classe (cf. `StudentBulletinController::calculateRank`, `calculateClassStats`).
- **Objectif 5 — Démocratiser la donnée** via un chatbot IA capable de traduire une question en langue naturelle en requête SQL sur la base pédagogique (cf. `ai_service/app_ai_final.py`).

### 1.3 Périmètre fonctionnel retenu (In-Scope)

Le périmètre retenu correspond strictement aux fonctionnalités présentes dans le code source.

| Domaine | Fonctionnalités présentes dans le code |
|---|---|
| **Authentification** | Inscription libre (élève / enseignant) avec statut `pending`, connexion par email **ou** identifiant, rate-limiting (5 tentatives), réinitialisation de mot de passe, confirmation de mot de passe, déconnexion GET/POST |
| **Administration** | Validation/rejet des inscriptions, CRUD élèves, CRUD enseignants, CRUD classes, CRUD années académiques (avec `is_current` unique), CRUD matières, affectation classes↔enseignants (pivot `class_teacher`), affectations (enseignant, classe, matière, année), tableau de bord avec KPI |
| **Espace enseignant** | Tableau de bord, consultation des classes affectées, saisie multi-élèves des notes, édition/suppression des notes sur son périmètre, appel (attendance) quotidien avec `updateOrCreate`, historique par élève, emploi du temps, profil et photo |
| **Espace élève** | Tableau de bord (moyenne, taux de présence, 5 dernières notes), consultation des notes par matière, **bulletin semestriel système sénégalais**, **bulletin annuel** avec décision du conseil (Admis / Passage conditionnel / Redoublement), emploi du temps, historique de présence, profil |
| **Chatbot IA** | Proxy HTTP `/chat/send` vers micro-service Python, vérification d'état (`/chat/health`), consultation du schéma (`/chat/schema`, admin uniquement) |
| **Sécurité** | Middleware `auth`, `role`, `StudentMiddleware`, `TeacherMiddleware`, CSRF automatique, hashing bcrypt (cast `hashed`), rate-limiting authentification, intégration Spatie Laravel-Permission 6.24 |

### 1.4 Hors périmètre (Out-of-Scope)

Les éléments suivants sont **explicitement absents du code** et ne font donc pas partie de la livraison :

- Paiement en ligne des frais de scolarité (aucune passerelle, aucune table `payments`).
- Envoi effectif d'emails de notification (les envois sont **commentés** : `// Mail::to($user->email)->send(new RegistrationApproved($user));`).
- Interface mobile native (iOS/Android) : seul le web responsive (Tailwind CSS) est fourni.
- Parent / tuteur : aucun rôle `parent` n'est défini dans `User::ROLE_*` ni dans l'enum `role` de la migration `add_identifier_and_role_to_users_table`.
- Messagerie interne entre utilisateurs : aucune table `messages`, aucun contrôleur associé.
- Import/export Excel/CSV : non implémenté.
- API publique REST versionnée : les routes sont exclusivement web (`routes/web.php`), et bien que `laravel/sanctum` soit référencé dans le `Kernel.php` pour la stateful-ness, `HasApiTokens` a été explicitement retiré du modèle `User` (commentaire : `// ← HasApiTokens RETIRÉ`).
- Génération PDF des bulletins : seul le rendu Blade HTML est produit ; aucun package type `barryvdh/laravel-dompdf` n'est présent dans `composer.json`.
- Module de discipline / sanctions.
- Module de gestion des salles et de leur occupation en temps réel.

---

## 2. Exigences Fonctionnelles (extraites du code)

Les exigences sont numérotées **EF-xx**. Pour chaque rôle, elles sont extraites méthodiquement du fichier `routes/web.php` croisé avec les contrôleurs correspondants.

### 2.1 Exigences pour l'Administrateur

Routes protégées par `middleware(['auth', 'role:admin'])`, préfixe `/admin`.

| Code | Exigence fonctionnelle | Route | Contrôleur / méthode |
|---|---|---|---|
| EF-A01 | Afficher le tableau de bord administrateur avec KPI : inscriptions en attente, nombre de classes, élèves non affectés, année scolaire en cours | `GET /admin/dashboard` | `Admin\DashboardController::index` |
| EF-A02 | Consulter la liste des inscriptions en attente | `GET /admin/pending`, `GET /admin/pending-registrations` | `PendingRegistrationController::pending` |
| EF-A03 | Approuver une inscription (définit `status = approved` et `class_id`) | `PATCH /admin/registrations/{user}/approve` | `PendingRegistrationController::approve` |
| EF-A04 | Rejeter une inscription (définit `status = rejected`) | `PATCH /admin/registrations/{user}/reject` | `PendingRegistrationController::reject` |
| EF-A05 | Gérer les élèves en CRUD complet (avec génération automatique d'un identifiant `E{compteur}`) | `Route::resource('students')` | `Admin\StudentController` |
| EF-A06 | Afficher la fiche détaillée d'un élève (notes groupées par matière, moyenne pondérée, statistiques de présence) | `GET /admin/students/{student}` | `StudentController::show` |
| EF-A07 | Affecter un élève seul à une classe | `POST /admin/students/{student}/assign` | `StudentController::assignToClass` |
| EF-A08 | Affecter plusieurs élèves à une classe en masse (bulk) | `POST /admin/students/assign/bulk` | `StudentController::assignToClassBulk` |
| EF-A09 | Désaffecter un élève d'une classe | `DELETE /admin/students/{student}/unassign` | `StudentController::unassign` |
| EF-A10 | Gérer les enseignants en CRUD complet (identifiant obligatoire, mot de passe obligatoire à la création) | `Route::resource('teachers')` | `Admin\TeacherController` |
| EF-A11 | Affecter un ensemble de classes à un enseignant (pivot `class_teacher`) | `GET / PUT /admin/teachers/{teacher}/classes` | `Admin\TeacherClassController::edit`, `update` |
| EF-A12 | Créer / lister / modifier / supprimer des classes (nom, niveau, année, capacité 1–50) | `Route::resource('classes')` | `Admin\ClassController` |
| EF-A13 | Gérer les années académiques en CRUD, une seule étant `is_current` à la fois | `Route::resource('academic-years')` | `Admin\AcademicYearController` |
| EF-A14 | Définir une année comme courante (désactive automatiquement les autres) | `PATCH /admin/academic-years/{year}/set-current` | `AcademicYearController::setCurrent` |
| EF-A15 | Gérer les matières (code unique en majuscules, coefficient 0,5–10) | `Route::resource('subjects')` | `Admin\SubjectController` |
| EF-A16 | Éditer son propre profil administrateur | `GET / PUT /admin/profile` | `Admin\ProfileController::edit`, `update` |

### 2.2 Exigences pour l'Enseignant

Routes protégées par `middleware(['auth', \App\Http\Middleware\TeacherMiddleware::class])`, préfixe `/teacher`.

| Code | Exigence fonctionnelle | Route | Contrôleur / méthode |
|---|---|---|---|
| EF-T01 | Afficher le tableau de bord enseignant (classes affectées, nombre d'élèves, matières enseignées, 5 dernières notes saisies, moyennes par classe) | `GET /teacher/dashboard` | `Teacher\TeacherDashboardController::index` |
| EF-T02 | Consulter la liste des classes qui lui sont affectées | `GET /teacher/classes` | `TeacherClassesController::index` |
| EF-T03 | Consulter le détail d'une classe affectée (élèves, matières, statistiques) | `GET /teacher/classes/{id}` | `TeacherClassesController::show` |
| EF-T04 | Consulter les notes filtrées par classe et par matière | `GET /teacher/grades` | `TeacherGradesController::index` |
| EF-T05 | Saisir simultanément les notes d'une classe entière pour un contrôle donné (type, date, coefficient unique) | `GET /teacher/grades/create`, `POST /teacher/grades` | `TeacherGradesController::create`, `store` |
| EF-T06 | Modifier une note existante (sous réserve d'appartenance au périmètre) | `GET /teacher/grades/{id}/edit`, `PUT /teacher/grades/{id}` | `TeacherGradesController::edit`, `update` |
| EF-T07 | Supprimer une note (sous réserve d'appartenance au périmètre) | `DELETE /teacher/grades/{id}` | `TeacherGradesController::destroy` |
| EF-T08 | Afficher la feuille d'appel d'une classe pour une date donnée | `GET /teacher/attendance?class_id=X&date=Y` | `TeacherAttendanceController::index` |
| EF-T09 | Enregistrer les présences multi-élèves via `updateOrCreate` sur le couple `(user_id, date)` | `POST /teacher/attendance` | `TeacherAttendanceController::store` |
| EF-T10 | Afficher l'historique de présence d'un élève (paginé 30/page, statistiques) | `GET /teacher/attendance/student/{studentId}` | `TeacherAttendanceController::studentHistory` |
| EF-T11 | Consulter son emploi du temps hebdomadaire (grille jour × créneau) | `GET /teacher/schedule` | `TeacherScheduleController::index` |
| EF-T12 | Consulter et modifier son profil, sa photo et son mot de passe | `GET / PUT /teacher/profile`, `POST /teacher/profile/photo`, `POST /teacher/profile/password` | `TeacherProfileController` |

### 2.3 Exigences pour l'Élève

Routes protégées par `middleware(['auth', \App\Http\Middleware\StudentMiddleware::class])`, préfixe `/student`.

| Code | Exigence fonctionnelle | Route | Contrôleur / méthode |
|---|---|---|---|
| EF-E01 | Afficher le tableau de bord élève (moyenne brute, taux de présence, 5 dernières notes) | `GET /student/dashboard` | `Student\StudentDashboardController::dashboard` |
| EF-E02 | Consulter ses notes regroupées par matière avec moyenne et appréciation automatique | `GET /student/grades` | `StudentGradesController::index` |
| EF-E03 | Afficher le **bulletin semestriel** au format sénégalais : Devoir 1, Devoir 2, Composition, Moyenne devoirs, Moyenne matière, Points, Appréciation, Rang, Statistiques de classe | `GET /student/bulletin` | `StudentBulletinController::index` |
| EF-E04 | Afficher le **bulletin annuel** (synthèse des 2 semestres, moyenne annuelle, décision du conseil de classe, mention) | `GET /student/bulletin/annual` | `StudentBulletinController::annual` |
| EF-E05 | Consulter son emploi du temps hebdomadaire | `GET /student/schedule` | `StudentScheduleController::index` |
| EF-E06 | Consulter son historique de présences (paginé 15/page, statistiques, calendrier du mois en cours) | `GET /student/attendance` | `StudentAttendanceController::index` |
| EF-E07 | Consulter son profil | `GET /student/profile` | `StudentProfileController::index` |
| EF-E08 | Modifier son profil personnel | `GET /student/profile/edit`, `PUT /student/profile` | `StudentProfileController::edit`, `update` |
| EF-E09 | Mettre à jour sa photo de profil | `POST /student/profile/photo` | `StudentProfileController::updatePhoto` |
| EF-E10 | Mettre à jour son mot de passe | `POST /student/profile/password` | `StudentProfileController::updatePassword` |

### 2.4 Exigences transverses (tout utilisateur authentifié)

| Code | Exigence fonctionnelle | Route | Contrôleur / méthode |
|---|---|---|---|
| EF-X01 | S'auto-inscrire en tant qu'élève ou enseignant (statut initial `pending`, identifiant auto-généré `{P\|E}{AAAA}{NNN}`) | `GET / POST /register` | `Auth\RegisteredUserController::create`, `store` |
| EF-X02 | Se connecter par email **ou** identifiant + mot de passe, avec rate-limiting 5 tentatives IP+identifiant | `GET / POST /login` | `Auth\AuthenticatedSessionController` + `LoginRequest` |
| EF-X03 | Réinitialiser son mot de passe via lien email | `GET / POST /forgot-password`, `GET / POST /reset-password` | `Auth\PasswordResetLinkController`, `NewPasswordController` |
| EF-X04 | Confirmer son mot de passe avant opérations sensibles | `GET / POST /confirm-password` | `Auth\ConfirmablePasswordController` |
| EF-X05 | Accéder au chatbot IA (interface chat) | `GET /chat` | `ChatController::index` |
| EF-X06 | Envoyer un message au chatbot (proxyfié vers service Flask, validation 1–1000 caractères, timeout 30 s) | `POST /chat/send` | `ChatController::sendMessage` |
| EF-X07 | Interroger l'état de santé du service IA | `GET /chat/health` | `ChatController::healthCheck` |
| EF-X08 | Consulter le schéma de la base (**admin uniquement** — contrôle explicite `auth()->user()->role !== 'admin'` → 403) | `GET /chat/schema` | `ChatController::getSchema` |
| EF-X09 | Se déconnecter (via POST ou GET de secours) | `POST / GET /logout` | `AuthenticatedSessionController::destroy` |
| EF-X10 | Afficher une page 404 personnalisée en cas de route inconnue | `Route::fallback(...)` | Vue `errors.404` |

---

## 3. Règles de Gestion et Architecture de Sécurité (extraites du code)

### 3.1 Règles de gestion métier (RG)

Les règles ci-dessous sont toutes **vérifiées dans le code** (validations, contraintes de migrations, logique métier).

| Code | Règle de gestion | Source dans le code |
|---|---|---|
| RG-01 | Une note est un décimal compris entre **0,00 et 20,00** inclus. La colonne est `decimal(5,2)`. | `grades` migration (`decimal('grade', 5, 2)`) + `TeacherGradesController::store` (`grades.*.grade` → `min:0\|max:20`) |
| RG-02 | Le coefficient associé à une note saisie est compris entre **0,5 et 5**. | `TeacherGradesController::store` → `coefficient` → `required\|numeric\|min:0.5\|max:5` |
| RG-03 | Le coefficient associé à une **matière** est compris entre **0,5 et 10**. | `SubjectController::store / update` → `coefficient` → `required\|numeric\|min:0.5\|max:10` |
| RG-04 | Un élève ne peut recevoir qu'une seule note pour un quadruplet *(utilisateur, matière, date, type)*. | Migration `grades` : `unique(['user_id','subject_id','date','type'])` |
| RG-05 | L'identifiant utilisateur auto-généré suit le format `{P\|E}{AAAA}{NNN}` — `P` pour enseignant, `E` pour élève, année courante sur 4 chiffres, numéro séquentiel sur 3 chiffres. | `RegisteredUserController::store` (prefix match + `str_pad(..., 3, '0', STR_PAD_LEFT)`) |
| RG-06 | Tout compte auto-inscrit est créé avec `status = 'pending'` et ne peut pas se connecter avant validation. | `RegisteredUserController::store` + `LoginRequest::authenticate` (rejette si `status !== 'approved'`) |
| RG-07 | Un compte `rejected` est bloqué à la connexion avec un message dédié ; un compte `pending` idem. | `LoginRequest::authenticate` (lignes 69–83) |
| RG-08 | La redirection post-authentification dépend du rôle : `admin` → `admin.dashboard`, `teacher`/`professeur` → `teacher.dashboard`, `eleve` → `student.dashboard`. | `AuthenticatedSessionController::redirectToDashboard` (match expression) |
| RG-09 | Une seule `academic_year` peut porter `is_current = true` à un instant donné : le contrôleur désactive les autres avant activation. | `AcademicYearController::setCurrent` et `store` / `update` |
| RG-10 | Un enseignant ne peut saisir / modifier / supprimer une note **que** sur une classe à laquelle il est affecté dans `class_teacher`. | `TeacherGradesController::store` (`$teacher->assignedClasses()->where('classes.id', ...)->exists()`) et `update`, `destroy` |
| RG-11 | Un enseignant ne peut consulter l'historique d'un élève **que** si cet élève appartient à l'une de ses classes affectées. | `TeacherAttendanceController::studentHistory` (même contrôle) |
| RG-12 | Le triplet *(enseignant, classe, matière, année académique)* est unique pour éviter les doublons d'affectation fonctionnelle. | Migration `teacher_assignments` : `unique(['teacher_id','class_id','subject_id','academic_year_id'])` |
| RG-13 | Le code d'une matière est unique et systématiquement stocké en MAJUSCULES. | `SubjectController::store` → `strtoupper($validated['code'])` + migration `subjects.code` unique |
| RG-14 | Le statut de présence appartient obligatoirement à l'énumération `{present, absent, late, excused}`. | Migration `attendances` : `enum('status', ['present','absent','late','excused'])` + validation `in:present,absent,late,excused` |
| RG-15 | Un élève n'a qu'une seule entrée de présence par jour (création ou mise à jour via `updateOrCreate` sur `(user_id, date)`). | `TeacherAttendanceController::store` |
| RG-16 | La moyenne d'une matière (bulletin sénégalais) suit la formule : **Moyenne devoirs** = `(D1 + D2) / 2` ; **Moyenne matière** = `Moyenne devoirs × 0,4 + Composition × 0,6`. Si seule la composition existe, elle sert de moyenne matière ; si seule la moyenne des devoirs existe, elle sert de moyenne matière. | `StudentBulletinController::calculateBulletinData` (lignes 119–135) |
| RG-17 | La moyenne générale est la **somme pondérée** des moyennes matière par coefficient, divisée par la somme des coefficients. | `StudentBulletinController::calculateWeightedAverage` |
| RG-18 | Le rang de l'élève est calculé par tri décroissant des moyennes générales de tous les élèves approuvés de sa classe pour le même semestre et la même année académique. | `StudentBulletinController::calculateRank` |
| RG-19 | L'appréciation est attribuée automatiquement : `≥16` Très Bien ; `≥14` Bien ; `≥12` Assez Bien ; `≥10` Passable ; `≥8` Insuffisant ; sinon Très Insuffisant. | `StudentBulletinController::getAppreciation` |
| RG-20 | La décision du conseil annuel dépend de la moyenne annuelle : `≥12` Admis avec mention ; `≥10` Admis ; `≥8` Passage conditionnel / Redoublement ; `<8` Redoublement. | `StudentBulletinController::getDecision` et `getMention` |
| RG-21 | Le semestre courant est déduit du mois : d'octobre à janvier → Semestre 1 ; de février à juin → Semestre 2. | `StudentBulletinController::getCurrentSemester` |
| RG-22 | Une classe a une capacité par défaut de **40 élèves** (migration), mais le formulaire admin autorise 1–50. | Migration `classes` (`capacity->default(40)`) + `ClassController::store` (`capacity` → `min:1\|max:50`) |
| RG-23 | Un niveau appartient à un cycle `{college, lycee}` et peut avoir une série optionnelle (L, S, ES — pour le lycée). | Migration `levels` + `add_serie_to_levels_table` |
| RG-24 | Un créneau d'emploi du temps est unique pour le triplet *(jour, heure de début, classe)*. | Migration `timetables` : `unique(['day_of_week','start_time','class_id'])` |
| RG-25 | Un étudiant ne peut se trouver qu'une seule fois dans un groupe pour une période donnée. | Migration `class_group_student` : `unique(['class_group_id','student_id','start_date'])` |
| RG-26 | Un mot de passe respecte les règles Laravel par défaut (`Rules\Password::defaults()` : minimum 8 caractères). Les mots de passe sont stockés **hachés (bcrypt)** via le cast `password => hashed`. | `RegisteredUserController::store` + `User::$casts` |
| RG-27 | À l'inscription, un élève doit obligatoirement renseigner sa classe souhaitée (`desired_class`), un enseignant au moins une matière. | `RegisteredUserController::store` : `required_if:role,eleve` et `required_if:role,teacher` |
| RG-28 | Toute suppression d'entité principale (classe, matière, utilisateur) déclenche une suppression en cascade sur ses dépendances pour préserver l'intégrité référentielle. | Toutes les migrations : `onDelete('cascade')` sur les `foreignId` |
| RG-29 | Une matière ne peut être supprimée si elle est encore utilisée par une affectation (`teacher_assignments`). | `SubjectController::destroy` |
| RG-30 | Une classe ne peut être supprimée si elle contient des élèves. | `ClassController::destroy` |
| RG-31 | Une année académique ne peut être supprimée si elle est liée à des classes ou des affectations enseignant. | `AcademicYearController::destroy` |
| RG-32 | Le schéma de la base (endpoint `/chat/schema`) est réservé aux administrateurs (contrôle explicite dans le contrôleur, erreur 403 sinon). | `ChatController::getSchema` |

### 3.2 Architecture de sécurité

La sécurité d'EduManager est conçue en **défense en profondeur** sur sept couches successives.

#### 3.2.1 Couche 1 — Routing et préfixes

Les routes sont regroupées par espace métier (`/admin`, `/teacher`, `/student`, `/chat`) et placées sous des `Route::middleware([...])` imbriqués, rendant impossible l'accès à une URL protégée sans passer par le middleware.

#### 3.2.2 Couche 2 — Authentification (`auth`)

Le middleware `auth` (Laravel) refuse toute requête sans session authentifiée et redirige vers `GET /login`. La politique de session applique `session.regenerate()` au succès et `session.invalidate()` à la déconnexion.

#### 3.2.3 Couche 3 — Contrôle de rôle (Custom Middlewares)

Trois middlewares custom, déclarés dans `app/Http/Kernel.php` (`$routeMiddleware`), appliquent le principe du moindre privilège :

- **`RoleMiddleware`** (alias `role`) : accepte un paramètre `role:admin`, vérifie `Auth::user()->role === $role`. En cas d'échec, **redirige vers le tableau de bord de rôle réel** de l'utilisateur (plutôt que de renvoyer un 403 brut), améliorant l'ergonomie.
- **`StudentMiddleware`** (alias `student`) : exige `Auth::user()->role === 'eleve'`.
- **`TeacherMiddleware`** (alias `teacher`) : exige `Auth::user()->role` ∈ `['professeur', 'teacher']` (tolère les deux appellations pour rétrocompatibilité).

#### 3.2.4 Couche 4 — Spatie Laravel-Permission (ACL granulaire)

Le projet intègre **Spatie Laravel-Permission 6.24** (`composer.json`) avec publication des tables standards (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` — migration `2026_02_01_221553_create_permission_tables`). Le modèle `User` utilise le trait `HasRoles`. Le seeder `RolePermissionSeeder` crée les trois rôles canoniques (`admin`, `teacher`, `student`) en guard `web`. Ce dispositif permet une migration future vers un contrôle de permissions plus granulaire (par ex. `grades.create`, `attendance.update`) sans refonte du système de rôles, tout en conservant le mécanisme actuel basé sur la colonne `users.role` (enum).

#### 3.2.5 Couche 5 — Vérifications métier dans les contrôleurs

Au-delà du routage, chaque action sensible est re-vérifiée dans le contrôleur. Exemples :

- `TeacherGradesController::store` : `$teacher->assignedClasses()->where('classes.id', $request->class_id)->exists()` (RG-10).
- `TeacherAttendanceController::studentHistory` : même vérification croisée (RG-11).
- `StudentController::edit / update / destroy` : garde-fou `if ($student->role !== 'eleve') abort(404);` — empêche le détournement d'URL pour éditer un enseignant via l'admin des élèves.

#### 3.2.6 Couche 6 — Policies et `authorize()`

Le `TeacherAssignmentController` illustre l'usage explicite des policies : `$this->authorize('viewAny', TeacherAssignment::class)`, `$this->authorize('create', TeacherAssignment::class)`, `$this->authorize('delete', $assignment)`. Le trait `AuthorizesRequests` est importé dans les contrôleurs concernés.

#### 3.2.7 Couche 7 — FormRequest et rate-limiting

`LoginRequest` centralise la validation du login et applique :

- Validation des règles (`identifier` et `password` requis).
- Double identifiant (email valide OU chaîne libre assimilée à `identifier`).
- **Rate-limiting** : clé composée de `lower(identifier) . '|' . ip()`, seuil de **5 tentatives** avant `Lockout`.
- Vérification post-authentification du statut (`rejected` et `pending` déconnectent immédiatement).

#### 3.2.8 Autres mécanismes transverses

- **CSRF** : la middleware `VerifyCsrfToken` est active sur toutes les routes du groupe `web` (`Kernel.php`), protégeant tous les POST/PUT/PATCH/DELETE.
- **Hachage des mots de passe** : cast `'password' => 'hashed'` dans `User::$casts` garantit un hashage bcrypt automatique à chaque setter.
- **Cookies / Sessions** : chiffrement via `EncryptCookies`, `StartSession`, `AddQueuedCookiesToResponse` (stack middleware `web`).
- **TrustProxies** : configuré pour les déploiements derrière un reverse-proxy.
- **Logs d'audit** : les actions critiques (login, envoi IA, approbation / rejet) sont journalisées via `Log::info` / `Log::error` (fichier `storage/logs/laravel.log`).

---

## 4. Architecture et Contraintes Techniques

### 4.1 Pile technologique

| Couche | Technologie | Version | Justification |
|---|---|---|---|
| OS / Serveur web | XAMPP (Apache 2.4) | — | Stack de développement Windows, simplicité d'installation |
| Langage back-end | PHP | 8.2 | Typage renforcé, enums, readonly, performance (JIT) |
| Framework applicatif | Laravel | 12.0 | ORM Eloquent, Artisan, Blade, Breeze, écosystème mature |
| Scaffolding Auth | Laravel Breeze | 2.3 | Implémentation minimaliste et lisible de l'auth en Blade |
| ACL | spatie/laravel-permission | 6.24 | Gestion rôles + permissions, guards multiples |
| SGBDR | MySQL / MariaDB (XAMPP) | 8.x / 10.x | Intégration native, compatibilité Eloquent, partagée avec le service IA |
| Front-end | Blade + Tailwind CSS | — | SSR classique, classes utilitaires, rapidité de développement |
| Build front | Vite | — | `vite.config.js`, compilation Tailwind/JS |
| Service IA | Python + Flask | 3.0 | Bibliothèques NLP/LLM (`langchain`, `langchain-google-genai`), intégration Gemini |
| Base IA | SQLAlchemy + PyMySQL | 2.0 / 1.1 | Accès direct en lecture à la base MySQL partagée |
| LLM | Google Gemini 1.5 Flash | — | Latence faible, coût maîtrisé, accès via `langchain-google-genai` |
| Tests | PHPUnit | 11.5 | Tests unitaires et d'intégration PHP |
| Qualité | Laravel Pint, Collision | — | Formatage automatique PSR-12, affichage d'erreurs |

### 4.2 Architecture MVC implémentée

L'application suit strictement le **pattern MVC 3-tiers** fourni nativement par Laravel.

```
┌──────────────────────────────────────────────────────┐
│  COUCHE PRÉSENTATION (CLIENT)                        │
│  Navigateur — HTML/CSS/JS — Blade SSR + Tailwind     │
└──────────────────────────────────────────────────────┘
                         │  HTTP(S)
                         ▼
┌──────────────────────────────────────────────────────┐
│  COUCHE APPLICATION (LARAVEL 12)                     │
│  ┌──────────────────────────────────────────────┐    │
│  │ Routes (routes/web.php, routes/auth.php)     │    │
│  │ ──────────────────────────────────────────── │    │
│  │ Middlewares : auth, role, StudentMiddleware, │    │
│  │   TeacherMiddleware, VerifyCsrfToken, Trust  │    │
│  │   Proxies, EncryptCookies, StartSession …    │    │
│  │ ──────────────────────────────────────────── │    │
│  │ Controllers (26 fichiers)                    │    │
│  │   • Admin\* (11 contrôleurs CRUD + tableaux) │    │
│  │   • Teacher\* (6 contrôleurs)                │    │
│  │   • Student\* (6 contrôleurs)                │    │
│  │   • Auth\* (Breeze)                          │    │
│  │   • ChatController (proxy IA)                │    │
│  │ ──────────────────────────────────────────── │    │
│  │ FormRequests (LoginRequest, ProfileUpdate…)  │    │
│  │ Policies (TeacherAssignment…)                │    │
│  │ Views Blade (resources/views/*)              │    │
│  └──────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────┘
                         │  Eloquent ORM
                         ▼
┌──────────────────────────────────────────────────────┐
│  COUCHE PERSISTANCE (MySQL)                          │
│  Tables : users, academic_years, levels, classes,    │
│   subjects, grades, attendances, schedules,          │
│   timetables, teacher_assignments, class_teacher,    │
│   class_group_student, class_groups, events,         │
│   assignments, + tables système Spatie Permission    │
└──────────────────────────────────────────────────────┘
                         │
                 ╔═══════╩═══════╗
                 ║ MICRO-SERVICE  ║  (hors conteneur Laravel)
                 ║   IA PYTHON    ║
                 ╚════════════════╝
```

**Responsabilités par couche :**

- **Models** (`app/Models/` — 13 classes Eloquent : `User`, `AcademicYear`, `Assignment`, `Attendance`, `ClassGroup`, `Event`, `Grade`, `Level`, `Schedule`, `SchoolClass`, `Subject`, `TeacherAssignment`, `Timetable`). Ils encapsulent les relations (`belongsTo`, `hasMany`, `belongsToMany`), les casts, les `$fillable` et les méthodes utilitaires (`User::isAdmin()`, `isTeacher()`, `isStudent()`, `isPending()`).
- **Views** (`resources/views/**/*.blade.php`) — moteur Blade, composants `x-app-layout`, directives `@auth`, organisés par rôle (`admin/`, `teacher/`, `student/`, `auth/`).
- **Controllers** — orchestrent les appels ORM, appliquent la validation (via `Request::validate` ou FormRequest), encapsulent la logique métier (calculs de moyennes, d'appréciations, de rangs) et renvoient une `View` ou un `RedirectResponse`.

### 4.3 Intégration du micro-service IA (Python Flask)

#### 4.3.1 Vue d'ensemble

Le chatbot repose sur une architecture **hétérogène Laravel ↔ Python** découplée par un appel HTTP local. Le service Python (dossier `ai_service/`, fichier principal `app_ai_final.py`) écoute sur `http://localhost:5000` et expose trois endpoints :

| Endpoint Flask | Méthode | Rôle |
|---|---|---|
| `/health` | GET | Vérification de l'état du service (DB, LLM, mode) |
| `/chat` | POST | Traitement d'une question en langue naturelle |
| `/schema` | GET | Retourne le schéma MySQL (tables + colonnes) |

#### 4.3.2 Pipeline côté Python

Le service implémente un agent **« Text-to-SQL »** fondé sur Google Gemini 1.5 Flash via LangChain :

1. Réception du message utilisateur en JSON (`POST /chat` `{"message": "..."}`).
2. Détection des salutations (réponse figée, économie de jetons LLM).
3. Extraction du schéma MySQL (via `SELECT ... FROM information_schema.tables / columns`).
4. Appel de `generate_sql_query(question, schema)` : le LLM Gemini génère **une unique requête SQL** conformément à un prompt ingénieré (règles sur les noms de tables, aliases, fonctions d'agrégation).
5. Exécution sécurisée via SQLAlchemy (`engine.connect().execute(text(sql))`).
6. Reformulation du résultat en français naturel via `generate_conversational_response`.
7. Réponse JSON : `{"response": "...", "status": "success", "sql_query": "...", "sql_result": [...]}`.

#### 4.3.3 Intégration côté Laravel (le `ChatController` proxy)

Le `ChatController` (`app/Http/Controllers/ChatController.php`) agit comme un **proxy HTTP simple** entre le navigateur et le service Flask. Son rôle :

```php
$response = Http::timeout($this->timeout)
    ->post($this->aiServiceUrl . '/chat', [
        'message' => $message
    ]);
```

**Bénéfices du pattern proxy :**

- **Découplage technologique** : Python reste le meilleur langage pour l'IA (bibliothèques LangChain, Gemini), PHP reste le meilleur pour le web transactionnel.
- **Isolation des pannes** : une exception Python ne fait pas tomber Laravel ; en cas d'erreur, le contrôleur renvoie un code HTTP cohérent (`503 connection_error`, `422 validation_error`, `500 internal_error`).
- **Journalisation unifiée** : tout message reçu / émis est tracé dans `storage/logs/laravel.log` avec `user_id` et `status`.
- **Sécurité centralisée** : la session Laravel reste le seul point de vérité de l'authentification (`auth()->id()` est passé dans les logs), le service Python **n'a pas à gérer l'identité** — il n'est pas exposé publiquement.
- **Timeout contrôlé** : 30 secondes pour `/chat`, 5 secondes pour `/health`, 10 secondes pour `/schema`. Au-delà, le contrôleur lève un `RequestException` propre.

#### 4.3.4 Variables d'environnement

```
# Laravel (.env)
AI_SERVICE_URL=http://localhost:5000

# ai_service (.env)
GOOGLE_API_KEY=<clé Gemini>
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=
DB_DATABASE=school_management_system
```

### 4.4 Contraintes techniques

| Contrainte | Description |
|---|---|
| **Compatibilité PHP** | PHP 8.2+ requis (contrainte `composer.json` : `"php": "^8.2"`) |
| **Environnement de développement** | XAMPP Windows (Apache + MySQL), Python 3.10+ pour le service IA |
| **Nommage des routes** | Convention Laravel (`admin.students.index`, `teacher.grades.store`) — facilite les redirections `route('...')` |
| **Cohabitation linguistique des rôles** | Le code tolère historiquement `'student'`/`'eleve'` et `'teacher'`/`'professeur'` pour préserver la rétro-compatibilité (cf. `whereIn('role', ['student','eleve'])`). Une migration uniformisante est planifiée. |
| **Données partagées** | Le service IA lit **directement** la base MySQL de Laravel (pas d'API intermédiaire) — ce choix assume un couplage fort en lecture, justifié par la simplicité. |
| **Déploiement** | Laravel déployable sur tout hébergement PHP 8.2 + MySQL ; le service IA nécessite un environnement Python isolé (`venv`) et la clé Gemini. |

---

## 5. Gestion de projet (GitHub)

### 5.1 Méthodologie Agile adoptée

Le projet a été conduit selon une adaptation **Scrum-simplifié** (« Scrum-ban ») sur des sprints courts d'une à deux semaines, adaptée à une équipe réduite et au calendrier du mémoire de fin d'études. Le choix de l'Agile se justifie par :

- **Cycle court de feedback** : chaque fonctionnalité (ex. saisie des notes, génération du bulletin sénégalais) a été livrée et testée en continu, sans attendre la fin du projet.
- **Adaptabilité du périmètre** : l'ajout tardif du module IA (voir commits de mars 2026) et du bulletin annuel (février 2026) a été absorbé sans refonte globale.
- **Priorisation par valeur** : les fonctionnalités critiques (authentification, CRUD, notes) ont été livrées avant les fonctionnalités périphériques (chatbot, statistiques de classe).

### 5.2 Versioning avec Git et GitHub

La gestion de versions repose exclusivement sur **Git**, avec un dépôt centralisé sur **GitHub**. L'historique du dépôt (`.git/` présent à la racine du projet) atteste d'un usage régulier depuis le démarrage.

**Stratégie de branching :**

| Branche | Rôle |
|---|---|
| `main` | Branche de production, toujours stable et déployable |
| `develop` | Branche d'intégration continue |
| `feature/<ticket>` | Une branche par fonctionnalité (ex. `feature/bulletin-senegalais`, `feature/chatbot-ia`) |
| `hotfix/<issue>` | Correctifs urgents mergés directement dans `main` puis rétro-portés |

**Conventions de commits** : prefixes explicites (`feat:`, `fix:`, `refactor:`, `docs:`, `chore:`) inspirés de la norme **Conventional Commits**, facilitant la génération automatique du changelog.

### 5.3 Suivi des tâches — GitHub Projects (Kanban)

Un tableau **GitHub Projects** de type *Kanban* a été utilisé pour orchestrer la progression du projet. Il est organisé en colonnes classiques :

| Colonne | Critère d'entrée |
|---|---|
| **Backlog** | Ticket créé, non priorisé, éligible à un sprint futur |
| **To Do** | Ticket priorisé pour le sprint courant |
| **In Progress** | Développement en cours sur une branche `feature/*` |
| **In Review** | Pull Request ouverte, en attente de relecture |
| **Done** | PR mergée sur `develop`, tests passants, démo validée |

Chaque carte est liée à une **GitHub Issue** dont elle hérite des labels (`backend`, `frontend`, `ia`, `security`, `bug`, `documentation`, `priority/high`, etc.) et des jalons (milestones).

### 5.4 Gestion des bugs et des évolutions — GitHub Issues

Les **GitHub Issues** sont le point d'entrée unique pour :

- les **bugs** (label `bug`) avec le gabarit : *environnement, étapes de reproduction, comportement attendu vs observé, logs* ;
- les **nouvelles fonctionnalités** (label `enhancement`) avec le gabarit : *contexte, user story, critères d'acceptation* ;
- les **tâches de refactoring / dette technique** (label `refactor`) ;
- les **questions / discussions** (label `question`).

Chaque Issue est référencée dans les commits et les PR par sa notation canonique `#NN`, générant automatiquement la traçabilité bidirectionnelle commit ↔ ticket ↔ release.

### 5.5 Revue de code et intégration

Toute branche `feature/*` est mergée via **Pull Request** sur `develop`, avec :

- au moins une relecture (binôme académique),
- validation manuelle des tests PHPUnit (`composer test`),
- vérification du formatage avec **Laravel Pint**,
- mise à jour éventuelle de la documentation.

### 5.6 Gestion des risques

Le projet présente quatre familles de risques : **techniques**, **humains**, **fonctionnels** et **opérationnels**. Le tableau ci-dessous synthétise les principaux risques identifiés et les mesures de mitigation effectivement mises en place dans le code et le processus.

| ID | Risque identifié | Catégorie | Probabilité | Impact | Solution mise en place |
|---|---|---|---|---|---|
| R-01 | **Indisponibilité du micro-service IA Python** (Flask non démarré, Gemini rate-limite, réseau local coupé) | Technique | Moyenne | Faible à modéré | `ChatController` capture les exceptions (`RequestException`), retourne un code 503 avec message utilisateur clair et journalise l'incident ; un endpoint `/chat/health` permet un monitoring explicite |
| R-02 | **Fuite de la clé API Google Gemini** | Sécurité | Faible | Critique | La clé est placée dans `ai_service/.env`, **non versionnée** (`.gitignore`), et validée au démarrage (`sys.exit` si absente ou égale à la valeur par défaut) |
| R-03 | **Injection SQL via le chatbot** (le LLM génère une requête malveillante) | Sécurité | Faible | Critique | Le prompt contraint Gemini à générer uniquement des `SELECT`, les requêtes sont exécutées avec SQLAlchemy `text()` qui échappe les paramètres ; l'utilisateur MySQL de l'application dispose de privilèges en lecture sur la base uniquement pour le service IA ; monitoring par `Log::info` côté Laravel |
| R-04 | **Retard dans le développement** (fonctionnalité prévue mais non livrée) | Gestion | Moyenne | Moyen | Méthode agile, priorisation stricte en début de sprint, *hors-scope* assumé et documenté (cf. §1.4), fonctionnalités « nice-to-have » reportées (PDF bulletins, notifications email) |
| R-05 | **Cohabitation linguistique des rôles** (`'eleve'`/`'student'`, `'professeur'`/`'teacher'`) source de bug subtil | Dette technique | Haute | Moyen | Le code tolère explicitement les deux valeurs via `whereIn('role', ['student','eleve'])` ; documentation de la dette technique dans la section 4.4 ; plan de migration inscrit au backlog |
| R-06 | **Perte de données en base** (crash XAMPP, suppression accidentelle) | Opérationnel | Faible | Critique | Migrations versionnées (`database/migrations/`), seeders reproductibles (`DatabaseSeeder`), sauvegarde manuelle SQL hebdomadaire, `.env` non versionné |
| R-07 | **Accès non autorisé à une zone d'administration** | Sécurité | Moyenne | Élevé | Défense en profondeur 7 couches (cf. §3.2), tests d'intégration sur les redirections de middleware, logs d'accès |
| R-08 | **Oubli d'un cas de validation côté formulaire** | Technique | Moyenne | Moyen | Emploi systématique de `Request::validate()` ou de `FormRequest` dédiés, coefficients et bornes explicites dans tous les contrôleurs sensibles |
| R-09 | **Défaillance de l'auto-inscription** (explosion du nombre de comptes `pending` non traités) | Fonctionnel | Faible | Moyen | Tableau de bord admin avec compteur `pendingCount`, écran de modération dédié (`/admin/pending`), tri `orderBy('created_at', 'desc')` |
| R-10 | **Non-conformité du bulletin sénégalais** | Fonctionnel | Faible | Critique (rejet du jury) | Formule centralisée dans une seule méthode `calculateBulletinData`, validée par référentiel officiel du ministère, tests de non-régression PHPUnit sur des cas-types |
| R-11 | **Incompatibilité entre versions de PHP / MySQL** sur la machine du jury | Déploiement | Moyenne | Moyen | Contraintes de version dans `composer.json` (`php: ^8.2`), documentation d'installation dans `README.md`, script `composer setup` automatisé |
| R-12 | **Faille CSRF sur les formulaires** | Sécurité | Très faible | Élevé | Middleware `VerifyCsrfToken` actif sur tout le groupe `web`, directive `@csrf` dans tous les formulaires Blade |
| R-13 | **Brute force sur la page de login** | Sécurité | Moyenne | Élevé | Rate-limiting 5 tentatives / IP+identifiant, blocage temporaire avec message `trans('auth.throttle')` |
| R-14 | **Conflit d'affectation** (deux enseignants sur la même matière dans la même classe, même année) | Fonctionnel | Faible | Moyen | Contrainte UNIQUE SQL (RG-12) + vérification programmatique dans `TeacherAssignmentController::storeAssignment` |

---

## Conclusion

EduManager satisfait l'ensemble des exigences énoncées dans le présent cahier des charges. L'architecture MVC orchestrée par Laravel 12, renforcée par Spatie Laravel-Permission et par un service IA Python Flask découplé, offre un compromis pertinent entre :

- **Productivité de développement** (écosystème Laravel complet, Breeze, Eloquent, Blade) ;
- **Sécurité réelle** (7 couches de défense en profondeur, validations systématiques, rate-limiting, hashing bcrypt) ;
- **Extensibilité** (Spatie ACL, architecture micro-service pour l'IA, routes organisées par rôle) ;
- **Conformité métier** au système éducatif sénégalais (semestres, séries, formules de moyenne pondérée, décision du conseil de classe).

La gestion de projet sur **GitHub** (versioning Git, GitHub Projects Kanban, Issues, Pull Requests) a assuré la traçabilité complète du code et des décisions, et le tableau de gestion des risques illustre une démarche d'ingénierie mature, conforme aux attendus d'un projet de fin d'études de niveau Bac+3 / Bac+5.
