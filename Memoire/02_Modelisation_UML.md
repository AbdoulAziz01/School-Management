# 2. Modélisation UML — EduManager

Chaque diagramme est fourni sous deux formats :

1. Une **description textuelle** ci-dessous pour la rédaction du mémoire.
2. Le **code source PlantUML** dans le sous-dossier `Memoire/diagrammes/` (fichiers `.puml`).

Pour générer les images, copiez le code dans [plantuml.com](https://plantuml.com) ou utilisez l'extension PlantUML dans VS Code / IntelliJ.

---

## 2.1 Diagramme de Cas d'Utilisation

**Fichier source** : `Memoire/diagrammes/01_use_case.puml`

### Description

Le diagramme présente les interactions globales des trois acteurs (Administrateur, Enseignant, Élève) avec le système EduManager, ainsi qu'un acteur secondaire **Service IA** (système externe).

- **Cas d'utilisation communs** : *S'inscrire*, *Se connecter*, *Se déconnecter*, *Gérer son profil*, *Discuter avec le chatbot*.
- **Package Administration** : *Approuver/rejeter une inscription*, *Gérer les élèves/enseignants/classes/matières/années*, *Affecter un élève à une classe*, *Affecter un enseignant (classe + matière + année)*.
- **Package Pédagogie** : *Saisir des notes*, *Modifier/supprimer une note*, *Faire l'appel*, *Consulter ses classes*, *Consulter l'emploi du temps*, *Consulter l'historique d'un élève*.
- **Package Scolarité élève** : *Consulter ses notes*, *Consulter son bulletin semestriel*, *Consulter ses présences*.

**Relations UML** :
- `<<include>>` : *Gérer son profil* et *Discuter avec le chatbot* incluent *Se connecter*.
- `<<extend>>` : *Modifier/supprimer une note* étend *Saisir des notes* ; *Consulter son bulletin* étend *Consulter ses notes*.
- `<<uses>>` : *Discuter avec le chatbot* utilise le service externe IA.

---

## 2.2 Diagramme de Classes

**Fichier source** : `Memoire/diagrammes/02_class_diagram.puml`

### Description

Le diagramme de classes représente 13 classes métier principales et leurs relations Eloquent. Les classes sont :

| Classe | Rôle |
|---|---|
| `User` | Utilisateur polyvalent (admin/teacher/eleve) avec statut |
| `AcademicYear` | Année scolaire (une seule `is_current`) |
| `Level` | Niveau scolaire (6e, Terminale) rattaché à un cycle (collège/lycée) |
| `SchoolClass` | Classe physique (ex : Terminale S1) |
| `Subject` | Matière enseignée (avec code unique) |
| `TeacherAssignment` | Affectation ternaire (enseignant × classe × matière × année) |
| `Grade` | Note d'un élève dans une matière |
| `Attendance` | Présence quotidienne d'un élève |
| `Schedule` | Créneau de l'emploi du temps |
| `Timetable` | Autre table d'emploi du temps (avec énumération de jours textuels) |
| `Assignment` | Devoir à rendre |
| `Event` | Événement (examen, vacances, réunion) |
| `ClassGroup` | Sous-groupe d'élèves au sein d'une classe |

### Relations clés

- `User — SchoolClass` : `belongsTo` via `class_id` (pour les élèves).
- `User ↔ SchoolClass` : `belongsToMany` via `class_teacher` (pour les enseignants).
- `User ↔ Subject` : `belongsToMany` via `teacher_subjects`.
- `SchoolClass ↔ Subject` : `belongsToMany` via `class_subject`.
- `Level ↔ Subject` : `belongsToMany` via `level_subject` (avec coefficient spécifique au niveau).
- `TeacherAssignment` : `belongsTo` vers User, SchoolClass, Subject, AcademicYear.
- `Grade` : `belongsTo` vers User (élève) et Subject.
- `Attendance` : `belongsTo` vers User.
- `Schedule` : `belongsTo` vers SchoolClass, Subject, User (enseignant).
- `ClassGroup ↔ User` : `belongsToMany` via `class_group_student`.

---

## 2.3 Diagrammes de Séquence

### 2.3.1 Authentification et redirection par rôle

**Fichier source** : `Memoire/diagrammes/03_sequence_auth.puml`

#### Description

Ce diagramme détaille le flux complet d'authentification :

1. L'utilisateur saisit son identifiant (email OU matricule) et son mot de passe.
2. `AuthenticatedSessionController::store` reçoit la requête via un `LoginRequest`.
3. Le `RateLimiter` vérifie qu'il n'y a pas plus de 5 tentatives en cours.
4. La méthode `authenticate()` détecte si l'identifiant est un email (`FILTER_VALIDATE_EMAIL`) et construit les *credentials*.
5. `Auth::attempt()` interroge la base (SELECT) et vérifie le hash bcrypt du mot de passe.
6. **Vérification du statut** :
   - Si `status = 'rejected'` → `logout()` + `ValidationException`.
   - Si `status = 'pending'` → `logout()` + `ValidationException`.
   - Si `status = 'approved'` → `session->regenerate()` puis `redirectToDashboard(user)`.
7. **Redirection par rôle** (match expression) :
   - `admin` → `/admin/dashboard`
   - `professeur` ou `teacher` → `/teacher/dashboard`
   - `eleve` → `/student/dashboard`
   - sinon → `/`

---

### 2.3.2 Saisie d'une note et calcul de moyenne

**Fichier source** : `Memoire/diagrammes/04_sequence_grades.puml`

#### Description

Ce diagramme est structuré en **deux phases** :

**Phase 1 — Saisie des notes par l'enseignant** :

1. L'enseignant soumet le formulaire (classe, matière, type, coefficient, date, notes[]).
2. `TeacherMiddleware` vérifie que `role ∈ {teacher, professeur}`.
3. `TeacherGradesController::store` valide les données (grade ∈ [0,20], coefficient ∈ [0.5,5]).
4. Vérification métier : l'enseignant est-il affecté à cette classe via `class_teacher` ?
5. `DB::beginTransaction()` → boucle `Grade::create()` pour chaque élève → `DB::commit()`.
6. Redirection avec message flash.

**Phase 2 — Consultation du bulletin par l'élève** :

1. L'élève clique sur "Bulletin".
2. `StudentBulletinController::index` (ou `StudentGradesController::bulletin`) récupère `$user->grades()->where('semester', $sem)->with('subject')->get()`.
3. `groupBy('subject.name')` pour grouper les notes par matière.
4. Pour chaque matière : `avg = grades.avg('grade')`, récupération du coefficient, génération de l'appréciation via `getAppreciation(avg)`.
5. **Calcul de la moyenne générale pondérée** :
   - `totalCoef = Σ coefficient`
   - `weightedSum = Σ (avg × coefficient)`
   - `generalAverage = weightedSum / totalCoef`
6. Rendu de la vue Blade `student.bulletin`.

---

### 2.3.3 Attribution d'un enseignant à une matière et une classe

**Fichier source** : `Memoire/diagrammes/05_sequence_assignment.puml`

#### Description

1. L'administrateur sélectionne un enseignant, une année, une classe et une matière dans un formulaire.
2. Middleware `role:admin` valide le rôle.
3. `TeacherAssignmentController::storeAssignment` reçoit la requête.
4. `authorize('create', TeacherAssignment::class)` vérifie la policy.
5. Validation des FK : `academic_year_id`, `class_id`, `subject_id` doivent exister.
6. Vérification anti-doublon : requête `WHERE teacher_id=? AND class_id=? AND subject_id=? AND academic_year_id=?`.
7. Si non-doublon : `DB::beginTransaction()` → `new TeacherAssignment(validated)` → `save()` → `DB::commit()`.
8. La **contrainte UNIQUE** garantit RG-10 même en cas de concurrence.
9. Redirection avec flash `'Affectation enregistrée'`.

---

## 2.4 Diagramme d'Activité — Prise de présence

**Fichier source** : `Memoire/diagrammes/06_activity_attendance.puml`

### Description

Le flux d'activité de l'appel comporte :

1. **Vérification préalable** : l'enseignant a-t-il des classes affectées ?
2. **Sélection** : choix d'une classe + date (par défaut aujourd'hui).
3. **Récupération** : le système récupère les élèves (role=eleve, status=approved) et les présences existantes (keyBy `user_id`).
4. **Pré-remplissage conditionnel** :
   - Si des présences existent → pré-remplissage.
   - Sinon → tous marqués `present` par défaut.
