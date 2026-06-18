<?php

namespace Database\Seeders;

// ════════════════════════════════════════════════════════════════════════════
//  CONFIGURATION — ALIAS DE MODÈLES
// ════════════════════════════════════════════════════════════════════════════
use App\Models\AcademicYear      as AcademicYearModel;
use App\Models\Level             as LevelModel;
use App\Models\School            as SchoolModel;
use App\Models\SchoolClass       as ClasseModel;
use App\Models\Subject           as SubjectModel;
use App\Models\TeacherAssignment as TeacherAssignmentModel;
use App\Models\User              as UserModel;
// ════════════════════════════════════════════════════════════════════════════

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * AzelieSchoolSeeder
 *
 * Recharge proprement la base pour une démonstration :
 *  1.  Nettoie toutes les données sauf le super_admin
 *  2.  Crée l'école "Institution Scolaire Azelie" à Dakar
 *  3.  Crée l'année scolaire 2025-2026
 *  4.  Crée 4 classes collège avec 5–10 élèves chacune
 *  5.  Crée 5 matières + 5 enseignants isolés par matière
 *  6.  Génère un historique complet de notes (S1 + S2)
 *  7.  Génère les données E-Learning / LMS complets
 *
 * Usage : php artisan db:seed --class=AzelieSchoolSeeder
 */
class AzelieSchoolSeeder extends Seeder
{
    // ─── Constantes de configuration ─────────────────────────────────────────

    private const SCHOOL_NAME = 'Institution Scolaire Azelie';
    private const SCHOOL_CITY = 'Dakar';
    private const SCHOOL_CODE = 'ISA-2025';
    private const SCHOOL_SLUG = 'institution-scolaire-azelie';
    private const YEAR_NAME   = '2025-2026';
    private const PASSWORD    = 'Passer01';

    // ─── Calendrier des évaluations ──────────────────────────────────────────

    private const EVALUATIONS = [
        ['semester' => 1, 'type' => 'devoir',       'date' => '2025-11-15', 'min' =>  8, 'max' => 18],
        ['semester' => 1, 'type' => 'composition',  'date' => '2026-01-20', 'min' =>  7, 'max' => 19],
        ['semester' => 2, 'type' => 'devoir',       'date' => '2026-03-15', 'min' =>  9, 'max' => 17],
        ['semester' => 2, 'type' => 'composition',  'date' => '2026-06-01', 'min' =>  8, 'max' => 20],
    ];

    // ─── Données des 4 classes ────────────────────────────────────────────────

    private const CLASSES_DEF = [
        ['name' => '6ème A', 'level_name' => '6ème', 'level_order' => 2],
        ['name' => '5ème A', 'level_name' => '5ème', 'level_order' => 3],
        ['name' => '4ème A', 'level_name' => '4ème', 'level_order' => 4],
        ['name' => '3ème A', 'level_name' => '3ème', 'level_order' => 5, 'diploma' => 'BFEM'],
    ];

    // ─── Données des 5 matières ──────────────────────────────────────────────

    private const SUBJECTS_DEF = [
        ['name' => 'Mathématiques',       'code' => 'MATH', 'coef' => 3, 'dept' => 'Sciences'],
        ['name' => 'Français',            'code' => 'FR',   'coef' => 3, 'dept' => 'Lettres'],
        ['name' => 'Histoire-Géographie', 'code' => 'HG',   'coef' => 2, 'dept' => 'Sciences Humaines'],
        ['name' => 'SVT',                 'code' => 'SVT',  'coef' => 2, 'dept' => 'Sciences'],
        ['name' => 'Physique-Chimie',     'code' => 'PC',   'coef' => 2, 'dept' => 'Sciences'],
    ];

    // ─── Données des 5 enseignants (1 par matière) ───────────────────────────

    private const TEACHERS_DEF = [
        ['first_name' => 'Mamadou',  'last_name' => 'Diallo', 'gender' => 'M', 'subject_code' => 'MATH'],
        ['first_name' => 'Aissatou', 'last_name' => 'Ndiaye', 'gender' => 'F', 'subject_code' => 'FR'],
        ['first_name' => 'Ousmane',  'last_name' => 'Ba',     'gender' => 'M', 'subject_code' => 'HG'],
        ['first_name' => 'Fatou',    'last_name' => 'Sow',    'gender' => 'F', 'subject_code' => 'SVT'],
        ['first_name' => 'Ibrahima', 'last_name' => 'Cissé',  'gender' => 'M', 'subject_code' => 'PC'],
    ];

    // ─── Prénoms & noms sénégalais ────────────────────────────────────────────

    private array $firstNamesMale = [
        'Mamadou', 'Ousmane', 'Ibrahima', 'Cheikh', 'Babacar', 'Aliou', 'Lamine',
        'Seydou', 'Daouda', 'Modou', 'Abdoulaye', 'Moussa', 'Pape', 'Assane',
        'Malick', 'Boubacar', 'Souleymane', 'Birame', 'Djibril', 'El Hadji',
    ];

