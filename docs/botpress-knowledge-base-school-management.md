# Base de connaissances Botpress — School Management System

<!-- 
  NOM SUGGÉRÉ À SAISIR DANS BOTPRESS (« Create a Knowledge Base ») :
  
  « School Management — Guide utilisateur et produit »
  
  Ou version courte :
  « Gestion Scolaire — FAQ & périmètre applicatif »
-->

---

## 1. Synthèse du produit

L’application est une plateforme web de **gestion d’établissement scolaire** développée avec **Laravel** (backend) et une interface **Bootstrap 5**. Elle sert à gérer des **élèves**, des **enseignants**, un **administrateur**, ainsi que les **classes**, **matières**, **années académiques**, **notes**, **présences** et **emplois du temps**. L’interface principale du site et des tableaux de bord est en **français**. Le périmètre est orienté gestion administrative et suivis pédagogiques ; ce n’est pas un système de paiement ou de LMS complet sauf fonctionnalités listées ci-dessous.

---

## 2. Publics et usages du chatbot

- **Visiteurs** : orientation vers l’inscription ou la connexion, explication du cycle d’activation des comptes.
- **Élèves** : où consulter notes, bulletin, présences, emploi du temps, profil.
- **Enseignants** : où saisir ou modifier notes, enregistrer présences, consulter classes et planning.
- **Administrateurs** : validation des inscriptions, gestion des utilisateurs et de la structure pédagogique.

**Ton recommandé** : professionnel, clair, en français ; phrases courtes ; privilégier les liens d’URLs relatives si l’utilisateur est déjà sur le site (voir section 8).

---

## 3. Rôles utilisateur (vérités techniques)

Dans la base applicative, les rôles stockés peuvent être notamment :

| Rôle en base       | Qui c’est dans l’application      | Accès réservé (préfixes d’URL) |
|-------------------|------------------------------------|----------------------------------|
| `admin`           | Administrateur de l’établissement  | `/admin/...`                     |
| `eleve`           | Élève                              | `/student/...`                   |
| `teacher`         | Enseignant (inscription formulaire)| `/teacher/...`                   |

**Compatibilité** : le code peut aussi reconnaître historiquement `professeur` comme enseignant et `student` comme élève dans certains flux, mais les valeurs courantes après inscription élève sont `eleve`, et **`teacher`** pour un professeur inscrit via le formulaire.

Un seul compte **`admin`** est prévu fonctionnellement pour la sécurité de l’établissement (éviter plusieurs super-admins non contrôlés).

---

## 4. Statuts de compte (inscription → accès)

Tout nouvel inscrit (élève ou enseignant) reçoit le statut **`pending`** jusqu’à décision d’un administrateur.

| Statut     | Effet utilisateur                                              |
|-----------|-----------------------------------------------------------------|
| `pending` | Ne peut pas se connecter comme un compte actif habituel ; message d’attente de validation. |
| `approved`| Compte actif après validation admin.                           |
| `rejected`| Compte refusé.                                                 |

Flux type : inscription → message indiquant que le compte sera activé après validation → **administrateur approuve ou rejette** depuis l’interface d’administration.

---

## 5. Authentification (informations critiques pour éviter les erreurs du bot)

- **Connexion** : les utilisateurs se connectent avec leur **identifiant unique** (champ **`identifier`**), **pas uniquement avec l’email** depuis le formulaire de connexion principal de l’application.
- Identifiants générés automatiquement à l’inscription :
  - **Élève** : préfixe **`E`** + année sur 4 chiffres + numéro séquentiel sur 3 chiffres → exemple **`E2026001`** (l’année dépend de l’année d’inscription).
  - **Enseignant (rôle formulaire `teacher`)** : préfixe **`P`** + même logique → exemple **`P2026001`**.
- **Mot de passe** défini à l’inscription ; réinitialisation possible via flux « mot de passe oublié » **par email** (routes Laravel standards `forgot-password` / `reset-password`).
- Nouveau compte élève : le formulaire exige une **classe souhaitée** (`desired_class`).
- Nouveau compte enseignant : sélection d’au moins une **matière** enseignée parmi celles configurées dans l’application.

