# Présentation de Soutenance — Système de Gestion Scolaire
### Contenu complet pour Gamma · 15 diapositives · 10–15 minutes

> **Instructions Gamma** : Importer ce fichier en tant que document source ou copier chaque bloc diapositive dans l'éditeur. Choisir un thème sombre/professionnel (ex. : *Navy*, *Slate* ou *Midnight*). Police recommandée : **Inter** ou **Plus Jakarta Sans**.

---

## DIAPOSITIVE 1 — Page de garde

### Titre affiché
**Conception et Réalisation d'un Système de Gestion Scolaire**
*Application web centralisée avec gestion des rôles, des notes et des présences*

### Sous-éléments
- **Réalisé par :** [Prénom NOM] — [Prénom NOM] *(binôme)*
- **Encadrant :** [Prénom NOM de l'encadrant]
- **Établissement :** [Nom de l'université / école]
- **Filière :** [Intitulé de la filière]
- **Année académique :** 2024–2025

### Notes du présentateur
> Bonjour à tous. Je me présente : [Nom]. Avec mon binôme [Nom], nous avons travaillé sur la conception et la réalisation d'un système de gestion scolaire. Je vous remercie de l'attention que vous nous accordez. Nous allons vous présenter ce projet en environ 12 minutes, en commençant par le contexte qui a motivé ce travail.

### Visuels recommandés
- Logo de l'établissement en haut à gauche
- Illustration ou icône représentant une interface web scolaire (style flat design)
- Fond dégradé bleu marine ou vert académique

### Design
Fond sombre, titre centré en grande police blanche, sous-titre en gris clair, bande colorée en bas pour l'établissement et l'année.

---

## DIAPOSITIVE 2 — Contexte et Problématique

### Titre affiché
**Un constat terrain : des pratiques chronophages et fragiles**

### Contenu (puces)
**Contexte**
- Les établissements scolaires gèrent quotidiennement des données sensibles : notes, présences, emplois du temps, dossiers élèves
- Une majorité recourt encore à des **carnets papier** ou des **fichiers Excel disparates**
- Les données circulent par messagerie, hors de tout cadre sécurisé

**Problèmes identifiés**
- ❌ **Dispersion** des sources d'information — plusieurs versions d'un même fichier
- ❌ **Risques d'erreurs** lors des ressaisies et calculs manuels de moyennes
- ❌ **Absence de contrôle d'accès** — qui peut lire ou modifier quoi ?
- ❌ **Délais** importants pour produire bulletins et états de présence

**Enjeux**
- Fiabiliser les données académiques pour les élèves et l'administration
- Garantir la confidentialité des résultats scolaires
- Moderniser les pratiques sans former lourdement les utilisateurs

### Notes du présentateur
> La gestion d'un établissement scolaire repose sur des opérations répétitives mais critiques. Lors de notre analyse, nous avons constaté que beaucoup d'écoles jonglent encore entre carnets de notes, tableurs Excel et échanges de fichiers par email. Cela engendre des erreurs, des incohérences et une perte de temps significative, surtout en période de bulletin ou de conseil de classe. C'est cette réalité qui a motivé notre projet.

### Visuels recommandés
- Schéma "avant/après" : à gauche chaos de fichiers Excel/papier, à droite interface web unifiée
- Icônes pour chaque problème (cadenas brisé, fichiers éparpillés, horloge)

### Design
Fond blanc cassé ou bleu très clair, icônes colorées sur fond de carte (card layout), titre en bleu foncé.

---

## DIAPOSITIVE 3 — Objectifs du Projet

### Titre affiché
**Ce que le projet doit accomplir**

### Contenu
**Objectif général**
> Concevoir et développer une plateforme web centralisée permettant à un établissement scolaire de gérer ses données académiques de manière sécurisée, fiable et accessible selon les rôles.

**Objectifs spécifiques**
1. **Centraliser** les données : élèves, enseignants, classes, matières, notes, présences, emplois du temps dans une seule base relationnelle
2. **Sécuriser les accès** par un système de rôles (Administrateur / Enseignant / Élève)
3. **Automatiser** le cycle de vie des comptes : inscription → validation admin → activation
4. **Simplifier** la saisie des notes et le suivi des présences pour les enseignants
5. **Offrir** aux élèves un accès instantané à leurs résultats, bulletins et emplois du temps
6. **Intégrer** une assistance conversationnelle pour guider les utilisateurs

### Notes du présentateur
> Notre objectif principal était de remplacer les pratiques dispersées par une plateforme unique. Mais nous voulions aller plus loin : structurer les accès par rôle pour que chaque utilisateur ne voie que ce qui le concerne, automatiser les tâches répétitives comme le calcul des moyennes, et même proposer un assistant intégré pour les questions courantes.

### Visuels recommandés
- Diagramme en cercles concentriques ou pyramide : Objectif général au centre, objectifs spécifiques autour
- Ou liste numérotée avec icônes pour chaque objectif

### Design
Fond clair, chiffres circulaires colorés pour chaque objectif spécifique, citation de l'objectif général encadrée.

---

## DIAPOSITIVE 4 — Analyse de l'Existant

### Titre affiché
**État des lieux : méthodes actuelles et leurs limites**

### Tableau comparatif

| Méthode | Avantages | Limites |
|---|---|---|
| **Registres papier** | Simple, aucun outil requis | Non consultable à distance, risque de perte |
| **Tableurs Excel** | Calcul automatisé, familier | Fichiers non synchronisés, structures hétérogènes |
| **Hybride papier+Excel** | Trace locale + calcul | Multiples ressaisies, source d'erreurs |
| **Notre solution web** | Centralisée, sécurisée, accessible | Nécessite une connexion internet |

**Conclusions de l'analyse**
- Aucune solution existante ne couvrait **l'ensemble** du cycle : inscription → validation → notes → bulletin → présences
- Les outils disponibles n'offraient pas de **gestion des rôles** adaptée au contexte scolaire
- Le besoin d'un **référentiel unique** et d'une **traçabilité** des actions était non satisfait

### Notes du présentateur
> En analysant les pratiques, on identifie trois grandes familles. Le papier : simple mais limité. Le tableur : un progrès, mais chaque enseignant a son propre fichier, ses propres formules, sans synchronisation. L'hybride combine les deux, et multiplie les risques de transfert manuel. Aucune de ces méthodes ne propose un contrôle d'accès, une traçabilité ou une consultation unifiée par tous les acteurs de l'école.

### Visuels recommandés
- Tableau comparatif visuel (alternance lignes blanches / grises)
- Icône ✅ / ❌ dans chaque cellule pour lisibilité rapide

### Design
Table claire avec en-têtes colorés, dernière ligne de la table mise en valeur (fond bleu = notre solution).

---

## DIAPOSITIVE 5 — Solution Proposée

### Titre affiché
**Une plateforme web intégrée, centrée sur les besoins scolaires**

### Contenu
**Présentation générale**
> Une application web développée sur mesure pour un établissement scolaire, accessible depuis tout navigateur, avec trois espaces distincts selon le profil de l'utilisateur.

**Fonctionnalités principales**

| Espace | Fonctionnalités clés |
|---|---|
| 🔐 **Administrateur** | Gestion élèves, enseignants, classes, matières, années académiques ; validation des inscriptions |
| 📚 **Enseignant** | Saisie des notes, suivi des présences, consultation de l'emploi du temps |
| 🎓 **Élève** | Consultation des notes, bulletin scolaire, emploi du temps, présences |
| 🤖 **Assistant** | Widget Botpress + interface de chat intégrée |

**Bénéfices attendus**
- ✅ Une seule source de vérité pour toutes les données académiques
- ✅ Réduction du temps de consolidation des résultats
- ✅ Accès instantané depuis tout appareil connecté
- ✅ Traçabilité complète des actions dans le système

### Notes du présentateur
> Notre solution est une application web accessible depuis n'importe quel navigateur. Elle propose trois espaces distincts : l'administrateur gère la structure de l'établissement, l'enseignant gère ses classes et ses notes, et l'élève consulte ses résultats et son emploi du temps. Un assistant conversationnel vient compléter le tout pour orienter les utilisateurs dans leurs questions courantes.

### Visuels recommandés
- Capture d'écran de la page d'accueil ou du tableau de bord administrateur
- Ou mockup montrant les 3 tableaux de bord côte à côte

### Design
Fond sombre, 3 cartes côte à côte (une par rôle) avec icône, couleur distincte et liste des fonctionnalités.

---

## DIAPOSITIVE 6 — Méthodologie

### Titre affiché
**Démarche, outils et technologies**

### Contenu

**Démarche : Agile / Scrum**
- Travail en **sprints** de 1 à 2 semaines avec livraisons incrémentales
- Rituels Scrum : Sprint Planning, Daily Scrum, Sprint Review, Rétrospective
- **Product Backlog** maintenu et priorisé tout au long du projet
- Collaboration sur **GitHub** : branches par fonctionnalité, Pull Requests, Issues

**Technologies utilisées**

| Couche | Technologie | Version |
|---|---|---|
| Back-end | Laravel (PHP) | 11 / PHP 8.2 |
| Front-end | Bootstrap | 5.3 |
| Base de données | MySQL | 8.0 |
| Authentification | Laravel Breeze + Spatie Roles | — |
| Assistant | Botpress + Chat interne | — |
| Versionning | Git / GitHub | — |
| Déploiement local | XAMPP | — |

**Outils de développement**
- VS Code · PHPStorm · phpMyAdmin · Postman · GitHub Projects (Kanban)

### Notes du présentateur
> Pour organiser notre travail, nous avons adopté une démarche Agile inspirée de Scrum. Cela nous a permis de livrer le projet par morceaux testables, d'identifier rapidement les blocages et de nous adapter. GitHub a été notre plateforme centrale : chaque fonctionnalité était développée sur sa propre branche, puis intégrée après revue. Côté technologies, nous avons choisi Laravel pour sa robustesse et son écosystème, Bootstrap 5 pour les interfaces, et MySQL pour la base relationnelle.

### Visuels recommandés
- Tableau des technologies avec logos (Laravel, PHP, MySQL, Bootstrap, GitHub)
- Ou diagramme de timeline des sprints

### Design
Fond gris clair ou blanc, icônes technologiques colorées, timeline des sprints en bas de diapositive.

---

## DIAPOSITIVE 7 — Analyse et Conception

### Titre affiché
**Modélisation du système : acteurs et interactions**

### Contenu

**Acteurs du système**
- **Administrateur** : acteur principal de la gestion de la structure
- **Enseignant** : acteur de la gestion pédagogique
- **Élève** : acteur consultatif, bénéficiaire final
- **Système** (automatismes : génération d'identifiants, envoi d'emails, calcul de moyennes)

**Principaux cas d'utilisation**

| Acteur | Cas d'utilisation |
|---|---|
| Administrateur | Gérer les élèves · Valider les inscriptions · Affecter enseignants/classes · Configurer l'année académique |
| Enseignant | Saisir les notes · Marquer les présences · Consulter l'emploi du temps |
| Élève | Consulter notes/bulletin · Voir emploi du temps · Contacter l'assistant |
| Tous | S'inscrire · Se connecter · Gérer son profil |

**Modèle de données clé**
Tables principales : `users` · `school_classes` · `subjects` · `grades` · `attendances` · `timetables` · `academic_years` · `assignments`

### Notes du présentateur
> Avant de coder, nous avons modélisé le système. Trois acteurs principaux interagissent avec la plateforme. L'administrateur est le gestionnaire de la structure. L'enseignant interagit avec ses classes au quotidien. L'élève est le bénéficiaire final. Le modèle de données est articulé autour d'une dizaine de tables relationnelles qui couvrent l'ensemble du cycle scolaire.

### Visuels recommandés
- **Diagramme de cas d'utilisation UML** (simplifié) : les 3 acteurs avec leurs cas respectifs
- Ou schéma entités-relations simplifié de la base de données

### Design
Fond blanc, diagramme UML centré, légende avec couleurs distinctes par acteur.

---

## DIAPOSITIVE 8 — Architecture du Système

### Titre affiché
**Architecture MVC Laravel — flux d'information**

### Contenu

**Architecture générale : MVC (Modèle–Vue–Contrôleur)**

```
Navigateur (Bootstrap 5)
        ↓  requête HTTP
    Routes Laravel
        ↓
  Middleware (Auth + Rôles)
        ↓
  Contrôleur (logique métier)
        ↓            ↓
  Modèle Eloquent   Vues Blade
        ↓
  Base MySQL
```

**Composants clés**
- **Routes** : séparées par rôle (`/admin/...`, `/teacher/...`, `/student/...`)
- **Middleware** : contrôle d'accès par rôle Spatie + vérification du statut du compte
- **Modèles Eloquent** : relations entre entités (`User`, `SchoolClass`, `Grade`, `Attendance`, etc.)
- **Services** : logique métier déportée (génération bulletins, promotions d'élèves)
- **Mails** : envoi automatique des identifiants après validation du compte

**Sécurité intégrée**
- Protection CSRF sur tous les formulaires
- Limitation des tentatives de connexion (rate limiting)
- Séparation stricte des espaces par middleware de rôle

### Notes du présentateur
> L'architecture suit le patron MVC de Laravel. Chaque requête du navigateur est d'abord interceptée par les middlewares qui vérifient l'authentification et le rôle de l'utilisateur. Le contrôleur traite ensuite la logique métier, interroge les modèles Eloquent qui communiquent avec la base MySQL, puis retourne une vue Blade. Cette séparation claire facilite la maintenance et la sécurité.

### Visuels recommandés
- Schéma de flux MVC (diagramme de blocs avec flèches)
- Arbre de routes montrant la séparation `/admin`, `/teacher`, `/student`

### Design
Fond sombre, schéma de flux centré avec blocs colorés et flèches blanches, légende à droite.

---

## DIAPOSITIVE 9 — Réalisation

### Titre affiché
**Interfaces développées — démonstration des parcours clés**

### Contenu

**Module 1 — Authentification & Inscription**
- Page de connexion avec gestion du statut (en attente / approuvé / rejeté)
- Inscription élève avec sélection de classe souhaitée
- Inscription enseignant avec sélection de matières

**Module 2 — Espace Administrateur**
- Tableau de bord : statistiques globales (nb élèves, enseignants, classes, inscriptions en attente)
- Validation des inscriptions en un clic avec envoi automatique d'identifiants par email
- CRUD complet : élèves, enseignants, classes, matières, années académiques, emplois du temps

**Module 3 — Espace Enseignant**
- Tableau de bord avec ses classes assignées
- Saisie et modification des notes par classe et matière
- Gestion des présences avec historique

**Module 4 — Espace Élève**
- Tableau de bord personnalisé avec résumé des notes et prochains cours
- Consultation des notes, du bulletin scolaire (trimestriel et annuel)
- Emploi du temps interactif, historique des présences

**Module 5 — Assistant conversationnel**
- Widget Botpress intégré sur toutes les pages
- Interface de chat interne (`/chat`) pour les questions sur le fonctionnement

### Notes du présentateur
> Passons maintenant à la réalisation concrète. L'application compte cinq modules principaux. Je vais vous montrer les interfaces les plus représentatives. [Montrer les captures d'écran une à une.] En commençant par la page de connexion, on peut voir que l'application gère différents états du compte. Le tableau de bord administrateur centralise tout. L'espace enseignant est pensé pour la rapidité de saisie. L'espace élève est épuré pour faciliter la consultation.

### Visuels recommandés
- **4 à 6 captures d'écran** des interfaces principales en grille 2×3 :
  1. Page de connexion
  2. Tableau de bord Admin
  3. Liste des inscriptions en attente
  4. Interface de saisie des notes (enseignant)
  5. Tableau de bord élève avec notes
  6. Page bulletin ou interface chat

### Design
Galerie de captures d'écran avec ombres portées, légendes courtes sous chaque capture, fond neutre.

---

## DIAPOSITIVE 10 — Résultats Obtenus

### Titre affiché
**Bilan : ce que le système apporte concrètement**

### Contenu

**Gains organisationnels**

| Avant | Après |
|---|---|
| Fichiers Excel dispersés | Base de données unique et cohérente |
| Calculs manuels de moyennes | Calcul automatisé en temps réel |
| Bulletins produits en plusieurs jours | Bulletin disponible en quelques clics |
| Présences dans des registres papier | Saisie et consultation en ligne |
| Identifiants transmis verbalement | Email automatique après validation |

**Fiabilité**
- Contraintes d'intégrité référentielle en base de données
- Validation des formulaires côté serveur (Laravel Form Requests)
- Tests manuels systématiques sur tous les parcours critiques

**Sécurité**
- Protection CSRF sur l'ensemble des formulaires
- Contrôle d'accès par rôle sur chaque route sensible
- Limitation des tentatives de connexion (brute-force protection)
- Vérification du statut du compte avant tout accès aux données

**Expérience utilisateur**
- Interface 100 % en français
- Navigation par tableau de bord adapté au rôle
- Messages d'erreur et de confirmation explicites

### Notes du présentateur
> En termes de résultats, le système répond à tous les besoins identifiés en analyse. La consolidation des notes et la génération de bulletins, qui pouvaient prendre plusieurs heures, se font maintenant en quelques clics. La traçabilité est assurée. La sécurité est intégrée à chaque niveau : formulaires, routes, middleware. L'interface en français avec des messages clairs facilite l'adoption par des utilisateurs non informaticiens.

### Visuels recommandés
- Tableau "Avant / Après" avec icônes ❌ et ✅
- Barre de progression ou indicateurs de gains (ex. : "100% de routes protégées par rôle")

### Design
Fond blanc, tableau comparatif visuel centré, badges colorés pour les points de sécurité.

---

## DIAPOSITIVE 11 — Difficultés Rencontrées et Solutions

### Titre affiché
**Défis techniques et solutions mises en œuvre**

### Contenu

| Défi rencontré | Solution appliquée |
|---|---|
| **Authentification par identifiant** (et non email) | Personnalisation de `LoginRequest` pour utiliser le champ `identifier` au lieu de `email` |
| **Erreur CSRF 419** lors de la connexion | Correction de la configuration de session + token CSRF dans le formulaire |
| **Gestion du statut du compte** (en attente, approuvé, rejeté) | Middleware personnalisé vérifiant le statut avant tout accès |
| **Redirection selon le rôle** après connexion | Logique de redirection dans `AuthenticatedSessionController` selon le rôle Spatie |
| **Cohérence des données** sur plusieurs tables liées | Transactions Eloquent + contraintes de clés étrangères |
| **Intégration de l'assistant IA** avec variables d'environnement | Détection de la configuration serveur + fallback gracieux si service non disponible |
| **Gestion des années académiques** multiples | Scope Eloquent `currentYear` + provisionnement automatique via `AcademicYearProvisioner` |

### Notes du présentateur
> Tout projet technique rencontre des obstacles. Le premier défi notable était l'authentification par identifiant personnalisé plutôt que par email, ce qui a nécessité de surcharger plusieurs composants Laravel. La gestion des statuts de compte — en attente, approuvé, rejeté — a nécessité un middleware dédié. Ces problèmes, bien que techniques, nous ont permis de mieux maîtriser le framework et de produire une solution plus robuste.

### Visuels recommandés
- Tableau deux colonnes (défi en rouge / solution en vert)
- Ou blocs d'alerte stylisés (🔴 Problème → 🟢 Solution)

### Design
Fond gris très clair, icônes d'alerte orange pour les défis, icônes de validation verte pour les solutions.

---

## DIAPOSITIVE 12 — Conclusion

### Titre affiché
**Un système opérationnel qui répond aux besoins identifiés**

### Contenu

**Ce qui a été réalisé**
- ✅ Plateforme web complète en **Laravel 11** avec **Bootstrap 5**
- ✅ Trois espaces utilisateurs distincts avec **contrôle d'accès par rôle**
- ✅ Cycle de vie complet des comptes : inscription → validation → activation
- ✅ Gestion des **notes**, **présences**, **emplois du temps**, **bulletins**
- ✅ **Assistant conversationnel** intégré (Botpress + chat interne)
- ✅ Développement collaboratif avec **Git/GitHub** en démarche **Agile**

**Atteinte des objectifs**
> Tous les objectifs initiaux ont été atteints : centralisation des données, sécurisation des accès, automatisation des tâches répétitives, et amélioration de l'expérience utilisateur pour les trois profils.

**Impact**
> Ce système transforme une gestion fragmentée et chronophage en un flux de travail numérique fluide, traçable et sécurisé — adaptable à tout établissement scolaire.

### Notes du présentateur
> Pour conclure, notre système répond à l'ensemble des besoins que nous avions identifiés en début de projet. Nous avons livré une plateforme fonctionnelle couvrant le cycle complet : de l'inscription d'un élève jusqu'à la consultation de son bulletin. La démarche Agile nous a permis de rester alignés sur les priorités réelles. Ce projet nous a également permis de maîtriser des compétences professionnelles concrètes en développement web full-stack.

### Visuels recommandés
- Checklist visuelle avec coches vertes
- Ou montage final de 3 interfaces (une par rôle) avec une flèche centrale "Un seul système"

### Design
Fond dégradé bleu marine, checklist blanche sur fond sombre, citation en encadré central.

---

## DIAPOSITIVE 13 — Perspectives

### Titre affiché
**Et demain ? Extensions et améliorations envisagées**

### Contenu

**Améliorations techniques prioritaires**
- 🔄 Mise en place d'un **pipeline CI/CD** (GitHub Actions) pour exécuter les tests automatiquement à chaque push
- 🧪 Couverture complète par des **tests automatisés** (PHPUnit / Pest) pour garantir la non-régression
- 🔒 Durcissement de la sécurité serveur avant déploiement institutionnel (HTTPS, sauvegardes, conformité RGPD)

**Extensions fonctionnelles**
- 📊 **Tableau de bord de pilotage** pour la direction : taux de réussite, statistiques par niveau, indicateurs d'assiduité
- 📄 **Export officiel** : génération de bulletins en PDF, attestations scolaires, relevés de notes
- 📱 **Application mobile** (PWA ou app native) pour une consultation optimale sur smartphone
- 📧 **Notifications** email/SMS : rappels d'absence, publication des résultats, événements importants
- 🤖 **IA avancée** : analyse prédictive des résultats, détection précoce des élèves en difficulté
- 🌐 **API REST documentée** pour intégration avec des services tiers (administration, ENT national)

### Notes du présentateur
> Ce projet est une base solide, mais plusieurs pistes d'évolution sont naturelles. Sur le plan technique, la priorité est d'automatiser les tests et de mettre en place un déploiement continu. Fonctionnellement, l'ajout d'un tableau de bord de pilotage pour la direction, la génération de PDF officiels, et des notifications automatiques enrichiraient significativement la valeur du système. À plus long terme, l'intelligence artificielle pourrait aider à détecter les élèves en difficulté avant qu'il ne soit trop tard.

### Visuels recommandés
- Roadmap visuelle (timeline horizontale avec jalons futurs)
- Ou grille d'icônes avec chaque perspective étiquetée

### Design
Fond neutre, icônes colorées en grille 2×3, titre de section en gras, description concise sous chaque icône.

---

## DIAPOSITIVE 14 — Remerciements

### Titre affiché
**Remerciements**

### Contenu
> Nous tenons à exprimer notre profonde gratitude à toutes les personnes qui ont contribué à la réussite de ce projet.

- **À notre encadrant**, [Prénom NOM], pour ses conseils avisés, sa disponibilité et ses orientations tout au long de ce travail.
- **Au jury**, pour le temps consacré à l'évaluation de ce mémoire et l'intérêt porté à notre travail.
- **À nos familles et proches**, pour leur soutien moral et leurs encouragements constants.
- **À nos camarades de promotion**, pour les échanges enrichissants et l'entraide dans les moments difficiles.
- **À la communauté open source** : Laravel, Bootstrap, Botpress — dont les outils ont rendu ce projet possible.

### Notes du présentateur
> Avant de clore cette présentation, nous tenons à remercier sincèrement notre encadrant pour sa guidance précieuse. Nous remercions également les membres du jury pour leur présence et l'attention qu'ils portent à notre travail. Enfin, un grand merci à nos familles pour leur soutien indéfectible.

### Visuels recommandés
- Fond sobre avec un élément graphique discret (ex. : formes géométriques douces)
- Photo de l'équipe ou illustration symbolique (mains tendues, équipe, étoiles)

### Design
Fond dégradé chaud (bleu → violet doux), texte centré en blanc, mise en page aérée et sobre.

---

## DIAPOSITIVE 15 — Questions et Réponses

### Titre affiché
**Merci de votre attention**
**Place aux questions**

### Contenu
*(Diapositive épurée — peu de texte)*

> « La technologie ne remplace pas les enseignants — elle leur donne du temps pour enseigner. »

**Coordonnées / Dépôt**
- 📧 [email de l'étudiant]
- 🔗 github.com/[username]/school-management *(si public)*

### Notes du présentateur
> Je vous remercie pour votre écoute. Nous sommes maintenant disponibles pour répondre à vos questions. [Rester calme, reformuler la question avant de répondre, et s'appuyer sur les diapositives précédentes si nécessaire.]

### Visuels recommandés
- Grand point d'interrogation stylisé ou illustration minimaliste
- Fond sobre avec titre centré en grande police

### Design
Fond sombre avec titre centré en grande police blanche, citation en italique, lien GitHub discret en bas.

---

## AIDE À L'IMPORT DANS GAMMA

Pour utiliser ce document dans **Gamma** :

1. Aller sur [gamma.app](https://gamma.app) → **Créer une présentation**
2. Choisir **"Importer"** → coller le contenu de ce fichier
3. Sélectionner un thème sombre professionnel (ex. *Navy*, *Slate*, *Midnight*)
4. Police recommandée : **Plus Jakarta Sans** ou **Inter**
5. Palette de couleurs suggérée :
   - Couleur principale : `#1e3a5f` (bleu marine)
   - Accent : `#3b82f6` (bleu vif)
   - Texte secondaire : `#64748b` (gris ardoise)
   - Succès : `#22c55e` (vert)
   - Alerte : `#ef4444` (rouge)
6. Pour chaque diapositive marquée **"Visuels recommandés"**, ajouter les captures d'écran réelles de l'application ou les diagrammes exportés depuis votre outil de modélisation (draw.io, Lucidchart, StarUML).

---

*Document généré pour la soutenance académique — à personnaliser avec les informations réelles de l'étudiant et les captures d'écran de l'application.*
