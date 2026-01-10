<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// La on a modifié pour afficher la vue "welcome-school" à la racine du site
// Ce sont les routes web de l'application. Elles sont chargées par le RouteServiceProvider
// et sont assignées au groupe "web" qui contient l'état de session, la protection
Route::get('/', function () {
    return view('welcome-school');
});

// Une route pour le tableau de bord, accessible uniquement aux utilisateurs authentifiés et vérifiés
// Le middleware signifie que l'utilisateur doit être connecté et avoir vérifié son adresse e-mail

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Routes pour la gestion du profil utilisateur, protégées par le middleware d'authentification
// Cela signifie que seules les personnes connectées peuvent accéder à ces routes
// Elles permettent d'éditer, mettre à jour et supprimer le profil utilisateur
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
// Inclusion des routes d'authentification supplémentaires définies dans le fichier auth.php
require __DIR__.'/auth.php';

// // Routes pour rediriger les utilisateurs vers leur tableau de bord respectif en fonction de leur rôle
// // Le middleware 'auth' garantit que seules les personnes connectées peuvent accéder à ces routes
// // On utilise une fonction anonyme pour vérifier le rôle de l'utilisateur connecté
Route::middleware(['auth'])->group(function () {

    // Redirection vers le tableau de bord approprié selon le rôle de l'utilisateur
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;

        return match ($role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default   => abort(403),
        };
    })->name('dashboard');
    //  Routes spécifiques pour chaque rôle d'utilisateur administratif
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Routes spécifiques pour chaque rôle d'utilisateur enseignant
    Route::get('/teacher/dashboard', function () {
        return view('teacher.dashboard');
    })->name('teacher.dashboard');

    // Routes spécifiques pour chaque rôle d'utilisateur étudiant
    Route::get('/student/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
});

// Routes pour la gestion des inscriptions en attente par l'administrateur
Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // Voir les inscriptions en attente
    Route::get('/admin/pending-registrations', function () {
        $pendingUsers = \App\Models\User::where('status', 'pending')->get();
        return view('admin.pending-registrations', compact('pendingUsers'));
    })->name('admin.pending');

    // Valider une inscription
    Route::post('/admin/approve/{user}', function (\App\Models\User $user) {
        $user->status = 'approved';
        $user->save();
        return redirect()->back()->with('success', 'Utilisateur validé avec succès.');
    })->name('admin.approve');

    // Rejeter une inscription
    Route::post('/admin/reject/{user}', function (\App\Models\User $user) {
        $user->status = 'rejected';
        $user->save();
        return redirect()->back()->with('success', 'Utilisateur rejeté.');
    })->name('admin.reject');
});
