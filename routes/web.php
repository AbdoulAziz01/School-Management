<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Teacher\TeacherClassesController;
use App\Http\Controllers\Teacher\TeacherGradesController;
use App\Http\Controllers\Teacher\TeacherAttendanceController;
use App\Http\Controllers\Teacher\TeacherScheduleController;
use App\Http\Controllers\Teacher\TeacherProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Student\StudentLmsController;
use App\Http\Controllers\Student\StudentQuizController;
use App\Http\Controllers\Student\StudentCardController;
use App\Http\Controllers\Student\StudentVirtualClassController;
use App\Http\Controllers\Teacher\TeacherLmsController;
use App\Http\Controllers\Teacher\TeacherVirtualClassController;
use App\Http\Controllers\Admin\AdminLmsController;
use App\Http\Controllers\Admin\AdminCardController;

// Page d'accueil
Route::get('/', function () {
    return view('welcome-school');
})->name('home');

// Vérification publique d'un bulletin (lien QR Code — aucune auth requise)
Route::get('/bulletin/verify', [\App\Http\Controllers\BulletinVerifyController::class, 'show'])
    ->name('bulletin.verify');

// Vérification QR carte scolaire (publique — signature Laravel requise)
Route::get('/card/verify/{student}', [StudentCardController::class, 'verify'])
    ->name('student.card.verify')
    ->middleware('signed');

// Routes d'authentification
require __DIR__.'/auth.php';

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
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
        } elseif (auth()->user()->role === 'directeur') {
            return redirect()->route('directeur.dashboard');
        } elseif (auth()->user()->role === 'comptable') {
            return redirect()->route('comptable.dashboard');
        } elseif (auth()->user()->role === 'caissier') {
            return redirect()->route('caisse.dashboard');
        }
        return redirect()->route('home');
    })->name('dashboard');

    // Notifications en base (badge barre de navigation), communes à tous les rôles
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::post('/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
    });
});

