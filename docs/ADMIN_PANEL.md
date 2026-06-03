# 📚 Documentation du Panneau d'Administration

## 🔐 Comptes de Démonstration

### Compte Administrateur
- **Email** : admin.senegal@gmail.com
- **Mot de passe** : passer01

### Compte Enseignant
- **Email** : moussa.diop@gmail.com
- **Mot de passe** : passer01

### Compte Étudiant
- **Email** : amadou.diallo@gmail.com
- **Mot de passe** : passer01

## 🏗️ Architecture de l'Application

L'application suit l'architecture MVC (Modèle-Vue-Contrôleur) de Laravel :

1. **Modèles (Models)** : Représentent les données et la logique métier
2. **Vues (Views)** : Affichent les données aux utilisateurs
3. **Contrôleurs (Controllers)** : Gèrent les requêtes et la logique d'application
4. **Routes** : Définissent les points d'entrée de l'application

## 📂 Structure des Fichiers

### Contrôleurs (app/Http/Controllers/Admin/)
- `DashboardController.php` : Gère le tableau de bord administrateur
- `StudentController.php` : Gère les opérations CRUD pour les étudiants
- `TeacherController.php` : Gère les opérations CRUD pour les enseignants
- `ClassController.php` : Gère les opérations CRUD pour les classes
- `AcademicYearController.php` : Gère les années scolaires
- `PendingRegistrationController.php` : Gère les inscriptions en attente
- `TeacherAssignmentController.php` : Gère les affectations des enseignants

### Modèles (app/Models/)
- `User.php` : Modèle principal pour tous les utilisateurs (étudiants, enseignants, admin)
- `SchoolClass.php` : Représente une classe
- `AcademicYear.php` : Représente une année scolaire
- `TeacherAssignment.php` : Gère les affectations des enseignants aux classes
- `Subject.php` : Matières enseignées
- `Level.php` : Niveaux scolaires

### Vues (resources/views/admin/)
- `dashboard.blade.php` : Tableau de bord administrateur
- `students/` : Vues pour la gestion des étudiants
- `teachers/` : Vues pour la gestion des enseignants
- `classes/` : Vues pour la gestion des classes
- `academic-years/` : Vues pour la gestion des années scolaires
- `pending-registrations.blade.php` : Liste des inscriptions en attente

## 🔄 Fonctionnalités CRUD

### 1. Gestion des Étudiants

#### Création (Create)
```php
// StudentController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'identifier' => 'required|unique:users',
        // ... autres champs
    ]);

    $student = User::create($validated);
    return redirect()->route('admin.students.show', $student);
}
```

#### Lecture (Read)
- Liste des étudiants avec pagination
- Détails d'un étudiant avec ses informations complètes

#### Mise à jour (Update)
- Modification des informations personnelles
- Changement de classe

#### Suppression (Delete)
- Suppression d'un étudiant avec confirmation

### 2. Gestion des Enseignants

#### Création (Create)
- Formulaire d'ajout avec validation
- Attribution automatique du rôle "professeur"

#### Gestion des Matières
- Attribution des matières enseignées
- Gestion des disponibilités

### 3. Gestion des Classes

#### Création
- Sélection du niveau et de l'année scolaire
- Attribution d'un professeur principal

#### Affectation des Élèves
- Interface glisser-déposer pour affecter les élèves
- Vérification des capacités de la classe

### 4. Années Scolaires

#### Création
- Définition des dates de début et de fin
- Marquage comme année scolaire courante

#### Clôture
- Archivage des données de l'année
- **Rapports** : menu Admin → **Rapports** (`/admin/reports`) — bulletins PDF par classe, rapport de fin d'année PDF, exports CSV (synthèse, notes)

## 🔄 Workflow d'Inscription

1. **Soumission du Formulaire**
   - L'utilisateur remplit le formulaire d'inscription
   - Le statut est défini sur "en attente"

2. **Vérification par l'Administrateur**
   - Vérification des documents
   - Vérification des places disponibles

3. **Approbation/Rejet**
   - Approbation : Création du compte utilisateur
   - Rejet : Notification avec motif

4. **Notification**
   - Email de confirmation
   - Instructions de connexion

## 📊 Tableau de Bord

### Statistiques Principales
- Nombre total d'étudiants
- Nombre d'enseignants
- Nombre de classes
- Taux de remplissage des classes

### Dernières Activités
- Dernières inscriptions
- Derniers bulletins de notes ajoutés
- Prochains événements

## 🔗 Relations entre les Tables

### Étudiant → Classe
```php
// User.php
public function schoolClass()
{
    return $this->belongsTo(SchoolClass::class, 'class_id');
}

// SchoolClass.php
public function students()
{
    return $this->hasMany(User::class, 'class_id')
        ->where('role', 'eleve');
}
```

### Enseignant → Matières
```php
// User.php
public function subjects()
{
    return $this->belongsToMany(Subject::class, 'teacher_subjects');
}
```

### Classe → Année Scolaire
```php
// SchoolClass.php
public function academicYear()
{
    return $this->belongsTo(AcademicYear::class);
}
```

## 🛠️ Installation et Configuration

1. **Prérequis**
   - PHP 8.2+
   - Composer
   - Base de données MySQL/PostgreSQL

2. **Installation**
   ```bash
   git clone [url-du-projet]
   cd school-management
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configuration**
   - Configurer le fichier `.env` avec les informations de la base de données
   - Exécuter les migrations : `php artisan migrate`
   - Lancer le serveur : `php artisan serve`

## 📝 Bonnes Pratiques

1. **Sécurité**
   - Validation des entrées utilisateur
   - Protection CSRF activée
   - Gestion des rôles et permissions

2. **Performance**
   - Chargement optimisé des relations (with, eager loading)
   - Mise en cache des données fréquemment utilisées

3. **Maintenabilité**
   - Suivi des standards PSR
   - Commentaires explicites
   - Structure de dossiers logique
