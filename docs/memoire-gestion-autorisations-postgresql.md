# Gestion des autorisations et des accès — EduManager (PostgreSQL, école unique)

> Section réutilisable dans le **chapitre 4** (architecture / sécurité) ou le **chapitre 5** (implémentation Laravel).  
> Formulation alignée sur le **code réel** du projet et sur **PostgreSQL**.

---

## Paragraphe recommandé pour le mémoire

Pour la gestion des autorisations et des accès au sein de la plateforme EduManager, nous avons adopté une **stratégie hybride**, adaptée à un établissement scolaire unique et à une base **PostgreSQL**.

Sur PostgreSQL, la colonne **`role`** de la table **`users`** est stockée en **`VARCHAR(50)`**, et non en type **ENUM** natif. Ce choix évite la rigidité d’un ENUM PostgreSQL (ajout ou renommage de profils sans migration lourde de type) tout en conservant un **profil métier explicite** pour chaque compte : administrateur de l’établissement, enseignant, élève, etc. Les valeurs courantes (`admin`, `teacher`, `eleve`, …) sont centralisées dans le modèle **`User`** sous forme de constantes, ce qui homogénéise le code applicatif.

Le **contrôle d’accès principal** repose sur cette colonne **`users.role`**, combinée à des **middlewares Laravel** dédiés :

- **`auth`** — refus des requêtes non authentifiées ;
- **`StudentMiddleware`** / **`TeacherMiddleware`** — restriction des espaces `/student/...` et `/teacher/...` ;
- groupes de routes **`/admin/...`** protégés pour le personnel de l’établissement ;
- **`RoleMiddleware`** — vérification du rôle attendu et redirection vers le tableau de bord approprié en cas d’accès non autorisé.

Les routes sont ainsi **segmentées par acteur** (administration, enseignant, élève), ce qui matérialise la séparation des responsabilités au niveau du routage, avant même l’exécution des contrôleurs.

En complément, le package **`spatie/laravel-permission`** (^6.24) est intégré au modèle **`User`** (trait **`HasRoles`**). Il provisionne une structure relationnelle standard (**tables `roles`, `permissions`, `model_has_roles`**, etc.) via le seeder **`RolePermissionSeeder`**. Cette couche prépare une **évolution vers des permissions plus fines** (droits granulaires au-delà du simple profil) sans remplacer, à ce stade du projet, le mécanisme principal basé sur **`users.role`** et les middlewares. L’attribution Spatie peut être synchronisée avec le profil métier lors de l’initialisation des données.

Des **contrôles métier supplémentaires** sont appliqués dans les contrôleurs : par exemple, un enseignant ne peut saisir des notes que pour les **classes qui lui sont affectées** ; seuls les comptes au statut **`approved`** accèdent pleinement au système après validation par l’administration (`pending` / `rejected`). La connexion est protégée par **`LoginRequest`** (validation des champs, limitation des tentatives, vérification du statut du compte). Les mots de passe sont **hashés** (bcrypt) et les formulaires web sont couverts par la protection **CSRF** de Laravel.

En résumé, la sécurité des accès repose sur une **défense en profondeur** : profil stocké en **`VARCHAR`** sur PostgreSQL, **middlewares** par espace fonctionnel, **règles métier** dans les contrôleurs, et **Spatie** comme socle extensible pour des permissions futures — sans prétendre que l’ensemble des droits a été entièrement délégué au seul package Spatie.

**[Capture de la partie concernée]** — Extrait de `routes/web.php` montrant les groupes `middleware` par préfixe `/admin`, `/teacher`, `/student`.

**[Capture de la partie concernée]** — Vue pgAdmin : colonne `role` (type `character varying`) et statut `status` sur la table `users`.

---

## Version courte (encadré ou résumé)

| Niveau | Mécanisme |
|--------|-----------|
| Données | `users.role` en **VARCHAR** (PostgreSQL), statuts `pending` / `approved` / `rejected` |
| Routage | Middlewares **`auth`**, **`StudentMiddleware`**, **`TeacherMiddleware`**, groupes `/admin` |
| Métier | Contrôleurs (affectations enseignant–classe, validation admin) |
| Extensibilité | **`spatie/laravel-permission`** (tables roles/permissions, trait `HasRoles`) |
| Web | CSRF, hashage bcrypt, rate-limiting à la connexion |

---

## ⚠️ Ne pas écrire tel quel (formulation initiale incorrecte)

La formulation suivante **ne correspond pas** au code actuel et expose à des questions du jury :

> *« Nous avons fait le choix de ne pas stocker de rôle en dur dans la table users »*  
> *« Nous avons intégralement délégué la gestion des profils à spatie/laravel-permission »*  
> *« Attribution via Gates et Policies sans altérer la table users »*

**Pourquoi :** la table **`users`** contient bien une colonne **`role`** ; les middlewares et la redirection post-login s’appuient dessus ; **Spatie** est présent mais **complémentaire** (seeder + évolutivité), pas substitut exclusif. Les **Policies** Laravel ne sont pas le mécanisme principal d’autorisation dans ce projet.

---

## Références code (pour la soutenance)

- Modèle : `app/Models/User.php` — constantes de rôles, trait `HasRoles`
- Middlewares : `app/Http/Middleware/StudentMiddleware.php`, `TeacherMiddleware.php`, `RoleMiddleware.php`
- Migration PostgreSQL : `database/migrations/2026_01_12_213805_update_role_enum_in_users_table.php` — passage du rôle en `VARCHAR(50)` sous **pgsql**
- Seeder Spatie : `database/seeders/RolePermissionSeeder.php`
- Routes : `routes/web.php` — groupes par préfixe et middleware
