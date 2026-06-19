<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder de démonstration complet — Collège sénégalais (6ème → 3ème).
 *
 * Réinitialise l'intégralité de la base de données, puis crée :
 *   - 1 Super Admin de test  (superadmin@edumanager.sn / Passer01)
 *   - 1 Directeur + 1 Surveillant
 *   - 4 classes : 6ème, 5ème, 4ème, 3ème  (2 élèves chacune)
 *   - 8 matières du cycle collège + 3 enseignants affectés aux 4 classes
 *   - Devoir 1 + Devoir 2 par élève et par matière (sem. 1 — pas de composition)
 *   - Absences aléatoires sur plusieurs élèves (testables via WhatsApp)
 *   - Numéro WhatsApp parent de test : +221 78 951 52 68 (tous les élèves)
 *
 * Usage :
 *   php artisan db:seed --class=SoutenanceCollegeDemoSeeder
 */
class SoutenanceCollegeDemoSeeder extends Seeder
{
    private string $password;
    private int $schoolId;

    /** Numéro WhatsApp partagé par tous les parents pour tester les notifications. */
    private const PARENT_WHATSAPP = '221789515268'; // +221 78 951 52 68 (Sénégal)
    private const PARENT_LANG     = 'fr_text';

    // ──────────────────────────────────────────────────────────────────────────
    // POINT D'ENTRÉE
    // ──────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $this->password = Hash::make('Passer01');

        $this->line('');
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║   DÉMO SOUTENANCE — Collège (6ème → 3ème)                  ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');

        // ── 1. Réinitialisation complète ───────────────────────────────────────
        $this->resetDatabase();

        // ── 2. École & année académique ────────────────────────────────────────
        $school       = $this->getOrCreateSchool();
        $this->schoolId = $school->id;
        $academicYear = $this->getOrCreateAcademicYear();
        $this->line("  Année académique : {$academicYear->name}");

        // ── 3. Comptes administratifs ──────────────────────────────────────────
        $this->createSuperAdmin();
        $this->createSchoolStaff();

        // ── 4. Matières ────────────────────────────────────────────────────────
        $subjects = $this->createSubjects();

        // ── 5. Niveaux + classes ───────────────────────────────────────────────
        $classes = $this->createLevelsAndClasses($subjects, $academicYear);

        // ── 6. Enseignants ─────────────────────────────────────────────────────
        $this->createTeachers($subjects, $classes, $academicYear);

        // ── 7. Élèves + parents (WhatsApp de test) ─────────────────────────────
        $students = $this->createStudentsWithParents($classes);

        // ── 8. Notes (Devoir 1 & 2 uniquement — aucune composition) ───────────
        $this->createGrades($students, $subjects, $academicYear);

        // ── 9. Absences aléatoires ─────────────────────────────────────────────
        $this->createAbsences($students);

        // ── Résumé final ───────────────────────────────────────────────────────
        $this->printSummary();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // RÉINITIALISATION BASE DE DONNÉES (MySQL & PostgreSQL)
    // ──────────────────────────────────────────────────────────────────────────

    private function resetDatabase(): void
    {
        $this->line('');
        $this->line('  ► Réinitialisation de la base de données...');

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $this->resetPostgres();
        } else {
            $this->resetMysql();
        }