// Plateforme super administrateur
Route::prefix('platform')->middleware(['auth', 'super_admin'])->name('platform.')->group(function () {
    Route::get('/', fn () => redirect()->route('platform.dashboard'));
    Route::get('/dashboard', [\App\Http\Controllers\Platform\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/students/unassigned', [\App\Http\Controllers\Platform\UnassignedStudentController::class, 'index'])->name('students.unassigned');
    Route::get('schools/{school}/inspection', [\App\Http\Controllers\Platform\SchoolInspectionController::class, 'index'])->name('schools.inspection');
    Route::get('schools/{school}/classes/{class}', [\App\Http\Controllers\Platform\SchoolInspectionDetailController::class, 'showClass'])->name('schools.classes.show');
    Route::get('schools/{school}/users/{user}', [\App\Http\Controllers\Platform\SchoolInspectionDetailController::class, 'showUser'])->name('schools.users.show');
    Route::get('schools/{school}/subjects/{subject}', [\App\Http\Controllers\Platform\SchoolInspectionDetailController::class, 'showSubject'])->name('schools.subjects.show');
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Platform\ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [\App\Http\Controllers\Platform\ProfileController::class, 'update'])->name('update');
    });
    Route::resource('schools', \App\Http\Controllers\Platform\SchoolController::class);
    Route::post('schools/{school}/students/{user}/assign-class', [\App\Http\Controllers\Platform\SchoolController::class, 'assignStudentToClass'])->name('schools.students.assign-class');
    Route::patch('schools/{school}/toggle-active', [\App\Http\Controllers\Platform\SchoolController::class, 'toggleActive'])->name('schools.toggle-active');
    Route::patch('schools/{school}/toggle-accounting-module', [\App\Http\Controllers\Platform\SchoolController::class, 'toggleAccountingModule'])->name('schools.toggle-accounting-module');
    Route::patch('schools/{school}/regenerate-code', [\App\Http\Controllers\Platform\SchoolController::class, 'regenerateCode'])->name('schools.regenerate-code');
    Route::prefix('schools/{school}/cycles')->name('schools.cycles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Platform\SchoolLevelController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Platform\SchoolLevelController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Platform\SchoolLevelController::class, 'store'])->name('store');
        Route::get('/{level}/edit', [\App\Http\Controllers\Platform\SchoolLevelController::class, 'edit'])->name('edit');
        Route::put('/{level}', [\App\Http\Controllers\Platform\SchoolLevelController::class, 'update'])->name('update');
        Route::delete('/{level}', [\App\Http\Controllers\Platform\SchoolLevelController::class, 'destroy'])->name('destroy');
    });
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
    
    // E-Learning / LMS
    Route::get('/lms', [StudentLmsController::class, 'index'])->name('lms.index');
    Route::get('/lms/lessons/{lesson}/download', [StudentLmsController::class, 'downloadLesson'])->name('lms.download');

    // Quiz interactifs (max 3 tentatives)
    Route::get('/quiz/{quiz}',                          [StudentQuizController::class, 'show'])  ->name('quiz.show');
    Route::post('/quiz/{quiz}/start',                   [StudentQuizController::class, 'start']) ->name('quiz.start');
    Route::get('/quiz/attempt/{attempt}',               [StudentQuizController::class, 'take'])  ->name('quiz.take');
    Route::post('/quiz/attempt/{attempt}/submit',       [StudentQuizController::class, 'submit'])->name('quiz.submit');
    Route::get('/quiz/attempt/{attempt}/result',        [StudentQuizController::class, 'result'])->name('quiz.result');

    // Classes Virtuelles Jitsi
    Route::get('/virtual-classes',                      [StudentVirtualClassController::class, 'index'])->name('virtual-class.index');
    Route::get('/virtual-classes/{virtualClass}/join',  [StudentVirtualClassController::class, 'join']) ->name('virtual-class.join');

    // Carte scolaire QR antifraude
    Route::get('/card',       [StudentCardController::class, 'show']) ->name('card.show');
    Route::get('/card/token', [StudentCardController::class, 'token'])->name('card.token');

    // Ma situation financière (module Comptabilité, établissements privés uniquement)
    Route::middleware('module:accounting')->prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Student\StudentPaymentsController::class, 'index'])->name('index');
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
    Route::post('/assignments/{assignment}/toggle', [TeacherClassesController::class, 'toggleAssignment'])->name('assignments.toggle');
    
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
    Route::get('/attendance/history', [TeacherAttendanceController::class, 'history'])->name('attendance.history');
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
    
    // E-Learning / LMS
    Route::get('/lms', [TeacherLmsController::class, 'index'])->name('lms.index');
    // Cours — (suite inchangée)
    Route::get('/lms/lessons/create', [TeacherLmsController::class, 'lessonCreate'])->name('lms.lesson.create');
    Route::post('/lms/lessons', [TeacherLmsController::class, 'lessonStore'])->name('lms.lesson.store');
    Route::delete('/lms/lessons/{lesson}', [TeacherLmsController::class, 'lessonDestroy'])->name('lms.lesson.destroy');
    // Devoirs
    Route::get('/lms/assignments/create', [TeacherLmsController::class, 'assignmentCreate'])->name('lms.assignment.create');
    Route::post('/lms/assignments', [TeacherLmsController::class, 'assignmentStore'])->name('lms.assignment.store');
    Route::get('/lms/assignments/{assignment}/submissions', [TeacherLmsController::class, 'assignmentSubmissions'])->name('lms.assignment.submissions');
    Route::patch('/lms/submissions/{submission}/grade', [TeacherLmsController::class, 'submissionGrade'])->name('lms.submission.grade');
    Route::delete('/lms/assignments/{assignment}', [TeacherLmsController::class, 'assignmentDestroy'])->name('lms.assignment.destroy');
    // Quiz
    Route::get('/lms/quizzes/create', [TeacherLmsController::class, 'quizCreate'])->name('lms.quiz.create');
    Route::post('/lms/quizzes', [TeacherLmsController::class, 'quizStore'])->name('lms.quiz.store');
    Route::delete('/lms/quizzes/{quiz}', [TeacherLmsController::class, 'quizDestroy'])->name('lms.quiz.destroy');

    // Classes Virtuelles Jitsi
    Route::get('/virtual-classes',                              [TeacherVirtualClassController::class, 'index'])  ->name('virtual-class.index');
    Route::get('/virtual-classes/create',                       [TeacherVirtualClassController::class, 'create']) ->name('virtual-class.create');
    Route::post('/virtual-classes',                             [TeacherVirtualClassController::class, 'store'])  ->name('virtual-class.store');
    Route::patch('/virtual-classes/{virtualClass}/toggle',      [TeacherVirtualClassController::class, 'toggle']) ->name('virtual-class.toggle');
    Route::get('/virtual-classes/{virtualClass}/join',          [TeacherVirtualClassController::class, 'join'])   ->name('virtual-class.join');
    Route::delete('/virtual-classes/{virtualClass}',            [TeacherVirtualClassController::class, 'destroy'])->name('virtual-class.destroy');

    // Mon salaire (module Comptabilité, établissements privés uniquement)
    Route::middleware('module:accounting')->prefix('salary')->name('salary.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Teacher\TeacherSalaryController::class, 'index'])->name('index');
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

    // Profil / informations de l'établissement (nom affiché dans l'ERP, distinct d'AzelieEdu)
    Route::get('/school-settings', [\App\Http\Controllers\Admin\SchoolSettingsController::class, 'edit'])
        ->name('admin.school.settings.edit');
    Route::put('/school-settings', [\App\Http\Controllers\Admin\SchoolSettingsController::class, 'update'])
        ->name('admin.school.settings.update');

    Route::get('/formation/lmd-settings', [\App\Http\Controllers\Admin\FormationLmdSettingsController::class, 'edit'])
        ->name('admin.formation.lmd-settings.edit');
    Route::put('/formation/lmd-settings', [\App\Http\Controllers\Admin\FormationLmdSettingsController::class, 'update'])
        ->name('admin.formation.lmd-settings.update');

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
    Route::get('/students/export', [StudentController::class, 'export'])->name('admin.students.export');

    // Import en masse (Excel/CSV) — voir StudentImportController
    Route::get('/students/import', [\App\Http\Controllers\Admin\StudentImportController::class, 'create'])->name('admin.students.import');
    Route::get('/students/import/template', [\App\Http\Controllers\Admin\StudentImportController::class, 'template'])->name('admin.students.import.template');
    Route::post('/students/import/upload', [\App\Http\Controllers\Admin\StudentImportController::class, 'upload'])->name('admin.students.import.upload');
    Route::get('/students/import/result/{token}', [\App\Http\Controllers\Admin\StudentImportController::class, 'result'])->name('admin.students.import.result');
    Route::get('/students/import/credentials/{token}', [\App\Http\Controllers\Admin\StudentImportController::class, 'downloadCredentials'])->name('admin.students.import.credentials');
    Route::get('/students/import/{token}/mapping', [\App\Http\Controllers\Admin\StudentImportController::class, 'mapping'])->name('admin.students.import.mapping');
    Route::post('/students/import/{token}/preview', [\App\Http\Controllers\Admin\StudentImportController::class, 'preview'])->name('admin.students.import.preview');
    Route::post('/students/import/{token}/store', [\App\Http\Controllers\Admin\StudentImportController::class, 'store'])->name('admin.students.import.store');

    Route::post('students/{student}/regenerate-credentials', [StudentController::class, 'regenerateCredentials'])
        ->name('admin.students.regenerate-credentials');
    Route::post('students/{student}/send-credentials', [StudentController::class, 'sendCredentials'])
        ->name('admin.students.send-credentials');

    // Ressource pour les étudiants (cette ligne DOIT venir après les routes personnalisées)
    Route::resource('students', StudentController::class)->names('admin.students');
    
    // Alias pour la rétrocompatibilité
    Route::get('/students/list', [StudentController::class, 'index'])->name('admin.students.list');
        
    // Gestion des enseignants
    // Import en masse (Excel/CSV) — DOIT être avant Route::resource, sinon
    // GET /teachers/import serait intercepté par GET /teachers/{teacher}.
    Route::get('/teachers/import', [\App\Http\Controllers\Admin\TeacherImportController::class, 'create'])->name('admin.teachers.import');
    Route::get('/teachers/import/template', [\App\Http\Controllers\Admin\TeacherImportController::class, 'template'])->name('admin.teachers.import.template');
    Route::post('/teachers/import/upload', [\App\Http\Controllers\Admin\TeacherImportController::class, 'upload'])->name('admin.teachers.import.upload');
    Route::get('/teachers/import/result/{token}', [\App\Http\Controllers\Admin\TeacherImportController::class, 'result'])->name('admin.teachers.import.result');
    Route::get('/teachers/import/credentials/{token}', [\App\Http\Controllers\Admin\TeacherImportController::class, 'downloadCredentials'])->name('admin.teachers.import.credentials');
    Route::get('/teachers/import/{token}/mapping', [\App\Http\Controllers\Admin\TeacherImportController::class, 'mapping'])->name('admin.teachers.import.mapping');
    Route::post('/teachers/import/{token}/preview', [\App\Http\Controllers\Admin\TeacherImportController::class, 'preview'])->name('admin.teachers.import.preview');
    Route::post('/teachers/import/{token}/store', [\App\Http\Controllers\Admin\TeacherImportController::class, 'store'])->name('admin.teachers.import.store');

    Route::resource('teachers', TeacherController::class)->names('admin.teachers');
    Route::post('teachers/{teacher}/send-invitation', [TeacherController::class, 'sendInvitation'])
        ->name('admin.teachers.send-invitation');
    Route::post('teachers/{teacher}/regenerate-credentials', [TeacherController::class, 'regenerateCredentials'])
        ->name('admin.teachers.regenerate-credentials');

    // Gestion des affectations de classes aux enseignants
    Route::prefix('teachers/{teacher}')->name('admin.teachers.')->group(function () {
        Route::get('/classes', [TeacherClassController::class, 'edit'])->name('classes.edit');
        Route::put('/classes', [TeacherClassController::class, 'update'])->name('classes.update');

        Route::get('/assignments', [TeacherAssignmentController::class, 'teacherAssignments'])->name('assignments');
        Route::get('/assignments/create', [TeacherAssignmentController::class, 'createAssignment'])->name('assignments.create');
        Route::post('/assignments', [TeacherAssignmentController::class, 'storeAssignment'])->name('assignments.store');
        Route::get('/assignments/subjects/{class}', [TeacherAssignmentController::class, 'getSubjectsForTeacherClass'])->name('assignments.subjects');
    });

    Route::delete('/teacher-assignments/{assignment}', [TeacherAssignmentController::class, 'destroyAssignment'])
        ->name('admin.teachers.assignments.destroy');
    Route::get('/teachers/assignments/classes/{academicYearId}', [TeacherAssignmentController::class, 'getClasses'])
        ->name('admin.teachers.assignments.classes');
    
    // Cycles de formation (écoles de formation uniquement)
    Route::resource('cycles', \App\Http\Controllers\Admin\LevelController::class)
        ->names('admin.cycles')
        ->parameters(['cycles' => 'level']);

    // Gestion des classes
    Route::get('/classes/count-by-level', [\App\Http\Controllers\Admin\ClassController::class, 'countByLevel'])
        ->name('admin.classes.count-by-level');
    Route::resource('classes', 'App\Http\Controllers\Admin\ClassController')->names('admin.classes');
    
    // Routes supplémentaires pour les classes
    Route::get('/classes/list', [\App\Http\Controllers\Admin\ClassController::class, 'list'])
        ->name('admin.classes.list');
    Route::get('/classes/create', [\App\Http\Controllers\Admin\ClassController::class, 'create'])
        ->name('admin.classes.create');
    Route::post('/classes/process-all-promotions', [\App\Http\Controllers\Admin\ClassController::class, 'processAllPromotions'])
        ->name('admin.classes.process-all-promotions');
    Route::post('/classes/{class}/process-promotions', [\App\Http\Controllers\Admin\ClassController::class, 'processPromotions'])
        ->name('admin.classes.process-promotions');
    Route::post('/classes/{class}/subjects/{subject}/toggle', [\App\Http\Controllers\Admin\ClassController::class, 'toggleSubject'])
        ->name('admin.classes.subjects.toggle');
    Route::post('/classes/{class}/subjects/{subject}/teacher', [\App\Http\Controllers\Admin\ClassController::class, 'assignSubjectTeacher'])
        ->name('admin.classes.subjects.assign-teacher');
    
    // Gestion des années académiques
    Route::resource('academic-years', 'App\Http\Controllers\Admin\AcademicYearController')->names('admin.academic-years');
    Route::patch('/academic-years/{academicYear}/set-current', [\App\Http\Controllers\Admin\AcademicYearController::class, 'setCurrent'])
        ->name('admin.academic-years.set-current');
    Route::post('/academic-years/{academicYear}/provision', [\App\Http\Controllers\Admin\AcademicYearController::class, 'provision'])
        ->name('admin.academic-years.provision');
    Route::patch('/academic-years/{academicYear}/close', [\App\Http\Controllers\Admin\AcademicYearController::class, 'close'])
        ->name('admin.academic-years.close');
    Route::patch('/academic-years/{academicYear}/reopen', [\App\Http\Controllers\Admin\AcademicYearController::class, 'reopen'])
        ->name('admin.academic-years.reopen');
    
    // Gestion des matières
    Route::resource('subjects', 'App\Http\Controllers\Admin\SubjectController')->names('admin.subjects');

    // Grille de notation du primaire (note max, coefficient, nb de compositions par niveau+matière)
    Route::get('/primary-grading', [\App\Http\Controllers\Admin\PrimaryGradingConfigController::class, 'index'])
        ->name('admin.primary-grading.index');
    Route::put('/primary-grading', [\App\Http\Controllers\Admin\PrimaryGradingConfigController::class, 'update'])
        ->name('admin.primary-grading.update');

    // Emplois du temps par classe
    Route::get('/schedules', [\App\Http\Controllers\Admin\ScheduleController::class, 'index'])
        ->name('admin.schedules.index');
    Route::get('/schedules/class/{class}/edit', [\App\Http\Controllers\Admin\ScheduleController::class, 'edit'])
        ->name('admin.schedules.edit');
    Route::post('/schedules/class/{class}', [\App\Http\Controllers\Admin\ScheduleController::class, 'store'])
        ->name('admin.schedules.store');
    Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Admin\ScheduleController::class, 'destroy'])
        ->name('admin.schedules.destroy');

    // E-Learning / LMS
    Route::get('/lms', [AdminLmsController::class, 'index'])->name('admin.lms.index');
    Route::delete('/lms/lessons/{lesson}', [AdminLmsController::class, 'destroyLesson'])->name('admin.lms.lesson.destroy');
    Route::delete('/lms/assignments/{assignment}', [AdminLmsController::class, 'destroyAssignment'])->name('admin.lms.assignment.destroy');
    Route::delete('/lms/quizzes/{quiz}', [AdminLmsController::class, 'destroyQuiz'])->name('admin.lms.quiz.destroy');

    // Cartes scolaires — liste élèves + aperçu + personnalisation
    Route::prefix('cards')->name('admin.cards.')->group(function () {
        Route::get('/',          [AdminCardController::class, 'index'])->name('index');
        Route::get('/settings',  [AdminCardController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminCardController::class, 'saveSettings'])->name('settings.save');
        Route::post('/toggle-enabled', [AdminCardController::class, 'toggleEnabled'])->name('toggle-enabled');
        Route::get('/{student}', [AdminCardController::class, 'show'])->name('show');
    });

    // Journal d'activité (Audit Log)
    Route::get('/audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])
        ->name('admin.audit-log.index');

    // Comptes du module Comptabilité (directeur/comptable/caissier) — établissements privés uniquement
    Route::middleware('module:accounting')->prefix('accounting-staff')->name('admin.accounting-staff.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'store'])->name('store');
        Route::get('/{accountingStaff}', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'show'])->name('show');
        Route::get('/{accountingStaff}/edit', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'edit'])->name('edit');
        Route::put('/{accountingStaff}', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'update'])->name('update');
        Route::post('/{accountingStaff}/regenerate-credentials', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'regenerateCredentials'])->name('regenerate-credentials');
        Route::delete('/{accountingStaff}', [\App\Http\Controllers\Admin\AccountingStaffController::class, 'destroy'])->name('destroy');
    });

    // Rapports admin : PDF bulletins, fin d'année, exports CSV
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/semester-pdf', [ReportController::class, 'semesterBulletinsPdf'])->name('admin.reports.semester-pdf');
    Route::get('/reports/annual-pdf', [ReportController::class, 'annualReportPdf'])->name('admin.reports.annual-pdf');
    Route::get('/reports/annual-bulletins-pdf', [ReportController::class, 'annualBulletinsPdf'])->name('admin.reports.annual-bulletins-pdf');
    Route::get('/reports/composition-pdf', [ReportController::class, 'compositionBulletinsPdf'])->name('admin.reports.composition-pdf');
    Route::get('/reports/semester-csv', [ReportController::class, 'semesterSummaryCsv'])->name('admin.reports.semester-csv');
    Route::get('/reports/annual-csv', [ReportController::class, 'annualSummaryCsv'])->name('admin.reports.annual-csv');
    Route::get('/reports/grades-csv', [ReportController::class, 'gradesCsv'])->name('admin.reports.grades-csv');

    // Présences : consultation transparence (lecture seule, toutes classes)
    Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/attendance/history', [\App\Http\Controllers\Admin\AttendanceController::class, 'history'])->name('admin.attendance.history');
});

