# Stratégie de Packaging des Abonnements SaaS
## Plateforme de Gestion d'Établissements Scolaires — Freemium / Standard / Premium

**Document interne — usage équipe produit**
Version 1.0 — Août 2026

Ce document sert de base de décision pour la conception du futur système d'abonnement de l'application. Il **n'inclut aucun prix**. Il détermine uniquement le contenu de chaque formule, les limites envisageables et les règles de restriction technique à construire plus tard.

---

## 1. Objectif du document

L'application a été auditée intégralement (contrôleurs, routes, modèles, migrations, services, middlewares, vues) afin d'identifier **toutes les fonctionnalités réellement implémentées**, sans supposition. Ce document :

1. Dresse l'inventaire complet du produit tel qu'il existe aujourd'hui ;
2. Classe les fonctionnalités par module fonctionnel ;
3. Propose une répartition Freemium / Standard / Premium fondée sur la valeur, le coût et le potentiel commercial de chaque fonctionnalité ;
4. Propose des limites quantitatives et des règles de feature gating (à documenter, pas à coder) ;
5. Fournit une base de décision stratégique pour l'étape suivante : tarification et implémentation technique des restrictions.

**Aucun prix n'est fixé dans ce document.** Mention systématique : *Tarification — à définir après validation du packaging et des coûts d'exploitation.*

---

## 2. Vision du packaging

