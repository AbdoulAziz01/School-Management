---
title: "Dossier complet — Plateforme de gestion d'établissements scolaires « AzelieEdu »"
---

<div class="cover">
<div class="cover-eyebrow">Documentation de référence</div>
<h1 class="cover-title">AzelieEdu</h1>
<p class="cover-subtitle">Plateforme de gestion d'établissements scolaires<br>Dossier fonctionnel, technique et produit</p>

<div class="cover-meta">
<table>
<tr><td>Type de document</td><td>Documentation fonctionnelle, technique et produit</td></tr>
<tr><td>Version</td><td>1.0</td></tr>
<tr><td>Date</td><td>7 août 2026</td></tr>
<tr><td>Statut</td><td>Document de référence</td></tr>
<tr><td>Périmètre</td><td>Analyse exhaustive du code source du dépôt <code>School-Management</code></td></tr>
</table>
</div>

<p class="cover-note">Ce document a été produit par l'analyse directe du code source (routes, contrôleurs, modèles, migrations, vues, services). Il distingue explicitement les fonctionnalités <strong>réellement implémentées</strong>, <strong>partiellement implémentées</strong> et <strong>prévues / potentielles</strong>. Aucune information n'a été inventée : lorsqu'un point n'est pas vérifiable dans le code, il est signalé comme tel.</p>
</div>

<div class="page-break"></div>

## Table des matières