// Module Comptabilité — 3 portails dédiés (établissements privés uniquement,
// voir module:accounting / SchoolModules). Même structure que les groupes
// admin/teacher/student ci-dessus : préfixe + middleware de rôle dédié.
Route::middleware(['auth', 'school.active', 'module:accounting', 'accounting.role:directeur'])
    ->prefix('directeur')->name('directeur.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Accounting\DirecteurDashboardController::class, 'index'])->name('dashboard');
        Route::get('/ledger/export', [\App\Http\Controllers\Accounting\DirecteurDashboardController::class, 'exportLedger'])->name('ledger.export');
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'edit'])->name('edit');
            Route::put('/', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'update'])->name('update');
            Route::post('/password', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'updatePassword'])->name('update-password');
        });

        // Paramétrage financier (Phase 6.2) — tableau croisé Niveau × Type de
        // frais en page d'accueil (index), gestion des types de frais
        // eux-mêmes (colonnes) sur une page dédiée (types).
        Route::prefix('fee-types')->name('fee-types.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'index'])->name('index');
            Route::get('/types', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'types'])->name('types');
            Route::get('/create', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'store'])->name('store');
            // Routes littérales ('/levels...') AVANT les wildcards à un
            // segment ('/{feeType}') — sinon PUT /levels serait intercepté
            // par PUT /{feeType} (Laravel matche dans l'ordre d'enregistrement).
            Route::put('/levels', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'updateLevelAmounts'])->name('levels.update');
            Route::get('/levels/{level}/history', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'history'])->name('levels.history');
            Route::get('/{feeType}/edit', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'edit'])->name('edit');
            Route::put('/{feeType}', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'update'])->name('update');
            Route::delete('/{feeType}', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'destroy'])->name('destroy');
            Route::get('/{feeType}/amounts', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'amounts'])->name('amounts');
            Route::put('/{feeType}/amounts', [\App\Http\Controllers\Accounting\FeeTypeController::class, 'updateAmounts'])->name('amounts.update');
        });

        Route::prefix('salaries')->name('salaries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\EmployeeSalaryController::class, 'index'])->name('index');
            Route::get('/checklist', [\App\Http\Controllers\Accounting\EmployeeSalaryController::class, 'checklist'])->name('checklist');
            // Paiement direct par le directeur (exception à la séparation des
            // tâches, cf. AccountingRoles) — même contrôleur que le comptable,
            // pas de logique dupliquée ; la permission salaire.payer fait foi.
            Route::post('/{salaryPayment}/pay', [\App\Http\Controllers\Accounting\SalaryPaymentController::class, 'pay'])->name('pay');
            Route::get('/{employee}/edit', [\App\Http\Controllers\Accounting\EmployeeSalaryController::class, 'edit'])->name('edit');
            Route::put('/{employee}', [\App\Http\Controllers\Accounting\EmployeeSalaryController::class, 'update'])->name('update');
        });

        Route::prefix('salary-receipts')->name('salary-receipts.')->group(function () {
            Route::get('/{salaryPayment}', [\App\Http\Controllers\Accounting\SalaryReceiptController::class, 'show'])->name('show');
            Route::get('/{salaryPayment}/pdf', [\App\Http\Controllers\Accounting\SalaryReceiptController::class, 'pdf'])->name('pdf');
        });

        // Journal des opérations + suivi financier élèves (Phase 7.1)
        Route::get('/ledger', [\App\Http\Controllers\Accounting\LedgerController::class, 'index'])->name('ledger.index');
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/debtors', [\App\Http\Controllers\Accounting\StudentFinanceController::class, 'debtors'])->name('debtors');
            Route::get('/{student}', [\App\Http\Controllers\Accounting\StudentFinanceController::class, 'show'])->name('show');
        });
        // Lecture seule : pas de route cancel pour le directeur (aucune
        // permission paiement.annuler pour ce rôle, voir AccountingRoles).
        Route::get('/payments', [\App\Http\Controllers\Accounting\PaymentCorrectionController::class, 'index'])->name('payments.index');

        // Centre de Commande — navigation en lecture seule dans l'établissement
        // (Classes → Élève/Enseignant/Matière). Aucune édition pédagogique ici :
        // uniquement de la consultation, voir SchoolBrowserController.
        Route::prefix('school')->name('school.')->group(function () {
            Route::get('/classes', [\App\Http\Controllers\Accounting\SchoolBrowserController::class, 'classesIndex'])->name('classes.index');
            Route::get('/classes/{class}', [\App\Http\Controllers\Accounting\SchoolBrowserController::class, 'classesShow'])->name('classes.show');
            Route::get('/classes/{class}/subjects/{subject}', [\App\Http\Controllers\Accounting\SchoolBrowserController::class, 'subjectGrades'])->name('subjects.grades');
            Route::get('/teachers', [\App\Http\Controllers\Accounting\SchoolBrowserController::class, 'teachersIndex'])->name('teachers.index');
            Route::get('/teachers/{teacher}', [\App\Http\Controllers\Accounting\SchoolBrowserController::class, 'teachersShow'])->name('teachers.show');
            Route::get('/students', [\App\Http\Controllers\Accounting\SchoolBrowserController::class, 'studentsIndex'])->name('students.index');
            Route::get('/students/{student}', [\App\Http\Controllers\Accounting\SchoolBrowserController::class, 'studentsShow'])->name('students.show');
        });

        // Personnel & élèves — vue d'ensemble pour le directeur, avec création
        // limitée à ses subordonnés directs (comptable/caissier).
        Route::prefix('personnel')->name('personnel.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\PersonnelController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Accounting\PersonnelController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Accounting\PersonnelController::class, 'store'])->name('store');
            Route::get('/{group}', [\App\Http\Controllers\Accounting\PersonnelController::class, 'show'])->name('show');
        });

        Route::get('/', fn () => redirect()->route('directeur.dashboard'));
    });

