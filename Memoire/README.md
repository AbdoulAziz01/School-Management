# Dossier Mémoire — EduManager

Ce dossier contient l'intégralité du **dossier de conception technique** généré à partir de l'analyse du code source de l'application **EduManager** (Laravel 12 + Laravel Breeze + MySQL), destiné à la partie *Analyse & Conception* du mémoire de fin d'études.

---

## Organisation des fichiers

### Fichier consolidé (tout-en-un)

| Fichier | Description |
|---|---|
| `Memoire.txt` | **Document complet** en texte brut (ASCII). À ouvrir avec n'importe quel éditeur (Notepad, Word, VS Code). |

### Fichiers séparés par section (pour une lecture ciblée et l'insertion directe dans Word)

| Fichier | Contenu |
|---|---|
| `01_Cahier_des_Charges.md` | Présentation générale + besoins fonctionnels par acteur (admin/enseignant/élève) + 24 règles de gestion |
| `02_Modelisation_UML.md` | Description textuelle de chaque diagramme UML + chemin vers le fichier PlantUML source |
| `03_Dictionnaire_de_Donnees.md` | Dictionnaire complet des 19 tables (champs, types, contraintes, descriptions) |
| `04_Architecture_Logicielle.md` | Justification MVC, architecture sécurité multicouche, micro-service IA, stack technologique |

### Diagrammes PlantUML (un fichier par diagramme)

Dossier : `diagrammes/`

| Fichier | Diagramme |
|---|---|
| `01_use_case.puml` | Diagramme de cas d'utilisation (3 acteurs + Service IA) |
| `02_class_diagram.puml` | Diagramme de classes (13 entités + relations Eloquent) |
| `03_sequence_auth.puml` | Séquence : authentification + redirection par rôle |
| `04_sequence_grades.puml` | Séquence : saisie de note + calcul du bulletin |
| `05_sequence_assignment.puml` | Séquence : affectation enseignant × classe × matière × année |
| `06_activity_attendance.puml` | Activité : prise de présence (appel) |
| `07_state_user.puml` | États-transitions : cycle de vie d'un compte utilisateur |
| `08_deployment.puml` | Déploiement : architecture matérielle 4 nœuds |

---

## Comment générer les images des diagrammes

### Option 1 — En ligne (le plus simple)

1. Rendez-vous sur [plantuml.com/plantuml](https://plantuml.com/plantuml) ou [plantuml.online](https://plantuml.online).
2. Ouvrez un fichier `.puml` du dossier `diagrammes/` et copiez son contenu.
3. Collez-le dans l'éditeur en ligne.
4. Téléchargez l'image au format PNG ou SVG.
5. Insérez-la dans votre document Word.

### Option 2 — Dans Visual Studio Code

1. Installez l'extension **PlantUML** de jebbs (`jebbs.plantuml`).
2. Installez Java + Graphviz (prérequis PlantUML).
3. Ouvrez un fichier `.puml` → `Alt+D` pour prévisualiser.
4. Clic droit → **Export Current Diagram** → choisir PNG/SVG.

### Option 3 — En ligne de commande

```bash
java -jar plantuml.jar Memoire/diagrammes/*.puml
```

Les images `.png` seront générées à côté de chaque fichier `.puml`.

---

## Ordre de lecture conseillé

Pour la rédaction du chapitre *Analyse & Conception* :

1. `01_Cahier_des_Charges.md` → introduction générale et règles métier
2. `02_Modelisation_UML.md` → lecture des diagrammes (dans l'ordre : cas d'utilisation → classes → séquence → activité → états → déploiement)
3. `03_Dictionnaire_de_Donnees.md` → détail physique de la base de données
4. `04_Architecture_Logicielle.md` → justification technique des choix d'architecture

---

## Points d'attention pour la soutenance

Trois points sont à surveiller (détaillés dans `04_Architecture_Logicielle.md` §4.5 et la conclusion) :

1. **Livewire n'est pas utilisé** dans le projet. Ne mentionnez pas Livewire dans la soutenance orale, ou soyez prêt·e à justifier pourquoi.
2. **Cohabitation de deux valeurs de rôles** (`teacher`/`professeur`, `eleve`/`student`) dans le code — c'est une dette technique héritée d'un refactoring.
3. **Règle RG-15** (calcul de la moyenne générale pondérée) est votre règle métier la plus technique. Apprenez-la par cœur.

---

*Dossier généré à partir de l'analyse automatique du code source (migrations, modèles Eloquent, contrôleurs, middlewares, routes).*
