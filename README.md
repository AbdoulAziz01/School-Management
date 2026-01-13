# 🎓 School Management System - Laravel

> **Projet d'apprentissage Laravel à travers une application réelle**

Application web de gestion d'établissement scolaire développée dans le cadre de l'apprentissage du framework Laravel. Ce projet permet de gérer élèves, professeurs et administrateurs avec des interfaces et permissions distinctes.

![Laravel](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple?style=flat-square&logo=bootstrap)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql)

---

## 🔧 Corrections et améliorations apportées

### Corrections de bugs
- **Erreur 419 (Page Expired) lors de la connexion**
  - Correction du formulaire de connexion pour utiliser correctement le jeton CSRF
  - Mise à jour de la configuration de la session dans `config/session.php`
  - Nettoyage du cache de configuration et des vues

- **Problèmes d'authentification**
  - Mise à jour du modèle `User` pour implémenter `MustVerifyEmail`
  - Ajout des méthodes `findForPassport` et `validateForPassportPasswordGrant` pour l'authentification avec le champ `identifier`
  - Correction de la méthode `throttleKey` dans `LoginRequest` pour utiliser `identifier` au lieu de `email`

- **Redirections après connexion**
  - Mise à jour de `AuthenticatedSessionController` pour rediriger vers le bon tableau de bord selon le rôle
  - Ajout de la vérification du statut de l'utilisateur (approuvé/en attente/rejeté)
  - Correction de la redirection après vérification d'email dans `VerifyEmailController`

### Améliorations
- **Sécurité renforcée**
  - Vérification du statut de l'utilisateur avant l'authentification
  - Protection contre les attaques par force brute avec limitation des tentatives de connexion
  - Vérification de l'email avant l'accès aux tableaux de bord

- **Expérience utilisateur**
  - Messages d'erreur plus clairs pour les utilisateurs en attente de validation
  - Redirection automatique vers le tableau de bord approprié après connexion
  - Meilleure gestion des erreurs de formulaire

- **Base de données**
  - Mise à jour du seeder pour créer des utilisateurs de test avec des rôles et des statuts appropriés
  - Correction des migrations pour éviter les conflits de colonnes

## 📖 Contexte du projet

Ce projet est né d'un objectif simple : **apprendre Laravel en construisant quelque chose de concret**. Plutôt que de suivre des tutoriels déconnectés de la réalité, l'idée était de créer une vraie application de gestion d'école avec :

- **3 types d'utilisateurs** : Admin, Professeur, Élève
- **Des fonctionnalités réelles** : inscription, validation, gestion des rôles
- **Une architecture professionnelle** : middleware, contrôleurs, authentification

**Objectif pédagogique :** Comprendre Laravel à travers la résolution de vrais problèmes techniques.

---

## 🎯 Objectifs du projet

### Objectif général
Créer un système de gestion scolaire (School Management System) avec Laravel permettant de gérer les utilisateurs selon leurs rôles.

### Objectifs spécifiques
- ✅ Mettre en place un système d'authentification sécurisé
- ✅ Implémenter un système de rôles (admin, teacher, student)
- ✅ Créer un processus d'inscription avec validation par l'admin
- ✅ Développer des interfaces distinctes pour chaque rôle
- ✅ Générer automatiquement des identifiants uniques
- ✅ Sécuriser l'accès aux différentes zones de l'application

---

## ✨ Fonctionnalités implémentées

### 🔐 Système d'authentification
- ✅ Inscription avec sélection de rôle (Élève ou Professeur uniquement)
- ✅ Génération automatique d'identifiants uniques (E2026001, P2026001, etc.)
- ✅ Statut "pending" par défaut pour les nouvelles inscriptions
- ✅ Connexion sécurisée avec vérification du statut
- ✅ Déconnexion avec protection CSRF
- ✅ Un seul admin dans le système (sécurité)