Route::middleware(['auth', 'school.active', 'module:accounting', 'accounting.role:comptable'])
    ->prefix('comptable')->name('comptable.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Accounting\ComptableDashboardController::class, 'index'])->name('dashboard');
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'edit'])->name('edit');
            Route::put('/', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'update'])->name('update');
            Route::post('/password', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'updatePassword'])->name('update-password');
        });

        // Dépenses (Phase 6.4)
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\ExpenseController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Accounting\ExpenseController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Accounting\ExpenseController::class, 'store'])->name('store');
            Route::post('/{expense}/cancel', [\App\Http\Controllers\Accounting\ExpenseController::class, 'cancel'])->name('cancel');
            Route::get('/{expense}/justificatif', [\App\Http\Controllers\Accounting\ExpenseController::class, 'downloadJustificatif'])->name('justificatif');
        });

        // Salaires (Phase 6.4)
        Route::prefix('salaries')->name('salaries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\SalaryPaymentController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\Accounting\SalaryPaymentController::class, 'generate'])->name('generate');
            Route::post('/{salaryPayment}/pay', [\App\Http\Controllers\Accounting\SalaryPaymentController::class, 'pay'])->name('pay');
            Route::post('/{salaryPayment}/cancel', [\App\Http\Controllers\Accounting\SalaryPaymentController::class, 'cancel'])->name('cancel');
        });

        Route::prefix('salary-receipts')->name('salary-receipts.')->group(function () {
            Route::get('/{salaryPayment}', [\App\Http\Controllers\Accounting\SalaryReceiptController::class, 'show'])->name('show');
            Route::get('/{salaryPayment}/pdf', [\App\Http\Controllers\Accounting\SalaryReceiptController::class, 'pdf'])->name('pdf');
        });

        // Corrections/annulations des paiements élèves (Phase 6.4)
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\PaymentCorrectionController::class, 'index'])->name('index');
            Route::post('/{payment}/cancel', [\App\Http\Controllers\Accounting\PaymentCorrectionController::class, 'cancel'])->name('cancel');
        });

        // Reçus, journal des opérations, suivi financier élèves (Phase 7.1)
        Route::prefix('receipts')->name('receipts.')->group(function () {
            Route::get('/search', [\App\Http\Controllers\Accounting\PaymentReceiptController::class, 'search'])->name('search');
            Route::get('/{payment}', [\App\Http\Controllers\Accounting\PaymentReceiptController::class, 'show'])->name('show');
            Route::get('/{payment}/pdf', [\App\Http\Controllers\Accounting\PaymentReceiptController::class, 'pdf'])->name('pdf');
        });

        Route::get('/ledger', [\App\Http\Controllers\Accounting\LedgerController::class, 'index'])->name('ledger.index');
        Route::prefix('students')->name('students.')->group(function () {
            Route::get('/debtors', [\App\Http\Controllers\Accounting\StudentFinanceController::class, 'debtors'])->name('debtors');
            Route::get('/{student}', [\App\Http\Controllers\Accounting\StudentFinanceController::class, 'show'])->name('show');
        });

        Route::get('/', fn () => redirect()->route('comptable.dashboard'));
    });