La plateforme est déjà **multi-tenant** : un rôle `super_admin` (nous, l'opérateur SaaS) gère un catalogue d'établissements (`School`), chacun étant un tenant isolé (données scopées par école via `SchoolScope`). Chaque établissement possède déjà un mécanisme d'activation de modules (`enabled_modules`, classe `App\Support\SchoolModules`, middleware `module:accounting`) — actuellement utilisé pour le module Comptabilité, réservé de facto aux établissements privés. **Cette fondation technique est directement réutilisable pour construire le futur système d'abonnement** : chaque plan (Freemium/Standard/Premium) peut se traduire par un ensemble de modules activés + des limites quantitatives stockées sur l'enregistrement `School`.

Le produit couvre aujourd'hui deux grandes familles d'établissements :
- **Établissements classiques** (primaire, collège, lycée, mixte) — notes, bulletins, présences, LMS, etc.
- **Écoles de formation professionnelle** — système de notation LMD (contrôle continu + examen par module), départements/promotions.

Le module Comptabilité complet (Directeur/Comptable/Caissier, caisse, salaires, grand livre) est aujourd'hui réservé aux établissements **privés** — c'est un signal fort pour le packaging : c'est une fonctionnalité à forte valeur, coûteuse à opérer (rôles multiples, sécurité financière), et donc un candidat naturel pour Standard+/Premium plutôt que Freemium.

---

## 3. Méthodologie de l'audit

Sources inspectées : `routes/web.php` et `routes/api.php` (644 lignes), 60+ contrôleurs (`app/Http/Controllers/{Admin,Accounting,Student,Teacher,Platform}`), modèles Eloquent (`app/Models`), middlewares de rôle et de gating, services métier (`app/Services`), exports (`app/Exports`), notifications (`app/Notifications`, `app/Jobs`), et le micro-service Python `ai_service/`.

Rôles utilisateurs identifiés dans le code (`User::ROLE_*`) :

| Rôle | Constante | Portée |
|---|---|---|
| Super Administrateur | `super_admin` | Opérateur de la plateforme SaaS (nous) — gère tous les établissements |
| Administrateur | `admin` | Gestion complète d'un établissement |
| Surveillant | `surveillant` | Sous-ensemble du staff école (accès proche admin sur certains modules) |
| Enseignant | `teacher` / `professeur` | Pédagogie : notes, présences, LMS, classes virtuelles |
| Élève | `eleve` / `student` | Consultation + interactions pédagogiques (quiz, LMS, classes virtuelles) |
| Directeur (comptabilité) | `directeur` | Pilotage financier, lecture seule sur les opérations, gère comptable/caissier |
| Comptable | `comptable` | Opérations financières : dépenses, salaires, corrections de paiement |
| Caissier | `caissier` | Encaissement élèves, sessions de caisse, reçus |

**Constat important : il n'existe aucun rôle ni portail « Parent » dans le code actuel** (pas de `ROLE_PARENT`, pas de contrôleur, pas de route). Le service `NotificationService` mentionne des `parent_lang` pour l'envoi WhatsApp, mais il s'agit d'un **canal de notification vers les parents**, pas d'un espace applicatif parent avec connexion. Toute formule ne doit donc **pas** promettre un « espace parent » — seulement une **notification aux parents**. C'est noté *À envisager* pour un futur espace parent dédié.

---

## 4. Inventaire complet des fonctionnalités

Légende État : **Disponible** (fonctionnel et routé) · **Partiellement disponible** (existe mais incomplet/non intégré) · **À envisager** (n'existe pas, piste future).
Légende Niveau : Freemium (F) · Standard (S) · Premium (P) · Add-on (A) · À déterminer (AD).

| Module | Fonctionnalité | Rôle concerné | État | Valeur commerciale | Niveau |
|---|---|---|---|---|---|
| Plateforme | Gestion multi-établissements (créer/activer/désactiver école) | Super Admin | Disponible | N/A (interne opérateur) | N/A |
| Plateforme | Activation/désactivation du module Comptabilité par école | Super Admin | Disponible | Forte — bascule de plan | N/A |
| Plateforme | Inspection en lecture seule d'une école (classes, users, matières) | Super Admin | Disponible | Support/QA | N/A |
| Plateforme | Génération de code établissement, réinitialisation mot de passe admin | Super Admin | Disponible | Support | N/A |
| Plateforme | Gestion des cycles/niveaux par établissement | Super Admin | Disponible | Configuration | N/A |
| Auth | Inscription avec choix de rôle (élève/enseignant) | Public | Disponible | Acquisition | F |
| Auth | Validation/rejet des inscriptions par l'admin | Admin | Disponible | Essentiel | F |
| Auth | Génération automatique d'identifiants uniques | Système | Disponible | Confort | F |
| Auth | OTP e-mail pour comptes staff (comptabilité) | Comptabilité | Disponible | Sécurité | S/P |
| Auth | Vérification d'e-mail, réinitialisation mot de passe | Tous | Disponible | Essentiel | F |
| Admin | Tableau de bord établissement (statistiques) | Admin | Disponible | Essentiel | F (basique) / S (détaillé) |
| Admin | Réglages établissement (identité, coordonnées, logo) | Admin | Disponible | Essentiel | F |
| Admin | Réglages LMD (formation professionnelle) | Admin | Disponible | Spécifique filière formation | S |
| Élèves | Fiche élève CRUD complète | Admin | Disponible | Essentiel | F (limité en nombre) |
| Élèves | Affectation à une classe (unitaire + en masse) | Admin | Disponible | Essentiel | F/S |
| Élèves | Import en masse Excel/CSV (mapping, aperçu, identifiants) | Admin | Disponible | Gain de temps fort | S |
| Élèves | Export élèves (CSV) | Admin | Disponible | Reporting | S |
| Élèves | Régénération / envoi des identifiants | Admin | Disponible | Support | F |
| Enseignants | Fiche enseignant CRUD complète | Admin | Disponible | Essentiel | F (limité en nombre) |
| Enseignants | Import en masse Excel/CSV | Admin | Disponible | Gain de temps fort | S |
| Enseignants | Invitation par e-mail, régénération identifiants | Admin | Disponible | Confort | F/S |
| Enseignants | Affectation classes/matières à un enseignant | Admin | Disponible | Essentiel | F |
| Classes | Gestion des classes (CRUD, matières par classe) | Admin | Disponible | Essentiel | F (limité en nombre) |
| Classes | Promotion automatique / manuelle de classe (fin d'année) | Admin | Disponible | Fort (évite ressaisie annuelle) | S |
| Classes | Cycles de formation (écoles pro) | Admin | Disponible | Spécifique filière formation | S |
| Années académiques | CRUD année académique, année courante, provisionnement, clôture/réouverture | Admin | Disponible | Essentiel structurant | F (1 année active) / S (historique) |
| Matières | CRUD matières | Admin | Disponible | Essentiel | F |
| Notes | Grille de notation primaire configurable (barème, coefficient, nb compositions) | Admin | Disponible | Différenciant (système sénégalais) | S |
| Notes | Saisie des notes | Enseignant | Disponible | Essentiel | F |
| Notes | Consultation des notes | Élève | Disponible | Essentiel | F |
| Bulletins | Calcul et affichage bulletin (système sénégalais) semestriel | Élève/Admin | Disponible | Cœur de valeur | F (consultation) / S (PDF) |
| Bulletins | Bulletin annuel | Élève/Admin | Disponible | Cœur de valeur | S |
| Bulletins | Génération PDF bulletins (semestre / année / composition, en masse) | Admin | Disponible | Fort — remplace impression manuelle | S/P |
| Bulletins | Vérification publique par QR code (sans authentification) | Public | Disponible | Anti-fraude, image de marque | S |
| Présences | Prise de présence par classe | Enseignant | Disponible | Essentiel | F |
| Présences | Historique de présence (par classe / par élève) | Enseignant | Disponible | Essentiel | F/S |
| Présences | Consultation présences (transparence, toutes classes, lecture seule) | Admin/Surveillant | Disponible | Contrôle | S |
| Présences | Consultation par l'élève | Élève | Disponible | Essentiel | F |
| Présences | Notification automatique WhatsApp à l'absence | Système/Parent | Disponible | Fort différenciant | S/P ou Add-on |
| Emploi du temps | Gestion des emplois du temps par classe | Admin | Disponible | Essentiel | F (basique) |
| Emploi du temps | Consultation enseignant / élève | Enseignant/Élève | Disponible | Essentiel | F |
| E-learning (LMS) | Publication de cours/leçons (upload) | Enseignant | Disponible | Fort différenciant | S |
| E-learning (LMS) | Téléchargement des leçons | Élève | Disponible | Fort différenciant | S |
| E-learning (LMS) | Devoirs — création, soumission, notation | Enseignant/Élève | Disponible | Fort différenciant | S/P |
| E-learning (LMS) | Quiz interactifs auto-corrigés (limite 3 tentatives, codée en dur) | Enseignant/Élève | Disponible | Fort différenciant | S/P |
| E-learning (LMS) | Supervision / suppression de contenu LMS par l'admin | Admin | Disponible | Contrôle | S |
| Classes virtuelles | Création de classe virtuelle (Jitsi) | Enseignant | Disponible | Fort différenciant, coût quasi nul (Jitsi) | S/P |
| Classes virtuelles | Participation à une classe virtuelle | Élève | Disponible | Fort différenciant | S/P |
| Cartes scolaires | Génération carte élève avec QR code anti-fraude | Admin | Disponible | Différenciant visuel + sécurité | S |
| Cartes scolaires | Personnalisation de la carte (réglages, activation) | Admin | Disponible | Différenciant | P |
| Cartes scolaires | Vérification publique de carte par QR (sans authentification) | Public | Disponible | Anti-fraude | S |
| Cartes scolaires | Consultation carte + jeton QR par l'élève | Élève | Disponible | Confort | S |
| Comptabilité — Paramétrage | Types de frais, grille montants par niveau, historique | Directeur | Disponible (module gated, privé) | Cœur de la valeur privée | P (ou add-on payant) |
| Comptabilité — Personnel | Création comptes Directeur/Comptable/Caissier | Admin | Disponible (module gated) | Essentiel filière privée | P |
| Comptabilité — Salaires | Génération, paiement, annulation des salaires | Comptable/Directeur | Disponible | Fort (paie du personnel) | P |
| Comptabilité — Salaires | Reçu de salaire PDF + vérification QR publique | Comptable/Directeur | Disponible | Différenciant | P |
| Comptabilité — Dépenses | Enregistrement dépenses + justificatif (upload/téléchargement) | Comptable | Disponible | Essentiel | P |
| Comptabilité — Caisse | Session de caisse (ouverture/fermeture, réconciliation) | Caissier | Disponible | Essentiel, contrôle interne | P |
| Comptabilité — Caisse | Détection d'écart de caisse + notification | Caissier/Directeur | Disponible | Contrôle financier fort | P |
| Comptabilité — Paiements élèves | Encaissement, recherche élève, situation financière | Caissier | Disponible | Cœur de la valeur privée | P |
| Comptabilité — Reçus | Reçu de paiement PDF (format ticket 80mm) + vérification QR publique | Caissier | Disponible | Fort différenciant terrain | P |
| Comptabilité — Corrections | Annulation/correction de paiement | Comptable | Disponible | Contrôle | P |
| Comptabilité — Grand livre | Journal des opérations, export | Directeur/Comptable | Disponible | Reporting financier | P |
| Comptabilité — Suivi élèves | Liste des débiteurs, fiche financière élève | Directeur/Comptable | Disponible | Fort levier de recouvrement | P |
| Comptabilité — Consultation | Centre de navigation lecture seule (classes/enseignants/élèves/notes) | Directeur | Disponible | Confort direction | P |
| Espace élève | Ma situation financière (paiements) | Élève | Disponible (module gated) | Transparence | P |
| Espace enseignant | Mon salaire | Enseignant | Disponible (module gated) | Transparence | P |
| Rapports | Export CSV (notes, résumés semestre/année) | Admin | Disponible | Reporting | S |
| Rapports | PDF bulletins en masse (semestre/année/composition) | Admin | Disponible | Fort gain de temps | S/P |
| Notifications | Notifications in-app (cloche, marquer lu/tout lu) | Tous | Disponible | Confort | F |
| Notifications | WhatsApp multilingue (texte FR, audio Wolof/Pulaar) vers parents | Système | Disponible (job en file d'attente, service tiers UltraMsg) | Très fort différenciant, coût variable (API tierce) | S (volume limité) / P / Add-on |
| Journal d'audit | Historique des actions (Spatie Activity Log) sur écoles/utilisateurs | Admin | Disponible | Conformité/traçabilité | S/P |
| IA — Chatbot stats | API SchoolBot (Botpress) : stats, redoublants, résultats, recherche élève/utilisateur | Intégration externe, protégée par secret Bearer | Disponible (API), nécessite un bot externe configuré | Différenciant fort, coût d'intégration | P / Add-on |
| IA — Agent SQL | Micro-service Python (Flask + LangChain + Gemini) répondant en langage naturel sur la base de données | — | Partiellement disponible (existe en microservice séparé, **non branché** à l'application Laravel — aucun `ChatController`/route trouvés) | Fort potentiel, coût IA à modéliser | À envisager (non productisable en l'état) |
| Sécurité | Middlewares de rôle, école active, module activé, en-têtes de sécurité, assainissement des entrées | Système | Disponible | Fondation obligatoire | F (inclus partout) |
| Sécurité | Signature d'URL pour vérifications publiques (bulletin, carte, reçus) | Système | Disponible | Confiance/anti-fraude | F (inclus) |
| Multi-établissement client | Un même client gère plusieurs écoles sous un abonnement | — | À envisager (le multi-tenant existe côté opérateur, pas encore packagé côté client) | Fort pour réseaux/groupes scolaires | À envisager / Premium futur |
| Espace parent connecté | Portail dédié avec identifiants parent | — | À envisager (n'existe pas ; seul un canal de notification WhatsApp existe) | Fort potentiel | À envisager |

---

## 5. Classification par module

**Plateforme / Multi-établissement** — Réservé à l'opérateur (nous), pas un module vendu à l'école.

**Authentification & Comptes** — Inscription, validation, identifiants, sécurité des comptes. Socle commun à tous les plans.

**Direction (Admin établissement)** — Tableau de bord, réglages école, réglages formation LMD, journal d'audit.

**Gestion des élèves** — Fiches, affectation, import/export en masse, régénération d'identifiants.

**Gestion des enseignants** — Fiches, import en masse, invitations, affectations classes/matières.

**Classes & Années académiques** — Classes, matières, promotions, cycles de formation, années académiques (création, clôture, réouverture, provisionnement).

**Notes et évaluations** — Saisie, consultation, grille de notation paramétrable (primaire).

**Bulletins** — Calcul semestriel/annuel (système sénégalais), génération PDF en masse, vérification publique QR.

**Présences / absences** — Prise, historique, consultation, notification automatique.

**Emploi du temps** — Gestion et consultation par classe/rôle.

**E-learning / LMS** — Cours, devoirs, quiz, supervision admin.

**Classes virtuelles** — Visioconférence Jitsi intégrée.

**Cartes scolaires** — Génération, personnalisation, vérification QR anti-fraude.

**Comptabilité** — Trois portails (Directeur, Comptable, Caissier) : frais, salaires, dépenses, caisse, reçus, grand livre, suivi des débiteurs. **Réservé aujourd'hui aux établissements privés.**

**Parents / Élèves** — Consultation (élève) ; pas d'espace parent connecté, seulement un canal de notification.

**Rapports / Exports** — PDF et CSV, bulletins en masse.

**Notifications** — In-app + WhatsApp multilingue (texte/audio).

**Documents** — Reçus de paiement/salaire PDF, bulletins PDF, cartes PDF/QR, justificatifs de dépense.

**IA / Chatbot** — API de statistiques pour bot externe (disponible) ; agent SQL en langage naturel (microservice séparé, non intégré).

**Sécurité & Infrastructure** — Rôles, gating par module, école active, en-têtes de sécurité, URLs signées.

---

## 6. Formule Freemium

**Positionnement :** point d'entrée gratuit pour découvrir la plateforme et gérer les opérations pédagogiques de base d'un petit établissement.

**Pour qui ?** Petits établissements publics, écoles en phase de test, structures à budget très limité.

**Objectif :** démontrer la valeur cœur (gestion élèves/enseignants/classes/notes/présences) sans exposer les fonctionnalités les plus coûteuses à opérer (LMS, classes virtuelles, comptabilité, WhatsApp) ni celles qui demandent le plus de support (import en masse, PDF en masse).

**Fonctionnalités incluses :**
- Inscription et validation des comptes
- Gestion des élèves et enseignants (CRUD unitaire)
- Gestion des classes, matières, une année académique active
- Saisie et consultation des notes
- Consultation du bulletin semestriel à l'écran (sans export PDF en masse)
- Prise et consultation des présences
- Emploi du temps (consultation + gestion basique)
- Notifications in-app (cloche)
- Tableau de bord basique

**Fonctionnalités limitées :**
- Nombre d'élèves, d'enseignants et de classes plafonné (voir section 8)
- Une seule année académique active à la fois (pas d'historique multi-année)
- Pas d'export CSV/PDF en masse

**Fonctionnalités non incluses :**
- Import en masse (Excel/CSV)
- E-learning / LMS (cours, devoirs, quiz)
- Classes virtuelles
- Cartes scolaires QR
- Module Comptabilité (Directeur/Comptable/Caissier)
- Notifications WhatsApp
- Journal d'audit
- API IA / Chatbot

**Limites quantitatives proposées :** voir section 8 (à valider avec les coûts d'infra réels).

---

## 7. Formule Standard

**Positionnement :** la formule de référence pour la gestion quotidienne complète d'un établissement scolaire classique.

**Pour qui ?** La majorité des établissements (primaire, collège, lycée, mixte), qu'ils soient publics ou privés sans besoin de comptabilité intégrée poussée.

**Objectif :** couvrir 100 % du cycle pédagogique administratif + activer les fonctionnalités à fort effet « waouh » et fort gain de temps (import en masse, bulletins PDF, LMS, classes virtuelles, cartes scolaires), avec des volumes confortables mais non illimités.

**Fonctionnalités incluses (en plus du Freemium) :**
- Import/export en masse (élèves, enseignants) avec mapping et génération d'identifiants
- Génération PDF des bulletins (semestre, année, composition) en masse + vérification QR publique
- Grille de notation primaire configurable
- Multi-année académique (historique, clôture/réouverture, provisionnement)
- Promotion automatique/manuelle de classe en fin d'année
- E-learning / LMS complet (cours, devoirs, quiz avec 3 tentatives)
- Classes virtuelles (volume mensuel limité)
- Cartes scolaires QR (génération standard, sans personnalisation avancée)
- Notifications WhatsApp (volume mensuel limité)
- Journal d'audit (historique limité dans le temps)
- Rapports/exports CSV et PDF

**Fonctionnalités limitées :**
- Volume de classes virtuelles et de messages WhatsApp plafonné
- Personnalisation de la carte scolaire non disponible (design standard uniquement)
- Historique du journal d'audit limité (ex. 6-12 mois)

**Fonctionnalités non incluses :**
- Module Comptabilité complet (Directeur/Comptable/Caissier, caisse, salaires, grand livre)
- API IA / Chatbot (SchoolBot)
- Support prioritaire

---

## 8. Formule Premium

**Positionnement :** l'offre complète pour les établissements — notamment privés — qui veulent exploiter tout l'écosystème numérique, y compris la gestion financière intégrée.

**Pour qui ?** Établissements privés avec besoins financiers structurés (frais de scolarité, salaires, caisse), groupes scolaires, établissements à forts effectifs, structures voulant un support prioritaire et l'IA de reporting.

**Objectif :** débloquer les fonctionnalités les plus coûteuses à opérer et à supporter, et celles à plus forte valeur perçue.

**Fonctionnalités incluses (en plus du Standard) :**
- Module Comptabilité complet : trois portails Directeur / Comptable / Caissier
  - Paramétrage des frais par niveau, historique des montants
  - Salaires (génération, paiement, reçu PDF, vérification QR)
  - Dépenses avec justificatifs
  - Caisse (sessions, réconciliation, détection d'écart)
  - Encaissement élèves, reçus PDF (format ticket), vérification QR publique
  - Grand livre, export comptable
  - Suivi des débiteurs, fiche financière élève
  - Espace élève « Ma situation financière » et espace enseignant « Mon salaire »
- Cartes scolaires personnalisables (design, réglages avancés)
- Volumes élevés/illimités : classes virtuelles, notifications WhatsApp, stockage
- Journal d'audit complet (historique illimité)
- API IA / Chatbot (SchoolBot) pour intégration d'un assistant conversationnel
- Support prioritaire

**Fonctionnalités non incluses (car inexistantes dans le produit actuel — à ne jamais promettre) :**
- Portail parent connecté (n'existe pas)
- Agent IA en langage naturel intégré nativement à l'application (le microservice existe mais n'est pas branché)
- Gestion de plusieurs établissements sous un seul abonnement client (le multi-tenant existe côté opérateur uniquement)

---

## 9. Matrice comparative des trois formules

| Fonctionnalité | Freemium | Standard | Premium |
|---|---|---|---|
| Gestion élèves / enseignants / classes | ✓ (limité) | ✓ | ✓ |
| Notes et consultation | ✓ | ✓ | ✓ |
| Bulletin — consultation écran | ✓ | ✓ | ✓ |
| Bulletin — export PDF en masse | — | ✓ | ✓ |
| Vérification publique bulletin (QR) | — | ✓ | ✓ |
| Présences / absences | ✓ | ✓ | ✓ |
| Notification WhatsApp absence | — | Limité | Avancé |
| Emploi du temps | ✓ | ✓ | ✓ |
| Multi-année académique / historique | — | ✓ | ✓ |
| Promotion automatique de classe | — | ✓ | ✓ |
| Import/export en masse | — | ✓ | ✓ |
| E-learning / LMS (cours, devoirs, quiz) | — | ✓ | ✓ |
| Classes virtuelles (Jitsi) | — | Limité | Avancé |
| Cartes scolaires QR | — | Standard | Personnalisable |
| Journal d'audit | — | Limité | Avancé (illimité) |
| Module Comptabilité (Directeur/Comptable/Caissier) | — | — | ✓ |
| Espace élève « Ma situation financière » | — | — | ✓ |
| Espace enseignant « Mon salaire » | — | — | ✓ |
| API IA / Chatbot (SchoolBot) | — | — | ✓ (ou Add-on) |
| Support | Communautaire | Standard | Prioritaire |

---

## 10. Limites quantitatives proposées

Ces valeurs sont des **propositions de structure**, pas des chiffres définitifs — à valider avec les coûts d'infrastructure réels (stockage fichiers, requêtes WhatsApp, génération PDF).

| Ressource | Freemium | Standard | Premium | Pourquoi cette limite est pertinente |
|---|---|---|---|---|
| Établissements gérés | 1 | 1 | 1 (multi-site = à envisager) | Le tenant actuel = 1 école ; le multi-site client n'existe pas encore côté produit |
| Élèves | Plafond bas (ex. quelques dizaines) | Plafond moyen | Élevé / sur devis | Coût de stockage (dossiers, notes) et de calcul (bulletins) croît linéairement |
| Enseignants | Plafond bas | Plafond moyen | Élevé | Corrélé au nombre de classes/élèves |
| Classes | Plafond bas | Plafond moyen | Élevé | Structure directement liée aux élèves |
| Comptes utilisateurs (admin/surveillant) | Minimal (1-2) | Quelques comptes | Illimité/élevé | Le nombre de comptes staff reflète la taille de l'établissement |
| Comptes comptabilité (Directeur/Comptable/Caissier) | 0 (module non inclus) | 0 (module non inclus) | Inclus, plafonné par rôle | Ces comptes n'existent que si le module Comptabilité est activé |
| Stockage documents (leçons, justificatifs, logos) | Faible | Moyen | Élevé | Coût d'hébergement fichiers directement proportionnel |
| Ressources pédagogiques (leçons/devoirs/quiz) | Non disponible | Plafond mensuel | Élevé/illimité | LMS non disponible en Freemium ; volume = coût de stockage + support |
| Classes virtuelles (sessions Jitsi/mois) | Non disponible | Plafond mensuel | Élevé/illimité | Jitsi lui-même est peu coûteux, mais la fonctionnalité a une forte valeur perçue → levier d'upsell |
| Notifications WhatsApp/mois | Non disponible | Plafond mensuel | Élevé/illimité ou Add-on volume | Coût variable direct (API tierce UltraMsg facturée au message) — la limite la plus sensible économiquement |
| Historique / années académiques archivées | Année courante uniquement | Historique limité (ex. 2-3 ans) | Illimité | Le stockage d'historique a un coût croissant mais une forte valeur de conformité |
| Rapports / exports PDF-CSV | Non disponible en masse | Plafond mensuel | Illimité | Génération PDF = coût CPU serveur |
| Journal d'audit (rétention) | Non disponible | Rétention courte | Rétention longue/illimitée | Coût de stockage des logs |

---

## 11. Restrictions intelligentes — logique de packaging

1. **Contrôler les coûts variables en premier** : les deux fonctionnalités à coût marginal réel (WhatsApp via API tierce facturée au message, stockage de documents) doivent être limitées dès le Freemium et plafonnées même en Standard — ce sont les limites les plus défendables économiquement.
2. **Donner une vraie valeur au Freemium** : le cœur pédagogique (élèves, notes, présences, bulletin à l'écran) reste gratuit et complet dans sa logique — un petit établissement peut réellement faire tourner sa gestion quotidienne dessus, ce qui crée l'habitude et la confiance nécessaires à la conversion.
3. **Créer un déclencheur de montée en gamme naturel** : les plafonds Freemium (nombre d'élèves/enseignants/classes) sont atteints organiquement par la croissance de l'établissement — pas par une fonctionnalité arbitrairement cachée. C'est le levier d'upsell le plus sain.
4. **Ne jamais frustrer sur l'essentiel réglementaire** : la consultation des notes et du bulletin par l'élève ne doit jamais être limitée — c'est une attente minimale, la bloquer nuirait à la réputation.
5. **Différencier clairement Standard vs Premium par un domaine entier (pas par des micro-limites)** : le module Comptabilité est le bon séparateur naturel Standard/Premium — il est déjà techniquement isolé (`module:accounting`), à forte valeur, et coûteux à supporter (sécurité financière, formation des rôles).

---

## 12. Feature gating — fonctionnalités nécessitant une restriction technique par abonnement

| Fonctionnalité | Plan minimum requis | Type de restriction | Comportement à la limite | Message proposé | Upgrade possible |
|---|---|---|---|---|---|
| Création d'élève | Freemium (plafonné) | Compteur (nb élèves actifs) | Blocage de la création | « Vous avez atteint la limite de X élèves de votre formule. Passez au Standard pour continuer. » | Oui |
| Création d'enseignant | Freemium (plafonné) | Compteur | Blocage de la création | « Limite d'enseignants atteinte. » | Oui |
| Création de classe | Freemium (plafonné) | Compteur | Blocage de la création | « Limite de classes atteinte. » | Oui |
| Import en masse (élèves/enseignants) | Standard | Accès module | Menu/route masqués ou 403 | « L'import en masse est disponible à partir du plan Standard. » | Oui |
| Export PDF bulletins en masse | Standard | Accès module | Bouton désactivé | « Passez au Standard pour exporter vos bulletins en PDF. » | Oui |
| E-learning / LMS (cours, devoirs, quiz) | Standard | Accès module (`module:lms` à créer sur le même modèle que `module:accounting`) | Section masquée | « L'E-learning est disponible à partir du Standard. » | Oui |
| Classes virtuelles | Standard | Accès module + compteur mensuel | Blocage création au-delà du quota | « Quota de classes virtuelles du mois atteint. » | Oui |
| Cartes scolaires QR | Standard | Accès module | Section masquée | « Les cartes scolaires sont disponibles à partir du Standard. » | Oui |
| Personnalisation carte scolaire | Premium | Accès fonctionnalité avancée | Options grisées | « La personnalisation de la carte est réservée au Premium. » | Oui |
| Notifications WhatsApp | Standard (quota) / Premium (quota élevé) | Compteur mensuel de messages | File d'attente suspendue au-delà du quota, fallback in-app | « Quota WhatsApp du mois atteint — passez au Premium pour un volume plus élevé. » | Oui |
| Module Comptabilité (Directeur/Comptable/Caissier) | Premium | Accès module (déjà existant : `module:accounting`) | 404 sur les routes concernées (déjà le comportement actuel) | « La Comptabilité est disponible à partir du Premium. » | Oui |
| Journal d'audit — historique étendu | Premium | Fenêtre de rétention | Entrées plus anciennes masquées/purgées | « L'historique complet est disponible en Premium. » | Oui |
| API IA / Chatbot (SchoolBot) | Premium (ou Add-on) | Vérification de plan avant émission du secret Bearer | Endpoint désactivé (503 déjà existant si non configuré) | « Activez le module IA pour connecter un assistant conversationnel. » | Oui, ou vente en Add-on |
| Multi-année académique / historique | Standard | Accès fonctionnalité | Une seule année active forcée | « L'historique multi-année est disponible à partir du Standard. » | Oui |

*Exemple illustratif (non codé) : un établissement en Freemium atteint 100 élèves → la création d'un nouvel élève est bloquée côté contrôleur → un message d'upgrade s'affiche avec un lien vers la page de changement de plan.*

---

## 13. Logique de montée en gamme

**Pourquoi choisir Freemium ?**
Pour tester la plateforme sans engagement, faire tourner la gestion pédagogique de base d'un petit établissement, et valider l'adéquation produit avant d'investir.

**Pourquoi passer au Standard ?**
Parce que l'établissement grandit (plus d'élèves/enseignants/classes que le plafond gratuit), ou parce qu'il veut gagner du temps (import en masse, bulletins PDF en un clic) et moderniser sa pédagogie (LMS, classes virtuelles, cartes scolaires). C'est la formule qui couvre le cycle scolaire complet.

**Pourquoi passer au Premium ?**
Parce que l'établissement (typiquement privé) a besoin de gérer ses finances dans le même outil : frais de scolarité, salaires, caisse, reçus, grand livre — au lieu de jongler entre l'ERP scolaire et un outil de comptabilité séparé. Le Premium est aussi le choix des établissements à fort volume (WhatsApp, classes virtuelles, stockage) ou qui veulent un support prioritaire et l'assistant IA.

---

## 14. Profils d'établissements par plan

**Freemium** — Petit établissement (souvent public), équipe administrative réduite, budget numérique limité, ou établissement en phase de découverte/évaluation du produit avant engagement.

**Standard** — Établissement de taille moyenne à grande (primaire/collège/lycée), avec une équipe pédagogique établie, un besoin réel de gestion quotidienne digitalisée (notes, présences, bulletins, LMS), public ou privé sans besoin de comptabilité intégrée.

**Premium** — Établissement privé avec gestion financière propre (frais de scolarité, salaires, caisse), établissement à effectifs importants, ou groupe scolaire souhaitant le meilleur niveau de service, de volume et de support.

*Ces profils sont des repères, pas des seuils imposés — un établissement public à forts effectifs peut très bien avoir besoin du Standard pour ses volumes, sans avoir besoin de la Comptabilité.*

---

## 15. Add-ons / options envisageables

| Add-on | Justification |
|---|---|
| Volume supplémentaire de notifications WhatsApp | Coût variable direct (API tierce facturée au message) — vente à l'usage plus juste qu'un plafond rigide |
| Stockage supplémentaire (documents, leçons, justificatifs) | Coût d'infrastructure direct |
| Élèves/enseignants supplémentaires au-delà du plafond du plan | Permet de rester sur un plan inférieur tout en dépassant temporairement un plafond |
| Personnalisation avancée de la carte scolaire (si non incluse au plan) | Fonctionnalité déjà présente techniquement (`AdminCardController::settings`), séparable |
| Accès à l'API IA / Chatbot (SchoolBot) en dehors du Premium | Fonctionnalité déjà isolée techniquement (secret Bearer dédié), vendable indépendamment |
| Établissement supplémentaire (pour un même client gérant plusieurs écoles) | Nécessite un développement produit (regroupement client), mais séparable commercialement dès sa sortie |
| Formation du personnel à l'outil | Service, pas une fonctionnalité logicielle |
| Accompagnement à la migration de données existantes | Service |

*Un add-on n'est proposé ici que lorsqu'il correspond à une fonctionnalité réellement isolable techniquement dans le code actuel (WhatsApp, stockage, cartes, IA) ou à un service humain clairement séparé du logiciel.*

---

## 16. Services complémentaires (hors abonnement logiciel)

Ces services ne doivent pas être considérés comme inclus dans un abonnement, quel qu'il soit :

- Installation et configuration initiale de l'établissement
- Migration/importation des données existantes depuis un autre système
- Formation du personnel administratif, des enseignants
- Accompagnement au démarrage (onboarding assisté)
- Support téléphonique/sur site (au-delà du support standard inclus)
- Personnalisation spécifique (développement sur mesure)
- Maintenance évolutive hors trajectoire produit standard

---

## 17. Comparaison de valeur

| Critère | Freemium | Standard | Premium |
|---|---|---|---|
| Pour qui ? | Petit établissement / test | Établissement moyen à grand | Établissement privé / fort volume |
| Positionnement | Découverte | Cœur de gamme | Haut de gamme complet |
| Niveau de gestion administrative | Basique | Complet | Complet + volumes élevés |
| Niveau pédagogique | Notes/présences/bulletin écran | + LMS, classes virtuelles, bulletins PDF | Identique Standard, volumes élevés |
| Niveau financier | Aucun | Aucun | Comptabilité complète (frais, salaires, caisse) |
| E-learning | Non disponible | Disponible | Disponible, volumes élevés |
| Ressources numériques (cartes, documents) | Non disponible | Cartes standard | Cartes personnalisables, stockage élevé |
| Reporting | Consultation seule | Export PDF/CSV | Export complet + audit illimité |
| Capacité (élèves/enseignants/classes) | Faible | Moyenne | Élevée / sur devis |
| Support | Communautaire | Standard | Prioritaire |

---

## 18. Matrice de priorité commerciale

| Fonctionnalité | Valeur utilisateur | Valeur commerciale | Coût potentiel | Potentiel d'upsell |
|---|---|---|---|---|
| Gestion élèves/enseignants/classes | Critique | Moyenne (attendu, pas différenciant) | Faible | Faible (mais nécessaire) |
| Notes / présences | Critique | Moyenne | Faible | Faible |
| Bulletin PDF en masse + QR | Forte | Forte | Moyen (CPU génération PDF) | Élevé |
| Import en masse | Forte | Forte | Faible | Élevé |
| E-learning / LMS | Forte | Forte | Moyen (stockage) | Élevé |
| Classes virtuelles | Forte | Moyenne à forte | Faible (Jitsi) | Élevé (forte valeur perçue) |
| Cartes scolaires QR | Moyenne | Moyenne | Faible | Moyen |
| Module Comptabilité complet | Critique (pour le privé) | Critique | Élevé (sécurité, support) | Critique — principal moteur de revenu |
| Notifications WhatsApp multilingue | Forte | Forte | Élevé (coût API variable) | Élevé — bon candidat add-on à l'usage |
| Journal d'audit | Moyenne | Faible à moyenne | Faible | Faible |
| API IA / Chatbot | Moyenne (encore expérimental) | Moyenne | Moyen (dépend fournisseur IA) | Moyen — bien en add-on |
| Espace parent connecté (inexistant) | Potentiellement critique | Potentiellement forte | Élevé (à construire) | À envisager pour une v2 |

---

## 19. Analyse stratégique et recommandations

**Le Freemium est-il suffisamment attractif ?**
Oui, à condition de garder le cœur pédagogique complet (élèves, notes, présences, bulletin à l'écran) et de ne limiter que les volumes et les fonctionnalités coûteuses/avancées (LMS, classes virtuelles, WhatsApp, comptabilité). Un Freemium qui bloquerait la consultation du bulletin ou la saisie de notes serait inutilisable et contre-productif.

**Le Freemium risque-t-il de cannibaliser le Standard ?**
Faible risque si les plafonds quantitatifs (élèves/enseignants/classes) sont calibrés en dessous de la taille réelle de la majorité des établissements cibles, et si l'import en masse + les bulletins PDF (fonctionnalités à fort gain de temps perçu) restent exclusivement Standard. Ce sont deux fonctionnalités que même un petit établissement finit par vouloir dès qu'il gère plus de quelques dizaines d'élèves.

**Le Standard contient-il suffisamment de valeur ?**
Oui — il couvre 100 % du cycle pédagogique/administratif d'un établissement classique (y compris LMS et classes virtuelles, qui sont des différenciateurs forts sur ce marché). C'est le plan qui doit être présenté comme **« le plus populaire »**, car il correspond au produit dans son intégralité pour tout établissement qui n'a pas de besoin financier intégré.

**Le Premium apporte-t-il une vraie différence ?**
Oui, et une différence nette : le module Comptabilité complet (trois portails métier, caisse, salaires, grand livre) est un bloc fonctionnel entier absent des autres plans, pas une simple histoire de volume. C'est le séparateur le plus défendable commercialement.

**Quelles fonctionnalités doivent absolument rester Premium ?**
Le module Comptabilité dans son ensemble (Directeur/Comptable/Caissier), la personnalisation avancée des cartes, l'historique d'audit illimité, et l'API IA/Chatbot (ou en add-on).

**Quelles limites sont les plus pertinentes ?**
Les notifications WhatsApp (coût variable réel par message) et le stockage de documents — ce sont les deux seules limites directement corrélées à un coût d'exploitation mesurable. Les plafonds d'élèves/enseignants/classes sont pertinents comme déclencheur d'upsell mais doivent être calibrés avec les vraies données de coût serveur/support.

**Quelles fonctionnalités devraient plutôt être des Add-ons ?**
Le volume de notifications WhatsApp au-delà d'un quota, le stockage supplémentaire, et l'accès à l'API IA/Chatbot pour un client Standard qui n'a pas besoin du Premium complet.

**Quelle formule devrait être présentée comme « la plus populaire » ?**
Le **Standard** — il correspond au produit pédagogique complet, sans le poids (et le besoin) de la comptabilité intégrée, ce qui correspond au profil du plus grand nombre d'établissements.

**Où placer le principal levier d'upsell ?**
Deux endroits complémentaires : (1) les plafonds quantitatifs Freemium → Standard, atteints naturellement par la croissance de l'établissement ; (2) le module Comptabilité complet Standard → Premium, déclenché par un besoin métier clair (gestion financière) plutôt que par une frustration artificielle.

---

## 20. Conclusion

Le produit actuel est **substantiellement plus riche** que ce que suggère la description historique du projet (« projet d'apprentissage Laravel ») : il couvre déjà la gestion pédagogique complète, un système de bulletins sénégalais avec vérification anti-fraude, un LMS avec quiz et classes virtuelles, des cartes scolaires QR, un module de comptabilité scolaire complet à trois rôles avec caisse et salaires, un système de notification WhatsApp multilingue (texte + audio Wolof/Pulaar), et une fondation technique de gating par module déjà en place (`enabled_modules` / `SchoolModules`) — directement réutilisable pour construire les trois formules décrites ici.

Deux zones ne doivent **jamais** être vendues comme existantes : un portail parent connecté, et un assistant IA en langage naturel intégré nativement à l'application (le microservice Python existe mais n'est pas branché à ce jour).

**Prochaines étapes suggérées (hors périmètre de ce document) :**
1. Valider avec l'équipe les plafonds quantitatifs de la section 10 à partir des coûts réels d'infrastructure et de l'API WhatsApp.
2. Fixer la tarification mensuelle/annuelle une fois le contenu des plans validé.
3. Concevoir le modèle de données du plan d'abonnement (probablement une extension du modèle `School` existant, sur le pattern `enabled_modules`).
4. Implémenter techniquement le feature gating décrit en section 12, module par module, en réutilisant le pattern `module:accounting` déjà en production.

**Tarification : à définir après validation du packaging et des coûts d'exploitation.**
