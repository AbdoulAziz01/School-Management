# 3. Dictionnaire de Données — EduManager

Les types reflètent fidèlement les migrations de `database/migrations/`.

---

## Table `users`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK**, AUTO_INCREMENT | Identifiant technique |
| identifier | VARCHAR | 255 | UNIQUE, NOT NULL | Matricule format `{P\|E}{AAAA}{NNN}` |
| name | VARCHAR | 255 | NOT NULL | Nom complet |
| email | VARCHAR | 255 | UNIQUE, NOT NULL | Adresse de connexion et contact |
| email_verified_at | TIMESTAMP | — | NULL | Date de vérification |
| password | VARCHAR | 255 | NOT NULL | Mot de passe hashé (bcrypt) |
| role | ENUM | — | NOT NULL, default `eleve` | Rôle : `admin`, `teacher`, `eleve` |
| status | ENUM | — | NOT NULL, default `pending` | Statut : `pending`, `approved`, `rejected` |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id`, ON DELETE SET NULL | Classe de l'élève |
| date_of_birth | DATE | — | NULL | Date de naissance |
| phone | VARCHAR | 255 | NULL | Téléphone |
| address | TEXT | — | NULL | Adresse postale |
| desired_class | VARCHAR | 255 | NULL | Classe souhaitée à l'inscription |
| profile_photo_path | VARCHAR | 255 | NULL | Chemin de la photo de profil |
| city, postal_code, country | VARCHAR | 255 | NULL | Compléments d'adresse |
| remember_token | VARCHAR | 100 | NULL | Jeton "se souvenir de moi" |
| created_at / updated_at | TIMESTAMP | — | NULL | Horodatage Laravel |

---

## Table `academic_years`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| name | VARCHAR | 255 | NOT NULL | Libellé (ex : `2025-2026`) |
| start_date | DATE | — | NOT NULL | Début de l'année |
| end_date | DATE | — | NOT NULL | Fin de l'année |
| is_current | BOOLEAN | — | NOT NULL, default false | Année en cours (une seule) |

---

## Table `levels`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| name | VARCHAR | 255 | NOT NULL | Nom (ex : `6ème`, `Terminale`) |
| order | INT | — | NOT NULL | Ordre d'affichage |
| cycle | ENUM | — | NOT NULL | `college` ou `lycee` |
| serie | VARCHAR | 255 | NULL | Série (L, S, ES) pour le lycée |

---

## Table `classes`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| name | VARCHAR | 255 | NOT NULL | Nom (ex : `6ème A`) |
| level_id | BigInt UNSIGNED | — | **FK** → `levels.id`, ON DELETE CASCADE | Niveau rattaché |
| academic_year_id | BigInt UNSIGNED | — | **FK** → `academic_years.id`, ON DELETE CASCADE | Année scolaire |
| capacity | INT | — | default 40 | Effectif maximum |

---

## Table `subjects`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| name | VARCHAR | 255 | NOT NULL | Nom (ex : `Mathématiques`) |
| code | VARCHAR | 20 | UNIQUE, NOT NULL | Code court (ex : `MATH`) |
| coefficient | INT | — | default 1 | Coefficient par défaut |
| description | TEXT | — | NULL | Description |
| is_active | BOOLEAN | — | default true | Matière active ou non |
| department | VARCHAR | 255 | NULL | Département |
| hours_per_week | FLOAT | — | default 2 | Volume horaire hebdo |
| is_core_subject | BOOLEAN | — | default false | Matière principale |
| created_by / updated_by | BigInt UNSIGNED | — | **FK** → `users.id` | Audit |

---

## Table `level_subject` (pivot)

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| level_id | BigInt UNSIGNED | — | **FK** → `levels.id`, CASCADE | Niveau |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id`, CASCADE | Matière |
| coefficient | INT | — | default 1 | Coefficient **spécifique** au niveau |
| is_compulsory | BOOLEAN | — | default true | Matière obligatoire pour ce niveau |
| UNIQUE | — | — | (level_id, subject_id) | Pas de doublon |

---

## Table `class_subject` (pivot)

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id`, CASCADE | Classe |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id`, CASCADE | Matière |
| UNIQUE | — | — | (class_id, subject_id) | Anti-doublon |

---

## Table `class_teacher` (pivot)

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id`, CASCADE | Classe |
| teacher_id | BigInt UNSIGNED | — | **FK** → `users.id`, CASCADE | Enseignant |
| UNIQUE | — | — | (class_id, teacher_id) | Anti-doublon |

---

## Table `teacher_subjects` (pivot)

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| teacher_id | BigInt UNSIGNED | — | **FK** → `users.id`, CASCADE | Enseignant |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id`, CASCADE | Matière |
| UNIQUE | — | — | (teacher_id, subject_id) | Anti-doublon |

---

## Table `teacher_assignments`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| teacher_id | BigInt UNSIGNED | — | **FK** → `users.id`, CASCADE | Enseignant |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id`, CASCADE | Classe |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id`, CASCADE | Matière |
| academic_year_id | BigInt UNSIGNED | — | **FK** → `academic_years.id`, CASCADE | Année |
| UNIQUE | — | — | (teacher_id, class_id, subject_id, academic_year_id) | **Clé métier** |

---