Route::middleware(['auth', 'school.active', 'module:accounting', 'accounting.role:caissier'])
    ->prefix('caisse')->name('caisse.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Accounting\CaisseController::class, 'index'])->name('dashboard');
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'edit'])->name('edit');
            Route::put('/', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'update'])->name('update');
            Route::post('/password', [\App\Http\Controllers\Accounting\AccountingProfileController::class, 'updatePassword'])->name('update-password');
        });

        // Session de caisse (Phase 6.3)
        Route::post('/session/open', [\App\Http\Controllers\Accounting\CaisseController::class, 'openSession'])->name('session.open');
        Route::get('/session/close', [\App\Http\Controllers\Accounting\CaisseController::class, 'closeSessionForm'])->name('session.close-form');
        Route::post('/session/close', [\App\Http\Controllers\Accounting\CaisseController::class, 'closeSession'])->name('session.close');

        // Recherche et encaissement élève
        Route::get('/students/search', [\App\Http\Controllers\Accounting\CaisseController::class, 'search'])->name('students.search');
        Route::get('/students/{student}/situation', [\App\Http\Controllers\Accounting\CaisseController::class, 'studentSituation'])->name('students.situation');
        Route::get('/students/{student}', [\App\Http\Controllers\Accounting\CaisseController::class, 'showStudent'])->name('students.show');
        Route::post('/students/{student}/pay', [\App\Http\Controllers\Accounting\CaisseController::class, 'pay'])->name('students.pay');

        // Reçus et historique
        Route::get('/receipts/search', [\App\Http\Controllers\Accounting\PaymentReceiptController::class, 'search'])->name('receipts.search');
        Route::get('/receipts/{payment}', [\App\Http\Controllers\Accounting\PaymentReceiptController::class, 'show'])->name('receipts.show');
        Route::get('/receipts/{payment}/pdf', [\App\Http\Controllers\Accounting\PaymentReceiptController::class, 'pdf'])->name('receipts.pdf');
        Route::get('/history', [\App\Http\Controllers\Accounting\CaisseController::class, 'history'])->name('history');
        Route::get('/sessions', [\App\Http\Controllers\Accounting\CaisseController::class, 'sessionHistory'])->name('sessions.index');

        Route::get('/', fn () => redirect()->route('caisse.dashboard'));
    });

// Vérification publique d'un reçu de paiement (lien QR Code — aucune auth requise)
Route::get('/payment-receipt/verify', [\App\Http\Controllers\Accounting\PaymentReceiptController::class, 'verify'])
    ->name('payment.receipt.verify');
Route::get('/salary-receipt/verify', [\App\Http\Controllers\Accounting\SalaryReceiptController::class, 'verify'])
    ->name('salary.receipt.verify');

// En cas de route non trouvée
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});