**Le bot ne doit jamais** demander ou stocker les mots de passe des utilisateurs, ni inventer des identifiants précis sans que l’utilisateur les cite.

---

## 6. Fonctionnalités par rôle (conformément aux routes réelles)

### 6.1 Administrateur (`/admin/...`)

- Tableau de bord avec vue d’ensemble de l’activité (`/admin/dashboard`).
- **Inscriptions en attente** : lister et **approuver** ou **rejeter** (`/admin/pending` ou `/admin/pending-registrations`).
- **Élèves** : liste, création, édition, détail ; **affectation à une classe** (formulaires dédiés type `/admin/students/assign`, affectation depuis la fiche élève).
- **Enseignants** : ressource CRUD ; gestion des **classes affectées** à un enseignant (`/admin/teachers/{id}/classes`).
- **Classes** : gestion (`/admin/classes`).
- **Années académiques** : gestion ; possibilité de **marquer une année comme courante** (`set-current`).
- **Matières** : gestion (`/admin/subjects`).
- Profil administrateur sous `/admin/profile`.

### 6.2 Élève (`/student/...`)

- Tableau de bord : `/student/dashboard`.
- **Notes** : `/student/grades`.
- **Bulletin** : `/student/bulletin` ; bulletin **annuel** : `/student/bulletin/annual` (adaptation au système **sénégalais** côté affichage / logique métier où applicable).
- **Emploi du temps** : `/student/schedule`.
- **Présences / absences** : `/student/attendance`.
- **Profil** : affichage, édition, photo, mot de passe sous `/student/profile`.

### 6.3 Enseignant (`/teacher/...`)

- Tableau de bord : `/teacher/dashboard`.
- **Mes classes** : liste et détail classe (`/teacher/classes`, `/teacher/classes/{id}`).
- **Notes** : index, création, édition, suppression (`/teacher/grades/...`).
- **Présences** : index et enregistrement ; historique par élève (`/teacher/attendance`, `/teacher/attendance/student/{studentId}`).
- **Emploi du temps** : `/teacher/schedule`.
- **Profil** : même type de sous-routes que l’élève sous `/teacher/profile`.

---

## 7. Concepts métier dans la base de données (pour réponses précises)

Entités Laravel typiques utilisées dans l’application :

- **User** : compte avec `identifier`, `role`, `status`, lien optionnel `class_id` pour un élève affecté à une classe.
- **SchoolClass** : classe (groupe scolaire).
- **Subject** : matière.
- **AcademicYear** : année scolaire ; une peut être définie comme **courante**.
- **Grade** : note liée élève / matière / contexte académique (trimestres, semestre selon migrations).
- **Attendance** / **TeacherAssignment** / **Assignment** / **Schedule** / **Timetable** / **Event** : présences, affectations prof–classe ou prof–matière, créneaux et événements d’emploi du temps selon modules activés.

Le bot **n’a pas accès automatique aux données individuelles** des utilisateurs (notes réelles d’un élève X, nom d’un prof Y) tant qu’une intégration API ou un outil n’est pas branché dans Botpress. Il doit dire clairement d’aller sur la page prévue après connexion plutôt que d’affirmer des données personnelles.

---

## 8. URLs utiles pour guider un utilisateur (chemins relatifs)

| Contexte           | Chemin principal        |
|--------------------|-------------------------|
| Accueil public     | `/`                     |
| Connexion          | `/login`                |
| Inscription        | `/register`             |
| Tableau de bord (routing interne Laravel) | `/dashboard` après connexion (redirige selon rôle) |
| Chat Laravel (page interne hors Botpress)| `/chat` (nécessite d’être connecté) |

Le **widget Botpress Webchat** est chargé dans les gabarits Blade des pages pour offrir une aide contextuelle tout en restant **distinct** du chat `/chat`, qui peut relayer les messages vers un **service IA Python** configuré (`AI_SERVICE_URL` côté serveur Laravel).

---

## 9. Sécurité et limites pour le bot

