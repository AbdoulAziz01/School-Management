<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\StudentGradesController;
use App\Http\Controllers\Student\StudentScheduleController;
use App\Http\Controllers\Student\StudentAttendanceController;
use App\Http\Controllers\Student\StudentBulletinController;
use App\Http\Controllers\Admin\StudentAssignmentController;
use App\Http\Controllers\Admin\PendingRegistrationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\TeacherClassController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherClassesController;
use App\Http\Controllers\Teacher\TeacherGradesController;
use App\Http\Controllers\Teacher\TeacherAttendanceController;
use App\Http\Controllers\Teacher\TeacherScheduleController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use App\Http\Controllers\ChatController;

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

    // Redirection vers le tableau de bord approprié selon le rôle
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'super_admin') {
            return redirect()->route('platform.dashboard');
        } elseif (auth()->user()->role === 'admin' || auth()->user()->role === 'surveillant') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'eleve') {
            return redirect()->route('student.dashboard');
        } elseif (in_array(auth()->user()->role, ['professeur', 'teacher'])) {
            return redirect()->route('teacher.dashboard');
        }
        return redirect()->route('home');
    })->name('dashboard');
});

// Plateforme super administrateur
Route::prefix('platform')->middleware(['auth', 'super_admin'])->name('platform.')->group(function () {
    Route::get('/', fn () => redirect()->route('platform.dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\Platform\DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Platform\ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [\App\Http\Controllers\Platform\ProfileController::class, 'update'])->name('update');
    });
    Route::resource('schools', \App\Http\Controllers\Platform\SchoolController::class);
    Route::patch('schools/{school}/toggle-active', [\App\Http\Controllers\Platform\SchoolController::class, 'toggleActive'])->name('schools.toggle-active');
    Route::patch('schools/{school}/regenerate-code', [\App\Http\Controllers\Platform\SchoolController::class, 'regenerateCode'])->name('schools.regenerate-code');
    Route::post('schools/{school}/admins', [\App\Http\Controllers\Platform\SchoolController::class, 'storeAdmin'])->name('schools.admins.store');
    Route::patch('schools/{school}/admins/{user}/password', [\App\Http\Controllers\Platform\SchoolController::class, 'resetAdminPassword'])->name('schools.admins.reset-password');
});

// Groupe de routes pour les étudiants
Route::middleware(['auth', 'school.active', \App\Http\Middleware\StudentMiddleware::class])->prefix('student')->name('student.')->group(function () {
    // Tableau de bord
    Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])->name('dashboard');
    
    // Notes
    Route::get('/grades', [StudentGradesController::class, 'index'])->name('grades');
    
    // Bulletin scolaire (système sénégalais)
    Route::get('/bulletin', [StudentBulletinController::class, 'index'])->name('bulletin');
    Route::get('/bulletin/annual', [StudentBulletinController::class, 'annual'])->name('bulletin.annual');
    
    // Emploi du temps
    Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule');
    
    // Absences/Présences
    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance');
    
    // Profil - Routes groupées
    Route::prefix('profile')->name('profile.')->group(function () {
        // Afficher le profil
        Route::get('/', [StudentProfileController::class, 'index'])->name('index');
        
        // Édition du profil
        Route::get('/edit', [StudentProfileController::class, 'edit'])->name('edit');
        Route::put('/', [StudentProfileController::class, 'update'])->name('update');
        
        // Mise à jour de la photo de profil
        Route::post('/photo', [StudentProfileController::class, 'updatePhoto'])->name('update-photo');
        
        // Mise à jour du mot de passe
        Route::post('/password', [StudentProfileController::class, 'updatePassword'])->name('update-password');
    });
    
    // Redirection de /student vers /student/dashboard
    Route::get('/', function () {
        return redirect()->route('student.dashboard');
    });
});