## Table `grades`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| user_id | BigInt UNSIGNED | — | **FK** → `users.id`, CASCADE | Élève |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id`, CASCADE | Matière |
| grade | DECIMAL | (5,2) | NOT NULL | Valeur numérique 0–20 |
| type | VARCHAR | 255 | NOT NULL | Devoir, Examen, Oral, TP… |
| coefficient | INT | — | default 1 | Pondération |
| semester | INT | — | default 1 | Semestre (1 ou 2) |
| academic_year_id | BigInt UNSIGNED | — | **FK** → `academic_years.id`, SET NULL | Année scolaire |
| date | DATE | — | NOT NULL | Date de l'évaluation |
| comments | TEXT | — | NULL | Remarques enseignant |
| appreciation | VARCHAR | 255 | NULL | Appréciation libre |
| UNIQUE | — | — | (user_id, subject_id, date, type) | Anti-doublon |

---

## Table `attendances`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| user_id | BigInt UNSIGNED | — | **FK** → `users.id`, CASCADE | Élève |
| date | DATE | — | NOT NULL | Jour concerné |
| status | ENUM | — | default `present` | `present`, `absent`, `late`, `excused` |
| reason | VARCHAR | 255 | NULL | Motif |
| justified | BOOLEAN | — | default false | Justification validée |
| INDEX | — | — | (user_id, date) | Index composite |

---

## Table `schedules`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id`, CASCADE | Classe |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id`, CASCADE | Matière |
| teacher_id | BigInt UNSIGNED | — | **FK** → `users.id`, CASCADE | Enseignant |
| day_of_week | TINYINT | — | NOT NULL | 1 (lundi) à 5 (vendredi) |
| start_time | TIME | — | NOT NULL | Heure de début |
| end_time | TIME | — | NOT NULL | Heure de fin |
| room | VARCHAR | 255 | NULL | Salle |
| class_group_id | BigInt UNSIGNED | — | **FK** → `class_groups.id`, NULL | Sous-groupe |

---

## Table `timetables`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| day_of_week | ENUM | — | NOT NULL | monday → saturday |
| start_time / end_time | TIME | — | NOT NULL | Plage horaire |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id` | Matière |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id` | Classe |
| teacher_id | BigInt UNSIGNED | — | **FK** → `users.id` | Enseignant |
| room | VARCHAR | 255 | NULL | Salle |
| UNIQUE | — | — | (day_of_week, start_time, class_id) | Anti-collision |

---

## Table `assignments`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| title | VARCHAR | 255 | NOT NULL | Titre du devoir |
| description | TEXT | — | NULL | Énoncé |
| due_date | DATETIME | — | NOT NULL | Échéance |
| subject_id | BigInt UNSIGNED | — | **FK** → `subjects.id` | Matière |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id` | Classe |
| teacher_id | BigInt UNSIGNED | — | **FK** → `users.id` | Auteur |
| file_path | VARCHAR | 255 | NULL | Pièce jointe |
| points | INT | — | default 100 | Points totaux |
| status | ENUM | — | default `draft` | `draft`, `published`, `graded` |

---

## Table `events`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| title | VARCHAR | 255 | NOT NULL | Titre |
| description | TEXT | — | NULL | Description |
| start_date | DATETIME | — | NOT NULL | Début |
| end_date | DATETIME | — | NULL | Fin |
| location | VARCHAR | 255 | NULL | Lieu |
| type | ENUM | — | default `other` | `exam`, `holiday`, `meeting`, `other` |
| class_id | BigInt UNSIGNED | — | **FK** → `classes.id`, NULL | Classe concernée (optionnel) |
| is_all_day | BOOLEAN | — | default false | Journée entière |

---

## Table `class_groups`

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| name | VARCHAR | 255 | NOT NULL | Nom du groupe |
| description | TEXT | — | NULL | — |
| school_class_id | BigInt UNSIGNED | — | **FK** → `classes.id`, CASCADE | Classe parente |
| academic_year_id | BigInt UNSIGNED | — | **FK** → `academic_years.id`, CASCADE | Année scolaire |
| is_active | BOOLEAN | — | default true | Statut |
| max_students | INT UNSIGNED | — | NULL | Capacité |
| created_by / updated_by | BigInt UNSIGNED | — | **FK** → `users.id`, SET NULL | Audit |
| deleted_at | TIMESTAMP | — | NULL | Soft delete |
| UNIQUE | — | — | (name, school_class_id, academic_year_id) | Anti-doublon |

---

## Table `class_group_student` (pivot)

| Champ | Type | Taille | Contraintes | Description |
|---|---|---|---|---|
| id | BigInt UNSIGNED | — | **PK** | Identifiant |
| class_group_id | BigInt UNSIGNED | — | **FK** → `class_groups.id`, CASCADE | Groupe |
| student_id | BigInt UNSIGNED | — | **FK** → `users.id`, CASCADE | Élève |
| start_date / end_date | DATE | — | NOT NULL / NULL | Période |
| UNIQUE | — | — | (class_group_id, student_id, start_date) | — |

---

## Tables système (Laravel + Spatie Permission)

- `password_reset_tokens` (email PK, token, created_at)
- `sessions` (id PK, user_id, ip_address, user_agent, payload, last_activity)
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` — files d'attente
- `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` — fournies par `spatie/laravel-permission` v6