### 👨‍💼 Espace Administrateur
- ✅ Dashboard avec statistiques
- ✅ Gestion des inscriptions en attente
- ✅ Validation ou rejet des nouveaux comptes
- ✅ Badge de notification (nombre d'inscriptions en attente)
- ✅ Accès protégé par middleware
- 🚧 Gestion complète des élèves (à venir)
- 🚧 Gestion des professeurs (à venir)
- 🚧 Gestion des classes et matières (à venir)

### 👨‍🏫 Espace Professeur
- ✅ Dashboard personnalisé
- ✅ Navigation adaptée au rôle
- 🚧 Mes classes (à venir)
- 🚧 Mes matières (à venir)
- 🚧 Saisie des notes (à venir)

### 👨‍🎓 Espace Élève
- ✅ Dashboard personnalisé
- ✅ Navigation adaptée au rôle
- 🚧 Mes matières (à venir)
- 🚧 Mon emploi du temps (à venir)
- 🚧 Mes résultats (à venir)

---

## 🛠️ Technologies utilisées

### Backend
- **Laravel 11.x** - Framework PHP moderne et élégant
- **PHP 8.2** - Langage de programmation côté serveur
- **MySQL 8.0** - Système de gestion de base de données
- **Laravel Breeze** - Kit d'authentification minimaliste

### Frontend
- **Bootstrap 5.3** - Framework CSS responsive
- **Blade** - Moteur de templates de Laravel
- **JavaScript Vanilla** - Interactions côté client

### Outils de développement
- **Composer** - Gestionnaire de dépendances PHP
- **NPM** - Gestionnaire de paquets JavaScript
- **XAMPP** - Environnement de développement (Apache + MySQL + PHP)
- **Git & GitHub** - Contrôle de version et hébergement du code
- **VS Code** - Éditeur de code

📝 PARTIE 2 - Installation complète

## 📥 Installation

### Prérequis système

Avant de commencer, assurez-vous d'avoir :

- **PHP >= 8.2** avec les extensions : OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js >= 16** et NPM
- **MySQL >= 8.0** ou MariaDB
- **Git** (pour cloner le projet)
- **XAMPP** ou tout autre serveur local

### Vérifier vos installations

```bash
# Vérifier PHP
php -v

# Vérifier Composer
composer -V

# Vérifier Node.js et NPM
node -v
npm -v

# Vérifier MySQL
mysql --version

Installation pas à pas
Étape 1 : Cloner le projet
git clone https://github.com/votre-username/school-management.git
cd school-management

Étape 2 : Installer les dépendances PHP
bash
composer install
Cette commande installe tous les packages PHP nécessaires définis dans composer.json, notamment Laravel et ses dépendances.

Étape 3 : Installer les dépendances JavaScript
bash
npm install
Cette commande installe Bootstrap, Vite et autres packages frontend.

Étape 4 : Configurer le fichier d'environnement
bash
# Copier le fichier d'exemple
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
La clé d'application est utilisée pour chiffrer les sessions et autres données sensibles.

Étape 5 : Configurer la base de données
Ouvrez le fichier .env et modifiez ces lignes :

text
APP_NAME="School Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_management_system
DB_USERNAME=root
DB_PASSWORD=
Note : Laissez DB_PASSWORD vide si vous utilisez XAMPP par défaut.

Étape 6 : Créer la base de données
Option A : Via phpMyAdmin

Ouvrez phpMyAdmin : http://localhost/phpmyadmin

Cliquez sur "Nouvelle base de données"

Nom : school_management_system

Interclassement : utf8mb4_unicode_ci

Cliquez sur "Créer"

Option B : Via ligne de commande MySQL

bash
mysql -u root -p
Puis dans MySQL :

sql
CREATE DATABASE school_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
Étape 7 : Exécuter les migrations
bash
php artisan migrate
Cette commande crée toutes les tables nécessaires :

users (avec colonnes : id, identifier, name, email, role, status, password, etc.)

password_reset_tokens

sessions

cache

Si vous voyez une erreur, vérifiez que :

MySQL est bien démarré dans XAMPP

La base de données existe

Les identifiants dans .env sont corrects

Étape 8 : Créer le compte administrateur
bash
php artisan tinker
Dans la console Tinker qui s'ouvre, tapez ces commandes une par une :

php
$admin = new App\Models\User();
$admin->identifier = 'ADMIN';
$admin->name = 'Administrateur';
$admin->email = 'admin@ecole.com';
$admin->password = Hash::make('admin123');
$admin->role = 'admin';
$admin->status = 'approved';
$admin->save();
exit
Explication :

identifier : Identifiant unique (ADMIN)

name : Nom affiché dans l'interface

email : Email pour la connexion

password : Mot de passe hashé avec bcrypt

role : Rôle 'admin' pour accès complet

status : 'approved' pour pouvoir se connecter immédiatement

Étape 9 : Compiler les assets frontend
bash
npm run dev
Cette commande compile Bootstrap, CSS et JavaScript. Laissez ce terminal ouvert pendant le développement pour le rechargement automatique.

Pour la production :

bash
npm run build
Étape 10 : Lancer le serveur de développement
Ouvrez un nouveau terminal et lancez :

bash
php artisan serve
Vous devriez voir :

text
INFO  Server running on [http://127.0.0.1:8000](http://127.0.0.1:8000)
Étape 11 : Accéder à l'application
Ouvrez votre navigateur et allez sur : http://127.0.0.1:8000

Identifiants administrateur :

Email : admin@ecole.com

Mot de passe : admin123

⚠️ Problèmes courants et solutions
Erreur : "SQLSTATE[HY000] [1045] Access denied"
Solution : Vérifiez les identifiants MySQL dans .env

Erreur : "Class 'Hash' not found"
Solution : Utilisez Illuminate\Support\Facades\Hash ou assurez-vous d'être dans Tinker

Erreur : "Column 'identifier' doesn't have a default value"
Solution : Vérifiez que la migration add_role_and_status_to_users_table a bien été exécutée

Erreur 419 "Page Expired" lors de la déconnexion
Solution : Ne tapez jamais /logout directement dans l'URL, utilisez toujours le bouton de déconnexion

Le serveur npm ne démarre pas
Solution :

bash
rm -rf node_modules package-lock.json
npm install
npm run dev
🔄 Commandes utiles
bash
# Relancer les migrations (⚠️ efface toutes les données)
php artisan migrate:fresh

# Voir les routes disponibles
php artisan route:list

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Arrêter le serveur
Ctrl + C dans le terminal

# Voir les logs d'erreur
tail -f storage/logs/laravel.log

📝 PARTIE 3 - Structure et Architecture

## 📁 Structure complète du projet

school-management/
├── app/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── Auth/
│ │ │ │ ├── AuthenticatedSessionController.php
│ │ │ │ ├── RegisteredUserController.php # Gestion inscription personnalisée
│ │ │ │ └── ...
│ │ │ ├── AdminController.php # Contrôleur admin
│ │ │ ├── TeacherController.php # Contrôleur professeur
│ │ │ └── StudentController.php # Contrôleur élève
│ │ └── Middleware/
│ │ ├── RoleMiddleware.php # Contrôle d'accès par rôle
│ │ └── ...
│ └── Models/
│ └── User.php # Modèle utilisateur avec rôles
│
├── bootstrap/
│ └── app.php # Configuration middleware + routes
│
├── database/
│ ├── migrations/
│ │ ├── 2014_10_12_000000_create_users_table.php
│ │ └── xxxx_add_role_and_status_to_users_table.php # Migration rôles + statuts
│ └── seeders/
│ └── DatabaseSeeder.php
│
├── resources/
│ ├── views/
│ │ ├── layouts/
│ │ │ └── app.blade.php # Layout principal avec sidebar
│ │ ├── auth/
│ │ │ ├── login.blade.php # Page de connexion
│ │ │ └── register.blade.php # Page d'inscription personnalisée
│ │ ├── admin/
│ │ │ ├── dashboard.blade.php # Dashboard administrateur
│ │ │ └── pending-registrations.blade.php # Gestion inscriptions en attente
│ │ ├── teacher/
│ │ │ └── dashboard.blade.php # Dashboard professeur
│ │ └── student/
│ │ └── dashboard.blade.php # Dashboard élève
│ └── css/
│ └── app.css
│
├── routes/
│ ├── web.php # Routes de l'application
│ └── auth.php # Routes d'authentification
│
├── storage/
│ └── logs/
│ └── laravel.log # Fichier de logs
│
├── .env # Configuration environnement
├── .env.example # Modèle de configuration
├── composer.json # Dépendances PHP
├── package.json # Dépendances JavaScript
├── vite.config.js # Configuration Vite
└── README.md # Documentation du projet


---

## 🏗️ Architecture de l'application

### Pattern MVC (Model-View-Controller)

Laravel utilise le pattern MVC qui sépare l'application en 3 couches :

**1. Model (Modèle) - `app/Models/`**
- Représente les données et la logique métier
- Interact avec la base de données via Eloquent ORM
- Exemple : `User.php`

**2. View (Vue) - `resources/views/`**
- Interface utilisateur avec Blade
- Affichage des données
- Exemple : `admin/dashboard.blade.php`

**3. Controller (Contrôleur) - `app/Http/Controllers/`**
- Logique de traitement des requêtes
- Fait le lien entre Model et View
- Exemple : `AdminController.php`

### Flux de requête

Utilisateur fait une requête → routes/web.php

Route appelle un Contrôleur → AdminController@dashboard

Contrôleur récupère les données → User::where('status', 'pending')->get()

Contrôleur passe les données à la Vue → view('admin.dashboard', compact('data'))

Vue affiche les données → Blade génère du HTML

Réponse envoyée au navigateur


---

## 🔐 Système d'authentification et rôles

### Table Users - Structure

| Colonne | Type | Description |
|---------|------|-------------|
| id | BIGINT | Identifiant unique auto-incrémenté |
| identifier | STRING | Identifiant personnalisé (E2026001, P2026001, ADMIN) |
| name | STRING | Nom complet de l'utilisateur |
| email | STRING | Adresse email (unique) |
| role | ENUM | Rôle : 'admin', 'teacher', 'student' |
| status | ENUM | Statut : 'pending', 'approved', 'rejected' |
| password | STRING | Mot de passe hashé (bcrypt) |
| created_at | TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | Date de dernière modification |

### Système de rôles

**3 rôles définis :**

1. **admin** - Administrateur
   - Valide/rejette les inscriptions
   - Gère les utilisateurs
   - Accès complet au système
   - Route : `/admin/*`

2. **teacher** - Professeur
   - Gère ses classes et matières
   - Saisit les notes
   - Route : `/teacher/*`

3. **student** - Élève
   - Consulte ses informations
   - Voit ses notes et emploi du temps
   - Route : `/student/*`

### Système de statuts

**3 statuts possibles :**

- **pending** - En attente de validation admin (défaut à l'inscription)
- **approved** - Compte validé, peut se connecter
- **rejected** - Compte refusé, ne peut pas se connecter

---

## 🚀 Utilisation de l'application

### Processus d'inscription complet

**1. L'utilisateur accède à `/register`**
Remplit le formulaire (nom, email, rôle, mot de passe)

Choisit son rôle : Élève ou Professeur (pas Admin)

Soumet le formulaire


**2. Traitement côté serveur (RegisteredUserController)**
```php
// Validation des données
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:users'],
    'role' => ['required', 'in:student,teacher'],
    'password' => ['required', 'confirmed'],
]);

// Génération automatique de l'identifiant
$prefix = $request->role === 'student' ? 'E' : 'P';
$year = date('Y'); // 2026
// Recherche du dernier numéro
$lastUser = User::where('identifier', 'like', $prefix.$year.'%')
                ->orderBy('id', 'desc')->first();
// Incrémentation
$newNumber = $lastUser ? substr($lastUser->identifier, -3) + 1 : 1;
$identifier = $prefix . $year . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
// Résultat : E2026001, E2026002, P2026001, etc.

// Création du compte avec statut "pending"
User::create([
    'identifier' => $identifier,
    'name' => $request->name,
    'email' => $request->email,
    'role' => $request->role,
    'password' => Hash::make($request->password),
    'status' => 'pending',
]);

// Redirection vers login avec message
return redirect()->route('login')
    ->with('status', 'Votre compte a été créé. Il sera activé après validation.');
3. L'utilisateur ne peut PAS encore se connecter

Le statut est "pending"

Doit attendre la validation de l'admin

Validation par l'admin
1. L'admin se connecte
Email : admin@ecole.com
Mot de passe : admin123

2. L'admin voit un badge rouge
Inscriptions en attente[1]

3. L'admin clique sur "Inscriptions en attente"
Liste affichée :
- Identifiant : E2026001
- Nom : Ahmadou Faye
- Email : a@gmail.com
- Rôle : student
- Date : 10/01/2026 12:34
- Actions : [Valider] [Rejeter]

4. L'admin valide le compte
// Méthode approve() dans AdminController
public function approve($id)
{
    $user = User::findOrFail($id);
    $user->status = 'approved';
    $user->save();
    
    return redirect()->back()
        ->with('success', 'Utilisateur validé avec succès.');
}
5. L'utilisateur peut maintenant se connecter

Son statut est passé à "approved"

Il peut accéder à son dashboard

Connexion
1. L'utilisateur accède à /login

2. Il entre ses identifiants
Identifiant : E2026001 (ou email : a@gmail.com)
Mot de passe : **
3. Vérification du statut
// Si status = 'pending' → Erreur
// Si status = 'rejected' → Erreur
// Si status = 'approved' → Connexion OK
4. Redirection selon le rôle

php
// Dans routes/web.php
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'teacher') {
        return redirect()->route('teacher.dashboard');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }
})->middleware(['auth'])->name('dashboard');
Déconnexion
Bouton dans la navbar :

text
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-sm">
        Déconnexion
    </button>
</form>
Important :

Utilise la méthode POST avec token CSRF

Ne jamais taper /logout dans l'URL (erreur 419)

🛡️ Sécurité implémentée
Protection CSRF
text
{{-- Tous les formulaires incluent le token CSRF --}}
<form method="POST" action="{{ route('login') }}">
    @csrf
    {{-- champs du formulaire --}}
</form>
Hachage des mots de passe
php
// Lors de l'inscription
'password' => Hash::make($request->password)

// Laravel utilise bcrypt avec salt automatique
Validation côté serveur
php
$request->validate([
    'email' => ['required', 'email', 'unique:users'],
    'role' => ['required', 'in:student,teacher'], // Seulement ces 2 rôles
]);
Middleware d'authentification
php
// Protège les routes - Seuls les utilisateurs connectés peuvent accéder
Route::middleware(['auth'])->group(function () {
    // Routes protégées
});
Middleware de rôle personnalisé
php
// Protège les routes par rôle spécifique
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Seuls les admins peuvent accéder
});
Un seul admin
php
// Dans le formulaire d'inscription, pas d'option "Admin"
<select name="role">
    <option value="student">Élève</option>
    <option value="teacher">Professeur</option>
    {{-- Pas d'option admin --}}
</select>

// Dans la validation
'role' => ['required', 'in:student,teacher'], // admin exclu

PARTIE 4 
📝 PARTIE 4 - Concepts Laravel appris et Code détaillé
text

## 🎓 Concepts Laravel appris en détail

---

## 1️⃣ Migrations - Gestion de la base de données

### Qu'est-ce qu'une migration ?

Une migration est comme un "historique de versions" pour votre base de données. Elle permet de créer, modifier ou supprimer des tables et colonnes de manière contrôlée.

### Création d'une migration

```bash
php artisan make:migration add_role_and_status_to_users_table
Code de la migration créée
Fichier : database/migrations/xxxx_add_role_and_status_to_users_table.php

php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuté lors de php artisan migrate
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter identifier après id
            $table->string('identifier')->unique()->after('id');
            
            // Ajouter role après email
            $table->enum('role', ['admin', 'teacher', 'student'])->after('email');
            
            // Ajouter status après role avec valeur par défaut
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('role');
        });
    }

    /**
     * Exécuté lors de php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['identifier', 'role', 'status']);
        });
    }
};
Types de colonnes utilisés
php
$table->string('name');              // VARCHAR(255)
$table->string('email')->unique();   // VARCHAR(255) UNIQUE
$table->text('description');         // TEXT
$table->integer('age');              // INT
$table->bigInteger('user_id');       // BIGINT
$table->enum('role', ['a', 'b']);    // ENUM avec valeurs fixes
$table->boolean('is_active');        // TINYINT(1)
$table->date('birth_date');          // DATE
$table->timestamp('created_at');     // TIMESTAMP
Commandes de migration
bash
# Exécuter toutes les migrations
php artisan migrate

# Annuler la dernière migration
php artisan migrate:rollback

# Réinitialiser et relancer toutes les migrations (⚠️ efface les données)
php artisan migrate:fresh

# Voir le statut des migrations
php artisan migrate:status
2️⃣ Eloquent ORM - Manipulation des données
Qu'est-ce qu'Eloquent ?
Eloquent est l'ORM (Object-Relational Mapping) de Laravel. Il permet de manipuler la base de données avec du code PHP au lieu de requêtes SQL brutes.

Modèle User
Fichier : app/Models/User.php

php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Champs remplissables en masse (mass assignment)
     */
    protected $fillable = [
        'identifier',
        'name',
        'email',
        'role',
        'status',
        'password',
    ];

    /**
     * Champs cachés lors de la sérialisation (API)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast automatique de types
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
Requêtes Eloquent - Exemples pratiques
Récupérer tous les utilisateurs

php
$users = User::all();
// SELECT * FROM users
Récupérer un utilisateur par ID

php
$user = User::find(1);
// SELECT * FROM users WHERE id = 1 LIMIT 1

// Ou lancer une exception si non trouvé
$user = User::findOrFail(1);
Filtrer avec where()

php
// Utilisateurs en attente
$pendingUsers = User::where('status', 'pending')->get();
// SELECT * FROM users WHERE status = 'pending'

// Utilisateurs élèves approuvés
$students = User::where('role', 'student')
                ->where('status', 'approved')
                ->get();
// SELECT * FROM users WHERE role = 'student' AND status = 'approved'
Trier avec orderBy()

php
$users = User::orderBy('created_at', 'desc')->get();
// SELECT * FROM users ORDER BY created_at DESC
Récupérer le premier résultat

php
$lastUser = User::where('identifier', 'like', 'E2026%')
                ->orderBy('id', 'desc')
                ->first();
// SELECT * FROM users WHERE identifier LIKE 'E2026%' 
// ORDER BY id DESC LIMIT 1
Compter les résultats

php
$pendingCount = User::where('status', 'pending')->count();
// SELECT COUNT(*) FROM users WHERE status = 'pending'
Créer un enregistrement

php
// Méthode 1 : create()
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);

// Méthode 2 : new + save()
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->password = Hash::make('password');
$user->save();
Mettre à jour un enregistrement

php
// Méthode 1 : update()
User::where('id', 1)->update(['status' => 'approved']);

// Méthode 2 : Modifier puis save()
$user = User::find(1);
$user->status = 'approved';
$user->save();
Supprimer un enregistrement

php
$user = User::find(1);
$user->delete();

// Ou directement
User::destroy(1);
3️⃣ Middleware - Contrôle d'accès
Qu'est-ce qu'un middleware ?
Un middleware est un "filtre" qui s'exécute avant ou après une requête HTTP. Il permet de vérifier, modifier ou bloquer des requêtes.

Création du RoleMiddleware
bash
php artisan make:middleware RoleMiddleware
Fichier : app/Http/Middleware/RoleMiddleware.php

php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Gérer une requête entrante
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  Le rôle requis (admin, teacher, student)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur a le bon rôle
        if (auth()->user()->role !== $role) {
            abort(403, 'Accès non autorisé');
        }

        // Si tout est OK, continuer vers la route
        return $next($request);
    }
}
Enregistrement du middleware
Fichier : bootstrap/app.php (Laravel 11)

php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enregistrer le middleware avec un alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
Utilisation dans les routes
php
// Protéger une seule route
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
     ->middleware(['auth', 'role:admin']);

// Protéger un groupe de routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});
4️⃣ Routes - Navigation dans l'application
Fichier de routes
Fichier : routes/web.php

php
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Page d'accueil publique
Route::get('/', function () {
    return view('welcome');
});

// Route dashboard avec redirection selon le rôle
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'teacher') {
        return redirect()->route('teacher.dashboard');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }
})->middleware(['auth'])->name('dashboard');

// ============================================
// ROUTES ADMIN (protégées par auth + role:admin)
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', [AdminController::class, 'dashboard'])
         ->name('admin.dashboard');
    
    // Inscriptions en attente
    Route::get('/pending-registrations', [AdminController::class, 'pendingRegistrations'])
         ->name('admin.pending');
    
    // Approuver une inscription
    Route::post('/approve/{id}', [AdminController::class, 'approve'])
         ->name('admin.approve');
    
    // Rejeter une inscription
    Route::post('/reject/{id}', [AdminController::class, 'reject'])
         ->name('admin.reject');
});

// ============================================
// ROUTES TEACHER
// ============================================
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])
         ->name('teacher.dashboard');
});

// ============================================
// ROUTES STUDENT
// ============================================
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])
         ->name('student.dashboard');
});

// Routes d'authentification (login, register, etc.)
require __DIR__.'/auth.php';
Types de routes
php
// Route GET
Route::get('/url', [Controller::class, 'method']);

// Route POST
Route::post('/url', [Controller::class, 'method']);

// Route avec nom (pour les redirections et liens)
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
     ->name('admin.dashboard');

// Dans une vue, générer l'URL :
// route('admin.dashboard') → /admin/dashboard

// Route avec paramètre
Route::get('/user/{id}', [UserController::class, 'show']);
Route::post('/approve/{id}', [AdminController::class, 'approve']);

// Groupe de routes avec middleware
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
});

// Groupe avec préfixe
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', ...);  // URL: /admin/dashboard
    Route::get('/users', ...);      // URL: /admin/users
});
Commandes utiles
bash
# Lister toutes les routes
php artisan route:list

# Lister les routes d'un contrôleur spécifique
php artisan route:list --name=admin

# Nettoyer le cache des routes
php artisan route:clear
5️⃣ Contrôleurs - Logique de l'application
Création de contrôleurs
bash
php artisan make:controller AdminController
php artisan make:controller TeacherController
php artisan make:controller StudentController
AdminController complet
Fichier : app/Http/Controllers/AdminController.php

php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Afficher le dashboard admin
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Afficher les inscriptions en attente
     */
    public function pendingRegistrations()
    {
        // Récupérer tous les utilisateurs avec status = 'pending'
        $pendingUsers = User::where('status', 'pending')->get();
        
        // Passer les données à la vue
        return view('admin.pending-registrations', compact('pendingUsers'));
    }

    /**
     * Approuver une inscription
     */
    public function approve($id)
    {
        // Trouver l'utilisateur ou renvoyer 404
        $user = User::findOrFail($id);
        
        // Changer le statut
        $user->status = 'approved';
        $user->save();
        
        // Rediriger avec message de succès
        return redirect()->back()
                         ->with('success', 'Utilisateur validé avec succès.');
    }

    /**
     * Rejeter une inscription
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'rejected';
        $user->save();
        
        return redirect()->back()
                         ->with('success', 'Utilisateur rejeté.');
    }
}
Register
text

### RegisteredUserController personnalisé

**Fichier : `app/Http/Controllers/Auth/RegisteredUserController.php`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Afficher le formulaire d'inscription
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription d'un nouvel utilisateur
     */
    public function store(Request $request): RedirectResponse
    {
        // Validation des données
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:student,teacher'], // Pas admin
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Génération de l'identifiant selon le rôle
        if ($request->role === 'student') {
            $prefix = 'E';
        } elseif ($request->role === 'teacher') {
            $prefix = 'P';
        } else {
            $prefix = 'A';
        }

        // Générer le numéro séquentiel
        $year = date('Y'); // 2026
        $lastUser = User::where('identifier', 'like', $prefix . $year . '%')
                        ->orderBy('id', 'desc')
                        ->first();

        if ($lastUser) {
            // Extraire les 3 derniers chiffres et incrémenter
            $lastNumber = (int) substr($lastUser->identifier, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Premier utilisateur de ce type cette année
            $newNumber = '001';
        }

        $identifier = $prefix . $year . $newNumber;
        // Résultat : E2026001, E2026002, P2026001, etc.

        // Créer l'utilisateur
        $user = User::create([
            'identifier' => $identifier,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'status' => 'pending', // En attente de validation
        ]);

        // NE PAS connecter automatiquement
        // Auth::login($user); ← Cette ligne est commentée

        // Rediriger vers login avec message
        return redirect()->route('login')
            ->with('status', 'Votre compte a été créé. Il sera activé après validation par un administrateur.');
    }
}
6️⃣ Validation - Sécuriser les données
Règles de validation utilisées
php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    'role' => ['required', 'in:student,teacher'],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);
Explication des règles
Règle	Description	Exemple
required	Champ obligatoire	Le nom est requis
string	Doit être une chaîne de caractères	"John Doe"
max:255	Maximum 255 caractères	Longueur limitée
email	Format email valide	test@example.com
unique:users	Doit être unique dans la table users	Email non déjà utilisé
in:a,b,c	Doit être une des valeurs listées	student ou teacher uniquement
confirmed	Doit avoir un champ _confirmation	password et password_confirmation
Affichage des erreurs dans Blade
text
{{-- Afficher toutes les erreurs --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Afficher l'erreur d'un champ spécifique --}}
@error('email')
    <div class="text-danger">{{ $message }}</div>