// Routes pour les enseignants
Route::middleware(['auth', 'school.active', \App\Http\Middleware\TeacherMiddleware::class])->prefix('teacher')->name('teacher.')->group(function () {
    // Tableau de bord
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    
    // Mes classes
    Route::get('/classes', [TeacherClassesController::class, 'index'])->name('classes.index');
    Route::get('/classes/{id}', [TeacherClassesController::class, 'show'])->name('classes.show');
    
    // Gestion des notes
    Route::get('/grades', [TeacherGradesController::class, 'index'])->name('grades.index');
    Route::get('/grades/create', [TeacherGradesController::class, 'create'])->name('grades.create');
    Route::post('/grades', [TeacherGradesController::class, 'store'])->name('grades.store');
    Route::get('/grades/{id}/edit', [TeacherGradesController::class, 'edit'])->name('grades.edit');
    Route::put('/grades/{id}', [TeacherGradesController::class, 'update'])->name('grades.update');
    Route::delete('/grades/{id}', [TeacherGradesController::class, 'destroy'])->name('grades.destroy');
    
    // Gestion des présences
    Route::get('/attendance', [TeacherAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [TeacherAttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/student/{studentId}', [TeacherAttendanceController::class, 'studentHistory'])->name('attendance.student-history');
    
    // Emploi du temps
    Route::get('/schedule', [TeacherScheduleController::class, 'index'])->name('schedule');
    
    // Profil enseignant
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [TeacherProfileController::class, 'index'])->name('index');
        Route::get('/edit', [TeacherProfileController::class, 'edit'])->name('edit');
        Route::put('/', [TeacherProfileController::class, 'update'])->name('update');
        Route::post('/password', [TeacherProfileController::class, 'updatePassword'])->name('update-password');
        Route::post('/photo', [TeacherProfileController::class, 'updatePhoto'])->name('update-photo');
    });
    
    // Redirection de /teacher vers /teacher/dashboard
    Route::get('/', function () {
        return redirect()->route('teacher.dashboard');
    });
});

// Routes administrateur
Route::prefix('admin')->middleware(['auth', 'school.admin', 'school.active'])->group(function () {
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

    // Profil / informations de l'établissement (nom affiché dans l'ERP, distinct d'EduManager)
    Route::get('/school-settings', [\App\Http\Controllers\Admin\SchoolSettingsController::class, 'edit'])
        ->name('admin.school.settings.edit');
    Route::put('/school-settings', [\App\Http\Controllers\Admin\SchoolSettingsController::class, 'update'])
        ->name('admin.school.settings.update');

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
    Route::post('/students/assign', [StudentController::class, 'storeAssignment'])->name('admin.students.assign.store');
    Route::post('/students/assign/bulk', [StudentController::class, 'assignToClassBulk'])->name('admin.students.assign.bulk');
    Route::post('/students/{student}/assign', [StudentController::class, 'assignToClass'])->name('admin.students.assign-to-class');
    Route::delete('/students/{student}/unassign', [StudentController::class, 'unassign'])->name('admin.students.unassign');
    Route::get('/students/search-suggestions', [StudentController::class, 'searchSuggestions'])->name('admin.students.search-suggestions');
    
    // Ressource pour les étudiants (cette ligne DOIT venir après les routes personnalisées)
    Route::resource('students', StudentController::class)->names('admin.students');
    
    // Alias pour la rétrocompatibilité
    Route::get('/students/list', [StudentController::class, 'index'])->name('admin.students.list');
        
    // Gestion des enseignants
    Route::resource('teachers', TeacherController::class)->names('admin.teachers');
    Route::post('teachers/{teacher}/send-invitation', [TeacherController::class, 'sendInvitation'])
        ->name('admin.teachers.send-invitation');

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
    
    // Gestion des matières
    Route::resource('subjects', 'App\Http\Controllers\Admin\SubjectController')->names('admin.subjects');

    // Emplois du temps par classe
    Route::get('/schedules', [\App\Http\Controllers\Admin\ScheduleController::class, 'index'])
        ->name('admin.schedules.index');
    Route::get('/schedules/class/{class}/edit', [\App\Http\Controllers\Admin\ScheduleController::class, 'edit'])
        ->name('admin.schedules.edit');
    Route::post('/schedules/class/{class}', [\App\Http\Controllers\Admin\ScheduleController::class, 'store'])
        ->name('admin.schedules.store');
    Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Admin\ScheduleController::class, 'destroy'])
        ->name('admin.schedules.destroy');
});

// Routes du Chatbot IA - Protégées par authentification
Route::middleware('auth')->prefix('chat')->name('chat.')->group(function () {
    // Page du chat
    Route::get('/', [ChatController::class, 'index'])->name('index');
    
    // Envoyer un message
    Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
    
    // Vérification de l'état du service
    Route::get('/health', [ChatController::class, 'healthCheck'])->name('health');
    
    // Schéma de la BDD (admin uniquement)
    Route::get('/schema', [ChatController::class, 'getSchema'])->name('schema');
});

// En cas de route non trouvée
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});