    private array $firstNamesFemale = [
        'Fatou', 'Aminata', 'Awa', 'Mariama', 'Khady', 'Aissatou', 'Ndeye', 'Coumba',
        'Rama', 'Bineta', 'Oumou', 'Astou', 'Rokhaya', 'Seynabou', 'Ndella',
        'Kine', 'Codou', 'Dado', 'Amy', 'Sokhna',
    ];

    private array $lastNames = [
        'Diop', 'Ndiaye', 'Fall', 'Sow', 'Diallo', 'Sy', 'Ba', 'Mbaye', 'Gueye',
        'Faye', 'Sarr', 'Thiam', 'Seck', 'Niang', 'Kane', 'Cissé', 'Diouf',
        'Samb', 'Touré', 'Camara', 'Badiane', 'Lo', 'Tall', 'Diaw', 'Wade',
        'Baldé', 'Barry', 'Ndoye', 'Ly', 'Ngom',
    ];

    // ─── État interne ─────────────────────────────────────────────────────────

    private string $hashedPassword;
    private int    $schoolId;
    private int    $academicYearId;

    // ════════════════════════════════════════════════════════════════════════════
    //  POINT D'ENTRÉE
    // ════════════════════════════════════════════════════════════════════════════

    public function run(): void
    {
        $this->hashedPassword = Hash::make(self::PASSWORD);

        $this->command->info('');
        $this->banner('AZELIE SCHOOL SEEDER — Démonstration');

        // ── Étape 1 : Nettoyage ───────────────────────────────────────────────
        $this->step(1, 'Nettoyage de la base de données');
        $this->disableForeignKeys();
        $this->cleanDatabase();
        $this->enableForeignKeys();

        // ── Étape 2 : École ───────────────────────────────────────────────────
        $this->step(2, 'Création de l\'école');
        $school          = $this->createSchool();
        $this->schoolId  = $school->id;
        $this->info("  → " . self::SCHOOL_NAME . " (ID {$this->schoolId})");

        // ── Étape 3 : Année scolaire ──────────────────────────────────────────
        $this->step(3, 'Création de l\'année scolaire');
        $academicYear         = $this->createAcademicYear();
        $this->academicYearId = $academicYear->id;
        $this->info("  → " . self::YEAR_NAME . " (ID {$this->academicYearId})");

        SchoolModel::withoutGlobalScopes()
            ->where('id', $this->schoolId)
            ->update(['default_academic_year_id' => $this->academicYearId]);

        // ── Étape 4 : Niveaux ─────────────────────────────────────────────────
        $this->step(4, 'Création des niveaux (6è → 3è)');
        $levels = $this->createLevels();

        // ── Étape 5 : Matières ────────────────────────────────────────────────
        $this->step(5, 'Création des 5 matières');
        $subjects = $this->createSubjects();
        foreach ($subjects as $code => $subj) {
            $this->info("  → [{$code}] {$subj->name} (coef. {$subj->coefficient})");
        }

        // ── Étape 6 : Classes ─────────────────────────────────────────────────
        $this->step(6, 'Création des 4 classes');
        $classes = $this->createClasses($levels, $subjects);

        // ── Étape 7 : Enseignants ─────────────────────────────────────────────
        $this->step(7, 'Création des 5 enseignants (1 par matière)');
        // Retourne désormais array<string, int> [code_matière => teacher_id]
        $teachersBySubject = $this->createTeachers($subjects, $classes);

        // ── Étape 8 : Élèves ──────────────────────────────────────────────────
        $this->step(8, 'Génération des élèves (5–10 par classe)');
        $studentsByClass = $this->createStudents($classes);

        // ── Étape 9 : Notes ───────────────────────────────────────────────────
        $this->step(9, 'Génération des notes (S1 + S2)');
        $totalGrades = $this->generateGrades($studentsByClass, $subjects);
        $this->info("  → {$totalGrades} notes insérées");

        // ── Étape 10 : E-Learning / LMS ───────────────────────────────────────
        $this->step(10, 'Génération des données E-Learning (LMS)');
        $lmsStats = $this->generateLmsData($classes, $subjects, $teachersBySubject, $studentsByClass);
        $this->info("  → {$lmsStats['lessons']} cours insérés (PDF + vidéo)");
        $this->info("  → {$lmsStats['assignments']} devoirs insérés");
        $this->info("  → {$lmsStats['submissions']} soumissions insérées (toutes corrigées)");
        $this->info("  → {$lmsStats['quizzes']} quiz insérés ({$lmsStats['questions']} questions, {$lmsStats['options']} options)");

        // ── Étape 11 : Restauration du compte Administrateur ──────────────────
        $this->step(11, 'Restauration du compte Administrateur');
        $this->call(AdminUserSeeder::class);
        $this->info("  → Compte admin restauré : gueyeabdoulaziz111@gmail.com / passer01");

        // ── Résumé ────────────────────────────────────────────────────────────
        $this->printSummary($studentsByClass, $subjects, $totalGrades, $lmsStats);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  ÉTAPE 1 — NETTOYAGE
    // ════════════════════════════════════════════════════════════════════════════

    private function cleanDatabase(): void
    {
        // ── Tables LMS (à nettoyer en premier : dépendants des autres) ────────
        $lmsTables = ['question_options', 'questions', 'quizzes', 'submissions', 'assignments', 'lessons'];
        foreach ($lmsTables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        // ── Tables pivots et données dérivées ─────────────────────────────────
        DB::table('grades')->truncate();
        DB::table('teacher_assignments')->truncate();
        DB::table('class_teacher')->truncate();
        DB::table('class_subject')->truncate();
        DB::table('level_subject')->truncate();
        DB::table('teacher_subjects')->truncate();

        // ── Tables principales ────────────────────────────────────────────────
        foreach (['attendances', 'timetables', 'schedules'] as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::table('classes')->truncate();
        DB::table('levels')->truncate();
        DB::table('subjects')->truncate();
        DB::table('academic_years')->truncate();

        // ── Utilisateurs (hors super_admin de l'environnement) ────────────────
        $superAdminEmail = env('SUPER_ADMIN_EMAIL', '');
        $deleted = DB::table('users')
            ->where(function ($q) use ($superAdminEmail) {
                $q->where('role', '!=', 'super_admin')
                  ->orWhere(function ($q2) use ($superAdminEmail) {
                      $q2->where('role', 'super_admin')
                         ->where('email', '!=', $superAdminEmail);
                  });
            })
            ->delete();
        $this->info("  → {$deleted} utilisateur(s) supprimé(s)");

        DB::table('schools')->truncate();
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  ÉTAPES 2–9 — IDENTIQUES À L'ORIGINAL
    // ════════════════════════════════════════════════════════════════════════════

    private function createSchool(): SchoolModel
    {
        return SchoolModel::create([
            'name'      => self::SCHOOL_NAME,
            'slug'      => self::SCHOOL_SLUG,
            'code'      => self::SCHOOL_CODE,
            'is_active' => true,
            'email'     => 'contact@azelie.sn',
            'city'      => self::SCHOOL_CITY,
            'address'   => '12 Avenue Cheikh Anta Diop, Dakar',
            'phone'     => '+221 33 800 00 00',
        ]);
    }

    private function createAcademicYear(): AcademicYearModel
    {
        return AcademicYearModel::create([
            'name'       => self::YEAR_NAME,
            'start_date' => '2025-10-01',
            'end_date'   => '2026-06-30',
            'is_current' => true,
            'is_closed'  => false,
            'school_id'  => $this->schoolId,
        ]);
    }

    /** @return array<string, LevelModel> */
    private function createLevels(): array
    {
        $levels = [];
        foreach (self::CLASSES_DEF as $def) {
            $key = $def['level_name'];
            if (! isset($levels[$key])) {
                $levels[$key] = LevelModel::create([
                    'name'      => $def['level_name'],
                    'order'     => $def['level_order'],
                    'cycle'     => 'college',
                    'school_id' => $this->schoolId,
                ]);
                $this->info("  → Niveau [{$key}] créé");
            }
        }
        return $levels;
    }

    /** @return array<string, SubjectModel> */
    private function createSubjects(): array
    {
        $subjects = [];
        foreach (self::SUBJECTS_DEF as $def) {
            $subjects[$def['code']] = SubjectModel::create([
                'name'            => $def['name'],
                'code'            => $def['code'],
                'coefficient'     => $def['coef'],
                'department'      => $def['dept'],
                'is_active'       => true,
                'is_core_subject' => true,
                'hours_per_week'  => 3,
                'school_id'       => $this->schoolId,
            ]);
        }
        return $subjects;
    }

    /**
     * @param  array<string, LevelModel>   $levels
     * @param  array<string, SubjectModel> $subjects
     * @return array<string, ClasseModel>
     */
    private function createClasses(array $levels, array $subjects): array
    {
        $classes = [];

        foreach (self::CLASSES_DEF as $def) {
            $level = $levels[$def['level_name']];

            $class = ClasseModel::create([
                'name'             => $def['name'],
                'level_id'         => $level->id,
                'academic_year_id' => $this->academicYearId,
                'capacity'         => 35,
                'school_id'        => $this->schoolId,
                'diploma_type'     => $def['diploma'] ?? null,
                'description'      => 'Classe ' . $def['name'] . ' — ' . self::YEAR_NAME,
            ]);

            foreach ($subjects as $subject) {
                DB::table('class_subject')->insert([
                    'class_id'   => $class->id,
                    'subject_id' => $subject->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($subjects as $code => $subject) {
                $coef = collect(self::SUBJECTS_DEF)->firstWhere('code', $code)['coef'] ?? 1;

                DB::table('level_subject')->updateOrInsert(
                    ['level_id' => $level->id, 'subject_id' => $subject->id],
                    ['coefficient' => $coef, 'is_compulsory' => true,
                     'created_at' => now(), 'updated_at' => now()]
                );
            }

            $classes[$def['name']] = $class;
            $this->info("  → Classe [{$def['name']}] — niveau {$def['level_name']}");
        }

        return $classes;
    }

    /**
     * @param  array<string, SubjectModel> $subjects
     * @param  array<string, ClasseModel>  $classes
     * @return array<string, int>  [subject_code => teacher_id]
     */
    private function createTeachers(array $subjects, array $classes): array
    {
        $teachersBySubject = [];

        foreach (self::TEACHERS_DEF as $td) {
            $firstName = $td['first_name'];
            $lastName  = $td['last_name'];
            $code      = $td['subject_code'];
            $email     = Str::lower(
                Str::ascii($firstName) . '.' . Str::ascii($lastName) . '.prof@azelie.sn'
            );

            $teacherId = DB::table('users')->insertGetId([
                'school_id'         => $this->schoolId,
                'identifier'        => 'PROF-' . $code . '-001',
                'name'              => "{$firstName} {$lastName}",
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'email'             => $email,
                'password'          => $this->hashedPassword,
                'role'              => 'teacher',
                'status'            => 'approved',
                'gender'            => $td['gender'],
                'city'              => 'Dakar',
                'country'           => 'Sénégal',
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $subject = $subjects[$code];

            DB::table('teacher_subjects')->insert([
                'teacher_id' => $teacherId,
                'subject_id' => $subject->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($classes as $class) {
                DB::table('class_teacher')->insert([
                    'class_id'   => $class->id,
                    'teacher_id' => $teacherId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                TeacherAssignmentModel::create([
                    'teacher_id'       => $teacherId,
                    'class_id'         => $class->id,
                    'subject_id'       => $subject->id,
                    'academic_year_id' => $this->academicYearId,
                    'school_id'        => $this->schoolId,
                ]);
            }

            $teachersBySubject[$code] = $teacherId;
            $this->info("  → {$firstName} {$lastName} ({$code}) — ID {$teacherId}");
        }

        return $teachersBySubject;
    }

    /**
     * @param  array<string, ClasseModel> $classes
     * @return array<string, int[]>
     */
    private function createStudents(array $classes): array
    {
        $studentsByClass = [];
        $usedEmails      = [];

        foreach ($classes as $className => $class) {
            $count      = rand(5, 10);
            $studentIds = [];
            $shortKey   = $this->classShortKey($className);

            for ($i = 1; $i <= $count; $i++) {
                $gender    = (rand(0, 1) === 0) ? 'M' : 'F';
                $firstName = $gender === 'M'
                    ? $this->firstNamesMale[array_rand($this->firstNamesMale)]
                    : $this->firstNamesFemale[array_rand($this->firstNamesFemale)];
                $lastName  = $this->lastNames[array_rand($this->lastNames)];

                $baseEmail = Str::lower(Str::ascii($firstName) . '.' . Str::ascii($lastName));
                $email     = $baseEmail . '.' . Str::lower($shortKey) . '@azelie.sn';
                $n = 1;
                while (in_array($email, $usedEmails)) {
                    $email = $baseEmail . $n . '.' . Str::lower($shortKey) . '@azelie.sn';
                    $n++;
                }
                $usedEmails[] = $email;

                $studentId = DB::table('users')->insertGetId([
                    'school_id'         => $this->schoolId,
                    'identifier'        => 'ELV-' . $shortKey . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'class_id'          => $class->id,
                    'name'              => "{$firstName} {$lastName}",
                    'first_name'        => $firstName,
                    'last_name'         => $lastName,
                    'email'             => $email,
                    'password'          => $this->hashedPassword,
                    'role'              => 'eleve',
                    'status'            => 'approved',
                    'gender'            => $gender,
                    'date_of_birth'     => now()->subYears(rand(11, 16))->subDays(rand(0, 364))->toDateString(),
                    'city'              => 'Dakar',
                    'country'           => 'Sénégal',
                    'email_verified_at' => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                $studentIds[] = $studentId;
            }

            $studentsByClass[$className] = $studentIds;
            $this->info("  → Classe {$className} : {$count} élève(s)");
        }

        return $studentsByClass;
    }

    private function generateGrades(array $studentsByClass, array $subjects): int
    {
        $gradesData = [];
        $now        = now()->toDateTimeString();

        foreach ($studentsByClass as $className => $studentIds) {
            foreach ($studentIds as $studentId) {
                foreach ($subjects as $code => $subject) {
                    foreach (self::EVALUATIONS as $eval) {
                        $grade = $this->randomGrade($eval['min'], $eval['max']);

                        $gradesData[] = [
                            'user_id'          => $studentId,
                            'subject_id'       => $subject->id,
                            'grade'            => $grade,
                            'type'             => $eval['type'],
                            'semester'         => $eval['semester'],
                            'date'             => $eval['date'],
                            'coefficient'      => $subject->coefficient,
                            'appreciation'     => $this->appreciation($grade),
                            'comments'         => $this->comment($grade, $eval['type']),
                            'academic_year_id' => $this->academicYearId,
                            'school_id'        => $this->schoolId,
                            'created_at'       => $now,
                            'updated_at'       => $now,
                        ];
                    }
                }
            }
        }

        collect($gradesData)->chunk(500)->each(fn ($c) => DB::table('grades')->insert($c->toArray()));

        return count($gradesData);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  ÉTAPE 10 — E-LEARNING / LMS
    // ════════════════════════════════════════════════════════════════════════════

    /**
     * Pour chaque classe × matière : 2 cours (PDF + vidéo) + 1 devoir + N soumissions.
     * Pour chaque matière : 1 quiz avec 3 questions QCM (4 options chacune).
     *
     * @param  array<string, ClasseModel>  $classes
     * @param  array<string, SubjectModel> $subjects
     * @param  array<string, int>          $teachersBySubject  [code => teacher_id]
     * @param  array<string, int[]>        $studentsByClass
     * @return array{lessons:int, assignments:int, submissions:int, quizzes:int, questions:int, options:int}
     */
    private function generateLmsData(
        array $classes,
        array $subjects,
        array $teachersBySubject,
        array $studentsByClass
    ): array {
        $now   = now()->toDateTimeString();
        $stats = ['lessons' => 0, 'assignments' => 0, 'submissions' => 0,
                  'quizzes' => 0, 'questions' => 0, 'options' => 0];

        $lessonsData     = [];
        $submissionsData = [];

        // ── Cours + Devoirs + Soumissions ─────────────────────────────────────
        foreach ($classes as $className => $class) {
            $shortKey   = $this->classShortKey($className);
            $studentIds = $studentsByClass[$className];

            foreach ($subjects as $code => $subject) {
                $teacherId = $teachersBySubject[$code];

                // Cours S1 — PDF (stocké dans storage/app/private/lms/lessons/)
                $lessonsData[] = [
                    'class_id'    => $class->id,
                    'subject_id'  => $subject->id,
                    'school_id'   => $this->schoolId,
                    'teacher_id'  => $teacherId,
                    'title'       => "Cours {$subject->name} — S1 — {$className}",
                    'description' => "Notions fondamentales de {$subject->name} pour le premier semestre.",
                    'file_type'   => 'pdf',
                    'file_path'   => "lms/lessons/{$shortKey}-{$code}-S1.pdf",
                    'is_published'=> 1,
                    'published_at'=> '2025-10-20 08:00:00',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];

                // Cours S2 — Vidéo (URL externe)
                $lessonsData[] = [
                    'class_id'    => $class->id,
                    'subject_id'  => $subject->id,
                    'school_id'   => $this->schoolId,
                    'teacher_id'  => $teacherId,
                    'title'       => "Cours {$subject->name} — S2 — {$className}",
                    'description' => "Approfondissement de {$subject->name} pour le second semestre.",
                    'file_type'   => 'video',
                    'file_path'   => 'https://www.youtube.com/watch?v=placeholder-' . Str::lower($code),
                    'is_published'=> 1,
                    'published_at'=> '2026-02-10 08:00:00',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];

                // Devoir (1 par classe × matière)
                $assignmentId = DB::table('assignments')->insertGetId([
                    'class_id'     => $class->id,
                    'subject_id'   => $subject->id,
                    'teacher_id'   => $teacherId,
                    'school_id'    => $this->schoolId,
                    'title'        => "Devoir maison — {$subject->name} — {$className}",
                    'description'  => "Travail individuel sur les notions de {$subject->name}.",
                    'instructions' => "Répondez de façon claire et structurée. Appuyez-vous sur les leçons vues en classe. Soins de la copie pris en compte.",
                    'due_date'     => '2026-04-30 23:59:00',
                    'points'       => 20,
                    'status'       => 'graded',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ]);
                $stats['assignments']++;

                // Soumissions — chaque élève a déjà rendu et est corrigé
                foreach ($studentIds as $studentId) {
                    $grade = round(rand(60, 200) / 10, 2); // 6.0–20.0
                    $submissionsData[] = [
                        'assignment_id' => $assignmentId,
                        'user_id'       => $studentId,
                        'school_id'     => $this->schoolId,
                        'file_path'     => "lms/submissions/{$shortKey}/{$studentId}-{$assignmentId}.pdf",
                        'submitted_at'  => '2026-04-25 10:00:00',
                        'grade'         => $grade,
                        'feedback'      => $this->submissionFeedback($grade),
                        'status'        => 'graded',
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
            }
        }

        collect($lessonsData)->chunk(200)->each(fn ($c) => DB::table('lessons')->insert($c->toArray()));
        $stats['lessons'] = count($lessonsData);

        collect($submissionsData)->chunk(500)->each(fn ($c) => DB::table('submissions')->insert($c->toArray()));
        $stats['submissions'] = count($submissionsData);

        // ── Quiz (1 par matière, partagé entre toutes les classes) ───────────
        $templates = $this->quizTemplates();

        foreach ($subjects as $code => $subject) {
            $teacherId = $teachersBySubject[$code];

            $quizId = DB::table('quizzes')->insertGetId([
                'subject_id'   => $subject->id,
                'school_id'    => $this->schoolId,
                'teacher_id'   => $teacherId,
                'title'        => "Quiz — {$subject->name}",
                'description'  => "Vérifiez vos connaissances fondamentales en {$subject->name}.",
                'is_published' => 1,
                'time_limit'   => 20,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $stats['quizzes']++;

            $questions = $templates[$code] ?? $templates['DEFAULT'];

            foreach ($questions as $qIdx => $qDef) {
                $questionId = DB::table('questions')->insertGetId([
                    'quiz_id'       => $quizId,
                    'question_text' => $qDef['text'],
                    'points'        => 1,
                    'order'         => $qIdx + 1,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
                $stats['questions']++;

                $optionsData = [];
                foreach ($qDef['options'] as $oIdx => $opt) {
                    $optionsData[] = [
                        'question_id' => $questionId,
                        'option_text' => $opt['text'],
                        'is_correct'  => $opt['correct'] ? 1 : 0,
                        'order'       => $oIdx + 1,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }
                DB::table('question_options')->insert($optionsData);
                $stats['options'] += count($optionsData);
            }
        }

        return $stats;
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  DONNÉES QCM PAR MATIÈRE
    // ════════════════════════════════════════════════════════════════════════════

    /**
     * @return array<string, array<int, array{text:string, options:array<int, array{text:string, correct:bool}>}>>
     */
    private function quizTemplates(): array
    {
        return [
            'MATH' => [
                [
                    'text'    => 'Quel est le résultat de 3 × 4 + 2 ?',
                    'options' => [
                        ['text' => '14',  'correct' => true],
                        ['text' => '18',  'correct' => false],
                        ['text' => '10',  'correct' => false],
                        ['text' => '20',  'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Combien vaut 7² (7 au carré) ?',
                    'options' => [
                        ['text' => '14',  'correct' => false],
                        ['text' => '49',  'correct' => true],
                        ['text' => '21',  'correct' => false],
                        ['text' => '56',  'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Quelle est la valeur de π arrondie au centième ?',
                    'options' => [
                        ['text' => '3,14', 'correct' => true],
                        ['text' => '3,12', 'correct' => false],
                        ['text' => '3,16', 'correct' => false],
                        ['text' => '3,41', 'correct' => false],
                    ],
                ],
            ],
            'FR' => [
                [
                    'text'    => 'Quel est le féminin du mot "acteur" ?',
                    'options' => [
                        ['text' => 'acteure',     'correct' => false],
                        ['text' => 'actrice',     'correct' => true],
                        ['text' => 'acteuse',     'correct' => false],
                        ['text' => 'acteuresse',  'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Quel temps exprime une action passée et terminée ?',
                    'options' => [
                        ['text' => 'Imparfait',      'correct' => false],
                        ['text' => 'Passé composé',  'correct' => true],
                        ['text' => 'Futur simple',   'correct' => false],
                        ['text' => 'Présent',        'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Qu\'est-ce qu\'un nom commun ?',
                    'options' => [
                        ['text' => 'Un mot qui désigne une personne ou une chose de façon générale', 'correct' => true],
                        ['text' => 'Un mot qui remplace un nom',                                     'correct' => false],
                        ['text' => 'Un mot qui désigne une personne précise',                        'correct' => false],
                        ['text' => 'Un mot qui qualifie un nom',                                     'correct' => false],
                    ],
                ],
            ],
            'HG' => [
                [
                    'text'    => 'Quelle est la capitale du Sénégal ?',
                    'options' => [
                        ['text' => 'Thiès',       'correct' => false],
                        ['text' => 'Saint-Louis', 'correct' => false],
                        ['text' => 'Dakar',       'correct' => true],
                        ['text' => 'Ziguinchor',  'correct' => false],
                    ],
                ],
                [
                    'text'    => 'En quelle année le Sénégal a-t-il obtenu son indépendance ?',
                    'options' => [
                        ['text' => '1958', 'correct' => false],
                        ['text' => '1960', 'correct' => true],
                        ['text' => '1962', 'correct' => false],
                        ['text' => '1945', 'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Quel est le plus grand continent du monde ?',
                    'options' => [
                        ['text' => 'Amérique', 'correct' => false],
                        ['text' => 'Afrique',  'correct' => false],
                        ['text' => 'Asie',     'correct' => true],
                        ['text' => 'Europe',   'correct' => false],
                    ],
                ],
            ],
            'SVT' => [
                [
                    'text'    => 'Quel organe assure la circulation du sang dans le corps humain ?',
                    'options' => [
                        ['text' => 'Le cerveau',   'correct' => false],
                        ['text' => 'Le foie',      'correct' => false],
                        ['text' => 'Le cœur',      'correct' => true],
                        ['text' => 'Les poumons',  'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Combien de chromosomes possède une cellule humaine normale ?',
                    'options' => [
                        ['text' => '23', 'correct' => false],
                        ['text' => '46', 'correct' => true],
                        ['text' => '48', 'correct' => false],
                        ['text' => '36', 'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Quel processus permet aux plantes de produire leur propre nourriture ?',
                    'options' => [
                        ['text' => 'La respiration',    'correct' => false],
                        ['text' => 'La photosynthèse',  'correct' => true],
                        ['text' => 'La digestion',      'correct' => false],
                        ['text' => 'La transpiration',  'correct' => false],
                    ],
                ],
            ],
            'PC' => [
                [
                    'text'    => 'Quelle est la formule chimique de l\'eau ?',
                    'options' => [
                        ['text' => 'CO₂',  'correct' => false],
                        ['text' => 'H₂O',  'correct' => true],
                        ['text' => 'O₂',   'correct' => false],
                        ['text' => 'NaCl', 'correct' => false],
                    ],
                ],
                [
                    'text'    => 'Quelle est l\'unité de mesure de l\'intensité électrique ?',
                    'options' => [
                        ['text' => 'Volt (V)',   'correct' => false],
                        ['text' => 'Watt (W)',   'correct' => false],
                        ['text' => 'Ampère (A)', 'correct' => true],
                        ['text' => 'Ohm (Ω)',    'correct' => false],
                    ],
                ],
                [
                    'text'    => 'À quelle température l\'eau bout-elle à pression atmosphérique normale ?',
                    'options' => [
                        ['text' => '90°C',  'correct' => false],
                        ['text' => '100°C', 'correct' => true],
                        ['text' => '110°C', 'correct' => false],
                        ['text' => '80°C',  'correct' => false],
                    ],
                ],
            ],
            'DEFAULT' => [
                [
                    'text'    => 'Quelle est la réponse correcte ?',
                    'options' => [
                        ['text' => 'Option A', 'correct' => true],
                        ['text' => 'Option B', 'correct' => false],
                        ['text' => 'Option C', 'correct' => false],
                        ['text' => 'Option D', 'correct' => false],
                    ],
                ],
            ],
        ];
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  HELPERS UTILITAIRES
    // ════════════════════════════════════════════════════════════════════════════

    private function randomGrade(int $min, int $max): float
    {
        return round(rand($min * 10, $max * 10) / 10, 2);
    }

    private function appreciation(float $grade): string
    {
        return match (true) {
            $grade >= 16 => 'Très Bien',
            $grade >= 14 => 'Bien',
            $grade >= 12 => 'Assez Bien',
            $grade >= 10 => 'Passable',
            $grade >= 8  => 'Insuffisant',
            default      => 'Très Insuffisant',
        };
    }

    private function comment(float $grade, string $type): string
    {
        $label    = ($type === 'composition') ? 'Composition' : 'Devoir';
        $comments = match (true) {
            $grade >= 16 => ["Excellent {$label}. Félicitations !", "Très bonne maîtrise du cours.", "Travail exemplaire, continuez ainsi."],
            $grade >= 14 => ["Bon {$label}. De bons efforts.", "Travail sérieux et bien fait.", "Bonne progression."],
            $grade >= 12 => ["Résultat satisfaisant.", "Peut encore progresser avec plus de travail.", "{$label} correct, quelques points à revoir."],
            $grade >= 10 => ["Résultat passable. Des lacunes à combler.", "Travail à améliorer.", "Des efforts supplémentaires sont attendus."],
            $grade >= 8  => ["Résultat insuffisant. Doit se ressaisir.", "Travail décevant, des bases manquent.", "Effort nécessaire pour rattraper le niveau."],
            default      => ["Résultat très insuffisant. Travail urgent à fournir.", "Situation alarmante, entretien conseillé.", "Doit impérativement revoir les bases."],
        };
        return $comments[array_rand($comments)];
    }

    private function submissionFeedback(float $grade): string
    {
        return match (true) {
            $grade >= 16 => 'Excellent travail, très bien structuré. Continuez sur cette lancée.',
            $grade >= 12 => 'Bon travail dans l\'ensemble, quelques points à approfondir.',
            $grade >= 10 => 'Travail satisfaisant mais des efforts supplémentaires sont nécessaires.',
            default      => 'Travail insuffisant. Revoir les notions de base du cours.',
        };
    }

    private function classShortKey(string $className): string
    {
        return preg_replace('/[^0-9A-Za-z]/u', '', $className);
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  GESTION DES CLÉS ÉTRANGÈRES
    // ════════════════════════════════════════════════════════════════════════════

    private function disableForeignKeys(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = replica');
        }
        $this->info('  → Contraintes FK désactivées (' . strtoupper($driver) . ')');
    }

    private function enableForeignKeys(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = DEFAULT');
        }
        $this->info('  → Contraintes FK réactivées');
    }

    // ════════════════════════════════════════════════════════════════════════════
    //  RÉSUMÉ FINAL
    // ════════════════════════════════════════════════════════════════════════════

    private function printSummary(array $studentsByClass, array $subjects, int $totalGrades, array $lmsStats): void
    {
        $totalStudents = array_sum(array_map('count', $studentsByClass));

        $this->command->info('');
        $this->banner('RÉSUMÉ DE LA DÉMONSTRATION');
        $this->command->info(sprintf('  %-30s %s', 'École :', self::SCHOOL_NAME));
        $this->command->info(sprintf('  %-30s %s', 'Ville :', self::SCHOOL_CITY));
        $this->command->info(sprintf('  %-30s %s', 'Année scolaire :', self::YEAR_NAME));
        $this->command->info('  ' . str_repeat('─', 50));
        $this->command->info(sprintf('  %-30s %d', 'Classes :', count(self::CLASSES_DEF)));
        $this->command->info(sprintf('  %-30s %d  (5–10 par classe)', 'Élèves :', $totalStudents));
        $this->command->info(sprintf('  %-30s %d', 'Enseignants :', count(self::TEACHERS_DEF)));
        $this->command->info(sprintf('  %-30s %d', 'Matières :', count($subjects)));
        $this->command->info(sprintf('  %-30s %d', 'Notes générées :', $totalGrades));
        $this->command->info('  ' . str_repeat('─', 50));
        $this->command->info('  MODULE E-LEARNING :');
        $this->command->info(sprintf('  %-30s %d  (PDF + vidéo par classe/matière)', 'Cours (Lessons) :', $lmsStats['lessons']));
        $this->command->info(sprintf('  %-30s %d  (1 par classe/matière)', 'Devoirs (Assignments) :', $lmsStats['assignments']));
        $this->command->info(sprintf('  %-30s %d  (tous corrigés avec note)', 'Soumissions :', $lmsStats['submissions']));
        $this->command->info(sprintf('  %-30s %d  (%d questions, %d options)', 'Quiz :', $lmsStats['quizzes'], $lmsStats['questions'], $lmsStats['options']));
        $this->command->info('  ' . str_repeat('─', 50));
        $this->command->info('  EFFECTIFS PAR CLASSE :');
        foreach ($studentsByClass as $className => $ids) {
            $this->command->info(sprintf('    %-12s → %d élève(s)', $className, count($ids)));
        }
        $this->command->info('  ' . str_repeat('─', 50));
        $this->command->info('  ★ COMPTE ADMINISTRATEUR :');
        $this->command->info('    Email    : gueyeabdoulaziz111@gmail.com');
        $this->command->info('    Password : passer01');
        $this->command->info('  ' . str_repeat('─', 50));
        $this->command->info('  MOT DE PASSE UNIVERSEL PROFS/ÉLÈVES : ' . self::PASSWORD);
        $this->command->info('  EXEMPLES :');
        $this->command->info('    Prof Maths    : mamadou.diallo.prof@azelie.sn  / ' . self::PASSWORD);
        $this->command->info('    Prof Français : aissatou.ndiaye.prof@azelie.sn / ' . self::PASSWORD);
        $this->banner('FIN DU SEEDER — BASE PRÊTE POUR LA DÉMO');
    }

    // ── Helpers console ───────────────────────────────────────────────────────

    private function step(int $n, string $label): void
    {
        $this->command->info('');
        $this->command->info("  ┌─ Étape {$n} : {$label}");
    }

    private function info(string $msg): void
    {
        $this->command->info($msg);
    }

    private function banner(string $text): void
    {
        $line = '  ═' . str_repeat('═', strlen($text) + 2) . '═';
        $this->command->info($line);
        $this->command->info("  ║ {$text} ║");
        $this->command->info($line);
    }
}