@enderror
7️⃣ Blade Templates - Interface utilisateur
Layout principal
Fichier : resources/views/layouts/app.blade.php

text
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

{{-- Navbar --}}
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="mb-0 navbar-brand h1">
            EduSchool Computer Science
        </span>
        
        <div class="d-flex align-items-center">
            <span class="text-white small me-3">
                {{ auth()->user()->name ?? '' }} ({{ auth()->user()->role ?? '' }})
            </span>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Déconnexion
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <aside class="py-4 bg-white col-md-3 col-lg-2 border-end min-vh-100">
            <nav class="nav flex-column">
                <span class="px-3 mb-2 text-muted text-uppercase small">Navigation</span>
                
                {{-- Lien Dashboard --}}
                <a href="{{ route('dashboard') }}" class="px-3 nav-link">
                    Dashboard
                </a>
                
                {{-- Liens selon le rôle --}}
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.pending') }}" class="px-3 nav-link">
                            Inscriptions en attente
                            @php
                                $pendingCount = \App\Models\User::where('status', 'pending')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge bg-danger">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="#" class="px-3 nav-link">Gestion des élèves</a>
                        <a href="#" class="px-3 nav-link">Gestion des profs</a>
                        <a href="#" class="px-3 nav-link">Classes & matières</a>
                        
                    @elseif(auth()->user()->role === 'teacher')
                        <a href="#" class="px-3 nav-link">Mes classes</a>
                        <a href="#" class="px-3 nav-link">Mes matières</a>
                        <a href="#" class="px-3 nav-link">Saisie des notes</a>
                        
                    @elseif(auth()->user()->role === 'student')
                        <a href="#" class="px-3 nav-link">Mes matières</a>
                        <a href="#" class="px-3 nav-link">Mon emploi du temps</a>
                        <a href="#" class="px-3 nav-link">Mes résultats</a>
                    @endif
                @endauth
            </nav>
        </aside>
        
        {{-- Contenu principal --}}
        <main class="py-4 col-md-9 col-lg-10">
            @isset($header)
                <div class="mb-3">
                    {{ $header }}
                </div>
            @endisset
            
            {{ $slot }}
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
Directives Blade principales
text
{{-- Afficher une variable (échappée HTML) --}}
{{ $variable }}

{{-- Afficher sans échappement (⚠️ dangereux) --}}
{!! $htmlContent !!}

{{-- Conditions --}}
@if($user->role === 'admin')
    Vous êtes admin
@elseif($user->role === 'teacher')
    Vous êtes professeur
@else
    Vous êtes élève
@endif

{{-- Boucles --}}
@foreach($users as $user)
    <li>{{ $user->name }}</li>
@endforeach

{{-- Vérifier si connecté --}}
@auth
    Contenu pour utilisateur connecté
@endauth

@guest
    Contenu pour utilisateur non connecté
@endguest

{{-- Inclure une vue --}}
@include('partials.header')

{{-- Définir une section --}}
@section('title', 'Dashboard')

{{-- Token CSRF (obligatoire dans les formulaires POST) --}}
@csrf

{{-- Méthode HTTP (pour PUT, PATCH, DELETE) --}}
@method('PUT')

{{-- Afficher les erreurs de validation --}}
@error('email')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
8️⃣ Artisan - Commandes CLI
bash
# Créer un contrôleur
php artisan make:controller NomController

# Créer un modèle
php artisan make:model NomModele

# Créer un middleware
php artisan make:middleware NomMiddleware

# Créer une migration
php artisan make:migration nom_de_la_migration

# Exécuter les migrations
php artisan migrate

# Annuler la dernière migration
php artisan migrate:rollback

# Réinitialiser toutes les migrations
php artisan migrate:fresh

# Lancer Tinker (console interactive)
php artisan tinker

# Lister les routes
php artisan route:list

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Lancer le serveur
php artisan serve

# Voir toutes les commandes disponibles
php artisan list
9️⃣ Tinker - Console interactive
Lancer Tinker
bash
php artisan tinker
Commandes utiles dans Tinker
php
// Voir tous les utilisateurs
User::all();

// Voir seulement certains champs
User::select('id', 'name', 'email', 'role')->get();

// Trouver un utilisateur
User::find(2);

// Modifier un utilisateur
$user = User::find(2);
$user->role = 'admin';
$user->status = 'approved';
$user->save();

// Créer un utilisateur
$admin = new User();
$admin->identifier = 'ADMIN';
$admin->name = 'Administrateur';
$admin->email = 'admin@ecole.com';
$admin->password = Hash::make('admin123');
$admin->role = 'admin';
$admin->status = 'approved';
$admin->save();