        $this->line('  ✓ Base de données réinitialisée (utilisateurs, notes, absences, classes...)');
    }

    /**
     * Réinitialisation MySQL — désactive les FK checks, truncate, réactive.
     */
    private function resetMysql(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($this->allTableNames() as $table) {
            $this->truncateIfExists($table);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Réinitialisation PostgreSQL — TRUNCATE … RESTART IDENTITY CASCADE
     * sur les tables parentes ; PostgreSQL propage le CASCADE automatiquement
     * à toutes les tables dépendantes (FK).
     */
    private function resetPostgres(): void
    {
        // Tables "racines" : le CASCADE propage aux tables enfants automatiquement.
        $roots = [
            'users',
            'academic_years',
            'subjects',
            'levels',
            'schools',        // cascade → classes, academic_years, users…
        ];

        // On truncate root par root pour contrôler l'ordre et éviter les conflits.
        foreach ($roots as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("TRUNCATE \"{$table}\" RESTART IDENTITY CASCADE;");
            }
        }

        // Logs & permissions (pas forcément référencés depuis les tables racines)
        foreach (['activity_log', 'model_has_roles', 'model_has_permissions'] as $table) {
            $this->truncateIfExists($table);
        }
    }

    /** Liste complète des tables à vider (pour MySQL). */
    private function allTableNames(): array
    {
        return [
            // LMS
            'quiz_attempts', 'question_options', 'questions', 'quizzes',
            'submissions', 'assignments', 'lessons', 'virtual_classes',
            // Scolaires
            'attendances', 'grades', 'events', 'schedules',
            'teacher_assignments', 'teacher_subjects', 'class_teacher',
            'class_subject', 'level_subject', 'class_groups',
            'classes', 'levels', 'subjects', 'academic_years',
            // Logs & permissions
            'activity_log', 'model_has_roles', 'model_has_permissions',
            // Utilisateurs (en dernier)
            'users',
        ];
    }

    private function truncateIfExists(string $table): void
    {
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ÉCOLE & ANNÉE ACADÉMIQUE
    // ──────────────────────────────────────────────────────────────────────────

    private function getOrCreateSchool(): School
    {
        $school = School::withoutGlobalScopes()->first();

        if (! $school) {
            $school = School::create([
                'name'               => 'Collège Sénégal — EduManager',
                'slug'               => 'college-senegal-edumanager',
                'code'               => 'CSN001',
                'is_active'          => true,
                'email'              => 'contact@college-senegal.sn',
                'phone'              => '+221 33 000 00 00',
                'city'               => 'Dakar',
                'address'            => 'Plateau, Dakar, Sénégal',
                'director_name'      => 'Assane Mbaye',
                'establishment_type' => 'college',
            ]);
        }

        $this->line("  École : {$school->name} (ID {$school->id})");
        return $school;
    }

    private function getOrCreateAcademicYear(): AcademicYear
    {
        return AcademicYear::updateOrCreate(
            ['name' => '2025-2026', 'school_id' => $this->schoolId],
            [
                'start_date' => '2025-10-01',
                'end_date'   => '2026-06-30',
                'is_current' => true,
                'is_closed'  => false,
                'school_id'  => $this->schoolId,
            ]
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // COMPTES ADMINISTRATIFS
    // ──────────────────────────────────────────────────────────────────────────

    private function createSuperAdmin(): void
    {
        $this->line('');
        $this->line('  ► Création du Super Administrateur...');

        $this->makeUser([
            'identifier' => 'SUPER-001',
            'role'       => 'super_admin',
            'first_name' => 'Super',
            'last_name'  => 'Administrateur',
            'email'      => 'superadmin@edumanager.sn',
            'gender'     => 'M',
        ]);

        $this->line('  ✓ Super Admin : superadmin@edumanager.sn  /  Passer01');
    }

    private function createSchoolStaff(): void
    {
        $this->makeUser([
            'identifier' => 'DIR-COL-001',
            'role'       => 'admin',
            'first_name' => 'Assane',
            'last_name'  => 'Mbaye',
            'email'      => 'directeur@college-senegal.sn',
            'gender'     => 'M',
        ]);

        $this->makeUser([
            'identifier' => 'SUR-COL-001',
            'role'       => 'surveillant',
            'first_name' => 'Alioune',
            'last_name'  => 'Thiam',
            'email'      => 'surveillant@college-senegal.sn',
            'gender'     => 'M',
        ]);

        $this->line('  ✓ Directeur : directeur@college-senegal.sn  /  Passer01');
        $this->line('  ✓ Surveillant : surveillant@college-senegal.sn  /  Passer01');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MATIÈRES DU CYCLE COLLÈGE (SÉNÉGAL)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Crée les 8 matières officielles du cycle collège.
     *
     * @return array<string, Subject>  clé = code matière (FR, MATH, …)
     */
    private function createSubjects(): array
    {
        $this->line('');
        $this->line('  ► Création des matières...');

        $defs = [
            ['name' => 'Français',          'code' => 'FR',   'coef' => 3, 'dept' => 'Lettres',           'hours' => 5],
            ['name' => 'Mathématiques',     'code' => 'MATH', 'coef' => 3, 'dept' => 'Sciences',          'hours' => 4],
            ['name' => 'SVT',               'code' => 'SVT',  'coef' => 2, 'dept' => 'Sciences',          'hours' => 2],
            ['name' => 'Physique-Chimie',   'code' => 'PC',   'coef' => 2, 'dept' => 'Sciences',          'hours' => 2],
            ['name' => 'Histoire-Géo',      'code' => 'HG',   'coef' => 2, 'dept' => 'Sciences Humaines', 'hours' => 3],
            ['name' => 'Anglais',           'code' => 'ANG',  'coef' => 2, 'dept' => 'Langues',           'hours' => 3],
            ['name' => 'Arabe',             'code' => 'AR',   'coef' => 1, 'dept' => 'Langues',           'hours' => 2],
            ['name' => 'EPS',               'code' => 'EPS',  'coef' => 1, 'dept' => 'Sport',             'hours' => 2],
        ];

        $subjects = [];
        foreach ($defs as $d) {
            $subjects[$d['code']] = Subject::updateOrCreate(
                ['code' => $d['code'], 'school_id' => $this->schoolId],
                [
                    'name'            => $d['name'],
                    'coefficient'     => $d['coef'],
                    'department'      => $d['dept'],
                    'hours_per_week'  => $d['hours'],
                    'is_active'       => true,
                    'is_core_subject' => true,
                    'school_id'       => $this->schoolId,
                ]
            );
        }

        $this->line('  ✓ 8 matières : FR · MATH · SVT · PC · HG · ANG · AR · EPS');
        return $subjects;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // NIVEAUX & CLASSES
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Crée les 4 niveaux collège + leurs classes, avec rattachement des matières.
     *
     * @return array<string, SchoolClass>  clé = nom du niveau ('6ème', '5ème', …)
     */
    private function createLevelsAndClasses(array $subjects, AcademicYear $ay): array
    {
        $this->line('');
        $this->line('  ► Création des niveaux et des classes...');

        // Coefficients officiels par matière pour le cycle collège
        $coefs = [
            'FR' => 3, 'MATH' => 3, 'SVT' => 2, 'PC' => 2,
            'HG' => 2, 'ANG' => 2, 'AR' => 1, 'EPS' => 1,
        ];

        $niveaux = [
            ['name' => '6ème', 'order' => 2, 'diploma' => null],
            ['name' => '5ème', 'order' => 3, 'diploma' => null],
            ['name' => '4ème', 'order' => 4, 'diploma' => null],
            ['name' => '3ème', 'order' => 5, 'diploma' => 'BFEM'],
        ];

        $classes = [];

        foreach ($niveaux as $niv) {
            // Niveau
            $level = Level::updateOrCreate(
                ['name' => $niv['name'], 'school_id' => $this->schoolId],
                [
                    'order'     => $niv['order'],
                    'cycle'     => 'college',
                    'school_id' => $this->schoolId,
                ]
            );

            // Matières ↔ Niveau (avec coefficients)
            foreach ($subjects as $code => $subject) {
                DB::table('level_subject')->updateOrInsert(
                    ['level_id' => $level->id, 'subject_id' => $subject->id],
                    [
                        'coefficient'  => $coefs[$code] ?? 1,
                        'is_compulsory'=> true,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]
                );
            }

            // Classe
            $class = SchoolClass::updateOrCreate(
                [
                    'name'             => $niv['name'],
                    'academic_year_id' => $ay->id,
                    'school_id'        => $this->schoolId,
                ],
                [
                    'level_id'     => $level->id,
                    'capacity'     => 30,
                    'school_id'    => $this->schoolId,
                    'diploma_type' => $niv['diploma'],
                    'description'  => $niv['diploma']
                        ? "Classe de {$niv['name']} — Examen {$niv['diploma']}"
                        : "Classe de {$niv['name']}",
                ]
            );

            // Matières ↔ Classe
            foreach ($subjects as $subject) {
                DB::table('class_subject')->updateOrInsert(
                    ['class_id' => $class->id, 'subject_id' => $subject->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            $classes[$niv['name']] = $class;
        }

        $this->line('  ✓ 4 classes : 6ème · 5ème · 4ème · 3ème (avec matières rattachées)');
        return $classes;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ENSEIGNANTS
    // ──────────────────────────────────────────────────────────────────────────

    private function createTeachers(array $subjects, array $classes, AcademicYear $ay): void
    {
        $this->line('');
        $this->line('  ► Création des enseignants...');

        $teacherDefs = [
            [
                'identifier' => 'PRF-COL-001',
                'first_name' => 'Khady',
                'last_name'  => 'Ndiaye',
                'email'      => 'khady.ndiaye@college-senegal.sn',
                'gender'     => 'F',
                'subjects'   => ['FR', 'ANG'],
            ],
            [
                'identifier' => 'PRF-COL-002',
                'first_name' => 'Ousmane',
                'last_name'  => 'Sow',
                'email'      => 'ousmane.sow@college-senegal.sn',
                'gender'     => 'M',
                'subjects'   => ['MATH', 'PC'],
            ],
            [
                'identifier' => 'PRF-COL-003',
                'first_name' => 'Boubacar',
                'last_name'  => 'Diallo',
                'email'      => 'boubacar.diallo@college-senegal.sn',
                'gender'     => 'M',
                'subjects'   => ['SVT', 'HG', 'AR', 'EPS'],
            ],
        ];

        foreach ($teacherDefs as $td) {
            $teacher = $this->makeUser([
                'identifier' => $td['identifier'],
                'role'       => 'teacher',
                'first_name' => $td['first_name'],
                'last_name'  => $td['last_name'],
                'email'      => $td['email'],
                'gender'     => $td['gender'],
            ]);

            // Matières du prof (teacher_subjects)
            foreach ($td['subjects'] as $code) {
                if (! isset($subjects[$code])) {
                    continue;
                }
                DB::table('teacher_subjects')->updateOrInsert(
                    ['teacher_id' => $teacher->id, 'subject_id' => $subjects[$code]->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            // Affectation aux 4 classes (class_teacher + teacher_assignments)
            foreach ($classes as $class) {
                DB::table('class_teacher')->updateOrInsert(
                    ['class_id' => $class->id, 'teacher_id' => $teacher->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                foreach ($td['subjects'] as $code) {
                    if (! isset($subjects[$code])) {
                        continue;
                    }
                    TeacherAssignment::updateOrCreate(
                        [
                            'teacher_id'       => $teacher->id,
                            'class_id'         => $class->id,
                            'subject_id'       => $subjects[$code]->id,
                            'academic_year_id' => $ay->id,
                        ],
                        ['school_id' => $this->schoolId]
                    );
                }
            }

            $matieresStr = implode(', ', $td['subjects']);
            $this->line("  ✓ {$td['first_name']} {$td['last_name']} ({$matieresStr}) — {$td['email']}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ÉLÈVES & PARENTS (numéro WhatsApp de test)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Crée 2 élèves par classe (8 au total), chacun avec un parent associé
     * au numéro WhatsApp de test +221 78 951 52 68.
     *
     * @return array  Liste d'entrées ['user' => User, 'class' => SchoolClass]
     */
    private function createStudentsWithParents(array $classes): array
    {
        $this->line('');
        $this->line('  ► Création des élèves et parents...');

        $defs = [
            '6ème' => [
                [
                    'identifier'  => 'ELV-6E-01',
                    'first_name'  => 'Awa',
                    'last_name'   => 'Fall',
                    'email'       => 'awa.fall@college-senegal.sn',
                    'gender'      => 'F',
                    'age'         => 12,
                    'parent_name' => 'Ibrahima Fall',
                ],
                [
                    'identifier'  => 'ELV-6E-02',
                    'first_name'  => 'Mamadou',
                    'last_name'   => 'Gueye',
                    'email'       => 'mamadou.gueye@college-senegal.sn',
                    'gender'      => 'M',
                    'age'         => 13,
                    'parent_name' => 'Oumar Gueye',
                ],
            ],
            '5ème' => [
                [
                    'identifier'  => 'ELV-5E-01',
                    'first_name'  => 'Khady',
                    'last_name'   => 'Diallo',
                    'email'       => 'khady.diallo@college-senegal.sn',
                    'gender'      => 'F',
                    'age'         => 13,
                    'parent_name' => 'Aminata Diallo',
                ],
                [
                    'identifier'  => 'ELV-5E-02',
                    'first_name'  => 'Lamine',
                    'last_name'   => 'Sow',
                    'email'       => 'lamine.sow@college-senegal.sn',
                    'gender'      => 'M',
                    'age'         => 14,
                    'parent_name' => 'Moussa Sow',
                ],
            ],
            '4ème' => [
                [
                    'identifier'  => 'ELV-4E-01',
                    'first_name'  => 'Fatou',
                    'last_name'   => 'Sy',
                    'email'       => 'fatou.sy@college-senegal.sn',
                    'gender'      => 'F',
                    'age'         => 14,
                    'parent_name' => 'Rokhaya Sy',
                ],
                [
                    'identifier'  => 'ELV-4E-02',
                    'first_name'  => 'Aliou',
                    'last_name'   => 'Badiane',
                    'email'       => 'aliou.badiane@college-senegal.sn',
                    'gender'      => 'M',
                    'age'         => 15,
                    'parent_name' => 'Daouda Badiane',
                ],
            ],
            '3ème' => [
                [
                    'identifier'  => 'ELV-3E-01',
                    'first_name'  => 'Rama',
                    'last_name'   => 'Diouf',
                    'email'       => 'rama.diouf@college-senegal.sn',
                    'gender'      => 'F',
                    'age'         => 15,
                    'parent_name' => 'Marième Diouf',
                ],
                [
                    'identifier'  => 'ELV-3E-02',
                    'first_name'  => 'Seydou',
                    'last_name'   => 'Ba',
                    'email'       => 'seydou.ba@college-senegal.sn',
                    'gender'      => 'M',
                    'age'         => 16,
                    'parent_name' => 'Cheikh Ba',
                ],
            ],
        ];

        $allStudents = [];

        foreach ($defs as $className => $studentList) {
            $class = $classes[$className];

            foreach ($studentList as $sd) {
                $student = $this->makeUser([
                    'identifier'      => $sd['identifier'],
                    'role'            => 'eleve',
                    'first_name'      => $sd['first_name'],
                    'last_name'       => $sd['last_name'],
                    'email'           => $sd['email'],
                    'gender'          => $sd['gender'],
                    'class_id'        => $class->id,
                    'date_of_birth'   => now()->subYears($sd['age'])->subDays(rand(0, 364))->toDateString(),
                    'parent_name'     => $sd['parent_name'],
                    'parent_whatsapp' => self::PARENT_WHATSAPP,
                    'parent_lang'     => self::PARENT_LANG,
                ]);

                $allStudents[] = [
                    'user'  => $student,
                    'class' => $class,
                ];
            }

            $this->line("  ✓ {$className} : {$studentList[0]['first_name']} {$studentList[0]['last_name']} · {$studentList[1]['first_name']} {$studentList[1]['last_name']}");
        }

        $this->line('  → Numéro WhatsApp parent (test) : +221 78 951 52 68 (tous les élèves)');
        return $allStudents;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // NOTES : DEVOIR 1 & DEVOIR 2 (sem. 1 — sans composition)
    // ──────────────────────────────────────────────────────────────────────────

    private function createGrades(array $students, array $subjects, AcademicYear $ay): void
    {
        $this->line('');
        $this->line('  ► Génération des notes (Devoir 1 + Devoir 2)...');

        // Dates réalistes dans le semestre 1
        $devoir1Date = '2025-11-15';
        $devoir2Date = '2026-01-25';

        $gradeCount = 0;

        foreach ($students as $entry) {
            $student = $entry['user'];

            foreach ($subjects as $code => $subject) {
                // Devoir 1 — semestre 1
                Grade::create([
                    'user_id'          => $student->id,
                    'subject_id'       => $subject->id,
                    'grade'            => $this->randomGrade(),
                    'type'             => 'Devoir',
                    'coefficient'      => 1,
                    'semester'         => 1,
                    'date'             => $devoir1Date,
                    'comments'         => "Devoir 1 — {$subject->name}",
                    'appreciation'     => null,
                    'academic_year_id' => $ay->id,
                    'school_id'        => $this->schoolId,
                ]);

                // Devoir 2 — semestre 1
                Grade::create([
                    'user_id'          => $student->id,
                    'subject_id'       => $subject->id,
                    'grade'            => $this->randomGrade(),
                    'type'             => 'Devoir',
                    'coefficient'      => 1,
                    'semester'         => 1,
                    'date'             => $devoir2Date,
                    'comments'         => "Devoir 2 — {$subject->name}",
                    'appreciation'     => null,
                    'academic_year_id' => $ay->id,
                    'school_id'        => $this->schoolId,
                ]);

                $gradeCount += 2;
            }
        }

        $this->line("  ✓ {$gradeCount} notes créées (Devoir 1 + Devoir 2 · sem. 1 · aucune composition)");
    }

    /**
     * Génère une note réaliste entre 6 et 18 sur 20 (distribution en cloche).
     */
    private function randomGrade(): float
    {
        // Palette de notes possibles et leurs poids (distribution réaliste)
        $grades  = [ 5,  6,  7,  8,  9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19];
        $weights = [ 1,  2,  4,  6,  9, 13, 16, 18, 16, 13,  9,  5,  3,  2,  1];

        $totalWeight = array_sum($weights);
        $rand        = rand(1, $totalWeight);
        $cumulative  = 0;

        foreach ($grades as $i => $grade) {
            $cumulative += $weights[$i];
            if ($rand <= $cumulative) {
                // Demi-point aléatoire (.0 ou .5) pour plus de réalisme
                return (float) $grade + (rand(0, 1) * 0.5);
            }
        }

        return 10.0;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ABSENCES ALÉATOIRES (testables via WhatsApp)
    // ──────────────────────────────────────────────────────────────────────────

    private function createAbsences(array $students): void
    {
        $this->line('');
        $this->line('  ► Génération des absences aléatoires...');

        // Générer un pool de jours de cours (lundi-vendredi) dans l'année scolaire
        $schoolDays = $this->generateSchoolDays('2025-10-06', '2026-06-10', 40);

        $absenceCount = 0;

        foreach ($students as $entry) {
            $student = $entry['user'];

            // 2 à 4 absences/retards par élève sur des jours différents
            $numAbsences  = rand(2, 4);
            $selectedDays = array_slice($schoolDays, 0, $numAbsences);

            foreach ($selectedDays as $date) {
                // 75 % absent, 25 % retard
                $status = (rand(1, 4) <= 3) ? 'absent' : 'late';

                Attendance::create([
                    'user_id'   => $student->id,
                    'date'      => $date,
                    'status'    => $status,
                    'reason'    => $status === 'absent' ? 'Absence non justifiée' : 'Retard',
                    'justified' => false,
                    'school_id' => $this->schoolId,
                ]);

                $absenceCount++;
            }

            // Mélanger le pool pour que chaque élève ait des jours différents
            shuffle($schoolDays);
        }

        $this->line("  ✓ {$absenceCount} absences/retards générés sur " . count($students) . " élèves");
        $this->line('  → Enregistrez une absence depuis l\'interface pour déclencher la notification WhatsApp.');
    }

    /**
     * Retourne un tableau mélangé de N jours ouvrés (lun-ven) dans la période donnée.
     */
    private function generateSchoolDays(string $start, string $end, int $count): array
    {
        $days    = [];
        $current = new \DateTime($start);
        $endDt   = new \DateTime($end);

        while ($current <= $endDt) {
            // 1=Lundi … 5=Vendredi, 6=Samedi, 7=Dimanche
            if ((int) $current->format('N') <= 5) {
                $days[] = $current->format('Y-m-d');
            }
            $current->modify('+1 day');
        }

        shuffle($days);
        return array_slice($days, 0, $count);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // MÉTHODE UTILITAIRE — CRÉER / METTRE À JOUR UN UTILISATEUR
    // ──────────────────────────────────────────────────────────────────────────

    private function makeUser(array $data): User
    {
        $user = User::withoutGlobalScopes()
            ->where('email', $data['email'])
            ->first();

        $firstName = $data['first_name'] ?? null;
        $lastName  = $data['last_name']  ?? null;

        $attributes = array_merge([
            'name'              => trim("{$firstName} {$lastName}"),
            'first_name'        => $firstName,
            'last_name'         => $lastName,
            'password'          => $this->password,
            'status'            => 'approved',
            'email_verified_at' => now(),
            'city'              => 'Dakar',
            'country'           => 'Sénégal',
        ], $data);

        // role et school_id ne sont PAS dans $fillable → forceFill obligatoire
        $attributes['school_id'] = $this->schoolId;

        if (! $user) {
            $user = new User();
        }

        $user->forceFill($attributes);
        $user->save();

        return $user;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function line(string $message): void
    {
        if (isset($this->command)) {
            $this->command->line($message);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // RÉSUMÉ FINAL
    // ──────────────────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $this->line('');
        $this->line('╔══════════════════════════════════════════════════════════════════╗');
        $this->line('║                    RÉCAPITULATIF DÉMO                           ║');
        $this->line('╠══════════════════════════════════════════════════════════════════╣');
        $this->line('║  Classes  : 6ème · 5ème · 4ème · 3ème (2 élèves chacune)       ║');
        $this->line('║  Élèves   : 8 (noms sénégalais réalistes + parent renseigné)   ║');
        $this->line('║  Profs    : 3  (FR/ANG · MATH/PC · SVT/HG/AR/EPS)             ║');
        $this->line('║  Matières : 8 (cycle collège officiel)                         ║');
        $this->line('║  Notes    : Devoir 1 + Devoir 2 · sem. 1 (aucune composition)  ║');
        $this->line('║  Absences : 2 à 4 par élève (aléatoires, sem. 1)              ║');
        $this->line('╠══════════════════════════════════════════════════════════════════╣');
        $this->line('║  MOT DE PASSE UNIVERSEL : Passer01                             ║');
        $this->line('╠══════════════════════════════════════════════════════════════════╣');
        $this->line('║  COMPTES DE CONNEXION                                           ║');
        $this->line('║  Super Admin  : superadmin@edumanager.sn                       ║');
        $this->line('║  Directeur    : directeur@college-senegal.sn                   ║');
        $this->line('║  Surveillant  : surveillant@college-senegal.sn                 ║');
        $this->line('║  Prof FR/ANG  : khady.ndiaye@college-senegal.sn               ║');
        $this->line('║  Prof MATH/PC : ousmane.sow@college-senegal.sn                ║');
        $this->line('║  Prof SVT/… : boubacar.diallo@college-senegal.sn            ║');
        $this->line('║  Élève 6ème   : awa.fall@college-senegal.sn                   ║');
        $this->line('║  Élève 5ème   : lamine.sow@college-senegal.sn                 ║');
        $this->line('║  Élève 4ème   : fatou.sy@college-senegal.sn                   ║');
        $this->line('║  Élève 3ème   : seydou.ba@college-senegal.sn                  ║');
        $this->line('╠══════════════════════════════════════════════════════════════════╣');
        $this->line('║  TEST WHATSAPP NOTIFICATIONS                                    ║');
        $this->line('║  Numéro parent (tous) : +221 78 951 52 68                      ║');
        $this->line('║  → Enregistrez une absence depuis l\'admin pour tester          ║');
        $this->line('║  → Ou ajoutez une note pour déclencher la notification note     ║');
        $this->line('╚══════════════════════════════════════════════════════════════════╝');
        $this->line('');
    }
}
