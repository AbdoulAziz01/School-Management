# 1. Cahier des Charges et Besoins — EduManager

## 1.1 Présentation générale

**EduManager** est une plateforme web de gestion scolaire destinée aux établissements du secondaire (collège et lycée) adaptée au **système éducatif sénégalais** (semestres 1 et 2, séries L/S/ES, notation sur 20). Elle centralise la gestion des inscriptions, des classes, des emplois du temps, des notes, des présences et des bulletins scolaires. Elle intègre également un assistant conversationnel (chatbot IA) accessible à tous les utilisateurs authentifiés.

Trois acteurs interagissent avec le système :

- **Administrateur** (`admin`) : pilote l'ensemble du système.
- **Enseignant** (`teacher` / `professeur`) : gère la pédagogie de ses classes.
- **Élève** (`eleve` / `student`) : consulte son parcours scolaire.

---

## 1.2 Besoins fonctionnels par acteur

### 1.2.1 Administrateur

Extraits de `routes/web.php` (groupe `admin` + middleware `role:admin`) :

| Fonctionnalité | Route | Description |
|---|---|---|
| Tableau de bord | `GET /admin/dashboard` | KPIs : inscriptions en attente, nombre de classes, élèves non affectés, année scolaire en cours |
| Gestion des inscriptions en attente | `GET /admin/pending`, `PATCH /registrations/{user}/approve`, `PATCH /registrations/{user}/reject` | Valider/rejeter les comptes élèves et enseignants auto-inscrits |
| CRUD Élèves | `Route::resource('students')` | Créer, lister, afficher, modifier, supprimer |
| Affectation d'élèves | `POST /admin/students/assign`, `POST /admin/students/assign/bulk`, `POST /admin/students/{student}/assign` | Affecter un ou plusieurs élèves à une classe (passe leur statut à `approved`) |
| CRUD Enseignants | `Route::resource('teachers')` | Gestion complète du corps enseignant |
| Affectation de classes à un enseignant | `GET /admin/teachers/{teacher}/classes`, `PUT /admin/teachers/{teacher}/classes` | Lier un enseignant à N classes (table pivot `class_teacher`) |
| Affectation matière × classe × année | `TeacherAssignmentController::storeAssignment` | Affectation triple : enseignant, classe, matière pour une année académique |
| CRUD Classes | `Route::resource('classes')` | Création selon Niveau + Année + Capacité |
| CRUD Années académiques | `Route::resource('academic-years')`, `PATCH /set-current` | Définir l'année scolaire en cours (une seule `is_current = true`) |
| CRUD Matières | `Route::resource('subjects')` | Gestion du référentiel des matières (code unique, coefficient, rattachement aux niveaux) |
| Profil administrateur | `GET/PUT /admin/profile` | Édition de son propre profil |

### 1.2.2 Enseignant

Extraits du groupe `teacher` + middleware `TeacherMiddleware` :

| Fonctionnalité | Route | Description |
|---|---|---|
| Tableau de bord | `GET /teacher/dashboard` | Classes affectées, nombre d'élèves, matières enseignées, notes récentes, moyennes par classe |
| Consultation des classes | `GET /teacher/classes`, `GET /teacher/classes/{id}` | Liste des classes affectées + détail (élèves, matières, statistiques) |
| Consultation des notes | `GET /teacher/grades` | Filtrage par classe + matière |
| Saisie des notes | `GET /teacher/grades/create`, `POST /teacher/grades` | Saisie multi-élèves pour un contrôle donné (type, date, coefficient) |
| Modification/Suppression d'une note | `PUT/DELETE /teacher/grades/{id}` | Uniquement pour les notes sur ses classes/matières |
| Gestion des présences | `GET/POST /teacher/attendance` | Appel quotidien multi-élèves (`updateOrCreate` par date) |
| Historique d'un élève | `GET /teacher/attendance/student/{studentId}` | Statistiques présent/absent/retard/excusé |
| Emploi du temps | `GET /teacher/schedule` | Consultation des créneaux |
| Profil | `GET/PUT /teacher/profile`, photo, mot de passe | Gestion personnelle |

### 1.2.3 Élève

Extraits du groupe `student` + middleware `StudentMiddleware` :

| Fonctionnalité | Route | Description |
|---|---|---|
| Tableau de bord | `GET /student/dashboard` | Moyenne générale, taux de présence, 5 dernières notes |
| Consultation des notes | `GET /student/grades` | Notes groupées par matière avec moyenne et appréciation |
| Bulletin semestriel | `GET /student/bulletin` | Bulletin système sénégalais (moyenne pondérée, rang, statistiques classe) |
| Bulletin annuel | `GET /student/bulletin/annual` | Synthèse des deux semestres |
| Emploi du temps | `GET /student/schedule` | Planning hebdomadaire |
| Présences | `GET /student/attendance` | Historique personnel |
| Profil | `GET/PUT /student/profile`, photo, mot de passe | Gestion personnelle |