// Compter les utilisateurs
User::count();

// Filtrer
User::where('role', 'student')->count();

// Supprimer un utilisateur
User::destroy(5);

// Quitter Tinker
exit

PARTIE 5
📝 PARTIE 5 - Problèmes rencontrés et Solutions + Conclusion
text

## 🐛 Problèmes rencontrés et solutions

Durant le développement, plusieurs problèmes ont été rencontrés et résolus. Voici le détail complet.

---

### ❌ PROBLÈME 1 : Compte ADMIN avec mauvais rôle

**Description du problème :**
- Le compte avec l'identifiant "ADMIN" était enregistré avec le rôle "student"
- Lors de la connexion, redirection vers `/student/dashboard` au lieu de `/admin/dashboard`
- L'admin ne pouvait pas accéder aux fonctionnalités administratives

**Cause :**
- Le compte a été créé manuellement avec le mauvais rôle dans la base de données

**Solution appliquée :**
```bash
php artisan tinker
php
// Chercher le compte
User::all();

// Trouver le compte avec identifier "ADMIN" (id: 2)
$admin = User::find(2);

// Corriger le rôle
$admin->role = 'admin';
$admin->status = 'approved';
$admin->save();

// Vérifier
User::find(2);
exit
Résultat : Le compte ADMIN redirige maintenant correctement vers /admin/dashboard

❌ PROBLÈME 2 : Middleware RoleMiddleware inexistant
Description du problème :

Erreur 500 : Target class [App\Http\Middleware\RoleMiddleware] does not exist

La page des inscriptions en attente ne s'affichait pas

Le middleware était référencé dans les routes mais n'existait pas

Message d'erreur complet :

text
Illuminate\Contracts\Container\BindingResolutionException
Target class [App\Http\Middleware\RoleMiddleware] does not exist.
Cause :

Le middleware était enregistré dans bootstrap/app.php

Mais le fichier RoleMiddleware.php n'avait pas été créé

Solution appliquée :

Étape 1 : Créer le middleware

bash
php artisan make:middleware RoleMiddleware
Étape 2 : Implémenter la logique

Fichier : app/Http/Middleware/RoleMiddleware.php

php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur a le bon rôle
        if (auth()->user()->role !== $role) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}
Étape 3 : Enregistrer dans bootstrap/app.php

php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
Résultat : La page des inscriptions en attente fonctionne correctement

❌ PROBLÈME 3 : Champ 'identifier' manquant lors de l'inscription
Description du problème :

Erreur lors de la création d'un nouveau compte

Message : Field 'identifier' doesn't have a default value

L'inscription échouait avec une erreur 500

Message d'erreur complet :

text
SQLSTATE[HY000]: General error: 1364 Field 'identifier' doesn't have a default value
Cause :

La table users a une colonne identifier obligatoire (NOT NULL)

Le contrôleur d'inscription ne générait pas cet identifiant

Laravel essayait d'insérer sans valeur pour identifier

Solution appliquée :

Modifier RegisteredUserController.php pour générer automatiquement l'identifiant :

php
public function store(Request $request): RedirectResponse
{
    // ... validation ...

    // Générer l'identifiant selon le rôle
    if ($request->role === 'student') {
        $prefix = 'E';
    } elseif ($request->role === 'teacher') {
        $prefix = 'P';
    } else {
        $prefix = 'A';
    }

    // Générer le numéro séquentiel
    $year = date('Y');
    $lastUser = User::where('identifier', 'like', $prefix . $year . '%')
                    ->orderBy('id', 'desc')
                    ->first();

    if ($lastUser) {
        $lastNumber = (int) substr($lastUser->identifier, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }

    $identifier = $prefix . $year . $newNumber;

    // Créer l'utilisateur AVEC l'identifiant
    $user = User::create([
        'identifier' => $identifier,  // ← Ligne ajoutée
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role,
        'password' => Hash::make($request->password),
        'status' => 'pending',
    ]);

    // ...
}
Résultat : Les inscriptions fonctionnent et génèrent des identifiants uniques (E2026001, P2026001, etc.)

❌ PROBLÈME 4 : Déconnexion avec erreur 419
Description du problème :

Erreur 419 "Page Expired" lors de la tentative de déconnexion

Se produit quand on tape /logout directement dans l'URL

Cause :

La route /logout nécessite une requête POST avec token CSRF

Accéder à /logout via l'URL utilise GET sans token

Le token CSRF a expiré ou est manquant

Solution :

Le bouton de déconnexion doit utiliser un formulaire POST avec @csrf :

text
{{-- ✅ BON --}}
<form method="POST" action="{{ route('logout') }}" class="mb-0">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-sm">
        Déconnexion
    </button>
</form>

{{-- ❌ MAUVAIS --}}
<a href="{{ route('logout') }}">Déconnexion</a>
Recommandation :

Ne jamais taper /logout dans la barre d'adresse

Toujours utiliser le bouton de déconnexion

Le bouton inclut automatiquement le token CSRF

Résultat : La déconnexion fonctionne parfaitement via le bouton

❌ PROBLÈME 5 : Connexion automatique après inscription
Description du problème :

Après l'inscription, l'utilisateur était automatiquement connecté

Il accédait directement au dashboard même avec statut "pending"

Cela court-circuitait le système de validation par l'admin

Cause :

Le code par défaut de Laravel Breeze contient Auth::login($user)

Cette ligne connecte automatiquement après inscription

Solution appliquée :

Dans RegisteredUserController.php, supprimer la connexion automatique :

php
public function store(Request $request): RedirectResponse
{
    // ... création de l'utilisateur ...

    // SUPPRIMER cette ligne :
    // Auth::login($user);

    // Rediriger vers login avec message
    return redirect()->route('login')
        ->with('status', 'Votre compte a été créé. Il sera activé après validation par un administrateur.');
}
Résultat :

L'utilisateur est redirigé vers la page de connexion après inscription

Il voit un message lui indiquant d'attendre la validation

Il ne peut se connecter qu'après approbation par l'admin

❌ PROBLÈME 6 : Option "Admin" dans le formulaire d'inscription
Description du problème :

N'importe qui pouvait s'inscrire en tant qu'admin

Risque de sécurité majeur

Plusieurs admins pouvaient être créés

Solution appliquée :

Étape 1 : Retirer l'option du formulaire

Dans resources/views/auth/register.blade.php :

text
<select name="role" id="role" required>
    <option value="">-- Choisir un rôle --</option>
    <option value="student">Élève</option>
    <option value="teacher">Professeur</option>
    {{-- Option Admin supprimée --}}
</select>
Étape 2 : Bloquer côté serveur

Dans RegisteredUserController.php :

php
$request->validate([
    'role' => ['required', 'in:student,teacher'], // admin exclu
]);
Résultat :

Seuls "Élève" et "Professeur" sont disponibles

Même en manipulant le HTML, la validation serveur bloque "admin"

Un seul admin existe dans le système

❌ PROBLÈME 7 : Erreur "User not found" dans Tinker
Description du problème :

php
$admin = User::where('name', 'ADMIN')->first();
// Résultat : null
Cause :

Le compte cherché n'existait pas avec le nom "ADMIN"

Le champ à chercher était identifier et non name

Solution :

php
// Voir tous les utilisateurs d'abord
User::all();

// Utiliser l'ID ou l'identifier
$admin = User::find(2);
// ou
$admin = User::where('identifier', 'ADMIN')->first();
Résultat : L'utilisateur est trouvé et peut être modifié

📊 Récapitulatif des apprentissages
Compétences techniques acquises
Backend Laravel

✅ Installation et configuration de Laravel

✅ Système d'authentification avec Breeze

✅ Migrations de base de données

✅ Eloquent ORM (requêtes, relations)

✅ Middleware personnalisé

✅ Contrôleurs et routes

✅ Validation de formulaires

✅ Gestion des sessions et messages flash

✅ Utilisation de Tinker

Frontend

✅ Blade templates et layouts

✅ Bootstrap 5 pour le design

✅ Formulaires avec protection CSRF

✅ Affichage conditionnel selon le rôle

Base de données

✅ Conception de schéma (users avec rôles)

✅ Migrations (ajout de colonnes)

✅ Types de données (enum, string, etc.)

Sécurité

✅ Hachage de mots de passe

✅ Protection CSRF

✅ Validation côté serveur

✅ Contrôle d'accès par rôle

✅ Middleware d'authentification

🎯 Commandes essentielles mémorisées
bash
# Projet
composer create-project laravel/laravel nom-projet
composer install
npm install

# Base de données
php artisan migrate
php artisan migrate:fresh
php artisan migrate:rollback

# Création de fichiers
php artisan make:model NomModele -mcr
php artisan make:controller NomController --resource
php artisan make:middleware NomMiddleware

## 🔑 Comptes de test

### Administrateur
- **Identifiant** : A00001
- **Email** : admin@example.com
- **Mot de passe** : password
- **Rôle** : Admin

### Enseignant
- **Identifiant** : P00001
- **Email** : teacher@example.com
- **Mot de passe** : password
- **Rôle** : Enseignant

### Étudiant
- **Identifiant** : E00001
- **Email** : student@example.com
- **Mot de passe** : password
- **Rôle** : Étudiant

> ℹ️ Tous les comptes sont déjà validés et prêts à l'emploi.

# Serveur
php artisan serve
npm run dev

# Debug
php artisan tinker
php artisan route:list

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
🚀 Prochaines étapes possibles
Fonctionnalités à développer
Module Classes

Créer une table classes

CRUD complet (Create, Read, Update, Delete)

Assigner des élèves aux classes

Module Matières

Créer une table subjects (matières)

Lier les matières aux professeurs

Lier les matières aux classes

Module Notes

Créer une table grades

Saisie des notes par les professeurs

Calcul automatique des moyennes

Génération de bulletins

Module Emploi du temps

Créer une table schedules

Interface calendrier

Affichage par classe et par élève

Améliorations

Notifications par email

Export PDF des bulletins

Statistiques et graphiques

Messagerie interne

Gestion des absences

Paiements en ligne

📚 Ressources utiles
Documentation officielle

Laravel : https://laravel.com/docs

Eloquent ORM : https://laravel.com/docs/eloquent

Blade Templates : https://laravel.com/docs/blade

Validation : https://laravel.com/docs/validation

Tutoriels recommandés

Grafikart Laravel (FR) : https://grafikart.fr/formations/laravel

Laracasts (EN) : https://laracasts.com

Laravel Daily (EN) : https://www.youtube.com/@LaravelDaily

Communauté

Forum Laravel : https://laracasts.com/discuss

Discord Laravel : https://discord.gg/laravel

Stack Overflow : https://stackoverflow.com/questions/tagged/laravel

🎓 Conclusion
Ce qui a été accompli
Ce projet a permis de créer une application web fonctionnelle de gestion d'école avec :

✅ Un système d'authentification complet
✅ Trois rôles distincts (Admin, Professeur, Élève)
✅ Un processus d'inscription avec validation
✅ Des dashboards personnalisés par rôle
✅ Une génération automatique d'identifiants
✅ Une sécurité robuste (CSRF, hachage, middleware)
✅ Une architecture propre et extensible

Leçons apprises
1. L'importance de la planification

Définir les rôles et permissions dès le début

Concevoir la structure de la base de données avant de coder

2. Le débogage est une compétence essentielle

Lire attentivement les messages d'erreur

Utiliser Tinker pour tester rapidement

Vérifier les logs (`storage/logs/           