- Le bot doit **orienter vers l’administration** ou le **support de l’établissement** pour : contestation officielle de note, problème légal ou disciplinaire grave, désactivation de compte, ou litige RGPD hors simple guide d’usage.
- Ne pas garantir une disponibilité à 100 % du service IA secondaire ou de l’API ; en cas de panne décrite par l’utilisateur, conseiller : vérifier la connexion, réessayer, contacter le responsable TI / l’établissement.
- **Pas de scraping** ou d’instructions pour contourner middlewares Laravel (`auth`, `role:admin`, middlewares élève ou enseignant).

---

## 10. Glossaire français (cohérence des réponses)

- **Bulletin** : relevé de résultats période ou synthèse annuelle selon vue.
- **Classe souhaitée** : niveau/classe renseigné à l’inscription élève avant affectation officielle admin.
- **Affectation** : lien élève ↔ classe ou enseignant ↔ classes/matères géré par l’administration.
- **Année courante** : année académique active pour les filtres métier lorsqu’implémentée.
- **Identifiant** : code type `E2026xxx` ou `P2026xxx` pour la connexion.

---

## 11. FAQs types (pour renforcer les récupérations sémantiques)

**Q : Je viens de m’inscrire, pourquoi je ne peux pas me connecter ?**  
R : Les comptes élèves et enseignants sont créés en attente (**pending**). Un administrateur doit **approuver** le compte. Utilisez après validation votre **identifiant** (ex. `E2026001`), pas nécessairement l’email, pour vous connecter.

**Q : J’ai oublié mon identifiant.**  
R : L’identifiant a été attribué à l’inscription ; vérifiez les emails ou documents donnés par l’établissement. Sinon, contactez l’**administration** via les canaux de l’école ; le bot ne peut pas retrouver cet identifiant sans accès données.

**Q : Où voir mes notes ?**  
R : En tant qu’élève connecté : menu **Élève** → **Notes** ou URL `/student/grades`.

**Q : Où sont les validations d’inscription ?**  
R : Pour un administrateur connecté : espace **`/admin/pending`** (ou équivalent liste inscriptions en attente).

**Q : Différence entre le chat Botpress et la page `/chat` ?**  
R : Le **widget Botpress** est l’assistant conversationnel général configuré dans Botpress. La page **`/chat`** Laravel est une autre fonctionnalité (interface interne peut appeler un service IA externe) ; tous deux peuvent coexister.

---

## 12. Déploiements et techno (informations générales, non critiques pour l’utilisateur final)

- Stack : **PHP / Laravel**, **Bootstrap 5**, base **relationnelle** (MySQL ou PostgreSQL selon environnement `.env`).
- Le domaine réel peut varier (**local**, **InfinityFree**, **Laravel Cloud**, etc.) : le bot doit utiliser les **URLs relatives** quand il guide quelqu’un déjà dans l’application, ou préciser « connectez-vous sur l’URL de votre établissement » si le contexte l’exige.

---

## 13. API JSON « school bot » (pour Botpress / outils tiers)

Accès lecture seule protégé par **Bearer token** (`SCHOOL_BOT_SECRET` sur le serveur). Toutes les URLs sont préfixées par `/api/bot/school/`.

- `GET …/stats` — effectifs (élèves, profs, classes, années courante, estimation redoublants).
- `GET …/stats/repeaters` — liste courte redoublants (heuristique multi-années de notes).
- `GET …/stats/outcomes` — agrégats pass / fail selon la **même logique de moyenne** que le bulletin semestriel.
- `GET …/students/search?q=…` — recherche **élèves uniquement** par nom ou identifiant (`E…`).
- `GET …/users/search?q=…` — recherche **élèves et enseignants** par nom ou identifiant (`E…`, `P…`, etc.). À utiliser dans Botpress pour les codes prof comme **`P2026001`** (la table Botpress seule suffit rarement sans colonne **`identifier`** synchronisée depuis Laravel).
- `GET …/students/{id}` — fiche élève restreinte (sans email ni données sensibles inutiles).

Headers : `Authorization: Bearer <secret>`, `Accept: application/json`. Secret absent côté serveur → erreur HTTP 503 ; token invalide → 401.

---

*Fin du document — à importer comme fichier unique ou coupé par sections dans Botpress selon vos limites de taille de fichier / chunking.*