1. [Présentation générale](#1-présentation-générale)
2. [Problématique adressée](#2-problématique-adressée)
3. [Proposition de valeur](#3-proposition-de-valeur)
4. [Utilisateurs et rôles](#4-utilisateurs-et-rôles)
5. [Architecture fonctionnelle et cartographie des pages](#5-architecture-fonctionnelle-et-cartographie-des-pages)
6. [Documentation page par page (pages clés)](#6-documentation-page-par-page-pages-clés)
7. [Tableaux de bord](#7-tableaux-de-bord)
8. [Module Élèves](#8-module-élèves)
9. [Module Enseignants](#9-module-enseignants)
10. [Module Classes / Formation](#10-module-classes--formation)
11. [Module Notes et évaluations](#11-module-notes-et-évaluations)
12. [Module Présence / Absences](#12-module-présence--absences)
13. [Module Finances / Comptabilité](#13-module-finances--comptabilité)
14. [Module Parents](#14-module-parents)
15. [Module Communication](#15-module-communication)
16. [Module E-learning (LMS) et classes virtuelles](#16-module-e-learning-lms-et-classes-virtuelles)
17. [Documents, rapports et exports](#17-documents-rapports-et-exports)
18. [Assistant IA / Chatbot](#18-assistant-ia--chatbot)
19. [Authentification et sécurité](#19-authentification-et-sécurité)
20. [Architecture technique](#20-architecture-technique)
21. [Architecture des données](#21-architecture-des-données)
22. [API et flux de données](#22-api-et-flux-de-données)
23. [Workflows métiers](#23-workflows-métiers)
24. [UX / UI](#24-uxui)
25. [Performance et scalabilité](#25-performance-et-scalabilité)
26. [Installation et déploiement](#26-installation-et-déploiement)
27. [Tests et qualité](#27-tests-et-qualité)
28. [Audit global du projet](#28-audit-global-du-projet)
29. [Points forts](#29-points-forts)
30. [Limites et risques](#30-limites-et-risques)
31. [Roadmap recommandée](#31-roadmap-recommandée)
32. [Vision produit](#32-vision-produit)
33. [Glossaire](#33-glossaire)
34. [Annexes](#34-annexes)

<div class="page-break"></div>

## 1. Présentation générale

### 1.1 Identité

**AzelieEdu** (nom déclaré dans `config/platform.php`, `.env.example` : `PLATFORM_NAME=AzelieEdu`, `APP_NAME=AzelieEdu`) est une plateforme web de gestion d'établissements scolaires et de centres de formation, développée en **Laravel 12 (PHP 8.2)**, à destination du marché sénégalais (nomenclature des cycles CI→CM2/6ème→Terminale, séries du lycée, format de bulletin sénégalais, intégration WhatsApp en français/wolof/pular, drapeau du Sénégal dans les bulletins).

C'est une application **multi-tenant** (plusieurs établissements hébergés sur une même instance, isolés par la colonne `school_id`), pilotée par une console d'exploitant de plateforme (rôle `super_admin`) qui crée, active/suspend et configure chaque établissement (« école » = tenant).

### 1.2 Catégorie de produit

- Secteur : **EdTech / logiciel de gestion scolaire (School ERP / SIS — Student Information System)**.
- Modèle : SaaS multi-établissements avec activation modulaire par établissement (le module « Comptabilité » est activable/désactivable par école, via `schools.toggle-accounting-module`).
- Deux grandes familles d'établissements gérées par la même base de code :
  - **Établissements classiques K-12** (primaire, collège, lycée) — gestion pédagogique traditionnelle (notes, bulletins, promotion automatique de classe).
  - **Centres de formation professionnelle / supérieure** (mode « Formation », avec système de notation LMD configurable — Licence/Master/Doctorat, contrôle continu + examen pondérés).

### 1.3 Objectifs et problématique adressée

Digitaliser et centraliser la gestion administrative, pédagogique et financière d'un établissement scolaire : inscriptions, classes, emplois du temps, notes, bulletins, présences, finances (frais de scolarité, salaires, caisse), et une couche pédagogique numérique (cours en ligne, devoirs, quiz, classes virtuelles).

### 1.4 Public cible

- Fondateurs / directions de groupes scolaires et centres de formation souhaitant digitaliser plusieurs établissements depuis une console unique.
- Établissements primaires, collèges, lycées et centres de formation professionnelle, principalement au Sénégal (mais l'architecture n'a rien de géographiquement verrouillé au-delà des textes/format bulletin).
- Utilisateurs internes de l'établissement : administration, direction, corps enseignant, personnel de surveillance, service comptable/caisse.
- Élèves et (indirectement, via WhatsApp) leurs parents/tuteurs.

### 1.5 Avantages par profil

| Profil | Avantages observés dans le code |
|---|---|
| **Établissement / direction** | Vue consolidée (dashboard), traçabilité financière (journal comptable immuable), audit log de toutes les actions sensibles, gestion multi-classes/multi-niveaux, génération automatique des bulletins et cartes scolaires. |
| **Enseignants** | Saisie de notes et présences en ligne, consultation d'emploi du temps, dépôt de cours/devoirs/quiz, classes virtuelles Jitsi, suivi de salaire. |
| **Élèves** | Consultation des notes/bulletins/présences en continu, accès aux cours/devoirs/quiz, classes virtuelles, carte scolaire numérique avec QR code, suivi des paiements de frais. |
| **Parents** (indirect) | Notifications WhatsApp automatiques en cas d'absence (français, wolof ou pular selon préférence enregistrée), déjà réellement câblées vers l'API UltraMsg. |
| **Administration/personnel comptable** | Séparation des rôles caissier / comptable / directeur financier avec workflows de caisse (ouverture/clôture de session), génération automatique des factures élèves et fiches de salaire, reçus imprimables (format ticket thermique 80mm). |

### 1.6 Vision de digitalisation

La plateforme centralise dans un seul système ce qui est traditionnellement dispersé entre cahiers papier, tableurs Excel isolés, et communication informelle : inscriptions, notes, présences, finances et supports pédagogiques partagent désormais un même référentiel de données par établissement, avec traçabilité (journal d'activité) et contrôle d'accès par rôle.

<div class="page-break"></div>

## 2. Problématique adressée

| Problème (gestion traditionnelle) | Solution apportée par AzelieEdu | Utilisateur concerné | Impact |
|---|---|---|---|
| Gestion papier des inscriptions, notes, présences | Formulaires numériques, base de données centralisée par établissement | Admin, enseignants | Réduction de la perte de données, accès instantané |
| Dispersion de l'information entre plusieurs outils (Excel, cahiers, SMS) | Une base unique par école (`school_id`), consultable par rôle | Tous | Source unique de vérité |
| Calcul manuel des moyennes/bulletins, erreurs de pondération | Moteur de calcul centralisé (`BulletinComputation`), formules différenciées primaire / secondaire / LMD | Admin, enseignants, élèves | Fiabilité, gain de temps, cohérence entre bulletin et outils statistiques (même moteur réutilisé par le chatbot) |
| Suivi des absences sur papier, communication tardive aux parents | Saisie numérique de l'appel, verrouillage anti-modification, notification WhatsApp automatique (si activée) | Enseignants, surveillants, parents | Traçabilité, réactivité |
| Gestion financière opaque (caisse papier, reçus manuels, pas de piste d'audit) | Journal comptable immuable alimenté automatiquement par des Observers, numérotation séquentielle des reçus, sessions de caisse avec rapprochement | Comptable, caissier, directeur | Fiabilité financière, détection des écarts de caisse |
| Aucune preuve d'authenticité des bulletins/reçus délivrés | QR code + lien de vérification signé (Laravel `signedRoute`) sur bulletins et reçus, page de vérification publique qui recalcule les données côté serveur | Direction, parents, tiers | Lutte contre la fraude documentaire |
| Difficulté à superviser plusieurs établissements | Console « Platform » (rôle `super_admin`) : tableau de bord multi-écoles, inspection à distance, activation/suspension d'école | Exploitant de la plateforme | Supervision centralisée d'un réseau d'écoles |
| Manque d'outils pédagogiques numériques | Module LMS (cours, devoirs notés, quiz à tentatives limitées, classes virtuelles Jitsi) | Enseignants, élèves | Continuité pédagogique, y compris à distance |

<div class="page-break"></div>

## 3. Proposition de valeur

- **Multi-établissement natif** : une même instance héberge plusieurs écoles, chacune isolée par un filtrage `school_id` appliqué automatiquement à toutes les requêtes (« *scoped global* » Eloquent), avec une console d'exploitation dédiée pour l'opérateur SaaS.
- **Deux modèles pédagogiques dans un seul socle** : établissements classiques (primaire/collège/lycée) et centres de formation professionnelle (mode LMD), avec des moteurs de notation distincts mais un même socle de tables.
- **Comptabilité opérationnelle réelle, pas un module cosmétique** : séparation des tâches (caissier encaisse, comptable enregistre les dépenses/salaires, directeur pilote et consulte), journal comptable immuable, verrouillage transactionnel (locks) pour éviter les doubles écritures, aucune suppression destructrice (tout est annulé, jamais supprimé).
- **Conformité documentaire** : bulletins et reçus vérifiables publiquement via QR code signé.
- **Canal de communication déjà connecté** : intégration WhatsApp réelle (API UltraMsg) pour notifier les parents en cas d'absence, avec support multilingue (français texte, wolof/pular audio pré-enregistré).
- **Modularité par établissement** : le module Comptabilité peut être activé/désactivé indépendamment pour chaque école depuis la console plateforme.

<div class="page-break"></div>

## 4. Utilisateurs et rôles

Les rôles réellement présents dans le code (`app/Models/User.php`, `database/seeders/RolePermissionSeeder.php`, `app/Support/AccountingRoles.php`) sont au nombre de **huit rôles actifs** + **un rôle de fondation non activé** (`tresorier`).

### 4.1 Tableau de synthèse

| Rôle (colonne `role`) | Portail | Périmètre | Statut |
|---|---|---|---|
| `super_admin` | `/platform/*` | Toute la plateforme (tous les établissements) | Actif |
| `admin` | `/admin/*` | Un établissement (école) | Actif |
| `surveillant` | `/admin/*` (accès restreint) | Un établissement, principalement vie scolaire | Actif |
| `teacher` / `professeur` (alias) | `/teacher/*` | Ses classes/matières assignées | Actif |
| `eleve` / `student` (alias) | `/student/*` | Ses propres données | Actif |
| `directeur` | `/directeur/*` (comptabilité) | Pilotage financier d'un établissement | Actif |
| `comptable` | `/comptable/*` | Écritures comptables d'un établissement | Actif |
| `caissier` | `/caisse/*` | Encaissements d'un établissement | Actif |
| `tresorier` | — | Prévu (validation d'écritures, banque, rapprochement) — **aucune route, contrôleur ni tableau de bord ne l'utilise** | Fondation seule, non activé |

### 4.2 Détail par rôle

**Super Administrateur (`super_admin`)** — Objectif : exploitant de la plateforme SaaS.
- Accès : bypass total des vérifications d'autorisation via `Gate::before()` (`AppServiceProvider`), non filtré par le scope multi-tenant (`TenantSchool::applyForUser` désactive le filtrage pour lui).
- Actions : créer/éditer/supprimer des établissements, activer/suspendre un établissement (suspension = déconnexion forcée immédiate des utilisateurs de cette école via `EnsureSchoolIsActive`), activer/désactiver le module Comptabilité par école, régénérer le code d'inscription d'une école, gérer les cycles/niveaux type, créer des comptes admin d'école et réinitialiser leurs mots de passe, inspecter en lecture seule les données de n'importe quelle école (classes, utilisateurs, matières), consulter un rapport cross-écoles des élèves non affectés à une classe.
- Restrictions : aucune restriction fonctionnelle particulière ; c'est le rôle racine.

**Administrateur d'établissement (`admin`)** — Objectif : gérer l'ensemble des opérations d'une école.
- Accès : tout `/admin/*`, strictement filtré à son propre `school_id` par le scope global.
- Actions : CRUD élèves/enseignants/classes/matières/années académiques, import Excel en masse (élèves, enseignants), approbation des inscriptions en attente, configuration de la notation primaire, emplois du temps, cartes scolaires, gestion des comptes du personnel comptable, consultation du journal d'audit, génération de rapports/exports, paramétrage de l'établissement et des réglages LMD.
- Restrictions : ne peut pas dépasser son propre établissement ; ne saisit jamais lui-même l'appel (présence) — action réservée aux enseignants.

**Surveillant (`surveillant`)** — Objectif : vie scolaire, un accès plus restreint que `admin` mais regroupé sous le même middleware `EnsureSchoolAdmin` (`ROLE_SCHOOL_STAFF`).
- Permissions Spatie attribuées : consultation élèves/classes/notes, consultation + saisie de présence — un périmètre plus étroit que `admin` dans la matrice de permissions, même si l'accès aux routes `/admin/*` n'est pas techniquement filtré différemment du rôle admin dans les middlewares observés (le contrôle fin repose sur les permissions Spatie, non encore branchées sur les routes).

**Enseignant (`teacher`/`professeur`)** — Objectif : activité pédagogique quotidienne.
- Accès : `/teacher/*`, restreint à ses classes/matières assignées (`TeacherAssignment`, pivot `class_teacher` pour le primaire).
- Actions : saisir/corriger des notes (une seule correction autorisée après saisie initiale), faire l'appel (verrouillé après enregistrement, aucune modification possible), consulter son emploi du temps, publier des cours/devoirs/quiz, organiser des classes virtuelles, consulter l'historique de son salaire (si module Comptabilité actif).
- Restrictions : accès aux classes vérifié à chaque action (garde anti-IDOR `TeacherClassResolver`/`TeacherSubjectResolver`).

**Élève (`eleve`/`student`)** — Objectif : consultation de son propre parcours.
- Accès : `/student/*`, données strictement personnelles.
- Actions : consulter notes/bulletins/présences/emploi du temps, accéder aux cours/devoirs/quiz (tentatives limitées à 3), rejoindre les classes virtuelles, consulter/imprimer sa carte scolaire, suivre ses paiements de frais (si module actif), gérer son profil.
- Restrictions : aucune permission Spatie explicite (rôle « lecture seule » par construction applicative plutôt que par permission).

**Directeur financier (`directeur`)** — Objectif : pilotage et supervision financière d'un établissement (rôle du module Comptabilité).
- Accès : `/directeur/*`, verrouillé au rôle exact (pas d'alias).
- Actions : configurer les types/montants de frais scolaires, consulter le grand livre (journal comptable), suivre les salaires et — exception documentée de séparation des tâches — payer lui-même un salaire, consulter les élèves débiteurs, parcourir en lecture seule classes/enseignants/élèves de son école, créer des comptes comptable/caissier (son personnel direct), exporter le journal comptable.
- Restrictions : aucune route d'annulation de paiement élève ne lui est ouverte (contrairement au comptable) — séparation des tâches volontaire, documentée dans le code.

**Comptable (`comptable`)** — Objectif : tenue comptable quotidienne.
- Accès : `/comptable/*`.
- Actions : enregistrer/annuler des dépenses, générer et payer les salaires (génération mensuelle idempotente), annuler des paiements élèves, rechercher/imprimer des reçus, consulter le journal et les élèves débiteurs.
- Restrictions : ne configure pas les types de frais (réservé au directeur).

**Caissier (`caissier`)** — Objectif : encaissement au guichet.
- Accès : `/caisse/*`.
- Actions : ouvrir/clôturer une session de caisse (une seule session ouverte à la fois par caissier), rechercher un élève, consulter sa situation financière, encaisser un paiement (ventilé sur une ou plusieurs factures), imprimer/rechercher des reçus, consulter l'historique de ses sessions.
- Restrictions : ne peut pas annuler un paiement (réservé au comptable), ne configure rien.

**Trésorier (`tresorier`)** — rôle et permissions définis dans le catalogue (`ecriture.valider`, `banque.gerer`, `rapprochement.effectuer`, `virement.effectuer`) mais **explicitement documenté dans le code comme non activé** : aucun compte, tableau de bord ou route ne le reconnaît actuellement. *Fonctionnalité prévue / potentielle, non implémentée.*

<div class="page-break"></div>

## 5. Architecture fonctionnelle et cartographie des pages

### 5.1 Vue d'ensemble des portails

L'application expose **six portails** distincts, chacun avec sa propre mise en page (layout Blade), sa barre latérale de navigation et son middleware de garde d'accès :

```
                        ┌──────────────────────────┐
                        │   /platform/*  (SaaS)     │  super_admin — toutes écoles
                        └──────────────────────────┘
                                     │
        ┌────────────────────────────────────────────────────┐
        │                    École (tenant, school_id)         │
        │                                                      │
   ┌────▼────┐   ┌──────────┐   ┌──────────┐   ┌───────────────────────────┐
   │ /admin/* │   │/teacher/*│   │/student/*│   │ Comptabilité (si activée)  │
   │  admin,  │   │ teacher  │   │  eleve   │   │  /directeur/* /comptable/* │
   │surveillant│  │          │   │          │   │        /caisse/*           │
   └──────────┘   └──────────┘   └──────────┘   └───────────────────────────┘
```

Middleware de garde par portail : `super_admin` (platform), `school.admin` + `school.active` (admin), `TeacherMiddleware` + `school.active` (teacher), `StudentMiddleware` + `school.active` (student), `accounting.role:<role>` + `module:accounting` + `school.active` (comptabilité, 3 portails séparés — voir §19).

### 5.2 Cartographie synthétique par module

> Légende État : ✅ Implémenté · 🟡 Partiel · ⏳ Prévu/absent

#### Authentification (public)

| Page | Route | Rôle | État |
|---|---|---|---|
| Connexion | `GET/POST login` | Public | ✅ |
| Inscription | `GET/POST register` | Public | ✅ |
| Mot de passe oublié | `forgot-password`, `reset-password/{token}` | Public | ✅ |
| Confirmation de mot de passe | `confirm-password` | Authentifié | ✅ |
| Vérification bulletin (QR) | `GET bulletin/verify` | Public (lien signé) | ✅ |
| Vérification carte scolaire | `GET card/verify/{student}` | Public (lien signé) | ✅ |
| Vérification reçu paiement/salaire | `GET payment-receipt/verify`, `salary-receipt/verify` | Public | ✅ |

#### Console Plateforme (`super_admin`)

| Page | Route | État |
|---|---|---|
| Tableau de bord plateforme | `platform.dashboard` | ✅ |
| Liste des établissements | `platform.schools.index` | ✅ |
| Créer / éditer un établissement | `platform.schools.create/edit` | ✅ |
| Fiche établissement | `platform.schools.show` | ✅ |
| Inspection d'une école (classes/utilisateurs/matières) | `platform.schools.inspection`, `.classes.show`, `.users.show`, `.subjects.show` | ✅ |
| Cycles/niveaux d'une école | `platform.schools.cycles.*` | ✅ |
| Élèves non affectés (multi-écoles) | `platform.students.unassigned` | ✅ |
| Activer/suspendre école, régénérer code, toggler module | actions PATCH dédiées | ✅ |
| Créer un admin d'école / réinitialiser son mot de passe | `platform.schools.admins.*` | ✅ |

#### Administration d'école (`admin`, `surveillant`)

| Page | Route (préfixe) | État |
|---|---|---|
| Tableau de bord | `admin.dashboard` | ✅ |
| Élèves — liste/fiche/CRUD | `admin.students.*` | ✅ |
| Import Excel élèves (4 étapes) | `admin.students.import.*` | ✅ |
| Inscriptions en attente | `admin.pending`, `admin.registrations.pending` | ✅ |
| Enseignants — liste/fiche/CRUD | `admin.teachers.*` | ✅ |
| Import Excel enseignants | `admin.teachers.import.*` | ✅ |
| Affectations enseignant↔classe↔matière | `admin.teachers.classes.*`, `admin.teachers.assignments.*` | ✅ |
| Classes — liste/fiche/CRUD, promotion | `admin.classes.*` | ✅ |
| Cycles de formation | `admin.cycles.*` | ✅ (écoles formation) |
| Matières | `admin.subjects.*` | ✅ |
| Configuration notation primaire | `admin.primary-grading.*` | ✅ |
| Années académiques (provisionnement, clôture) | `admin.academic-years.*` | ✅ |
| Emplois du temps | `admin.schedules.*` | ✅ |
| Présences (lecture seule) | `admin.attendance.*` | ✅ |
| E-learning (supervision) | `admin.lms.*` | ✅ |
| Cartes scolaires | `admin.cards.*` | ✅ |
| Journal d'audit | `admin.audit-log.index` | ✅ |
| Comptes personnel comptable | `admin.accounting-staff.*` | ✅ (si module actif) |
| Rapports & exports (PDF/CSV) | `admin.reports.*` | ✅ |
| Paramètres établissement | `admin.school.settings.*` | ✅ |
| Paramètres LMD | `admin.formation.lmd-settings.*` | ✅ (écoles formation) |
| Profil admin | `admin.profile.*` | ✅ |

#### Enseignant (`teacher`)

| Page | Route | État |
|---|---|---|
| Tableau de bord | `teacher.dashboard` | ✅ |
| Mes classes | `teacher.classes.*` | ✅ |
| Notes (saisie/édition) | `teacher.grades.*` | ✅ |
| Présences (appel, historique) | `teacher.attendance.*` | ✅ |
| Emploi du temps | `teacher.schedule.index` | ✅ |
| E-learning (cours/devoirs/quiz) | `teacher.lms.*` | ✅ |
| Classes virtuelles | `teacher.virtual-classes.*`/`teacher.virtual-class.*` | ✅ |
| Mon salaire | `teacher.salary.index` | ✅ (si module actif) |
| Profil | `teacher.profile.*` | ✅ |

#### Élève (`eleve`)

| Page | Route | État |
|---|---|---|
| Tableau de bord | `student.dashboard` | ✅ |
| Mes notes | `student.grades.index` | ✅ |
| Bulletin (semestre/annuel) | `student.bulletin.*` | ✅ |
| Emploi du temps | `student.schedule.index` | ✅ |
| Présences | `student.attendance.index` | ✅ |
| E-learning | `student.lms.index` | ✅ |
| Quiz (passage, résultat) | `student.quiz.*` | ✅ |
| Classes virtuelles | `student.virtual-classes.*` | ✅ |
| Carte scolaire | `student.card.*` | ✅ |
| Mes paiements | `student.payments.index` | ✅ (si module actif) |
| Profil | `student.profile.*` | ✅ |

#### Comptabilité — Directeur / Comptable / Caissier

| Page | Route | Rôle | État |
|---|---|---|---|
| Dashboard financier | `directeur.dashboard` / `comptable.dashboard` / `caisse.dashboard` | chacun | ✅ |
| Types & montants de frais | `directeur.fee-types.*` | directeur | ✅ |
| Salaires — checklist/paiement | `directeur.salaries.*`, `comptable.salaries.*` | directeur, comptable | ✅ |
| Dépenses | `comptable.expenses.*` | comptable | ✅ |
| Paiements — consultation/annulation | `directeur.payments.index` (lecture), `comptable.payments.*` (annulation) | directeur, comptable | ✅ |
| Reçus (paiement/salaire) | `*.receipts.*`, `*.salary-receipts.*` | comptable, caissier, directeur | ✅ |
| Grand livre / journal | `*.ledger.index` | tous 3 | ✅ |
| Élèves débiteurs / fiche financière | `*.students.debtors`, `*.students.show` | tous 3 | ✅ |
| École en lecture seule (classes/élèves/profs) | `directeur.school.*` | directeur | ✅ |
| Personnel comptable (créer comptable/caissier) | `directeur.personnel.*` | directeur | ✅ |
| Session de caisse (ouvrir/clôturer) | `caisse.session.*` | caissier | ✅ |
| Encaissement élève | `caisse.students.*` (search/situation/pay) | caissier | ✅ |
| Historique caisse | `caisse.history`, `caisse.sessions.index` | caissier | ✅ |

<div class="page-break"></div>

## 6. Documentation page par page (pages clés)

> Le dépôt contient plus de **150 vues Blade**. Cette section détaille les pages les plus représentatives de chaque flux métier majeur ; la cartographie complète (§5) couvre l'exhaustivité des routes/pages. La liste brute de tous les fichiers de vues figure en Annexe (§34.3).

### Tableau de bord Administrateur

- **Objectif** : donner à l'admin d'école une vue d'ensemble immédiate de son établissement.
- **Utilisateurs** : `admin`, `surveillant`.
- **Fonctionnalités** : indicateurs (effectifs élèves/enseignants/classes), widgets de synthèse, raccourcis vers les modules.
- **Données affichées** : compteurs issus de `Admin\DashboardController`.
- **Actions possibles** : navigation vers les modules ; pas d'action de modification directe sur cette page.
- **Workflow** : connexion → redirection automatique par rôle (`/dashboard` → `admin.dashboard`) → affichage.
- **Sécurité** : `school.admin` + `school.active` (déconnexion forcée si l'école est suspendue par la plateforme).
- **État** : ✅ Implémenté (existence d'une variante `dashboard_temp.blade.php` suggérant une v2 en cours d'itération — non confirmée comme active).

### Fiche Élève (`admin/students/show.blade.php`)

- **Objectif** : consulter le profil complet d'un élève et l'évolution de ses notes.
- **Utilisateurs** : `admin`, `surveillant`.
- **Fonctionnalités** : identité, classe, statistiques de présence (présent/absent/retard/excusé + taux), historique de notes, graphique Chart.js d'évolution des moyennes par matière avec sélecteur de matière.
- **Données** : `User` (élève), `Grade`, `Attendance` agrégés par `StudentController::show`.
- **Actions** : modifier, envoyer les identifiants, régénérer le mot de passe, exporter.
- **Règles métier** : le taux de présence est calculé à partir des statuts d'`Attendance` ; le champ `justified` est lu mais aucune action de justification n'a été trouvée dans le contrôleur (voir §12, fonctionnalité partielle).
- **État** : ✅ Implémenté (hors justification d'absence, 🟡 partielle).

### Import Élèves — assistant en 4 étapes

- **Objectif** : intégrer en masse une population d'élèves depuis un fichier Excel/CSV.
- **Utilisateurs** : `admin`.
- **Workflow utilisateur** : (1) téléversement du fichier → (2) mapping des colonnes (association automatique par alias avec correction manuelle) → (3) aperçu des lignes valides/invalides avant import → (4) résultat de l'import + export des identifiants/mots de passe générés (`StudentsImportCredentialsExport`).
- **Règles métier** : détection de doublons (nom+date de naissance), résolution de classe par texte libre normalisé, validation des dates flexibles, un seul essai gratuit puis import transactionnel par lot.
- **Sécurité** : réservé à `admin`, protégé par `school.admin`.
- **État** : ✅ Implémenté (mécanique complète, y compris gestion d'erreurs de mapping).

### Saisie de notes — Enseignant (`teacher/grades/*`)

- **Objectif** : permettre à un enseignant d'entrer/corriger les notes de ses classes.
- **Utilisateurs** : `teacher`.
- **Fonctionnalités** : sélection classe + matière, grille élèves × colonnes d'évaluation (nombre de colonnes dépendant du cycle : Devoir1/Devoir2/Composition en secondaire, N compositions configurables en primaire).
- **Règles métier** : une note ne peut être corrigée qu'**une seule fois** après saisie initiale (`Grade::canStillBeEditedByTeacher()`) ; toute correction déclenche une notification email + notification in-app vers l'administration (`GradeEditedByTeacherMail`/`Notification`).
- **Sécurité** : vérification que l'enseignant est bien affecté à la classe/matière (`TeacherSubjectResolver`) avant d'autoriser la saisie — garde anti-IDOR.
- **État** : ✅ Implémenté.

### Appel / Présence — Enseignant (`teacher/attendance/index.blade.php`)

- **Objectif** : enregistrer la présence quotidienne d'une classe.
- **Utilisateurs** : `teacher`.
- **Workflow** : sélection de la classe → grille présent/absent/retard/excusé par élève → soumission.
- **Règle métier critique** : une fois l'appel soumis pour une date donnée, il est **définitivement verrouillé** — aucune route de modification/suppression n'existe dans `TeacherAttendanceController`. En cas d'absence, une notification WhatsApp est envoyée au parent si son numéro et sa langue préférée sont renseignés et si la fonctionnalité est activée (`NOTIFY_PARENTS_ENABLED`).
- **État** : ✅ Implémenté (le verrouillage est une décision de conception assumée, pas un bug).

### Bulletin élève (`student/bulletin.blade.php`, `bulletin-senegal.blade.php`, `bulletin-annuel.blade.php`)

- **Objectif** : consulter/imprimer le bulletin de notes officiel.
- **Utilisateurs** : `eleve`, et en export PDF par lot pour `admin`.
- **Fonctionnalités** : moyennes par matière pondérées par coefficient, moyenne générale, appréciations, mention, décision (admis/redouble), QR code de vérification d'authenticité.
- **Données** : calculées par `BulletinComputation` (moteur unique partagé avec les exports admin et le chatbot).
- **Sécurité** : le QR code pointe vers une route signée (`bulletin.verify`) qui recalcule les données côté serveur plutôt que de faire confiance au contenu du PDF — anti-fraude réel.
- **État** : ✅ Implémenté, y compris pour le format sénégalais spécifique.

### Guichet Caisse (`accounting/caisse/guichet.blade.php`, `pay.blade.php`)

- **Objectif** : encaisser un paiement de frais scolaires au comptant.
- **Utilisateurs** : `caissier`.
- **Workflow** : ouverture de session de caisse (obligatoire, une seule session ouverte à la fois) → recherche de l'élève → consultation de sa situation financière (factures dues) → saisie du paiement (méthode : espèces/Wave/Orange Money/virement/chèque), ventilation automatique sur les factures dues → génération d'un reçu numéroté séquentiellement, imprimable au format ticket 80mm.
- **Règles métier** : verrouillage transactionnel (`lockForUpdate`) des factures et du compteur de reçu pour éviter toute collision entre caissiers simultanés ; le paiement ne peut jamais dépasser le solde dû.
- **État** : ✅ Implémenté, avec garde-fous transactionnels réels.

### Tableau de bord Directeur financier (`accounting/directeur/dashboard.blade.php`)

- **Objectif** : vision consolidée de la santé financière de l'établissement.
- **Fonctionnalités** : graphique en courbes recettes vs dépenses sur 6 mois, graphique en secteurs de répartition des dépenses par catégorie, indicateurs (solde, taux de paiement, masse salariale, élèves débiteurs).
- **Données** : lues **exclusivement** depuis le journal comptable (`LedgerEntry`), jamais recalculées depuis les tables `payments`/`expenses` — garantie documentée d'exactitude dashboard = vérité comptable.
- **État** : ✅ Implémenté.

<div class="page-break"></div>

## 7. Tableaux de bord

| Dashboard | Rôle | Indicateurs identifiés dans le code |
|---|---|---|
| Plateforme | `super_admin` | Graphique d'évolution des inscriptions (toutes écoles), graphique de répartition des rôles utilisateurs, statistiques cross-écoles (`PlatformMetrics`) |
| Admin d'école | `admin`/`surveillant` | Effectifs élèves/enseignants/classes, synthèses académiques |
| Enseignant | `teacher` | Graphique de performance par matière, courbe d'évolution filtrable par classe/matière |
| Élève | `eleve` | Synthèse notes/présences récentes |
| Directeur financier | `directeur` | Courbe recettes/dépenses (6 mois), camembert dépenses par catégorie, solde, taux de paiement, masse salariale, élèves débiteurs — alimentés uniquement par `LedgerEntry` (`AccountingDashboardService`) |
| Comptable | `comptable` | KPI financiers du périmètre comptable (dépenses, salaires, paiements) |
| Caissier | `caissier` | Vue guichet : état de session de caisse, historique du jour |

Tous les graphiques identifiés sont rendus via **Chart.js** (canvas), présents dans : dashboard admin, dashboard enseignant, dashboard plateforme, dashboard directeur, fiche élève (évolution des notes), fiche classe (évolution de classe).

<div class="page-break"></div>

## 8. Module Élèves

### 8.1 Fonctionnalités réellement implémentées

- **Inscription/création** : formulaire admin (`StudentController::store`) générant un identifiant unique par école (préfixe « E »), un mot de passe aléatoire, upload photo/acte de naissance, et déclenchement de l'événement `StudentEnrolled`.
- **Génération automatique des factures à l'inscription** : si le module Comptabilité est actif, `GenerateStudentInvoicesOnEnrollment` crée automatiquement les factures de frais applicables (`StudentInvoiceService`).
- **Import en masse** (Excel/CSV) avec assistant 4 étapes, détection de doublons, mapping intelligent des colonnes.
- **Profil élève** : informations personnelles, classe, historique de notes, statistiques de présence.
- **Recherche** : recherche multi-mots (nom/identifiant/email) avec suggestions floues en repli (`StudentSearch`).
- **Affectation/désaffectation de classe** : individuelle ou en masse, verrouillée si l'année académique est clôturée.
- **Promotion de classe** : moteur complet de règles de passage (CI→CM2→6ème...→Terminale), calcul de moyenne normalisée, détection de fin de cycle, redoublement, exécutable par élève/par classe/pour toute l'année (`StudentClassPromotionService`), réservé aux établissements classiques (exclut les écoles « formation »).
- **Envoi des identifiants de connexion** par email, avec diagnostics d'erreurs SMTP spécifiques (Brevo).
- **Export** Excel/CSV des élèves (filtrable par classe).
- **Carte scolaire numérique** avec QR code de vérification publique.
- **Suivi des paiements** (si module Comptabilité actif) côté élève.

### 8.2 Fonctionnalités partiellement implémentées

- **Justification d'absence** : une colonne `justified`/`justification_status` existe et est lue (statistiques), mais aucune route/contrôleur ne permet de la modifier — la fonctionnalité de justification d'absence par un parent/élève n'a pas de chemin d'écriture identifié.

### 8.3 Fonctionnalités prévues / potentielles

- Portail parent dédié (aujourd'hui, seule la notification WhatsApp existe ; aucun compte « parent » avec son propre login n'a été trouvé).
- Suivi comportemental/disciplinaire structuré (aucun module « vie scolaire/discipline » distinct identifié — voir §14/§30).

<div class="page-break"></div>

## 9. Module Enseignants

### Fonctionnalités implémentées

- **Import en masse** (Excel/CSV), sans import automatique des classes (assignées manuellement après import, car dépendantes de l'emploi du temps).
- **Génération d'identifiants/mot de passe**, envoi d'email optionnel (désactivé par défaut, contrôlé par `TEACHER_SEND_CREDENTIALS_EMAIL`).
- **Affectation classes/matières** : deux mécanismes coexistants — pivot direct `class_teacher` (professeur principal de primaire) et table `TeacherAssignment` (classe × matière × année, secondaire, plusieurs enseignants possibles).
- **Résolution d'accès** (`TeacherClassResolver`/`TeacherSubjectResolver`) : garde anti-IDOR unifiant les deux mécanismes ci-dessus, avec prise en compte des matières désactivées par niveau/classe.
- **Fiche enseignant**, **gestion des affectations**, **emploi du temps**, **salaire** (si module actif).

### État

✅ Implémenté de façon mature, avec correction documentée d'un bug historique (enseignants de secondaire voyant 0 classes).

<div class="page-break"></div>

## 10. Module Classes / Formation

### 10.1 Classes (établissements classiques)

- Création/édition de classes rattachées à un niveau (`Level`) et une année académique, avec normalisation automatique du nom (6ème, Terminale, etc.), capacité, salle.
- Groupes de classes (`ClassGroup`) pour subdiviser une classe en sous-groupes (ex. groupes de TP).
- Attribution de matières par classe (`class_subject`, avec activation/désactivation par classe).
- Statistiques de classe (`ClassStatisticsService`), graphique d'évolution des moyennes de classe.

### 10.2 Formation (LMD / enseignement supérieur)

- **`FormationDepartment`** (département) → **`FormationPromotion`** (cohorte : filière + type de diplôme + année de formation, rattachée à une année académique) → **`SchoolClass`** (« groupes », ex. BEP1-1, MC2-1).
- Détection automatique du type de classe formation (`SchoolClass::isFormationGroup()`).
- Paramétrage LMD par établissement (`admin.formation.lmd-settings`) : pondération contrôle continu / examen, note minimale de validation.
- Provisionnement d'année dédié pour les écoles formation (`FormationAcademicYearProvisioner`), progression automatique d'année de diplôme.

### État

✅ Implémenté pour les deux subsystèmes ; ils partagent les mêmes tables sous-jacentes (`levels`, `classes`), distingués par le champ `establishment_type` de l'école et des colonnes optionnelles.

<div class="page-break"></div>

## 11. Module Notes et évaluations

### 11.1 Séquences d'évaluation

- **Secondaire** : séquence fixe Devoir 1 → Devoir 2 → Composition, par semestre, notée sur 20.
- **Primaire** : nombre de « compositions » configurable (jusqu'à 6) et barème configurable (souvent /10), paramétré par niveau/matière via `admin.primary-grading`.
- **Formation (LMD)** : Contrôle Continu (CC) + Examen, pondérés selon un ratio configurable par établissement.

### 11.2 Formules de calcul (implémentées)

- Secondaire : `moyenne_matière = 50 % moyenne des devoirs + 50 % composition` (repli sur la seule valeur disponible si l'une manque).
- Primaire : moyenne simple des compositions renseignées.
- Formation/LMD : moyenne pondérée CC/Examen par module, validée si ≥ note minimale configurée, avec repli gracieux si un seul type de note est disponible.
- Moyenne générale pondérée par coefficients de matière, ramenée en ratio pour gérer les barèmes hétérogènes (10 en primaire, 20 sinon).

### 11.3 Bulletins

- Génération PDF (DomPDF) : bulletins semestriels et annuels (mono-feuille pour le primaire, deux feuilles S1+S2 pour le secondaire), feuilles de composition (primaire), rapport de fin d'année (classement/décision/mention).
- QR code de vérification publique signé sur chaque bulletin.
- Export CSV (résumé semestriel, résumé annuel, notes brutes).

### 11.4 Règles métier

- Une note ne peut être corrigée qu'une seule fois après saisie initiale par l'enseignant.
- Le semestre courant est déterminé automatiquement par la date (mois ≥ 10 ou ≤ 1 → semestre 1, sinon semestre 2) — **pas de configuration par établissement**.
- Les matières désactivées pour une classe/niveau sont exclues du calcul de moyenne.

### État

✅ Implémenté et particulièrement mature (module le plus documenté du code, avec de nombreuses corrections de bugs référencées dans les commentaires).

<div class="page-break"></div>

## 12. Module Présence / Absences

### Fonctionnalités implémentées

- Saisie de l'appel par l'enseignant (statuts : présent/absent/retard/excusé), avec verrouillage définitif après enregistrement (pas d'édition ni de suppression possible).
- Vérification anti-manipulation : chaque élève soumis doit réellement appartenir à la classe.
- Historique consultable par classe (enseignant, admin) et par élève.
- Statistiques de présence sur la fiche élève (compteurs + taux).
- Décompte des absences par semestre intégré au bulletin.
- Notification WhatsApp automatique au parent en cas d'absence (si activée et numéro renseigné).

### Fonctionnalité partiellement implémentée

- **Justification d'absence** : colonne `justification_status` existante et lue, mais aucun chemin d'écriture (formulaire/route) identifié pour la faire évoluer — 🟡 partiel.

### Fonctionnalité non trouvée dans le code

- Pas de workflow de « retard » distinct avec calcul de minutes cumulées exploité en dehors du champ brut `minutes_late` (colonne présente, pas de vue dédiée identifiée qui l'exploite).

<div class="page-break"></div>

## 13. Module Finances / Comptabilité

C'est le module le plus rigoureusement conçu du projet (verrouillages transactionnels, idempotence, aucune suppression destructrice). Activable/désactivable indépendamment par établissement (`schools.toggle-accounting-module`).

### 13.1 Configuration des frais

- **`FeeType`** (type de frais : inscription, réinscription, mensualité, examen, autre) et **`FeeAmount`** (montant par type × année académique × niveau, ou « tous niveaux » si niveau non précisé) — configurés par le directeur (`directeur.fee-types.*`).

### 13.2 Facturation automatique

- **`StudentInvoiceService`** génère automatiquement les factures élèves : une facture pour un frais non récurrent, une facture par mois pour un frais récurrent (mensualité), de façon idempotente (jamais de doublon). Déclenché à l'inscription et disponible en commande console.

### 13.3 Cycle de paiement

1. Le caissier ouvre une session de caisse (une seule session ouverte à la fois par registre de caisse).
2. `PaymentService::recordPayment()` verrouille les factures ciblées, valide que chaque montant n'excède pas le solde dû, crée un `Payment` avec numéro de reçu séquentiel par établissement (`ReceiptNumberGenerator`, format `REC-{école}-{année}-{00001}`), ventile le montant sur une ou plusieurs factures via `PaymentAllocation`, et met à jour le statut de chaque facture (en attente → partiel → payé). Tout est transactionnel.
3. Annulation (`cancel()`) : ne supprime jamais — repasse le paiement en statut `cancelled` avec traçabilité complète (qui, quand, pourquoi), et restaure le solde des factures concernées.
4. Clôture de session : calcul automatique du solde attendu (ouverture + paiements complétés de la session) comparé au solde réel saisi par le caissier ; en cas d'écart > 0,01, notification automatique à tous les directeurs financiers.

### 13.4 Journal comptable (grand livre)

- **`LedgerEntry`** : journal **immuable**, alimenté exclusivement par des Observers Eloquent (jamais par un contrôleur directement) sur `Payment`, `Expense`, `SalaryPayment`. Chaque écriture porte un type (recette/dépense), une source polymorphe, un montant signé ; une annulation génère une écriture de contre-passation plutôt qu'une modification de l'écriture d'origine.
- Le tableau de bord financier ne lit **que** ce journal, garantissant que les indicateurs affichés reflètent toujours la réalité comptable.

### 13.5 Dépenses

- Enregistrement avec justificatif (upload), catégorisation (fournitures, matériel, internet, électricité, eau, entretien, prime, autre), jamais supprimées (annulation uniquement).

### 13.6 Salaires

- **`EmployeeSalaryProfile`** : historique immuable du salaire mensuel d'un employé (toute modification ferme la période active et ouvre une nouvelle ligne, jamais de mise à jour en place).
- **`SalaryPaymentService::generateForPeriod()`** génère une fiche de paiement par employé actif et par mois, de façon idempotente. Paiement partiel supporté avec verrouillage anti-collision. Annulation avec traçabilité, jamais de suppression.
- Le directeur peut exceptionnellement payer directement un salaire (dérogation documentée à la séparation des tâches, chaque paiement enregistrant qui l'a exécuté).

### 13.7 Reçus

- Reçus de paiement et de salaire imprimables au format ticket thermique 80mm (`ThermalTicketPdf`, algorithme dédié pour éviter les pages blanches), recherche de reçu, page de vérification publique.

### État

✅ Implémenté de façon complète et défensive (verrouillages, idempotence, intégrité transactionnelle, piste d'audit systématique). Aucun stub identifié dans ce module.

<div class="page-break"></div>

## 14. Module Parents

**Constat factuel** : il n'existe **aucun rôle « parent » avec compte de connexion dédié** dans le code (`User::role` ne comporte pas de valeur « parent »). L'interaction avec les parents se limite à :

- Des champs de contact sur le compte élève (`parent_name`, `parent_whatsapp`, `parent_lang`).
- Des **notifications WhatsApp automatiques** sortantes (absence de l'enfant), sans portail de consultation entrant pour le parent.

### État

🟡 **Partiellement implémenté** : canal de communication sortant réel et fonctionnel (voir §15), mais **pas de portail parent** (consultation notes/bulletins/paiements par le parent lui-même). Un véritable « espace parent » est une fonctionnalité **prévue/potentielle**, pas existante.

<div class="page-break"></div>

## 15. Module Communication

### Fonctionnalités implémentées

- **Notifications WhatsApp** via l'API tierce **UltraMsg** (`app/Services/NotificationService.php`), réellement intégrée (pas un simulacre) :
  - Message texte en français, ou message **audio pré-enregistré** en wolof/pular selon la langue préférée du parent (`parent_lang`), avec repli automatique et journalisé vers le texte français si le fichier audio n'existe pas.
  - Événement câblé et déclenché en production : **absence** (depuis `TeacherAttendanceController`).
  - Événements définis dans le code mais **sans déclencheur identifié** : retard, note publiée, réunion — chemins de code présents mais non utilisés actuellement (🟡 latents/inutilisés).
  - Envoi asynchrone via file d'attente (`SendWhatsAppNotification` Job, 3 tentatives).
  - Interrupteur global : `NOTIFY_PARENTS_ENABLED` (désactivé par défaut dans `.env.example`).
- **Notifications internes (cloche)** : table `notifications` standard Laravel, marquage lu/tout lire, utilisée pour alerter (ex. écart de caisse détecté, note modifiée par un enseignant).
- **Emails transactionnels** : identifiants élève/enseignant, OTP personnel comptable, alerte de modification de note — via Brevo (SMTP), avec diagnostics d'erreurs spécifiques.

### Fonctionnalités prévues / potentielles

- SMS, notifications push mobiles, messagerie interne bidirectionnelle (aucune trouvée dans le code).

<div class="page-break"></div>

## 16. Module E-learning (LMS) et classes virtuelles

Module réel et fonctionnel, pas une simple maquette.

### 16.1 Cours (Lessons)

- Dépôt de fichiers (PDF/DOC, 20 Mo max) ou lien vidéo/externe par matière/classe, publication immédiate (pas de brouillon différé implémenté malgré la présence des champs `is_published`/`published_at`).

### 16.2 Devoirs (Assignments / Submissions)

- Création de devoirs (échéance, barème 1-100), dépôt de copie par l'élève, notation + retour par l'enseignant (statuts brouillon/publié/noté).

### 16.3 Quiz (QCM)

- Jusqu'à 30 questions par quiz, jusqu'à 5 options par question.
- **3 tentatives maximum par élève**, comptage protégé par verrouillage transactionnel (contournement spécifique Postgres documenté dans le code), reprise automatique d'une tentative inachevée, correction automatique, page de résultat avec tentatives restantes.

### 16.4 Classes virtuelles

- Intégration **Jitsi Meet** réelle (pas de serveur média propre requis) : planification (titre, classe, matière, date/heure, durée, mot de passe optionnel), génération d'un nom de salle unique, ouverture/fermeture par l'enseignant, rejoint par élève/enseignant via iframe intégrée.

### 16.5 Supervision

- Vue d'ensemble admin (statistiques de dépôts/notation, suppression de contenu) sur l'ensemble de l'école.

### État

✅ Implémenté — module compact mais complet (cours, devoirs notés, quiz avec limite de tentatives, visioconférence).

<div class="page-break"></div>

## 17. Documents, rapports et exports

| Document | Format | Générateur | Public visé |
|---|---|---|---|
| Bulletin semestriel/annuel (par élève et par classe) | PDF | `BulletinReportService` + DomPDF | Élève, admin |
| Feuille de composition (primaire) | PDF | idem | Admin |
| Rapport annuel de classe (classement/décision/mention) | PDF paysage | idem | Admin |
| Export CSV notes brutes / résumé semestriel / résumé annuel | CSV (BOM + `;`, compatible Excel FR) | `ReportController` | Admin |
| Carte scolaire élève | PDF/HTML + QR code | `Admin\AdminCardController` / `StudentCardController` | Admin, élève |
| Reçu de paiement / reçu de salaire | PDF ticket thermique 80mm | `ThermalTicketPdf` | Caissier, comptable, directeur |
| Export élèves (liste) | Excel/CSV | `StudentsExport` (Maatwebsite Excel) | Admin |
| Identifiants générés (élèves/enseignants) après import | Excel | `*ImportCredentialsExport` | Admin |
| Export du grand livre comptable | CSV/PDF | `Accounting\DirecteurDashboardController::exportLedger` | Directeur |

Tous les PDF sont produits par **barryvdh/laravel-dompdf** ; les bulletins et cartes intègrent un QR code (**simplesoftwareio/simple-qrcode**) pointant vers une route Laravel signée, vérifiée côté serveur — pas de confiance aveugle dans le contenu du fichier téléchargé.

<div class="page-break"></div>

## 18. Assistant IA / Chatbot

Trois éléments distincts coexistent dans le dépôt — il est important de ne pas les confondre.

### 18.1 API de statistiques « SchoolBot » (réellement utilisée)

`app/Services/SchoolBot/*` + `Api\SchoolBotController` : une **API JSON en lecture seule**, protégée par un jeton secret partagé (`SCHOOL_BOT_SECRET`, middleware `EnsureSchoolBotSecret`), destinée à alimenter un **agent externe Botpress**. Endpoints : statistiques globales, redoublants (heuristique documentée, faute de champ dédié), taux de réussite (recalculés avec le même moteur que les bulletins, `BulletinComputation`, pour garantir la cohérence), recherche d'élèves/utilisateurs.

### 18.2 Widget de chat React (interface utilisateur)

`resources/js/agent-widget.jsx` + `AgentChat.jsx` : une bulle de chat flottante montée dans les layouts admin/plateforme, qui envoie les questions à un **microservice Node externe** (`VITE_AI_AGENT_URL`, `http://localhost:3000/api/agent` par défaut) — ce microservice Node **n'est pas présent dans ce dépôt**.

### 18.3 Prototype Python (`ai_service/`) — probablement abandonné

`ai_service/app_ai.py` et `app_ai_final.py` : un prototype Flask + LangChain + Google Gemini, connecté directement à une base **MySQL**, générant du SQL en langage naturel. **Incohérent avec le reste du projet**, qui utilise PostgreSQL (nombreuses requêtes `ilike` dans le code Eloquent). Aucune route, configuration ou fichier JS du dépôt ne référence ce service. **Bug confirmé** : dans `app_ai_final.py`, la fonction du endpoint `/chat` référence la variable `sql_query` avant toute affectation (la fonction `generate_sql_query()` n'est jamais appelée) — ce code plante sur toute requête non triviale.

### Conclusion

L'intégration IA réellement opérationnelle est l'ensemble **(A) API SchoolBot + (B) widget React**, destinée à un agent Botpress externe non inclus dans ce dépôt. Le dossier `ai_service/` est un **prototype expérimental cassé, non branché**, à traiter comme code mort — recommandation : le retirer du dépôt ou le documenter clairement comme archivé (voir §30).

<div class="page-break"></div>

## 19. Authentification et sécurité

### 19.1 Authentification

- Laravel Breeze (guard `web`, session), un seul provider (`users`, modèle `App\Models\User`).
- **Aucun guard API/token** configuré (pas de Sanctum/Passport) — cohérent avec l'absence d'API REST publique (la seule route API utilise un jeton secret partagé, pas un guard Laravel).
- Réinitialisation de mot de passe standard (jeton, expiration 60 min), reconfirmation de mot de passe pour les actions sensibles (fenêtre de 3h).
- OTP dédié pour la création de comptes du personnel comptable (`StaffOtpMail`/`StaffOtpMailer`).

### 19.2 Autorisation

- **Double mécanisme coexistant, documenté comme transitoire dans le code** :
  1. Middlewares personnalisés par chaîne de rôle (`school.admin`, `super_admin`, `TeacherMiddleware`, `StudentMiddleware`, `accounting.role:<role>`) — **c'est le mécanisme réellement appliqué sur toutes les routes**.
  2. Rôles et permissions **Spatie laravel-permission** — provisionnés (`RolePermissionSeeder`), synchronisés automatiquement à chaque sauvegarde d'utilisateur (`syncRoleFromColumn`), mais **non branchés** sur les routes (aucun middleware `permission:`, aucun appel `$user->can()` trouvé dans les contrôleurs analysés). Le seeder documente lui-même cet état comme une fondation en vue d'une migration future vers des Policies — un écart assumé, pas un oubli caché.
- `Gate::before()` accorde un accès total inconditionnel au rôle `super_admin`.

### 19.3 Multi-tenant (isolation entre établissements)

- Isolation par **colonne `school_id`** (base de données partagée, schéma partagé) plutôt que par base de données séparée par établissement.
- Un **scope global Eloquent** (`SchoolScope`, activé via le trait `BelongsToSchool`) filtre automatiquement toute requête sur les modèles concernés par le `school_id` de l'utilisateur en session (`TenantSchool`, alimenté par le middleware `SyncTenantSchoolSession`).
- Le `super_admin` a le filtrage désactivé (accès à toutes les écoles).
- **Incohérence relevée** : `QuizAttempt` possède une colonne `school_id` mais n'utilise pas le trait `BelongsToSchool` — non auto-filtré par le scope (à corriger).
- Suppression d'une école bloquée au niveau base de données par des contraintes `restrictOnDelete()` sur `school_id` pour la plupart des tables tenant (empêche une suppression en cascade destructrice) — la suppression réelle passe par un contrôleur qui refuse les écoles non vides, ou par une commande console dédiée aux jeux de données de test.

### 19.4 Suspension d'établissement

- `EnsureSchoolIsActive` : si l'école d'un utilisateur est marquée inactive par la plateforme, **déconnexion forcée immédiate** avec message d'erreur — mécanisme réel et cohérent avec le contrôle plateforme (§4.1).

### 19.5 Protection applicative

- **En-têtes de sécurité** (`SecurityHeaders` middleware) : CSP stricte, HSTS (1 an, sous-domaines inclus), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy` désactivant caméra/micro/géolocalisation/paiement, suppression des en-têtes `X-Powered-By`/`Server`.
  - ⚠️ **Point à surveiller** : la CSP autorise explicitement `http://localhost:3000` (origine du microservice IA de développement) en `connect-src` — probablement un oubli de nettoyage avant mise en production (voir §30).
- **Assainissement des entrées** (`SanitizeInput` middleware, global) : `strip_tags()` récursif sur toutes les chaînes de la requête, à l'exception des champs mot de passe.
- **Chiffrement au repos** : certains champs personnels de l'utilisateur (téléphone, adresse, téléphone du tuteur, WhatsApp parent) sont chiffrés via les casts Eloquent `encrypted`.
- **Journal d'audit** (spatie/laravel-activitylog) sur les entités académiques et financières principales (voir §21), avec présentation humanisée en français (`ActivityLogPresenter`) — **non couvert** : entités LMS (cours/devoirs/quiz/soumissions) et `TeacherAssignment`.
- **Liens signés Laravel** pour la vérification publique de bulletins/cartes/reçus — mécanisme cryptographique standard, réutilisé de façon cohérente.
- Aucune tentative d'injection SQL directe identifiée (usage exclusif de l'ORM Eloquent / requêtes paramétrées dans le code revu).

### 19.6 Non-vérifiable / non couvert dans cet audit

- Politique de complexité de mot de passe précise, verrouillage anti-brute-force au-delà du `RateLimiter` du login (`LoginRequest`) — non détaillée davantage dans cette analyse.
- Tests de pénétration ou scan de vulnérabilités automatisé — **information non identifiable dans l'état actuel du projet** (aucun outil de ce type détecté dans le dépôt).

<div class="page-break"></div>

## 20. Architecture technique

### 20.1 Stack technique (issu de `composer.json` / `package.json`)

| Couche | Technologie |
|---|---|
| Backend | PHP 8.2+, **Laravel 12** |
| Base de données | SQLite (dev par défaut), **PostgreSQL** (production — évident à l'usage massif de `ilike`), MySQL supporté en configuration alternative |
| ORM | Eloquent (natif Laravel) |
| Frontend | **Blade** (rendu serveur) pour l'essentiel de l'UI + **React 19** en îlot pour le widget de chat IA (via Vite) |
| Build frontend | **Vite 7**, `laravel-vite-plugin`, `@vitejs/plugin-react` |
| Bibliothèques JS notables | FullCalendar (calendrier), Axios |
| PDF | `barryvdh/laravel-dompdf` |
| QR codes | `simplesoftwareio/simple-qrcode` |
| Excel/CSV | `maatwebsite/excel` |
| Rôles & permissions | `spatie/laravel-permission` |
| Journal d'activité | `spatie/laravel-activitylog` |
| Sauvegardes | `spatie/laravel-backup` |
| Authentification | `laravel/breeze` |
| PWA | Service Worker maison (`public/sw.js`), manifest (`public/manifest.json`) |
| Visioconférence | Jitsi Meet (service tiers externe, sans infrastructure médiatique propre) |
| WhatsApp | API tierce UltraMsg |
| Email | SMTP via Brevo |

### 20.2 Communication entre couches

```
Navigateur ─HTTP/Session─▶ Middlewares (sécurité, tenant, rôle) ─▶ Contrôleur
      ▲                                                                │
      │                                                                ▼
   Vue Blade ◀────────────── Services métier (Payment, Bulletin, ...) ◀── Modèles Eloquent ──▶ PostgreSQL/SQLite
      │
      └── Widget React (îlot) ──HTTP──▶ Microservice Node externe (hors dépôt) ──▶ (LLM externe éventuel)

App externe Botpress ──Bearer token──▶ /api/bot/school/* (Api\SchoolBotController, lecture seule)
```

### 20.3 PWA / offline

- **Service Worker maison** (`public/sw.js`, v6) : stratégie *cache-first* pour les assets statiques/pédagogiques, *network-first* pour les pages HTML, page de secours hors-ligne (`offline.html`).
- **Manifest** (`public/manifest.json`) : app installable (`display: standalone`), point d'entrée `/admin`, icônes 192/512.
- Un pré-cache référence des CDN externes (Bootstrap, jQuery, FontAwesome, Google Fonts) — dépendance à ces CDN pour le fonctionnement hors-ligne initial.

### 20.4 Déploiement observé

- `vercel.json` présent (déploiement Vercel possible pour les assets statiques buildés, `outputDirectory: public/build`) — usage exact non confirmable sans accès à la configuration Vercel réelle du projet.
- Configuration mentionnée dans `.env.example` pour un déploiement mutualisé type **InfinityFree** (MySQL) et pour **Laravel Cloud** (PostgreSQL, variables auto-injectées).

<div class="page-break"></div>

## 21. Architecture des données

### 21.1 Modèle conceptuel (résumé)

```
School (tenant racine)
 ├─< User (rôle : super_admin/admin/surveillant/teacher/eleve/directeur/comptable/caissier)
 ├─< AcademicYear ──< SchoolClass, TeacherAssignment, FeeAmount, StudentInvoice, FormationPromotion
 ├─< Level ──< SchoolClass ; Level ─(pivot)─ Subject
 ├─< Subject ──< Grade, Attendance, TeacherAssignment, Lesson, Assignment, Quiz
 ├─< SchoolClass ──< User(élèves), TeacherAssignment, ClassGroup, Schedule, Lesson, Assignment, Event, VirtualClass
 ├─< FormationDepartment ──< FormationPromotion ──< SchoolClass
 ├─< FeeType ──< FeeAmount
 ├─< StudentInvoice ──< PaymentAllocation ──▶ Payment
 ├─< CashRegister (1 par utilisateur) ──< CashSession ──< Payment, Expense, SalaryPayment
 ├─< Expense, EmployeeSalaryProfile ──< SalaryPayment
 ├─< LedgerEntry (source polymorphe : Payment | Expense | SalaryPayment)
 ├─< AccountingCounter (compteurs séquentiels, ex. numérotation des reçus)
 └─< Lesson, Assignment ──< Submission ; Quiz ──< Question ──< QuestionOption ; Quiz ──< QuizAttempt
```

### 21.2 Entités principales

| Entité | Rôle | Attributs clés | Relations principales |
|---|---|---|---|
| `School` | Tenant racine | code, is_active, establishment_type/category, enabled_modules, formation_lmd_settings | hasMany users/classes/academicYears/subjects |
| `User` | Compte (tous rôles) | role, status, identifier, school_id, class_id | school, class, teacherAssignments, grades, attendances, studentInvoices |
| `AcademicYear` | Année scolaire | is_current, is_closed | hasMany classes, teacherAssignments |
| `Level` | Niveau/cycle | cycle (primaire/college/lycee/formation), serie | hasMany classes ; pivot subjects |
| `SchoolClass` | Classe / promotion | name, capacity, formation_* | belongsTo Level/AcademicYear ; hasMany students, TeacherAssignment |
| `Subject` | Matière | coefficient, lmd_settings | pivot levels, teachers ; hasMany grades |
| `Grade` | Note | grade, type, semester, teacher_edited_at | belongsTo user, subject |
| `Attendance` | Présence | status, justification_status | belongsTo user, subject, class |
| `FeeType`/`FeeAmount` | Catalogue de frais | category, is_recurring, amount | scoped par année/niveau |
| `StudentInvoice` | Facture élève | amount_due, amount_paid, status | hasMany PaymentAllocation |
| `Payment` | Encaissement | receipt_number, status, method | hasMany PaymentAllocation |
| `CashSession`/`CashRegister` | Session de caisse | opening/closing balance, difference | hasMany Payment |
| `Expense` | Dépense | category, status | — |
| `EmployeeSalaryProfile`/`SalaryPayment` | Salaire | monthly_amount, period | historique immuable |
| `LedgerEntry` | Écriture comptable | entry_type, source_type/id, amount (signé) | morphTo source |
| `Lesson`/`Assignment`/`Submission`/`Quiz`/`Question`/`QuestionOption`/`QuizAttempt` | LMS | — | chaîne pédagogique |
| `VirtualClass` | Classe virtuelle | room_name, scheduled_at | belongsTo teacher/class/subject |

### 21.3 Multi-tenant : détail

Voir §19.3. Colonnes `school_id` obligatoires (`NOT NULL`) sur la quasi-totalité des tables métier depuis la migration `enforce_school_id_not_null_on_tenant_tables`.

### 21.4 Intégrité et suppression

- Aucune suppression physique (« hard delete ») des enregistrements financiers — modèle « annulation, jamais suppression » uniforme sur `Payment`, `Expense`, `SalaryPayment`.
- Contraintes de clé étrangère à trois niveaux : `restrictOnDelete()` (protège l'historique — `school_id`, factures, profils de salaire), `cascadeOnDelete()` (enregistrements strictement possédés — allocations de paiement, questions de quiz), `nullOnDelete()` (liens optionnels d'audit — `cancelled_by`, `cash_session_id`).
- Pas de suppression logique (`SoftDeletes`) utilisée dans le code applicatif, malgré une colonne `deleted_at` présente sur `class_groups` (incohérence mineure relevée).

<div class="page-break"></div>

## 22. API et flux de données

### 22.1 API existante

La seule surface API HTTP du projet est **`routes/api.php`**, préfixe `bot/school`, protégée par un jeton secret partagé (pas de guard Laravel) :

| Méthode | Route | Rôle/accès | Objectif |
|---|---|---|---|
| GET | `/api/bot/school/stats` | Bearer secret | Statistiques globales d'une école |
| GET | `/api/bot/school/stats/repeaters` | Bearer secret | Estimation des redoublants |
| GET | `/api/bot/school/stats/outcomes` | Bearer secret | Taux de réussite/échec |
| GET | `/api/bot/school/students/search` | Bearer secret | Recherche d'élèves |
| GET | `/api/bot/school/users/search` | Bearer secret | Recherche d'utilisateurs |
| GET | `/api/bot/school/students/{id}` | Bearer secret | Détail d'un élève |

Il n'existe **aucune API REST publique** destinée à des clients tiers ou une application mobile — toute l'application fonctionne en rendu serveur (Blade) avec formulaires classiques, à l'exception de ce point d'entrée dédié à un agent conversationnel externe.

### 22.2 Flux type

```
Utilisateur → Formulaire Blade (POST) → Middleware(s) sécurité/tenant/rôle → Contrôleur
   → Service métier (validation, transaction, verrous) → Modèle Eloquent → Base de données
   → Observer (le cas échéant, ex. LedgerEntry) → Redirection + message flash → Vue Blade
```

<div class="page-break"></div>

## 23. Workflows métiers

### 23.1 Inscription d'un élève

1. L'admin ouvre le formulaire de création d'élève.
2. Saisie identité + classe + informations tuteur, upload photo/acte de naissance.
3. Le système génère un identifiant unique et un mot de passe.
4. Enregistrement → événement `StudentEnrolled`.
5. Si module Comptabilité actif : génération automatique des factures de frais applicables à l'année en cours.
6. Les identifiants sont affichés une fois à l'admin (jamais stockés en clair) et peuvent être envoyés par email.

### 23.2 Saisie d'une note

1. L'enseignant sélectionne une classe et une matière (vérification d'affectation).
2. Grille de saisie par élève selon la séquence d'évaluation du cycle.
3. Enregistrement de la note.
4. Une correction ultérieure est possible **une seule fois** ; elle déclenche une notification (email + in-app) vers l'administration.

### 23.3 Appel de présence

1. L'enseignant choisit sa classe.
2. Statut par élève (présent/absent/retard/excusé).
3. Soumission → verrouillage définitif de cette date pour cette classe.
4. Notification WhatsApp au parent en cas d'absence (si configuré).

### 23.4 Paiement des frais scolaires

1. Le caissier ouvre sa session de caisse (obligatoire).
2. Recherche de l'élève, consultation du solde dû.
3. Saisie du paiement (méthode, montant), ventilation automatique sur les factures dues.
4. Génération d'un reçu numéroté, imprimable.
5. Journal comptable mis à jour automatiquement (écriture « recette »).
6. Clôture de session : rapprochement attendu/réel, alerte au directeur en cas d'écart.

### 23.5 Génération d'un bulletin

1. Fin de période (semestre/année).
2. `BulletinComputation` calcule les moyennes par matière (pondérées par coefficient), la moyenne générale, l'appréciation, la mention, la décision.
3. Rendu PDF avec QR code de vérification signé.
4. Consultation par l'élève, export en lot par l'admin (par classe).

### 23.6 Promotion de fin d'année

1. L'année académique doit être **clôturée** (`is_closed`).
2. Le moteur (`StudentClassPromotionService`) calcule la moyenne annuelle normalisée de chaque élève.
3. Comparaison au seuil de passage (configurable, 10/20 par défaut).
4. Affectation automatique à la classe de niveau supérieur (avec correspondance de groupe A/B/C), gestion des redoublants et des sorties de cycle (fin de Terminale, fin de CM2 en établissement primaire-only).
5. Réservé aux établissements à notation classique (exclut les écoles « formation »/LMD).

<div class="page-break"></div>

## 24. UX/UI

### 24.1 Navigation

- Chaque portail dispose d'une **barre latérale (sidebar)** dédiée et contextualisée par rôle (listée en détail §5/§6), plus une barre de navigation supérieure partagée (`partials/portal-top-navbar.blade.php`).
- Widget de chat IA flottant intégré dans les layouts admin/plateforme (`partials/ai-agent-widget.blade.php`).

### 24.2 Composants réutilisables identifiés

- Composants Blade génériques : `text-input`, `input-label`, `input-error`, `primary-button`, `secondary-button`, `danger-button`, `auth-session-status`, `admin/form-field`.
- Modales pour actions ponctuelles (listes classes/enseignants/élèves, checklists de salaire).
- Filtres de période/année académique réutilisables (`partials/dashboard-year-filter.blade.php`).
- Sauvegarde automatique de brouillon de formulaire (`public/js/form-draft-autosave.js` + `partials/form-draft-meta.blade.php`).

### 24.3 Graphiques

Chart.js utilisé de façon cohérente sur tous les tableaux de bord et fiches disposant d'indicateurs chiffrés (voir §7).

### 24.4 Évaluation professionnelle

**Points positifs** : séparation claire des portails par rôle, cohérence des sidebars, réutilisation de composants Blade, présence d'un système de brouillon auto-sauvegardé (bonne pratique pour les formulaires longs comme l'inscription élève).

**Points d'attention** : présence de deux versions de dashboard admin (`dashboard.blade.php` / `dashboard_temp.blade.php`) suggérant une UI en transition non finalisée ; dépendance à des CDN externes (Bootstrap, jQuery, FontAwesome) plutôt qu'un bundle local complet, ce qui fragilise l'expérience hors-ligne réelle malgré le Service Worker.

### 24.5 Accessibilité

**Information non identifiable dans l'état actuel du projet** : aucune analyse d'accessibilité (contrastes, ARIA, navigation clavier) n'a pu être conduite sans exécution/inspection visuelle de l'application.

<div class="page-break"></div>

## 25. Performance et scalabilité

### 25.1 Constats issus du code

- Utilisation systématique de l'ORM Eloquent avec relations explicites — risque classique de requêtes N+1 non exclu sans profilage réel (non mesuré dans cet audit).
- Verrouillages transactionnels (`lockForUpdate`) présents précisément là où c'est nécessaire (compteurs de reçus, sessions de caisse, tentatives de quiz, paiements de salaire) — signe d'une attention réelle à la concurrence, sans quoi des collisions financières seraient possibles avec plusieurs caissiers simultanés.
- Le cache des permissions Spatie est configuré (24h) mais son usage réel dans le flux de requêtes n'a pas été mesuré.
- Le Service Worker (PWA) réduit la charge réseau pour les assets statiques une fois installés côté client.

### 25.2 Scalabilité multi-établissement

- Architecture **shared-database, shared-schema** (un seul schéma PostgreSQL, isolation par colonne `school_id` + scope Eloquent) : convient à un volume modéré à élevé d'établissements sur une seule instance de base de données, mais ne permet pas d'isolation physique forte (une faille de scope global impacterait potentiellement plusieurs écoles à la fois — risque à surveiller, cf. §19.3 sur `QuizAttempt`).
- Pas de sharding, pas de réplication, pas de file d'attente distribuée observés — la configuration `.env.example` par défaut utilise `QUEUE_CONNECTION=database` et `CACHE_STORE=database` (adaptés à un volume modeste ; Redis est configuré en option mais pas imposé).
- Le multi-tenant actuel scale bien fonctionnellement en nombre d'établissements (colonne indexée), mais la charge de calcul (bulletins, promotions, imports Excel) est exécutée de façon synchrone dans la majorité des flux observés (ex. `GenerateStudentInvoicesOnEnrollment` n'est pas mis en file d'attente) — à surveiller si le volume d'inscriptions simultanées augmente fortement.

### 25.3 Réponse à la question : « la plateforme peut-elle passer de quelques centaines d'élèves à plusieurs établissements et plusieurs milliers d'utilisateurs ? »

Réponse nuancée, fondée sur le code : le modèle de données et les contrôles d'accès sont conçus pour le multi-établissement dès l'origine (ce n'est pas un ajout tardif), ce qui est un point structurant favorable. En revanche, aucun élément d'infrastructure de montée en charge (cache distribué, files asynchrones systématiques, tests de charge, index de performance documentés au-delà des contraintes d'unicité) n'a été identifié dans le dépôt — un passage à plusieurs milliers d'utilisateurs simultanés nécessiterait probablement : mise en file d'attente systématique des traitements lourds (imports, génération de bulletins en masse, promotions), passage à Redis pour cache/sessions/queue, et un audit de requêtes N+1 non réalisé ici.

<div class="page-break"></div>

## 26. Installation et déploiement

> Cette section se limite strictement à ce qui est présent dans le dépôt (`composer.json`, `.env.example`, `README.md` implicite via scripts composer) — aucune commande n'est inventée.

### 26.1 Prérequis (déduits de `composer.json`/`package.json`)

- PHP ^8.2, extensions Laravel standard.
- Node.js ≥ 18 (`package.json engines`).
- Base de données : SQLite (par défaut en développement), PostgreSQL ou MySQL en production.

### 26.2 Installation (script `composer.json` → `composer run setup`)

```
composer install
copier .env.example vers .env (si absent)
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

### 26.3 Développement (script `composer.json` → `composer run dev`)

Lance en parallèle (via `concurrently`) : `php artisan serve`, `php artisan queue:listen --tries=1`, `php artisan pail --timeout=0` (logs), `npm run dev` (Vite).

### 26.4 Variables d'environnement notables (`.env.example`)

- `APP_NAME`/`PLATFORM_NAME` = AzelieEdu, `DEFAULT_SCHOOL_NAME`.
- Compte super-admin initial : `SUPER_ADMIN_NAME/EMAIL/PASSWORD` (créé à la migration si renseigné).
- `DB_CONNECTION` : sqlite par défaut ; sections commentées prêtes à l'emploi pour MySQL local, hébergement mutualisé InfinityFree, ou **Laravel Cloud (PostgreSQL)**.
- Mail : `MAIL_MAILER=smtp` via **Brevo**, avec repli documenté `MAIL_MAILER=log` pour le développement.
- `TEACHER_SEND_CREDENTIALS_EMAIL=false` par défaut.
- `SCHOOL_BOT_SECRET`, `SCHOOL_BOT_PASSING_GRADE_MIN` (optionnel).
- `VITE_AI_AGENT_URL=http://localhost:3000` (microservice IA externe, développement).
- `BACKUP_NOTIFICATION_EMAIL`, `BACKUP_ARCHIVE_PASSWORD` (spatie/laravel-backup).
- `NOTIFY_PARENTS_ENABLED=false` par défaut.

### 26.5 Déploiement

- `vercel.json` présent (`outputDirectory: public/build`) : suggère un pipeline où les assets frontend sont buildés et potentiellement déployés séparément — l'hébergement du backend Laravel lui-même sur Vercel n'est pas confirmable depuis ce seul fichier (Vercel n'exécute pas nativement PHP/Laravel en continu ; configuration réelle non vérifiable dans ce dépôt).
- Le dépôt référence un guide `deploy/infinityfree/instructions.txt` (mentionné en commentaire dans `.env.example`) — ce fichier n'a pas été localisé/vérifié dans l'arborescence explorée ; **information non totalement vérifiable**.
- Support explicite de **Laravel Cloud** (PostgreSQL managé, variables `DB_*` injectées automatiquement).

<div class="page-break"></div>

## 27. Tests et qualité

### 27.1 Tests présents

- Framework : **PHPUnit 11** (`phpunit.xml`), scripts `composer test`.
- **Tests Feature** (`tests/Feature/`) : essentiellement le socle d'authentification standard de Laravel Breeze (connexion, vérification email, confirmation/réinitialisation de mot de passe, profil) + `ExampleTest.php` par défaut.
- **Tests Unit** (`tests/Unit/`) : `SchoolClassNameTest`, `SchoolClassProvisionerTest`, `SchoolGradingModeTest`, `SchoolLevelProvisionerTest` — couvrent une partie du cœur métier (normalisation des noms de classe, provisionnement, mode de notation).
- **Constat** : la couverture de test est **très inférieure au périmètre fonctionnel réel** — aucun test identifié sur les modules Comptabilité (paiements, caisse, salaires, journal), Notes/Bulletins, Promotion d'élèves, Import Excel, LMS/Quiz, ou Multi-tenant — alors que ce sont les modules les plus critiques du point de vue intégrité des données et argent.

### 27.2 Qualité / outillage

- **Laravel Pint** (formatage de code PHP) présent en dépendance de développement.
- Aucun outil de CI/CD (GitHub Actions ou équivalent) identifié dans l'arborescence explorée.
- Aucun linting JS/TS (ESLint) ni typage statique (TypeScript) identifié côté frontend.
- Aucune configuration de monitoring applicatif (Sentry, etc.) identifiée.

### 27.3 Niveau de maturité technique

Le code métier lui-même est **mature et défensif** (verrous transactionnels, idempotence, docblocks explicatifs référençant des corrections de bugs passées), mais ce niveau de rigueur n'est **pas accompagné d'une couverture de tests automatisés équivalente** — un déséquilibre notable entre la qualité de conception et la qualité de vérification automatisée.

<div class="page-break"></div>

## 28. Audit global du projet

| Critère | Note /10 | Justification |
|---|---|---|
| Fonctionnalités | 8 | Périmètre fonctionnel large et réellement implémenté (académique, financier, LMS, multi-établissement) ; quelques zones grises (justification d'absence, portail parent absent). |
| UX/UI | 6 | Portails cohérents par rôle, composants réutilisés, mais dashboard admin en double version et dépendance CDN externe fragilisant l'offline. Non vérifié en conditions réelles d'usage (pas de test navigateur exécuté dans cet audit). |
| Architecture | 7 | Multi-tenant pensé dès la conception, séparation Services/Support/Observers claire, mais coexistence de deux systèmes d'autorisation (rôle-string vs Spatie non branché) et duplication (Schedule/Timetable, deux mécanismes d'affectation enseignant). |
| Sécurité | 6.5 | En-têtes de sécurité réels, liens signés pour vérification publique, chiffrement de champs sensibles, verrouillage transactionnel financier — mais permissions Spatie non appliquées, CSP autorisant une origine de développement, incohérence de scope tenant sur `QuizAttempt`. |
| Performance | 5.5 | Aucune preuve de profilage ou d'optimisation N+1 systématique ; traitements lourds majoritairement synchrones. |
| Scalabilité | 5 | Bon fondement multi-tenant, mais infrastructure de montée en charge (queues asynchrones généralisées, cache distribué) non mise en œuvre par défaut. |
| Maintenabilité | 7 | Code très documenté (docblocks français détaillés, justification des choix), mais peu de Form Requests dédiées et validations inline dans les contrôleurs. |
| Documentation | 6 | Documentation interne (`docs/ADMIN_PANEL.md`) partielle et partiellement obsolète (comptes de démo probablement fictifs) ; ce dossier comble une grande partie du manque. |
| Tests | 3.5 | Couverture très faible au regard du périmètre métier critique (finances, notes, promotion). |
| Maturité produit | 6.5 | Fonctionnalités cœur de métier solides et déployées avec de vrais garde-fous ; module IA en partie orphelin (prototype cassé non nettoyé), rôle `tresorier` en friche. |

<div class="page-break"></div>

## 29. Points forts

- **Comptabilité de qualité professionnelle** : journal immuable, verrous transactionnels, idempotence, séparation des tâches, aucune suppression destructrice — un niveau de rigueur rarement observé dans ce type de projet.
- **Multi-tenant pensé dès la conception**, pas ajouté après coup (scope global, colonnes obligatoires, contraintes FK protectrices).
- **Deux modèles pédagogiques (classique et formation/LMD) unifiés** dans un même socle de données, avec des moteurs de calcul distincts et cohérents.
- **Anti-fraude documentaire réel** : QR codes vérifiés par recalcul serveur, pas par confiance dans le PDF.
- **Intégration WhatsApp fonctionnelle et multilingue** (français, wolof, pular), avec dégradation gracieuse.
- **Documentation interne du code exceptionnelle** : de très nombreux docblocks expliquent le *pourquoi* des décisions (corrections de bugs passés, choix de séparation des tâches, limitations Postgres/MySQL) — un atout rare pour la maintenabilité future.
- **Module LMS et classes virtuelles réellement fonctionnels**, pas de simple maquette.

<div class="page-break"></div>

## 30. Limites et risques

- **Permissions Spatie non appliquées** : le système de permissions fines existe en base mais n'est pas branché sur les routes — tout le contrôle d'accès réel repose sur des chaînes de rôle en dur, ce qui est fonctionnel mais rigide et plus difficile à faire évoluer finement (ex. donner un droit précis à un `surveillant` sans lui donner tout l'accès admin).
- **`QuizAttempt` non scopé au tenant** (absence du trait `BelongsToSchool`) — incohérence à corriger pour garantir l'étanchéité multi-établissement sur cette table précise.
- **CSP en production autorisant `http://localhost:3000`** — probable résidu de configuration de développement à retirer avant mise en production réelle.
- **Prototype `ai_service/` cassé et non branché** (bug de variable non initialisée dans `app_ai_final.py`) laissé dans le dépôt — source de confusion pour un nouvel arrivant et surface d'attaque inutile si jamais exposé par erreur.
- **Rôle `tresorier` en friche** : permissions définies mais aucune route/compte ne l'utilise — dette de conception à trancher (le finaliser ou le retirer du catalogue).
- **Portail parent absent** : seule une notification sortante existe ; aucun espace de consultation dédié aux parents.
- **Justification d'absence** : colonne présente, aucune interface pour l'utiliser — fonctionnalité à terminer ou à retirer du modèle de données.
- **Couverture de tests automatisés très faible** sur les modules les plus sensibles (argent, notes, promotion, imports) — risque de régression silencieuse lors de futures évolutions.
- **Duplication fonctionnelle** `Schedule` vs `Timetable` (deux tables/modèles au périmètre très proche) — dette technique à clarifier.
- **Deux mécanismes d'affectation enseignant↔classe coexistants** (`class_teacher` pivot et `TeacherAssignment`) — complexité de maintenance, source historique documentée d'un bug déjà corrigé, risque de récidive.
- **Commande console `app:reset-user-password`** enregistrée mais totalement vide (stub) — à implémenter ou supprimer pour éviter une fausse impression de fonctionnalité disponible.
- **Traitements synchrones potentiellement lourds** (génération de factures à l'inscription, imports Excel volumineux) sans mise en file d'attente systématique — risque de dégradation de la réactivité en cas de pics d'usage.
- **Absence de CI/CD et de couverture de sécurité automatisée** (pas de pipeline identifié) — les régressions ou failles ne sont pas détectées automatiquement avant mise en production.

<div class="page-break"></div>

## 31. Roadmap recommandée

### Phase 1 — Corrections critiques
- Corriger l'absence de scope tenant sur `QuizAttempt`.
- Retirer `http://localhost:3000` de la CSP de production (ou la rendre conditionnelle à l'environnement).
- Décider du sort de `ai_service/` (suppression ou isolement clair hors du dépôt principal applicatif) et corriger/retirer le bug de `app_ai_final.py`.
- Implémenter ou supprimer la commande `app:reset-user-password`.

**Priorité** : haute — ce sont des risques de sécurité/cohérence directement identifiés dans le code actuel.

### Phase 2 — Stabilisation
- Ajouter des tests automatisés sur les modules Comptabilité, Notes/Bulletins, Promotion, Import Excel (les modules à plus fort impact en cas de régression).
- Mettre en place un pipeline CI (lint + tests) à chaque pull request.
- Clarifier/fusionner `Schedule` et `Timetable`.
- Documenter ou supprimer les modèles d'événements WhatsApp non déclenchés (retard, note, réunion).

### Phase 3 — Version production
- Basculer les traitements lourds (imports, génération de factures, envoi d'emails) en file d'attente asynchrone systématique.
- Finaliser la politique de permissions fines (brancher Spatie sur les routes/Policies) pour permettre des rôles personnalisés par établissement.
- Compléter la fonctionnalité de justification d'absence (ou la retirer proprement du schéma).

### Phase 4 — Scalabilité
- Passage à Redis pour cache/session/queue en production à volume croissant.
- Audit de performance (requêtes N+1, index) sur les listes à fort volume (élèves, paiements, journal comptable).
- Tests de charge multi-établissements.

### Phase 5 — Fonctionnalités avancées
- Espace parent dédié (comptes de connexion, consultation notes/bulletins/paiements/présences).
- Finalisation du rôle `tresorier` (rapprochement bancaire, virements) ou retrait explicite du catalogue.
- Notifications SMS en complément de WhatsApp.

### Phase 6 — Intelligence artificielle / automatisation
- Clarifier et sécuriser l'architecture de l'assistant IA (widget → microservice → agent Botpress), avec authentification robuste bout en bout.
- Analyse prédictive (risque de décrochage, prévision d'effectifs) à partir des données déjà centralisées.
- Génération automatique de rapports de synthèse pour la direction.

<div class="page-break"></div>

## 32. Vision produit

> Cette section est une **vision prospective**, pas une description de fonctionnalités existantes.

À moyen terme, AzelieEdu pourrait évoluer vers : une application mobile native ou PWA renforcée (le socle Service Worker/manifest existe déjà) ; un véritable portail parent avec consultation en temps réel ; des paiements en ligne (mobile money direct, au-delà de l'enregistrement d'un paiement déjà encaissé) ; une analyse prédictive des résultats scolaires et du risque de décrochage, en s'appuyant sur les données déjà structurées (notes, présences, historique) ; un assistant administratif conversationnel consolidé (unifiant l'API SchoolBot existante avec une interface interne robuste, au lieu du prototype expérimental actuel) ; un mode hors-ligne renforcé pour les zones à connectivité limitée ; et une extension à d'autres pays au-delà du Sénégal, l'essentiel de l'architecture (multi-tenant, formation/LMD, comptabilité) n'étant pas intrinsèquement liée à un seul pays, à l'exception du format de bulletin et de la terminologie des cycles actuellement codés en dur pour le contexte sénégalais.

<div class="page-break"></div>

## 33. Glossaire

| Terme | Définition |
|---|---|
| **API** | Interface de programmation applicative — ici, une unique API JSON en lecture seule pour un agent externe (§22). |
| **RBAC** | Contrôle d'accès basé sur les rôles (Role-Based Access Control) — implémenté via des rôles en dur et, en fondation, via Spatie Permission. |
| **CRUD** | Create/Read/Update/Delete — opérations de base de gestion d'une entité. |
| **ORM** | Object-Relational Mapping — ici, Eloquent (Laravel). |
| **SaaS** | Software as a Service — modèle d'hébergement mutualisé multi-clients, ici multi-établissements. |
| **PWA** | Progressive Web App — application web installable avec fonctionnement hors-ligne partiel. |
| **Multi-tenant** | Architecture où plusieurs clients (« tenants », ici les écoles) partagent la même instance applicative avec isolation logique des données. |
| **LMD** | Licence-Master-Doctorat — système de notation/crédits utilisé par le mode « Formation » de la plateforme. |
| **KPI** | Indicateur clé de performance (Key Performance Indicator). |
| **IDOR** | Insecure Direct Object Reference — faille où un utilisateur accède à des données ne lui appartenant pas via un identifiant manipulé ; plusieurs gardes anti-IDOR ont été identifiées dans le code (résolveurs d'affectation enseignant, vérification d'appartenance de classe). |
| **Scope global (Eloquent)** | Filtre automatiquement appliqué à toutes les requêtes d'un modèle (ici, filtrage par `school_id`). |
| **Ledger / grand livre** | Journal comptable, ici immuable, retraçant tous les mouvements financiers. |
| **Idempotent** | Se dit d'une opération qui peut être répétée sans produire de doublon ou d'effet indésirable (ex. génération mensuelle des salaires). |

<div class="page-break"></div>

## 34. Annexes

### 34.1 Arborescence du projet (racine, hors `vendor/`, `node_modules/`, `.git/`)

```
app/{Console,Events,Exports,Http,Jobs,Listeners,Mail,Models,Notifications,
     Observers,Providers,Services,Support,Traits,View}
ai_service/            (prototype Python Flask+LangChain, non branché — voir §18.3)
config/                (app, auth, permission, platform, school, services, ...)
database/{factories,migrations,seeders}
docs/                  (documentation interne existante)
lang/fr/                (traductions françaises)
public/                 (assets buildés, manifest PWA, service worker)
resources/{css,js,views}
routes/{web.php, auth.php, api.php, console.php}
storage/, tests/, vendor/
composer.json, package.json, vite.config.js, vercel.json, phpunit.xml
```

### 34.2 Rôles (récapitulatif)

`super_admin`, `admin`, `surveillant`, `teacher`/`professeur`, `eleve`/`student`, `directeur`, `comptable`, `caissier`, `tresorier` (non activé).

### 34.3 Modèles Eloquent (35)

AcademicYear, AccountingCounter, Assignment, Attendance, CashRegister, CashSession, ClassGroup, EmployeeSalaryProfile, Event, Expense, FeeAmount, FeeType, FormationDepartment, FormationPromotion, Grade, LedgerEntry, Lesson, Level, Payment, PaymentAllocation, Question, QuestionOption, Quiz, QuizAttempt, SalaryPayment, Schedule, School, SchoolClass, Subject, Submission, TeacherAssignment, Timetable, User, VirtualClass (+ traits/concerns `BelongsToSchool`).

### 34.4 Contrôleurs par domaine (nombre)

Accounting (15), Admin (24), Api (1), Auth (9 standard + inscription école), Platform (6), Student (10), Teacher (9), + contrôleurs racine (BulletinVerify, Notification).

### 34.5 Middlewares personnalisés

`RoleMiddleware` (non utilisé actuellement), `EnsureSchoolBotSecret`, `SuperAdminMiddleware`, `EnsureSchoolAdmin`, `EnsureSchoolIsActive`, `EnsureSchoolModuleEnabled`, `EnsureAccountingRole`, `StudentMiddleware`, `TeacherMiddleware`, `SyncTenantSchoolSession`, `SecurityHeaders`, `SanitizeInput`, `TrustProxies`.

### 34.6 Matrice rôles × permissions Spatie (fondation, non appliquée aux routes)

| Rôle | Permissions clés |
|---|---|
| admin | students/classes/grades/teachers.*, attendance.view/record, users.manage, settings.manage |
| surveillant | students/classes/grades/attendance.view, attendance.record |
| teacher | students/classes.view, grades.view/create/update, attendance.view/record |
| eleve | (aucune) |
| directeur | parametrage.*, ecriture/journal/grand_livre/balance/caisse.consulter, salaire.payer, rapport_financier.* |
| comptable | ecriture.creer/consulter, depense.creer/annuler, salaire.generer/payer/annuler, paiement.annuler, rapport_financier.* |
| caissier | caisse.consulter/ouvrir/cloturer/encaisser/decaisser, recette.creer, penalite.appliquer |
| tresorier | ecriture.valider, banque.gerer, rapprochement.effectuer, virement.effectuer *(non utilisé)* |

### 34.7 Variables d'environnement principales

Voir détail §26.4 : `APP_NAME`, `PLATFORM_NAME`, `SUPER_ADMIN_*`, `DB_CONNECTION` (+ variantes MySQL/PostgreSQL/InfinityFree/Laravel Cloud), `MAIL_*` (Brevo), `TEACHER_SEND_CREDENTIALS_EMAIL`, `SCHOOL_BOT_SECRET`, `SCHOOL_BOT_PASSING_GRADE_MIN`, `VITE_AI_AGENT_URL`, `BACKUP_NOTIFICATION_EMAIL`, `BACKUP_ARCHIVE_PASSWORD`, `NOTIFY_PARENTS_ENABLED`.

### 34.8 Dépendances principales

Voir détail §20.1 (`composer.json`/`package.json`).

### 34.9 Recommandations techniques (synthèse)

Voir §30 (limites) et §31 (roadmap) pour le détail priorisé.

---

<div class="doc-footer-note">
Document généré par analyse directe du code source du dépôt <code>School-Management</code>. Toute information non vérifiable dans le code est explicitement signalée comme telle dans le corps du document. Ce dossier ne remplace pas une revue de sécurité formelle ni un test de charge réel.
</div>
