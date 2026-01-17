<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Admin\StudentAssignmentController;
use App\Http\Controllers\Admin\PendingRegistrationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\TeacherClassController;

// Route de débogage temporaire
Route::get('/debug/student', function() {
    $user = Auth::user();
    
    if (!$user) {
        return 'Aucun utilisateur connecté. <a href="'.route('login').'">Se connecter</a>';
    }
    
    return [
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_authenticated' => Auth::check(),
            'is_admin' => $user->role === 'admin',
            'is_student' => $user->role === 'eleve'
        ],
        'session' => session()->all(),
        'routes' => [
            'login' => route('login'),
            'student_dashboard' => route('student.dashboard'),
            'admin_dashboard' => route('admin.dashboard')
        ]
    ];
});

// Page d'accueil
Route::get('/', function () {
    return view('welcome-school');
})->name('home');

// Routes d'authentification
require __DIR__.'/auth.php';

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tableau de bord étudiant
    Route::middleware('role:eleve')->group(function () {
        Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    });
    
    // Tableau de bord professeur
    Route::middleware('role:professeur')->group(function () {
        Route::get('/teacher/dashboard', function () {
            return view('teacher.dashboard');
        })->name('teacher.dashboard');
    });
    
    // Gestion du profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Redirection vers le tableau de bord approprié selon le rôle
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'eleve') {
            return redirect()->route('student.dashboard');
        }
        return redirect()->route('home');
    })->name('dashboard');
});

// Routes étudiant
Route::prefix('student')->middleware(['auth', 'role:eleve'])->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/timetable', [StudentTimetableController::class, 'index'])->name('student.timetable');
    Route::get('/assignments', [StudentAssignmentController::class, 'index'])->name('student.assignments');
    Route::get('/grades', [StudentGradeController::class, 'index'])->name('student.grades');
    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('student.attendance');
});

// Routes administrateur
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    // Redirection de /admin vers /admin/dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
    
    // Profil administrateur
    Route::prefix('profile')->name('admin.profile.')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('update');
    });
    
    // Tableau de bord administrateur
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Gestion des inscriptions en attente
    Route::get('/pending', [PendingRegistrationController::class, 'pending'])
        ->name('admin.pending');
        
    Route::get('/pending-registrations', [PendingRegistrationController::class, 'pending'])
        ->name('admin.registrations.pending');
        
    // Gestion des inscriptions
    Route::patch('/registrations/{user}/approve', [PendingRegistrationController::class, 'approve'])
        ->name('admin.registrations.approve');
        
    Route::patch('/registrations/{user}/reject', [PendingRegistrationController::class, 'reject'])
        ->name('admin.registrations.reject');

    // Gestion des étudiants - Routes personnalisées (DOIT être avant la ressource)
    Route::get('/students/assign', [StudentController::class, 'showAssignForm'])->name('admin.students.assign');
    Route::post('/students/assign/bulk', [StudentController::class, 'assignToClassBulk'])->name('admin.students.assign.bulk');
    Route::post('/students/{student}/assign', [StudentController::class, 'assignToClass'])->name('admin.students.assign-to-class');
    Route::delete('/students/{student}/unassign', [StudentController::class, 'unassign'])->name('admin.students.unassign');
    
    // Ressource pour les étudiants (cette ligne DOIT venir après les routes personnalisées)
    Route::resource('students', StudentController::class)->names('admin.students');
    
    // Alias pour la rétrocompatibilité
    Route::get('/students/list', [StudentController::class, 'index'])->name('admin.students.list');
        
    // Gestion des enseignants
    Route::resource('teachers', TeacherController::class)->names('admin.teachers');
    
    // Gestion des affectations de classes aux enseignants
    Route::prefix('teachers/{teacher}')->name('admin.teachers.')->group(function() {
        Route::get('/classes', [TeacherClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes', [TeacherClassController::class, 'update'])->name('classes.update');
    });
    
    // Gestion des classes
    Route::resource('classes', 'App\Http\Controllers\Admin\ClassController')->names('admin.classes');
    
    // Routes supplémentaires pour les classes
    Route::get('/classes/list', [\App\Http\Controllers\Admin\ClassController::class, 'list'])
        ->name('admin.classes.list');
    Route::get('/classes/create', [\App\Http\Controllers\Admin\ClassController::class, 'create'])
        ->name('admin.classes.create');
    
    // Gestion des années académiques
    Route::resource('academic-years', 'App\Http\Controllers\Admin\AcademicYearController')->names('admin.academic-years');
    Route::patch('/academic-years/{academicYear}/set-current', [\App\Http\Controllers\Admin\AcademicYearController::class, 'setCurrent'])
        ->name('admin.academic-years.set-current');
});

// Autres routes publiques
// ...

// En cas de route non trouvée
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

// Route pour la page des inscriptions en attente (temporaire)
Route::get('/pending', function() {
    return view('admin.pending');
})->name('admin.pending');