# 🎓 School Management System - Laravel

> **Projet d'apprentissage Laravel à travers une application réelle**

Application web de gestion d'établissement scolaire développée dans le cadre de l'apprentissage du framework Laravel. Ce projet permet de gérer élèves, professeurs et administrateurs avec des interfaces et permissions distinctes.

![Laravel](https://img.shields.io/badge/Laravel-11-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple?style=flat-square&logo=bootstrap)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=flat-square&logo=mysql)

---

## 📖 Contexte du projet

Ce projet est né d'un objectif simple : **apprendre Laravel en construisant quelque chose de concret**. Plutôt que de suivre des tutoriels déconnectés de la réalité, l'idée était de créer une vraie application de gestion d'école avec :

- **3 types d'utilisateurs** : Admin, Professeur, Élève
- **Des fonctionnalités réelles** : inscription, validation, gestion des rôles
- **Une architecture professionnelle** : middleware, contrôleurs, authentification

**Objectif pédagogique :** Comprendre Laravel à travers la résolution de vrais problèmes techniques.

---

## 🎯 Objectifs du projet

### Objectif général
Créer un système de gestion scolaire (School Management System) avec Laravel permettant de gérer les utilisateurs selon leurs rôles.

### Objectifs spécifiques
- ✅ Mettre en place un système d'authentification sécurisé
- ✅ Implémenter un système de rôles (admin, teacher, student)
- ✅ Créer un processus d'inscription avec validation par l'admin
- ✅ Développer des interfaces distinctes pour chaque rôle
- ✅ Générer automatiquement des identifiants uniques
- ✅ Sécuriser l'accès aux différentes zones de l'application

---

## ✨ Fonctionnalités implémentées

### 🔐 Système d'authentification
- ✅ Inscription avec sélection de rôle (Élève ou Professeur uniquement)
- ✅ Génération automatique d'identifiants uniques (E2026001, P2026001, etc.)
- ✅ Statut "pending" par défaut pour les nouvelles inscriptions
- ✅ Connexion sécurisée avec vérification du statut
- ✅ Déconnexion avec protection CSRF
- ✅ Un seul admin dans le système (sécurité)

### 👨‍💼 Espace Administrateur
- ✅ Dashboard avec statistiques
- ✅ Gestion des inscriptions en attente
- ✅ Validation ou rejet des nouveaux comptes
- ✅ Badge de notification (nombre d'inscriptions en attente)
- ✅ Accès protégé par middleware
- 🚧 Gestion complète des élèves (à venir)
- 🚧 Gestion des professeurs (à venir)
- 🚧 Gestion des classes et matières (à venir)

### 👨‍🏫 Espace Professeur
- ✅ Dashboard personnalisé
- ✅ Navigation adaptée au rôle
- 🚧 Mes classes (à venir)
- 🚧 Mes matières (à venir)
- 🚧 Saisie des notes (à venir)

### 👨‍🎓 Espace Élève
- ✅ Dashboard personnalisé
- ✅ Navigation adaptée au rôle
- 🚧 Mes matières (à venir)
- 🚧 Mon emploi du temps (à venir)
- 🚧 Mes résultats (à venir)

---

## 🛠️ Technologies utilisées

### Backend
- **Laravel 11.x** - Framework PHP moderne et élégant
- **PHP 8.2** - Langage de programmation côté serveur
- **MySQL 8.0** - Système de gestion de base de données
- **Laravel Breeze** - Kit d'authentification minimaliste

### Frontend
- **Bootstrap 5.3** - Framework CSS responsive
- **Blade** - Moteur de templates de Laravel
- **JavaScript Vanilla** - Interactions côté client

### Outils de développement
- **Composer** - Gestionnaire de dépendances PHP
- **NPM** - Gestionnaire de paquets JavaScript
- **XAMPP** - Environnement de développement (Apache + MySQL + PHP)
- **Git & GitHub** - Contrôle de version et hébergement du code
- **VS Code** - Éditeur de code

📝 PARTIE 2 - Installation complète

## 📥 Installation

### Prérequis système

Avant de commencer, assurez-vous d'avoir :

- **PHP >= 8.2** avec les extensions : OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js >= 16** et NPM
- **MySQL >= 8.0** ou MariaDB
- **Git** (pour cloner le projet)
- **XAMPP** ou tout autre serveur local

### Vérifier vos installations

```bash
# Vérifier PHP
php -v

# Vérifier Composer
composer -V

# Vérifier Node.js et NPM
node -v
npm -v

# Vérifier MySQL
mysql --version

Installation pas à pas
Étape 1 : Cloner le projet
git clone https://github.com/votre-username/school-management.git
cd school-management

Étape 2 : Installer les dépendances PHP
bash
composer install
Cette commande installe tous les packages PHP nécessaires définis dans composer.json, notamment Laravel et ses dépendances.

Étape 3 : Installer les dépendances JavaScript
bash
npm install
Cette commande installe Bootstrap, Vite et autres packages frontend.

Étape 4 : Configurer le fichier d'environnement
bash
# Copier le fichier d'exemple
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
La clé d'application est utilisée pour chiffrer les sessions et autres données sensibles.

Étape 5 : Configurer la base de données
Ouvrez le fichier .env et modifiez ces lignes :

text
APP_NAME="School Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_management_system
DB_USERNAME=root
DB_PASSWORD=
Note : Laissez DB_PASSWORD vide si vous utilisez XAMPP par défaut.

Étape 6 : Créer la base de données
Option A : Via phpMyAdmin

Ouvrez phpMyAdmin : http://localhost/phpmyadmin

Cliquez sur "Nouvelle base de données"

Nom : school_management_system

Interclassement : utf8mb4_unicode_ci

Cliquez sur "Créer"

Option B : Via ligne de commande MySQL

bash
mysql -u root -p
Puis dans MySQL :

sql
CREATE DATABASE school_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
Étape 7 : Exécuter les migrations
bash
php artisan migrate
Cette commande crée toutes les tables nécessaires :

users (avec colonnes : id, identifier, name, email, role, status, password, etc.)

password_reset_tokens

sessions

cache

Si vous voyez une erreur, vérifiez que :

MySQL est bien démarré dans XAMPP

La base de données existe

Les identifiants dans .env sont corrects

Étape 8 : Créer le compte administrateur
bash
php artisan tinker
Dans la console Tinker qui s'ouvre, tapez ces commandes une par une :

php
$admin = new App\Models\User();
$admin->identifier = 'ADMIN';
$admin->name = 'Administrateur';
$admin->email = 'admin@ecole.com';
$admin->password = Hash::make('admin123');
$admin->role = 'admin';
$admin->status = 'approved';
$admin->save();
exit
Explication :

identifier : Identifiant unique (ADMIN)

name : Nom affiché dans l'interface

email : Email pour la connexion

password : Mot de passe hashé avec bcrypt

role : Rôle 'admin' pour accès complet

status : 'approved' pour pouvoir se connecter immédiatement

Étape 9 : Compiler les assets frontend
bash
npm run dev
Cette commande compile Bootstrap, CSS et JavaScript. Laissez ce terminal ouvert pendant le développement pour le rechargement automatique.

Pour la production :

bash
npm run build
Étape 10 : Lancer le serveur de développement
Ouvrez un nouveau terminal et lancez :

bash
php artisan serve
Vous devriez voir :

text
INFO  Server running on [http://127.0.0.1:8000](http://127.0.0.1:8000)
Étape 11 : Accéder à l'application
Ouvrez votre navigateur et allez sur : http://127.0.0.1:8000

Identifiants administrateur :

Email : admin@ecole.com

Mot de passe : admin123

⚠️ Problèmes courants et solutions
Erreur : "SQLSTATE[HY000] [1045] Access denied"
Solution : Vérifiez les identifiants MySQL dans .env

Erreur : "Class 'Hash' not found"
Solution : Utilisez Illuminate\Support\Facades\Hash ou assurez-vous d'être dans Tinker

Erreur : "Column 'identifier' doesn't have a default value"
Solution : Vérifiez que la migration add_role_and_status_to_users_table a bien été exécutée

Erreur 419 "Page Expired" lors de la déconnexion
Solution : Ne tapez jamais /logout directement dans l'URL, utilisez toujours le bouton de déconnexion

Le serveur npm ne démarre pas
Solution :

bash
rm -rf node_modules package-lock.json
npm install
npm run dev
🔄 Commandes utiles
bash
# Relancer les migrations (⚠️ efface toutes les données)
php artisan migrate:fresh

# Voir les routes disponibles
php artisan route:list

# Nettoyer le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Arrêter le serveur
Ctrl + C dans le terminal

# Voir les logs d'erreur
tail -f storage/logs/laravel.log

📝 PARTIE 3 - Structure et Architecture

## 📁 Structure complète du projet

school-management/
├── app/
│ ├── Http/
│ │ ├── Controllers/
│ │ │ ├── Auth/
│ │ │ │ ├── AuthenticatedSessionController.php
│ │ │ │ ├── RegisteredUserController.php # Gestion inscription personnalisée
│ │ │ │ └── ...
│ │ │ ├── AdminController.php # Contrôleur admin
│ │ │ ├── TeacherController.php # Contrôleur professeur
│ │ │ └── StudentController.php # Contrôleur élève
│ │ └── Middleware/
│ │ ├── RoleMiddleware.php # Contrôle d'accès par rôle
│ │ └── ...
│ └── Models/
│ └── User.php # Modèle utilisateur avec rôles
│
├── bootstrap/
│ └── app.php # Configuration middleware + routes
│
├── database/
│ ├── migrations/
│ │ ├── 2014_10_12_000000_create_users_table.php
│ │ └── xxxx_add_role_and_status_to_users_table.php # Migration rôles + statuts
│ └── seeders/
│ └── DatabaseSeeder.php
│
├── resources/
│ ├── views/
│ │ ├── layouts/
│ │ │ └── app.blade.php # Layout principal avec sidebar
│ │ ├── auth/
│ │ │ ├── login.blade.php # Page de connexion
│ │ │ └── register.blade.php # Page d'inscription personnalisée
│ │ ├── admin/
│ │ │ ├── dashboard.blade.php # Dashboard administrateur
│ │ │ └── pending-registrations.blade.php # Gestion inscriptions en attente
│ │ ├── teacher/
│ │ │ └── dashboard.blade.php # Dashboard professeur
│ │ └── student/
│ │ └── dashboard.blade.php # Dashboard élève
│ └── css/
│ └── app.css
│
├── routes/
│ ├── web.php # Routes de l'application
│ └── auth.php # Routes d'authentification
│
├── storage/
│ └── logs/
│ └── laravel.log # Fichier de logs
│
├── .env # Configuration environnement
├── .env.example # Modèle de configuration
├── composer.json # Dépendances PHP
├── package.json # Dépendances JavaScript
├── vite.config.js # Configuration Vite
└── README.md # Documentation du projet


---

## 🏗️ Architecture de l'application

### Pattern MVC (Model-View-Controller)

Laravel utilise le pattern MVC qui sépare l'application en 3 couches :

**1. Model (Modèle) - `app/Models/`**
- Représente les données et la logique métier
- Interact avec la base de données via Eloquent ORM
- Exemple : `User.php`

**2. View (Vue) - `resources/views/`**
- Interface utilisateur avec Blade
- Affichage des données
- Exemple : `admin/dashboard.blade.php`

**3. Controller (Contrôleur) - `app/Http/Controllers/`**
- Logique de traitement des requêtes
- Fait le lien entre Model et View
- Exemple : `AdminController.php`

### Flux de requête

Utilisateur fait une requête → routes/web.php

Route appelle un Contrôleur → AdminController@dashboard

Contrôleur récupère les données → User::where('status', 'pending')->get()

Contrôleur passe les données à la Vue → view('admin.dashboard', compact('data'))

Vue affiche les données → Blade génère du HTML

Réponse envoyée au navigateur


---

## 🔐 Système d'authentification et rôles

### Table Users - Structure

| Colonne | Type | Description |
|---------|------|-------------|
| id | BIGINT | Identifiant unique auto-incrémenté |
| identifier | STRING | Identifiant personnalisé (E2026001, P2026001, ADMIN) |
| name | STRING | Nom complet de l'utilisateur |
| email | STRING | Adresse email (unique) |
| role | ENUM | Rôle : 'admin', 'teacher', 'student' |
| status | ENUM | Statut : 'pending', 'approved', 'rejected' |
| password | STRING | Mot de passe hashé (bcrypt) |
| created_at | TIMESTAMP | Date de création |
| updated_at | TIMESTAMP | Date de dernière modification |

### Système de rôles

**3 rôles définis :**

1. **admin** - Administrateur
   - Valide/rejette les inscriptions
   - Gère les utilisateurs
   - Accès complet au système
   - Route : `/admin/*`

2. **teacher** - Professeur
   - Gère ses classes et matières
   - Saisit les notes
   - Route : `/teacher/*`

3. **student** - Élève
   - Consulte ses informations
   - Voit ses notes et emploi du temps
   - Route : `/student/*`

### Système de statuts

**3 statuts possibles :**

- **pending** - En attente de validation admin (défaut à l'inscription)
- **approved** - Compte validé, peut se connecter
- **rejected** - Compte refusé, ne peut pas se connecter

---

## 🚀 Utilisation de l'application

### Processus d'inscription complet

**1. L'utilisateur accède à `/register`**
Remplit le formulaire (nom, email, rôle, mot de passe)

Choisit son rôle : Élève ou Professeur (pas Admin)

Soumet le formulaire


**2. Traitement côté serveur (RegisteredUserController)**
```php
// Validation des données
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'email', 'unique:users'],
    'role' => ['required', 'in:student,teacher'],
    'password' => ['required', 'confirmed'],
]);

// Génération automatique de l'identifiant
$prefix = $request->role === 'student' ? 'E' : 'P';
$year = date('Y'); // 2026
// Recherche du dernier numéro
$lastUser = User::where('identifier', 'like', $prefix.$year.'%')
                ->orderBy('id', 'desc')->first();
// Incrémentation
$newNumber = $lastUser ? substr($lastUser->identifier, -3) + 1 : 1;
$identifier = $prefix . $year . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
// Résultat : E2026001, E2026002, P2026001, etc.

// Création du compte avec statut "pending"
User::create([
    'identifier' => $identifier,
    'name' => $request->name,
    'email' => $request->email,
    'role' => $request->role,
    'password' => Hash::make($request->password),
    'status' => 'pending',
]);

// Redirection vers login avec message
return redirect()->route('login')
    ->with('status', 'Votre compte a été créé. Il sera activé après validation.');
3. L'utilisateur ne peut PAS encore se connecter

Le statut est "pending"

Doit attendre la validation de l'admin

Validation par l'admin
1. L'admin se connecte
Email : admin@ecole.com
Mot de passe : admin123

2. L'admin voit un badge rouge
Inscriptions en attente[1]

3. L'admin clique sur "Inscriptions en attente"
Liste affichée :
- Identifiant : E2026001
- Nom : Ahmadou Faye
- Email : a@gmail.com
- Rôle : student
- Date : 10/01/2026 12:34
- Actions : [Valider] [Rejeter]

4. L'admin valide le compte
// Méthode approve() dans AdminController
public function approve($id)
{
    $user = User::findOrFail($id);
    $user->status = 'approved';
    $user->save();
    
    return redirect()->back()
        ->with('success', 'Utilisateur validé avec succès.');
}
5. L'utilisateur peut maintenant se connecter

Son statut est passé à "approved"

Il peut accéder à son dashboard

Connexion
1. L'utilisateur accède à /login

2. Il entre ses identifiants
Identifiant : E2026001 (ou email : a@gmail.com)
Mot de passe : **
3. Vérification du statut
// Si status = 'pending' → Erreur
// Si status = 'rejected' → Erreur
// Si status = 'approved' → Connexion OK
4. Redirection selon le rôle

php
// Dans routes/web.php
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'teacher') {
        return redirect()->route('teacher.dashboard');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }
})->middleware(['auth'])->name('dashboard');
Déconnexion
Bouton dans la navbar :

text
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-sm">
        Déconnexion
    </button>
</form>
Important :

Utilise la méthode POST avec token CSRF

Ne jamais taper /logout dans l'URL (erreur 419)

🛡️ Sécurité implémentée
Protection CSRF
text
{{-- Tous les formulaires incluent le token CSRF --}}
<form method="POST" action="{{ route('login') }}">
    @csrf
    {{-- champs du formulaire --}}
</form>
Hachage des mots de passe
php
// Lors de l'inscription
'password' => Hash::make($request->password)

// Laravel utilise bcrypt avec salt automatique
Validation côté serveur
php
$request->validate([
    'email' => ['required', 'email', 'unique:users'],
    'role' => ['required', 'in:student,teacher'], // Seulement ces 2 rôles
]);
Middleware d'authentification
php
// Protège les routes - Seuls les utilisateurs connectés peuvent accéder
Route::middleware(['auth'])->group(function () {
    // Routes protégées
});
Middleware de rôle personnalisé
php
// Protège les routes par rôle spécifique
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Seuls les admins peuvent accéder
});
Un seul admin
php
// Dans le formulaire d'inscription, pas d'option "Admin"
<select name="role">
    <option value="student">Élève</option>
    <option value="teacher">Professeur</option>
    {{-- Pas d'option admin --}}
</select>

// Dans la validation
'role' => ['required', 'in:student,teacher'], // admin exclu

PARTIE 4 
📝 PARTIE 4 - Concepts Laravel appris et Code détaillé
text

## 🎓 Concepts Laravel appris en détail

---

## 1️⃣ Migrations - Gestion de la base de données

### Qu'est-ce qu'une migration ?

Une migration est comme un "historique de versions" pour votre base de données. Elle permet de créer, modifier ou supprimer des tables et colonnes de manière contrôlée.

### Création d'une migration

```bash
php artisan make:migration add_role_and_status_to_users_table
Code de la migration créée
Fichier : database/migrations/xxxx_add_role_and_status_to_users_table.php

php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuté lors de php artisan migrate
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter identifier après id
            $table->string('identifier')->unique()->after('id');
            
            // Ajouter role après email
            $table->enum('role', ['admin', 'teacher', 'student'])->after('email');
            
            // Ajouter status après role avec valeur par défaut
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->after('role');
        });
    }

    /**
     * Exécuté lors de php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['identifier', 'role', 'status']);
        });
    }
};
Types de colonnes utilisés
php
$table->string('name');              // VARCHAR(255)
$table->string('email')->unique();   // VARCHAR(255) UNIQUE
$table->text('description');         // TEXT
$table->integer('age');              // INT
$table->bigInteger('user_id');       // BIGINT
$table->enum('role', ['a', 'b']);    // ENUM avec valeurs fixes
$table->boolean('is_active');        // TINYINT(1)
$table->date('birth_date');          // DATE
$table->timestamp('created_at');     // TIMESTAMP
Commandes de migration
bash
# Exécuter toutes les migrations
php artisan migrate

# Annuler la dernière migration
php artisan migrate:rollback

# Réinitialiser et relancer toutes les migrations (⚠️ efface les données)
php artisan migrate:fresh

# Voir le statut des migrations
php artisan migrate:status
2️⃣ Eloquent ORM - Manipulation des données
Qu'est-ce qu'Eloquent ?
Eloquent est l'ORM (Object-Relational Mapping) de Laravel. Il permet de manipuler la base de données avec du code PHP au lieu de requêtes SQL brutes.

Modèle User
Fichier : app/Models/User.php

php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Champs remplissables en masse (mass assignment)
     */
    protected $fillable = [
        'identifier',
        'name',
        'email',
        'role',
        'status',
        'password',
    ];

    /**
     * Champs cachés lors de la sérialisation (API)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast automatique de types
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
Requêtes Eloquent - Exemples pratiques
Récupérer tous les utilisateurs

php
$users = User::all();
// SELECT * FROM users
Récupérer un utilisateur par ID

php
$user = User::find(1);
// SELECT * FROM users WHERE id = 1 LIMIT 1

// Ou lancer une exception si non trouvé
$user = User::findOrFail(1);
Filtrer avec where()

php
// Utilisateurs en attente
$pendingUsers = User::where('status', 'pending')->get();
// SELECT * FROM users WHERE status = 'pending'

// Utilisateurs élèves approuvés
$students = User::where('role', 'student')
                ->where('status', 'approved')
                ->get();
// SELECT * FROM users WHERE role = 'student' AND status = 'approved'
Trier avec orderBy()

php
$users = User::orderBy('created_at', 'desc')->get();
// SELECT * FROM users ORDER BY created_at DESC
Récupérer le premier résultat

php
$lastUser = User::where('identifier', 'like', 'E2026%')
                ->orderBy('id', 'desc')
                ->first();
// SELECT * FROM users WHERE identifier LIKE 'E2026%' 
// ORDER BY id DESC LIMIT 1
Compter les résultats

php
$pendingCount = User::where('status', 'pending')->count();
// SELECT COUNT(*) FROM users WHERE status = 'pending'
Créer un enregistrement

php
// Méthode 1 : create()
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);

// Méthode 2 : new + save()
$user = new User();
$user->name = 'John Doe';
$user->email = 'john@example.com';
$user->password = Hash::make('password');
$user->save();
Mettre à jour un enregistrement

php
// Méthode 1 : update()
User::where('id', 1)->update(['status' => 'approved']);

// Méthode 2 : Modifier puis save()
$user = User::find(1);
$user->status = 'approved';
$user->save();
Supprimer un enregistrement

php
$user = User::find(1);
$user->delete();

// Ou directement
User::destroy(1);
3️⃣ Middleware - Contrôle d'accès
Qu'est-ce qu'un middleware ?
Un middleware est un "filtre" qui s'exécute avant ou après une requête HTTP. Il permet de vérifier, modifier ou bloquer des requêtes.

Création du RoleMiddleware
bash
php artisan make:middleware RoleMiddleware
Fichier : app/Http/Middleware/RoleMiddleware.php

php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Gérer une requête entrante
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  Le rôle requis (admin, teacher, student)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur a le bon rôle
        if (auth()->user()->role !== $role) {
            abort(403, 'Accès non autorisé');
        }

        // Si tout est OK, continuer vers la route
        return $next($request);
    }
}
Enregistrement du middleware
Fichier : bootstrap/app.php (Laravel 11)

php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enregistrer le middleware avec un alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
Utilisation dans les routes
php
// Protéger une seule route
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
     ->middleware(['auth', 'role:admin']);

// Protéger un groupe de routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});
4️⃣ Routes - Navigation dans l'application
Fichier de routes
Fichier : routes/web.php

php
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Page d'accueil publique
Route::get('/', function () {
    return view('welcome');
});

// Route dashboard avec redirection selon le rôle
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'teacher') {
        return redirect()->route('teacher.dashboard');
    } elseif ($user->role === 'student') {
        return redirect()->route('student.dashboard');
    }
})->middleware(['auth'])->name('dashboard');

// ============================================
// ROUTES ADMIN (protégées par auth + role:admin)
// ============================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', [AdminController::class, 'dashboard'])
         ->name('admin.dashboard');
    
    // Inscriptions en attente
    Route::get('/pending-registrations', [AdminController::class, 'pendingRegistrations'])
         ->name('admin.pending');
    
    // Approuver une inscription
    Route::post('/approve/{id}', [AdminController::class, 'approve'])
         ->name('admin.approve');
    
    // Rejeter une inscription
    Route::post('/reject/{id}', [AdminController::class, 'reject'])
         ->name('admin.reject');
});

// ============================================
// ROUTES TEACHER
// ============================================
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])
         ->name('teacher.dashboard');
});

// ============================================
// ROUTES STUDENT
// ============================================
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])
         ->name('student.dashboard');
});

// Routes d'authentification (login, register, etc.)
require __DIR__.'/auth.php';
Types de routes
php
// Route GET
Route::get('/url', [Controller::class, 'method']);

// Route POST
Route::post('/url', [Controller::class, 'method']);

// Route avec nom (pour les redirections et liens)
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
     ->name('admin.dashboard');

// Dans une vue, générer l'URL :
// route('admin.dashboard') → /admin/dashboard

// Route avec paramètre
Route::get('/user/{id}', [UserController::class, 'show']);
Route::post('/approve/{id}', [AdminController::class, 'approve']);

// Groupe de routes avec middleware
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
});

// Groupe avec préfixe
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', ...);  // URL: /admin/dashboard
    Route::get('/users', ...);      // URL: /admin/users
});
Commandes utiles
bash
# Lister toutes les routes
php artisan route:list

# Lister les routes d'un contrôleur spécifique
php artisan route:list --name=admin

# Nettoyer le cache des routes
php artisan route:clear
5️⃣ Contrôleurs - Logique de l'application
Création de contrôleurs
bash
php artisan make:controller AdminController
php artisan make:controller TeacherController
php artisan make:controller StudentController
AdminController complet
Fichier : app/Http/Controllers/AdminController.php

php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Afficher le dashboard admin
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Afficher les inscriptions en attente
     */
    public function pendingRegistrations()
    {
        // Récupérer tous les utilisateurs avec status = 'pending'
        $pendingUsers = User::where('status', 'pending')->get();
        
        // Passer les données à la vue
        return view('admin.pending-registrations', compact('pendingUsers'));
    }

    /**
     * Approuver une inscription
     */
    public function approve($id)
    {
        // Trouver l'utilisateur ou renvoyer 404
        $user = User::findOrFail($id);
        
        // Changer le statut
        $user->status = 'approved';
        $user->save();
        
        // Rediriger avec message de succès
        return redirect()->back()
                         ->with('success', 'Utilisateur validé avec succès.');
    }

    /**
     * Rejeter une inscription
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'rejected';
        $user->save();
        
        return redirect()->back()
                         ->with('success', 'Utilisateur rejeté.');
    }
}
Register
text

### RegisteredUserController personnalisé

**Fichier : `app/Http/Controllers/Auth/RegisteredUserController.php`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Afficher le formulaire d'inscription
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription d'un nouvel utilisateur
     */
    public function store(Request $request): RedirectResponse
    {
        // Validation des données
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:student,teacher'], // Pas admin
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Génération de l'identifiant selon le rôle
        if ($request->role === 'student') {
            $prefix = 'E';
        } elseif ($request->role === 'teacher') {
            $prefix = 'P';
        } else {
            $prefix = 'A';
        }

        // Générer le numéro séquentiel
        $year = date('Y'); // 2026
        $lastUser = User::where('identifier', 'like', $prefix . $year . '%')
                        ->orderBy('id', 'desc')
                        ->first();

        if ($lastUser) {
            // Extraire les 3 derniers chiffres et incrémenter
            $lastNumber = (int) substr($lastUser->identifier, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            // Premier utilisateur de ce type cette année
            $newNumber = '001';
        }

        $identifier = $prefix . $year . $newNumber;
        // Résultat : E2026001, E2026002, P2026001, etc.

        // Créer l'utilisateur
        $user = User::create([
            'identifier' => $identifier,
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'status' => 'pending', // En attente de validation
        ]);

        // NE PAS connecter automatiquement
        // Auth::login($user); ← Cette ligne est commentée

        // Rediriger vers login avec message
        return redirect()->route('login')
            ->with('status', 'Votre compte a été créé. Il sera activé après validation par un administrateur.');
    }
}
6️⃣ Validation - Sécuriser les données
Règles de validation utilisées
php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    'role' => ['required', 'in:student,teacher'],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);
Explication des règles
Règle	Description	Exemple
required	Champ obligatoire	Le nom est requis
string	Doit être une chaîne de caractères	"John Doe"
max:255	Maximum 255 caractères	Longueur limitée
email	Format email valide	test@example.com
unique:users	Doit être unique dans la table users	Email non déjà utilisé
in:a,b,c	Doit être une des valeurs listées	student ou teacher uniquement
confirmed	Doit avoir un champ _confirmation	password et password_confirmation
Affichage des erreurs dans Blade
text
{{-- Afficher toutes les erreurs --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Afficher l'erreur d'un champ spécifique --}}
@error('email')
    <div class="text-danger">{{ $message }}</div>
@enderror
7️⃣ Blade Templates - Interface utilisateur
Layout principal
Fichier : resources/views/layouts/app.blade.php

text
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

{{-- Navbar --}}
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="mb-0 navbar-brand h1">
            EduSchool Computer Science
        </span>
        
        <div class="d-flex align-items-center">
            <span class="text-white small me-3">
                {{ auth()->user()->name ?? '' }} ({{ auth()->user()->role ?? '' }})
            </span>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Déconnexion
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        {{-- Sidebar --}}
        <aside class="py-4 bg-white col-md-3 col-lg-2 border-end min-vh-100">
            <nav class="nav flex-column">
                <span class="px-3 mb-2 text-muted text-uppercase small">Navigation</span>
                
                {{-- Lien Dashboard --}}
                <a href="{{ route('dashboard') }}" class="px-3 nav-link">
                    Dashboard
                </a>
                
                {{-- Liens selon le rôle --}}
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.pending') }}" class="px-3 nav-link">
                            Inscriptions en attente
                            @php
                                $pendingCount = \App\Models\User::where('status', 'pending')->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge bg-danger">{{ $pendingCount }}</span>
                            @endif
                        </a>
                        <a href="#" class="px-3 nav-link">Gestion des élèves</a>
                        <a href="#" class="px-3 nav-link">Gestion des profs</a>
                        <a href="#" class="px-3 nav-link">Classes & matières</a>
                        
                    @elseif(auth()->user()->role === 'teacher')
                        <a href="#" class="px-3 nav-link">Mes classes</a>
                        <a href="#" class="px-3 nav-link">Mes matières</a>
                        <a href="#" class="px-3 nav-link">Saisie des notes</a>
                        
                    @elseif(auth()->user()->role === 'student')
                        <a href="#" class="px-3 nav-link">Mes matières</a>
                        <a href="#" class="px-3 nav-link">Mon emploi du temps</a>
                        <a href="#" class="px-3 nav-link">Mes résultats</a>
                    @endif
                @endauth
            </nav>
        </aside>
        
        {{-- Contenu principal --}}
        <main class="py-4 col-md-9 col-lg-10">
            @isset($header)
                <div class="mb-3">
                    {{ $header }}
                </div>
            @endisset
            
            {{ $slot }}
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
Directives Blade principales
text
{{-- Afficher une variable (échappée HTML) --}}
{{ $variable }}

{{-- Afficher sans échappement (⚠️ dangereux) --}}
{!! $htmlContent !!}

{{-- Conditions --}}
@if($user->role === 'admin')
    Vous êtes admin
@elseif($user->role === 'teacher')
    Vous êtes professeur
@else
    Vous êtes élève
@endif

{{-- Boucles --}}
@foreach($users as $user)
    <li>{{ $user->name }}</li>
@endforeach

{{-- Vérifier si connecté --}}
@auth
    Contenu pour utilisateur connecté
@endauth

@guest
    Contenu pour utilisateur non connecté
@endguest

{{-- Inclure une vue --}}
@include('partials.header')

{{-- Définir une section --}}
@section('title', 'Dashboard')

{{-- Token CSRF (obligatoire dans les formulaires POST) --}}
@csrf

{{-- Méthode HTTP (pour PUT, PATCH, DELETE) --}}
@method('PUT')

{{-- Afficher les erreurs de validation --}}
@error('email')
    <div class="alert alert-danger">{{ $message }}</div>
@enderror
8️⃣ Artisan - Commandes CLI
bash
# Créer un contrôleur
php artisan make:controller NomController

# Créer un modèle
php artisan make:model NomModele

# Créer un middleware
php artisan make:middleware NomMiddleware

# Créer une migration
php artisan make:migration nom_de_la_migration

# Exécuter les migrations
php artisan migrate

# Annuler la dernière migration
php artisan migrate:rollback

# Réinitialiser toutes les migrations
php artisan migrate:fresh

# Lancer Tinker (console interactive)
php artisan tinker

# Lister les routes
php artisan route:list

# Nettoyer les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Lancer le serveur
php artisan serve

# Voir toutes les commandes disponibles
php artisan list
9️⃣ Tinker - Console interactive
Lancer Tinker
bash
php artisan tinker
Commandes utiles dans Tinker
php
// Voir tous les utilisateurs
User::all();

// Voir seulement certains champs
User::select('id', 'name', 'email', 'role')->get();

// Trouver un utilisateur
User::find(2);

// Modifier un utilisateur
$user = User::find(2);
$user->role = 'admin';
$user->status = 'approved';
$user->save();

// Créer un utilisateur
$admin = new User();
$admin->identifier = 'ADMIN';
$admin->name = 'Administrateur';
$admin->email = 'admin@ecole.com';
$admin->password = Hash::make('admin123');
$admin->role = 'admin';
$admin->status = 'approved';
$admin->save();

// Compter les utilisateurs
User::count();

// Filtrer
User::where('role', 'student')->count();

// Supprimer un utilisateur
User::destroy(5);

// Quitter Tinker
exit

PARTIE 5
📝 PARTIE 5 - Problèmes rencontrés et Solutions + Conclusion
text

## 🐛 Problèmes rencontrés et solutions

Durant le développement, plusieurs problèmes ont été rencontrés et résolus. Voici le détail complet.

---

### ❌ PROBLÈME 1 : Compte ADMIN avec mauvais rôle

**Description du problème :**
- Le compte avec l'identifiant "ADMIN" était enregistré avec le rôle "student"
- Lors de la connexion, redirection vers `/student/dashboard` au lieu de `/admin/dashboard`
- L'admin ne pouvait pas accéder aux fonctionnalités administratives

**Cause :**
- Le compte a été créé manuellement avec le mauvais rôle dans la base de données

**Solution appliquée :**
```bash
php artisan tinker
php
// Chercher le compte
User::all();

// Trouver le compte avec identifier "ADMIN" (id: 2)
$admin = User::find(2);

// Corriger le rôle
$admin->role = 'admin';
$admin->status = 'approved';
$admin->save();

// Vérifier
User::find(2);
exit
Résultat : Le compte ADMIN redirige maintenant correctement vers /admin/dashboard

❌ PROBLÈME 2 : Middleware RoleMiddleware inexistant
Description du problème :

Erreur 500 : Target class [App\Http\Middleware\RoleMiddleware] does not exist

La page des inscriptions en attente ne s'affichait pas

Le middleware était référencé dans les routes mais n'existait pas

Message d'erreur complet :

text
Illuminate\Contracts\Container\BindingResolutionException
Target class [App\Http\Middleware\RoleMiddleware] does not exist.
Cause :

Le middleware était enregistré dans bootstrap/app.php

Mais le fichier RoleMiddleware.php n'avait pas été créé

Solution appliquée :

Étape 1 : Créer le middleware

bash
php artisan make:middleware RoleMiddleware
Étape 2 : Implémenter la logique

Fichier : app/Http/Middleware/RoleMiddleware.php

php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur a le bon rôle
        if (auth()->user()->role !== $role) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}
Étape 3 : Enregistrer dans bootstrap/app.php

php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
Résultat : La page des inscriptions en attente fonctionne correctement

❌ PROBLÈME 3 : Champ 'identifier' manquant lors de l'inscription
Description du problème :

Erreur lors de la création d'un nouveau compte

Message : Field 'identifier' doesn't have a default value

L'inscription échouait avec une erreur 500

Message d'erreur complet :

text
SQLSTATE[HY000]: General error: 1364 Field 'identifier' doesn't have a default value
Cause :

La table users a une colonne identifier obligatoire (NOT NULL)

Le contrôleur d'inscription ne générait pas cet identifiant

Laravel essayait d'insérer sans valeur pour identifier

Solution appliquée :

Modifier RegisteredUserController.php pour générer automatiquement l'identifiant :

php
public function store(Request $request): RedirectResponse
{
    // ... validation ...

    // Générer l'identifiant selon le rôle
    if ($request->role === 'student') {
        $prefix = 'E';
    } elseif ($request->role === 'teacher') {
        $prefix = 'P';
    } else {
        $prefix = 'A';
    }

    // Générer le numéro séquentiel
    $year = date('Y');
    $lastUser = User::where('identifier', 'like', $prefix . $year . '%')
                    ->orderBy('id', 'desc')
                    ->first();

    if ($lastUser) {
        $lastNumber = (int) substr($lastUser->identifier, -3);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '001';
    }

    $identifier = $prefix . $year . $newNumber;

    // Créer l'utilisateur AVEC l'identifiant
    $user = User::create([
        'identifier' => $identifier,  // ← Ligne ajoutée
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role,
        'password' => Hash::make($request->password),
        'status' => 'pending',
    ]);

    // ...
}
Résultat : Les inscriptions fonctionnent et génèrent des identifiants uniques (E2026001, P2026001, etc.)

❌ PROBLÈME 4 : Déconnexion avec erreur 419
Description du problème :

Erreur 419 "Page Expired" lors de la tentative de déconnexion

Se produit quand on tape /logout directement dans l'URL

Cause :

La route /logout nécessite une requête POST avec token CSRF

Accéder à /logout via l'URL utilise GET sans token

Le token CSRF a expiré ou est manquant

Solution :

Le bouton de déconnexion doit utiliser un formulaire POST avec @csrf :

text
{{-- ✅ BON --}}
<form method="POST" action="{{ route('logout') }}" class="mb-0">
    @csrf
    <button type="submit" class="btn btn-outline-danger btn-sm">
        Déconnexion
    </button>
</form>

{{-- ❌ MAUVAIS --}}
<a href="{{ route('logout') }}">Déconnexion</a>
Recommandation :

Ne jamais taper /logout dans la barre d'adresse

Toujours utiliser le bouton de déconnexion

Le bouton inclut automatiquement le token CSRF

Résultat : La déconnexion fonctionne parfaitement via le bouton

❌ PROBLÈME 5 : Connexion automatique après inscription
Description du problème :

Après l'inscription, l'utilisateur était automatiquement connecté

Il accédait directement au dashboard même avec statut "pending"

Cela court-circuitait le système de validation par l'admin

Cause :

Le code par défaut de Laravel Breeze contient Auth::login($user)

Cette ligne connecte automatiquement après inscription

Solution appliquée :

Dans RegisteredUserController.php, supprimer la connexion automatique :

php
public function store(Request $request): RedirectResponse
{
    // ... création de l'utilisateur ...

    // SUPPRIMER cette ligne :
    // Auth::login($user);

    // Rediriger vers login avec message
    return redirect()->route('login')
        ->with('status', 'Votre compte a été créé. Il sera activé après validation par un administrateur.');
}
Résultat :

L'utilisateur est redirigé vers la page de connexion après inscription

Il voit un message lui indiquant d'attendre la validation

Il ne peut se connecter qu'après approbation par l'admin

❌ PROBLÈME 6 : Option "Admin" dans le formulaire d'inscription
Description du problème :

N'importe qui pouvait s'inscrire en tant qu'admin

Risque de sécurité majeur

Plusieurs admins pouvaient être créés

Solution appliquée :

Étape 1 : Retirer l'option du formulaire

Dans resources/views/auth/register.blade.php :

text
<select name="role" id="role" required>
    <option value="">-- Choisir un rôle --</option>
    <option value="student">Élève</option>
    <option value="teacher">Professeur</option>
    {{-- Option Admin supprimée --}}
</select>
Étape 2 : Bloquer côté serveur

Dans RegisteredUserController.php :

php
$request->validate([
    'role' => ['required', 'in:student,teacher'], // admin exclu
]);
Résultat :

Seuls "Élève" et "Professeur" sont disponibles

Même en manipulant le HTML, la validation serveur bloque "admin"

Un seul admin existe dans le système

❌ PROBLÈME 7 : Erreur "User not found" dans Tinker
Description du problème :

php
$admin = User::where('name', 'ADMIN')->first();
// Résultat : null
Cause :

Le compte cherché n'existait pas avec le nom "ADMIN"

Le champ à chercher était identifier et non name

Solution :

php
// Voir tous les utilisateurs d'abord
User::all();

// Utiliser l'ID ou l'identifier
$admin = User::find(2);
// ou
$admin = User::where('identifier', 'ADMIN')->first();
Résultat : L'utilisateur est trouvé et peut être modifié

📊 Récapitulatif des apprentissages
Compétences techniques acquises
Backend Laravel

✅ Installation et configuration de Laravel

✅ Système d'authentification avec Breeze

✅ Migrations de base de données

✅ Eloquent ORM (requêtes, relations)

✅ Middleware personnalisé

✅ Contrôleurs et routes

✅ Validation de formulaires

✅ Gestion des sessions et messages flash

✅ Utilisation de Tinker

Frontend

✅ Blade templates et layouts

✅ Bootstrap 5 pour le design

✅ Formulaires avec protection CSRF

✅ Affichage conditionnel selon le rôle

Base de données

✅ Conception de schéma (users avec rôles)

✅ Migrations (ajout de colonnes)

✅ Types de données (enum, string, etc.)

Sécurité

✅ Hachage de mots de passe

✅ Protection CSRF

✅ Validation côté serveur

✅ Contrôle d'accès par rôle

✅ Middleware d'authentification

🎯 Commandes essentielles mémorisées
bash
# Projet
composer create-project laravel/laravel nom-projet
composer install
npm install

# Base de données
php artisan migrate
php artisan migrate:fresh
php artisan migrate:rollback

# Création de fichiers
php artisan make:controller NomController
php artisan make:middleware NomMiddleware
php artisan make:migration nom_migration
php artisan make:model NomModel

# Serveur
php artisan serve
npm run dev

# Debug
php artisan tinker
php artisan route:list

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
🚀 Prochaines étapes possibles
Fonctionnalités à développer
Module Classes

Créer une table classes

CRUD complet (Create, Read, Update, Delete)

Assigner des élèves aux classes

Module Matières

Créer une table subjects (matières)

Lier les matières aux professeurs

Lier les matières aux classes

Module Notes

Créer une table grades

Saisie des notes par les professeurs

Calcul automatique des moyennes

Génération de bulletins

Module Emploi du temps

Créer une table schedules

Interface calendrier

Affichage par classe et par élève

Améliorations

Notifications par email

Export PDF des bulletins

Statistiques et graphiques

Messagerie interne

Gestion des absences

Paiements en ligne

📚 Ressources utiles
Documentation officielle

Laravel : https://laravel.com/docs

Eloquent ORM : https://laravel.com/docs/eloquent

Blade Templates : https://laravel.com/docs/blade

Validation : https://laravel.com/docs/validation

Tutoriels recommandés

Grafikart Laravel (FR) : https://grafikart.fr/formations/laravel

Laracasts (EN) : https://laracasts.com

Laravel Daily (EN) : https://www.youtube.com/@LaravelDaily

Communauté

Forum Laravel : https://laracasts.com/discuss

Discord Laravel : https://discord.gg/laravel

Stack Overflow : https://stackoverflow.com/questions/tagged/laravel

🎓 Conclusion
Ce qui a été accompli
Ce projet a permis de créer une application web fonctionnelle de gestion d'école avec :

✅ Un système d'authentification complet
✅ Trois rôles distincts (Admin, Professeur, Élève)
✅ Un processus d'inscription avec validation
✅ Des dashboards personnalisés par rôle
✅ Une génération automatique d'identifiants
✅ Une sécurité robuste (CSRF, hachage, middleware)
✅ Une architecture propre et extensible

Leçons apprises
1. L'importance de la planification

Définir les rôles et permissions dès le début

Concevoir la structure de la base de données avant de coder

2. Le débogage est une compétence essentielle

Lire attentivement les messages d'erreur

Utiliser Tinker pour tester rapidement

Vérifier les logs (`storage/logs/           
## 🚀 Dernières Mises à Jour (Janvier 2026)

### Nouvelles Fonctionnalités

#### Gestion des Années Scolaires
- Interface complète de gestion des années scolaires (CRUD)
- Possibilité de définir une année scolaire comme année courante
- Validation des dates (début < fin, pas de chevauchement)
- Gestion des dépendances avant suppression
- Affichage des statistiques par année

#### Gestion des Classes
- Création et configuration des classes
- Association des classes aux années scolaires
- Gestion des capacités et descriptions
- Relations avec les niveaux et les matières

#### Améliorations Techniques
- Mise à jour du modèle [SchoolClass](cci:2://file:///c:/xampp/htdocs/school-managment/school-management/app/Models/SchoolClass.php:8:0-37:1) avec les relations
- Ajout de la colonne `class_id` à la table `users`
- Amélioration du tableau de bord administrateur
- Optimisation des requêtes de base de données

### Prochaines Étapes
- [ ] Gestion des emplois du temps
- [ ] Gestion des notes et évaluations
- [ ] Messagerie interne
- [ ] Tableaux de bord personnalisés