5. **Saisie itérative** : pour chaque élève, choix d'un statut (`present`/`absent`/`late`/`excused`) + motif optionnel.
6. **Soumission** vers `POST /teacher/attendance`.
7. **Traitement serveur** (dans une partition dédiée) :
   - Validation des données.
   - Contrôle d'accès (`class_teacher`).
   - Boucle `Attendance::updateOrCreate([user_id, date], [status, reason])` dans une transaction.
8. **Confirmation** : redirection + message flash.

---

## 2.5 Diagramme d'États-Transitions — Compte utilisateur

**Fichier source** : `Memoire/diagrammes/07_state_user.puml`

### Description

Le cycle de vie d'un compte utilisateur comprend les états suivants :

| État | Description |
|---|---|
| `Brouillon` | L'utilisateur est en train de remplir le formulaire `/register` |
| `EnAttente` | Compte créé (`status = pending`) — connexion impossible |
| `Rejete` | L'admin a refusé le compte (`status = rejected`) — connexion impossible |
| `Approuve` | L'admin a approuvé et affecté une classe (`status = approved`) |
| `Supprime` | État final après `destroy()` par l'admin |

**Sous-états de `Approuve`** :
- `Actif` → `SessionOuverte` (login) → `Actif` (logout/expiration)
- `Actif` → `MotDePasseReinitialise` (admin reset) → `Actif`

**Transitions** :
- `Brouillon → EnAttente` : soumission du formulaire.
- `EnAttente → Approuve` : clic "Approuver" + affectation `class_id`.
- `EnAttente → Rejete` : clic "Rejeter".
- `Approuve → Supprime` : suppression en cascade.

---

## 2.6 Diagramme de Déploiement

**Fichier source** : `Memoire/diagrammes/08_deployment.puml`

### Description

L'architecture matérielle se compose de **quatre nœuds** :

1. **Poste client** : navigateur web (Chrome/Firefox/Edge) communiquant via HTTPS.
2. **Serveur Web** (XAMPP sous Windows ou Nginx sous Linux) :
   - Apache/Nginx sur port 80/443
   - PHP-FPM 8.2
   - Application Laravel 12 (Routing, Controllers, Models, Views, Middleware)
   - Dossier `storage/` (photos profil, logs, cache, sessions)
3. **Service IA** : micro-service Python + Flask sur port 5000 (`app_ai_final.py`).
4. **Serveur de données** : MySQL/MariaDB sur port 3306 (base `edumanager`, 16 tables métier).

**Flux de communication** :
- `Navigateur ↔ Serveur Web` : HTTPS
- `Laravel → MySQL` : PDO / Eloquent (TCP 3306)
- `Laravel → Service IA` : HTTP POST `/chat` (via variable `AI_SERVICE_URL`)
- `Service IA → MySQL` : lecture du schéma (optionnel, pour le RAG)