### 1.2.4 Fonctionnalités transverses

- **Auto-inscription** (`GET/POST /register`) pour les rôles `eleve` et `teacher` (statut initial `pending`).
- **Authentification** par email **ou identifiant** + mot de passe (`POST /login`), rate-limiting 5 tentatives.
- **Chatbot IA** (`/chat`) — proxy vers un micro-service Python Flask/FastAPI (`app_ai_final.py`) localisé à `http://localhost:5000`.

---

## 1.3 Règles de gestion (RG)

Ces règles sont extraites directement des validations et du code métier.

| Code | Règle de gestion | Source |
|---|---|---|
| RG-01 | Une note est un décimal compris entre **0 et 20** (inclus) | `TeacherGradesController::store` → `min:0\|max:20` |
| RG-02 | Le coefficient d'une note est compris entre **0.5 et 5** | `TeacherGradesController::store` → `min:0.5\|max:5` |
| RG-03 | Un élève ne peut recevoir qu'**une seule note** pour un triplet *(matière, date, type)* | Migration `grades` → `unique(['user_id','subject_id','date','type'])` |
| RG-04 | L'identifiant utilisateur suit le format `{P\|E}{AAAA}{NNN}` (ex : `E2026001` pour un élève, `P2026001` pour un professeur) | `RegisteredUserController::store` |
| RG-05 | Tout compte auto-inscrit est créé en statut `pending` et inactivable à la connexion | `RegisteredUserController` + `LoginRequest::authenticate` |
| RG-06 | Un compte `rejected` ne peut jamais se connecter | `LoginRequest::authenticate` |
| RG-07 | La redirection post-login dépend du rôle : `admin` → `/admin/dashboard`, `teacher/professeur` → `/teacher/dashboard`, `eleve` → `/student/dashboard` | `AuthenticatedSessionController::redirectToDashboard` |
| RG-08 | Une seule année académique peut être `is_current = true` | Logique contrôleur `AcademicYearController::setCurrent` |
| RG-09 | Un enseignant ne peut saisir/modifier des notes **que** pour ses classes affectées (`class_teacher`) | `TeacherGradesController::store` (vérif `assignedClasses`) |
| RG-10 | Un triplet *(enseignant, classe, matière, année)* est unique dans `teacher_assignments` | Migration → `unique(['teacher_id','class_id','subject_id','academic_year_id'])` |
| RG-11 | Le code d'une matière est unique et stocké en MAJUSCULES | `SubjectController::store` → `strtoupper($validated['code'])` |
| RG-12 | Le coefficient d'une matière est compris entre **0.5 et 10** | `SubjectController::store` |
| RG-13 | Le statut de présence appartient à l'énumération `{present, absent, late, excused}` | Migration `attendances` + constantes `Attendance::STATUS_*` |
| RG-14 | Un élève n'a qu'**une seule** entrée de présence par jour (via `updateOrCreate`) | `TeacherAttendanceController::store` |
| RG-15 | La moyenne par matière est la **moyenne arithmétique non pondérée** des notes ; la moyenne générale est **pondérée** par les coefficients des matières | `StudentGradesController::bulletin` (somme pondérée/total coef) |
| RG-16 | L'appréciation est attribuée automatiquement : `≥16` Excellent, `≥14` Très bon, `≥12` Bon, `≥10` Satisfaisant, `≥8` Insuffisant, `<8` Très insuffisant | `StudentGradesController::getAppreciation` |
| RG-17 | Le trimestre courant est déduit du mois : 9–12 → T1, 1–3 → T2, 4–8 → T3 | `StudentGradesController::getCurrentTrimester` |
| RG-18 | Une classe a une **capacité par défaut de 40** élèves | Migration `classes` → `capacity->default(40)` |
| RG-19 | Un niveau appartient à un cycle : `college` ou `lycee` (+ série optionnelle L/S/ES pour le lycée) | Migration `levels` |
| RG-20 | Le mot de passe suit les règles Laravel par défaut (`Password::defaults()`, min 8 caractères) | `RegisteredUserController::store` |
| RG-21 | Un enseignant doit obligatoirement sélectionner au moins une matière à l'inscription | Validation `subjects.required_if:role,teacher` |
| RG-22 | Un élève doit obligatoirement indiquer sa classe souhaitée à l'inscription | Validation `desired_class.required_if:role,eleve` |
| RG-23 | La suppression en cascade s'applique sur toutes les FK pour maintenir l'intégrité | Toutes les migrations → `onDelete('cascade')` |
| RG-24 | Un créneau d'emploi du temps est unique pour *(jour, heure, classe)* | Migration `timetables` → `unique(['day_of_week','start_time','class_id'])` |
