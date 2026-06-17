<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de soutenance — Système scolaire sénégalais, 3 cycles distincts.
 *
 * Cycles : Primaire (CM2/CFEE) · Moyen (6è→3è/BFEM) · Secondaire (Seconde→Terminale/BAC)
 * Règle  : exactement 2 élèves par classe, enseignants strictement isolés par cycle.
 *
 * Usage : php artisan db:seed --class=SenegalTroisCyclesSoutenanceSeeder
 */
class SenegalTroisCyclesSoutenanceSeeder extends Seeder
{
    private string $password;
    private int $schoolId;

    // ──────────────────────────────────────────────
    // POINT D'ENTRÉE
    // ──────────────────────────────────────────────

    public function run(): void
    {
        $this->password = Hash::make('Passer01');

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║  SOUTENANCE — Système Scolaire Sénégalais (3 cycles)    ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');

        $this->cleanDemoData();

        $school = School::withoutGlobalScopes()->first();
        if (! $school) {
            $school = $this->createDefaultSchool();
        }
        $this->schoolId = $school->id;
        $this->command->info("École : {$school->name} (ID {$school->id})");

        $academicYear = $this->getOrCreateAcademicYear();
        $this->command->info("Année académique : {$academicYear->name}");

        // ── 3 cycles ──────────────────────────────────────────────────────────
        $this->command->info('');
        $this->seedCyclePrimaire($academicYear);

        $this->command->info('');
        $this->seedCycleMoyen($academicYear);

        $this->command->info('');
        $this->seedCycleSecondaire($academicYear);

        $this->printSummary();
    }

    // ──────────────────────────────────────────────
    // CYCLE PRIMAIRE  (CM2 → CFEE)
    // ──────────────────────────────────────────────

    private function seedCyclePrimaire(AcademicYear $ay): void
    {
        $this->command->info('▶ CYCLE PRIMAIRE ─────────────────────────────────────────');

        // Matières
        $subjects = $this->createSubjects([
            ['name' => 'Français',           'code' => 'FR',   'coef' => 3, 'dept' => 'Lettres'],
            ['name' => 'Mathématiques',      'code' => 'MATH', 'coef' => 3, 'dept' => 'Sciences'],
            ['name' => 'Sciences',           'code' => 'SCIEN','coef' => 2, 'dept' => 'Sciences'],
            ['name' => 'Histoire-Géographie','code' => 'HG',   'coef' => 2, 'dept' => 'Sciences Humaines'],
            ['name' => 'EDHC',               'code' => 'EDHC', 'coef' => 1, 'dept' => 'Sciences Humaines'],
        ]);

        // Niveau CM2
        $level = Level::updateOrCreate(
            ['name' => 'CM2', 'school_id' => $this->schoolId],
            ['order' => 1, 'cycle' => 'primaire', 'description' => 'Certificat de Fin d\'Études Élémentaires (CFEE)']
        );
        $this->syncLevelSubjects($level, $subjects, [
            'FR' => 3, 'MATH' => 3, 'SCIEN' => 2, 'HG' => 2, 'EDHC' => 1,
        ]);

        // Classe CM2
        $class = $this->createClass('CM2', $level, $ay, 'CFEE');
        $this->attachSubjectsToClass($class, $subjects);

        // Personnel du cycle primaire
        $this->makeUser([
            'identifier' => 'DIR-PRI-001', 'role' => 'admin',
            'first_name' => 'Modou',   'last_name' => 'Sarr',
            'email'      => 'modou.sarr@edumanager.com',
            'gender'     => 'M',
        ]);
        $this->makeUser([
            'identifier' => 'SUR-PRI-001', 'role' => 'surveillant',
            'first_name' => 'Coumba',  'last_name' => 'Niang',
            'email'      => 'coumba.niang@edumanager.com',
            'gender'     => 'F',
        ]);

        // Enseignants primaire (strictement CM2)
        $teachers = [
            [
                'identifier' => 'PRF-PRI-001',
                'first_name' => 'Aminata', 'last_name' => 'Fall',
                'email'      => 'aminata.fall.primaire@edumanager.com',
                'gender'     => 'F',
                'subjects'   => ['FR', 'SCIEN', 'EDHC'],
            ],
            [
                'identifier' => 'PRF-PRI-002',
                'first_name' => 'Ibrahima', 'last_name' => 'Diop',
                'email'      => 'ibrahima.diop.primaire@edumanager.com',
                'gender'     => 'M',
                'subjects'   => ['MATH', 'HG'],
            ],
        ];
        foreach ($teachers as $td) {
            $this->createTeacher($td, $subjects, [$class], $ay);
        }

        // Élèves CM2 (exactement 2)
        $this->createStudent(['identifier' => 'ELV-CM2-01', 'first_name' => 'Aminata', 'last_name' => 'Diop',
            'email' => 'aminata.diop.cm2@edumanager.com', 'gender' => 'F', 'age' => 12], $class);
        $this->createStudent(['identifier' => 'ELV-CM2-02', 'first_name' => 'Pape', 'last_name' => 'Ndiaye',
            'email' => 'pape.ndiaye.cm2@edumanager.com', 'gender' => 'M', 'age' => 13], $class);

        $this->command->info("  ✓ CM2 : 2 élèves · 2 enseignants · 1 directeur · 1 surveillant (CFEE)");
    }

    // ──────────────────────────────────────────────
    // CYCLE MOYEN  (6è → 3è, examen BFEM en 3è)
    // ──────────────────────────────────────────────

    private function seedCycleMoyen(AcademicYear $ay): void
    {
        $this->command->info('▶ CYCLE MOYEN (Collège) ──────────────────────────────────');

        // Matières
        $subjects = $this->createSubjects([
            ['name' => 'Français',           'code' => 'FR',  'coef' => 3, 'dept' => 'Lettres'],
            ['name' => 'Mathématiques',      'code' => 'MATH','coef' => 3, 'dept' => 'Sciences'],
            ['name' => 'SVT',                'code' => 'SVT', 'coef' => 2, 'dept' => 'Sciences'],
            ['name' => 'Physique-Chimie',    'code' => 'PC',  'coef' => 2, 'dept' => 'Sciences'],
            ['name' => 'Histoire-Géographie','code' => 'HG',  'coef' => 2, 'dept' => 'Sciences Humaines'],
            ['name' => 'Anglais',            'code' => 'ANG', 'coef' => 2, 'dept' => 'Langues'],
            ['name' => 'Arabe',              'code' => 'AR',  'coef' => 1, 'dept' => 'Langues'],
            ['name' => 'EPS',                'code' => 'EPS', 'coef' => 1, 'dept' => 'Sport'],
        ]);

        $coefs = ['FR' => 3, 'MATH' => 3, 'SVT' => 2, 'PC' => 2,
                  'HG' => 2, 'ANG' => 2, 'AR' => 1, 'EPS' => 1];

        // Définition des 4 niveaux collège
        $niveaux = [
            ['name' => '6ème', 'order' => 2, 'exam' => false],
            ['name' => '5ème', 'order' => 3, 'exam' => false],
            ['name' => '4ème', 'order' => 4, 'exam' => false],
            ['name' => '3ème', 'order' => 5, 'exam' => true, 'diploma' => 'BFEM'],
        ];

        // Élèves par classe
        $elevesDef = [
            '6ème' => [
                ['identifier' => 'ELV-6E-01', 'first_name' => 'Awa',    'last_name' => 'Fall',   'email' => 'awa.fall.6e@edumanager.com',   'gender' => 'F', 'age' => 12],
                ['identifier' => 'ELV-6E-02', 'first_name' => 'Mamadou','last_name' => 'Gueye',  'email' => 'mamadou.gueye.6e@edumanager.com','gender' => 'M', 'age' => 12],
            ],
            '5ème' => [
                ['identifier' => 'ELV-5E-01', 'first_name' => 'Khady',  'last_name' => 'Diallo', 'email' => 'khady.diallo.5e@edumanager.com', 'gender' => 'F', 'age' => 13],
                ['identifier' => 'ELV-5E-02', 'first_name' => 'Lamine', 'last_name' => 'Sow',    'email' => 'lamine.sow.5e@edumanager.com',   'gender' => 'M', 'age' => 13],
            ],
            '4ème' => [
                ['identifier' => 'ELV-4E-01', 'first_name' => 'Fatou',  'last_name' => 'Sy',     'email' => 'fatou.sy.4e@edumanager.com',     'gender' => 'F', 'age' => 14],
                ['identifier' => 'ELV-4E-02', 'first_name' => 'Aliou',  'last_name' => 'Badiane','email' => 'aliou.badiane.4e@edumanager.com','gender' => 'M', 'age' => 14],
            ],
            '3ème' => [
                ['identifier' => 'ELV-3E-01', 'first_name' => 'Rama',   'last_name' => 'Diouf',  'email' => 'rama.diouf.3e@edumanager.com',   'gender' => 'F', 'age' => 15],
                ['identifier' => 'ELV-3E-02', 'first_name' => 'Seydou', 'last_name' => 'Ba',     'email' => 'seydou.ba.3e@edumanager.com',     'gender' => 'M', 'age' => 15],
            ],
        ];

        $classes = [];
        foreach ($niveaux as $niv) {
            $level = Level::updateOrCreate(
                ['name' => $niv['name'], 'school_id' => $this->schoolId],
                ['order' => $niv['order'], 'cycle' => 'college']
            );
            $this->syncLevelSubjects($level, $subjects, $coefs);

            $diploma = $niv['exam'] ? $niv['diploma'] : null;
            $class   = $this->createClass($niv['name'], $level, $ay, $diploma);
            $this->attachSubjectsToClass($class, $subjects);

            foreach ($elevesDef[$niv['name']] as $ed) {
                $this->createStudent($ed, $class);
            }

            $classes[] = $class;
        }

        // Personnel du cycle collège
        $this->makeUser([
            'identifier' => 'DIR-COL-001', 'role' => 'admin',
            'first_name' => 'Assane', 'last_name' => 'Mbaye',
            'email'      => 'assane.mbaye.college@edumanager.com',
            'gender'     => 'M',
        ]);
        $this->makeUser([
            'identifier' => 'SUR-COL-001', 'role' => 'surveillant',
            'first_name' => 'Alioune', 'last_name' => 'Thiam',
            'email'      => 'alioune.thiam.college@edumanager.com',
            'gender'     => 'M',
        ]);

        // Enseignants collège (strictement 6è–3è)
        $teachers = [
            [
                'identifier' => 'PRF-COL-001',
                'first_name' => 'Khady',   'last_name' => 'Ndiaye',
                'email'      => 'khady.ndiaye.college@edumanager.com',
                'gender'     => 'F',
                'subjects'   => ['FR', 'ANG'],
            ],
            [
                'identifier' => 'PRF-COL-002',
                'first_name' => 'Ousmane', 'last_name' => 'Sow',
                'email'      => 'ousmane.sow.college@edumanager.com',
                'gender'     => 'M',
                'subjects'   => ['MATH', 'PC'],
            ],
            [
                'identifier' => 'PRF-COL-003',
                'first_name' => 'Boubacar', 'last_name' => 'Diallo',
                'email'      => 'boubacar.diallo.college@edumanager.com',
                'gender'     => 'M',
                'subjects'   => ['SVT', 'HG', 'AR', 'EPS'],
            ],
        ];
        foreach ($teachers as $td) {
            $this->createTeacher($td, $subjects, $classes, $ay);
        }

        $this->command->info("  ✓ 4 classes (6è→3è) : 8 élèves · 3 enseignants · 1 directeur · 1 surveillant");
        $this->command->info("  ✓ Classe d'examen : 3ème (BFEM)");
    }

    // ──────────────────────────────────────────────
    // CYCLE SECONDAIRE  (Seconde → Terminale/BAC)
    // ──────────────────────────────────────────────

    private function seedCycleSecondaire(AcademicYear $ay): void
    {
        $this->command->info('▶ CYCLE SECONDAIRE (Lycée) ───────────────────────────────');

        // Matières
        $subjects = $this->createSubjects([
            ['name' => 'Français',           'code' => 'FR',   'coef' => 3, 'dept' => 'Lettres'],
            ['name' => 'Mathématiques',      'code' => 'MATH', 'coef' => 4, 'dept' => 'Sciences'],
            ['name' => 'SVT',                'code' => 'SVT',  'coef' => 3, 'dept' => 'Sciences'],
            ['name' => 'Physique-Chimie',    'code' => 'PC',   'coef' => 3, 'dept' => 'Sciences'],
            ['name' => 'Philosophie',        'code' => 'PHILO','coef' => 3, 'dept' => 'Lettres'],
            ['name' => 'Histoire-Géographie','code' => 'HG',   'coef' => 2, 'dept' => 'Sciences Humaines'],
            ['name' => 'Anglais',            'code' => 'ANG',  'coef' => 2, 'dept' => 'Langues'],
            ['name' => 'EPS',                'code' => 'EPS',  'coef' => 1, 'dept' => 'Sport'],
        ]);

        $coefs = ['FR' => 3, 'MATH' => 4, 'SVT' => 3, 'PC' => 3,
                  'PHILO' => 3, 'HG' => 2, 'ANG' => 2, 'EPS' => 1];

        $niveaux = [
            ['name' => 'Seconde',   'order' => 6, 'exam' => false],
            ['name' => 'Terminale', 'order' => 7, 'exam' => true, 'diploma' => 'BAC'],
        ];

        $elevesDef = [
            'Seconde' => [
                ['identifier' => 'ELV-SEC-01', 'first_name' => 'Ndeye',   'last_name' => 'Camara', 'email' => 'ndeye.camara.seconde@edumanager.com', 'gender' => 'F', 'age' => 16],
                ['identifier' => 'ELV-SEC-02', 'first_name' => 'Malick',  'last_name' => 'Sarr',   'email' => 'malick.sarr.seconde@edumanager.com',  'gender' => 'M', 'age' => 16],
            ],
            'Terminale' => [
                ['identifier' => 'ELV-TER-01', 'first_name' => 'Bineta',  'last_name' => 'Wade',   'email' => 'bineta.wade.terminale@edumanager.com',  'gender' => 'F', 'age' => 18],
                ['identifier' => 'ELV-TER-02', 'first_name' => 'Babacar', 'last_name' => 'Touré',  'email' => 'babacar.toure.terminale@edumanager.com', 'gender' => 'M', 'age' => 18],
            ],
        ];

        $classes = [];
        foreach ($niveaux as $niv) {
            $level = Level::updateOrCreate(
                ['name' => $niv['name'], 'school_id' => $this->schoolId],
                ['order' => $niv['order'], 'cycle' => 'lycee']
            );
            $this->syncLevelSubjects($level, $subjects, $coefs);

            $diploma = $niv['exam'] ? $niv['diploma'] : null;
            $class   = $this->createClass($niv['name'], $level, $ay, $diploma);
            $this->attachSubjectsToClass($class, $subjects);

            foreach ($elevesDef[$niv['name']] as $ed) {
                $this->createStudent($ed, $class);
            }

            $classes[] = $class;
        }

        // Personnel du cycle lycée
        $this->makeUser([
            'identifier' => 'DIR-LYC-001', 'role' => 'admin',
            'first_name' => 'Daouda',     'last_name' => 'Faye',
            'email'      => 'daouda.faye.lycee@edumanager.com',
            'gender'     => 'M',
        ]);
        $this->makeUser([
            'identifier' => 'SUR-LYC-001', 'role' => 'surveillant',
            'first_name' => 'Souleymane', 'last_name' => 'Cissé',
            'email'      => 'souleymane.cisse.lycee@edumanager.com',
            'gender'     => 'M',
        ]);

        // Enseignants lycée (strictement Seconde/Terminale)
        $teachers = [
            [
                'identifier' => 'PRF-LYC-001',
                'first_name' => 'Mariama', 'last_name' => 'Sy',
                'email'      => 'mariama.sy.lycee@edumanager.com',
                'gender'     => 'F',
                'subjects'   => ['FR', 'PHILO'],
            ],
            [
                'identifier' => 'PRF-LYC-002',
                'first_name' => 'Cheikh',  'last_name' => 'Gueye',
                'email'      => 'cheikh.gueye.lycee@edumanager.com',
                'gender'     => 'M',
                'subjects'   => ['MATH', 'PC'],
            ],
            [
                'identifier' => 'PRF-LYC-003',
                'first_name' => 'Fatou',   'last_name' => 'Ba',
                'email'      => 'fatou.ba.lycee@edumanager.com',
                'gender'     => 'F',
                'subjects'   => ['SVT', 'HG', 'ANG', 'EPS'],
            ],
        ];
        foreach ($teachers as $td) {
            $this->createTeacher($td, $subjects, $classes, $ay);
        }

        $this->command->info("  ✓ 2 classes (Seconde→Terminale) : 4 élèves · 3 enseignants · 1 directeur · 1 surveillant");
        $this->command->info("  ✓ Classe d'examen : Terminale (BAC)");
    }

    // ──────────────────────────────────────────────
    // MÉTHODES UTILITAIRES
    // ──────────────────────────────────────────────

    /**
     * Crée ou met à jour des matières ; retourne un tableau indexé par code.
     *
     * @return array<string, Subject>
     */
    private function createSubjects(array $defs): array
    {
        $map = [];
        foreach ($defs as $d) {
            $map[$d['code']] = Subject::updateOrCreate(
                ['code' => $d['code'], 'school_id' => $this->schoolId],
                [
                    'name'           => $d['name'],
                    'coefficient'    => $d['coef'],
                    'department'     => $d['dept'],
                    'is_active'      => true,
                    'is_core_subject'=> true,
                    'hours_per_week' => 3,
                    'school_id'      => $this->schoolId,
                ]
            );
        }
        return $map;
    }

    /**
     * Attache les matières à un niveau avec les coefficients officiels.
     *
     * @param array<string, Subject> $subjects
     * @param array<string, int>     $coefs    code → coefficient
     */
    private function syncLevelSubjects(Level $level, array $subjects, array $coefs): void
    {
        foreach ($subjects as $code => $subject) {
            $coef = $coefs[$code] ?? 1;
            DB::table('level_subject')->updateOrInsert(
                ['level_id' => $level->id, 'subject_id' => $subject->id],
                ['coefficient' => $coef, 'is_compulsory' => true,
                 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Crée ou retrouve une classe pour l'année en cours.
     */
    private function createClass(
        string       $name,
        Level        $level,
        AcademicYear $ay,
        ?string      $diplomaType = null
    ): SchoolClass {
        return SchoolClass::updateOrCreate(
            ['name' => $name, 'academic_year_id' => $ay->id, 'school_id' => $this->schoolId],
            [
                'level_id'     => $level->id,
                'capacity'     => 30,
                'school_id'    => $this->schoolId,
                'diploma_type' => $diplomaType,
                'description'  => $diplomaType
                    ? "Classe de {$name} — Examen {$diplomaType}"
                    : "Classe de {$name}",
            ]
        );
    }

    /**
     * Attache toutes les matières d'un tableau à une classe (sans doublons).
     *
     * @param array<string, Subject> $subjects
     */
    private function attachSubjectsToClass(SchoolClass $class, array $subjects): void
    {
        foreach ($subjects as $subject) {
            DB::table('class_subject')->updateOrInsert(
                ['class_id' => $class->id, 'subject_id' => $subject->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Crée un enseignant, l'associe à ses matières ET exclusivement aux classes du cycle.
     *
     * @param array<string, Subject>  $allSubjects  code → Subject
     * @param SchoolClass[]           $cycleClasses  classes du cycle uniquement
     */
    private function createTeacher(
        array        $td,
        array        $allSubjects,
        array        $cycleClasses,
        AcademicYear $ay
    ): void {
        $teacher = $this->makeUser([
            'identifier' => $td['identifier'],
            'role'       => 'teacher',
            'first_name' => $td['first_name'],
            'last_name'  => $td['last_name'],
            'email'      => $td['email'],
            'gender'     => $td['gender'],
        ]);

        // Associer les matières au prof (teacher_subjects)
        foreach ($td['subjects'] as $code) {
            if (! isset($allSubjects[$code])) {
                continue;
            }
            DB::table('teacher_subjects')->updateOrInsert(
                ['teacher_id' => $teacher->id, 'subject_id' => $allSubjects[$code]->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Affecter le prof aux classes du cycle UNIQUEMENT (class_teacher + teacher_assignments)
        foreach ($cycleClasses as $class) {
            DB::table('class_teacher')->updateOrInsert(
                ['class_id' => $class->id, 'teacher_id' => $teacher->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            foreach ($td['subjects'] as $code) {
                if (! isset($allSubjects[$code])) {
                    continue;
                }
                TeacherAssignment::updateOrCreate(
                    [
                        'teacher_id'       => $teacher->id,
                        'class_id'         => $class->id,
                        'subject_id'       => $allSubjects[$code]->id,
                        'academic_year_id' => $ay->id,
                    ],
                    ['school_id' => $this->schoolId]
                );
            }
        }
    }

    /**
     * Crée un élève et l'inscrit dans la classe.
     */
    private function createStudent(array $sd, SchoolClass $class): void
    {
        $this->makeUser([
            'identifier' => $sd['identifier'],
            'role'       => 'eleve',
            'first_name' => $sd['first_name'],
            'last_name'  => $sd['last_name'],
            'email'      => $sd['email'],
            'gender'     => $sd['gender'],
            'class_id'   => $class->id,
            'date_of_birth' => now()->subYears($sd['age'])->subDays(rand(0, 364))->toDateString(),
        ]);
    }

    /**
     * Crée ou met à jour un utilisateur en contournant la protection fillable
     * (role et school_id ne sont pas dans $fillable du modèle User).
     */
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

        // role et school_id ne sont PAS dans fillable → forceFill obligatoire
        $attributes['school_id'] = $this->schoolId;

        if (! $user) {
            $user = new User();
        }

        $user->forceFill($attributes);
        $user->save();

        return $user;
    }

    // ──────────────────────────────────────────────
    // NETTOYAGE DES DONNÉES DE DÉMO
    // ──────────────────────────────────────────────

    private function cleanDemoData(): void
    {
        $this->command->info('Nettoyage des données @edumanager.com...');

        $userIds = DB::table('users')
            ->where('email', 'like', '%@edumanager.com')
            ->pluck('id')
            ->toArray();

        if (! empty($userIds)) {
            DB::table('grades')->whereIn('user_id', $userIds)->delete();
            DB::table('teacher_assignments')->whereIn('teacher_id', $userIds)->delete();
            DB::table('teacher_subjects')->whereIn('teacher_id', $userIds)->delete();
            DB::table('class_teacher')->whereIn('teacher_id', $userIds)->delete();
            DB::table('users')->whereIn('id', $userIds)->delete();
            $this->command->info("  " . count($userIds) . " compte(s) supprimé(s).");
        }
    }

    // ──────────────────────────────────────────────
    // HELPERS ÉCOLE / ANNÉE
    // ──────────────────────────────────────────────

    private function createDefaultSchool(): School
    {
        return School::create([
            'name'     => 'École Liberté — Sénégal',
            'slug'     => 'ecole-liberte-senegal',
            'code'     => 'ELS001',
            'is_active'=> true,
            'email'    => 'contact@ecole-liberte.sn',
            'city'     => 'Dakar',
        ]);
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

    // ──────────────────────────────────────────────
    // RÉSUMÉ FINAL
    // ──────────────────────────────────────────────

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════╗');
        $this->command->info('║              RÉCAPITULATIF SOUTENANCE                    ║');
        $this->command->info('╠══════════════════════════════════════════════════════════╣');
        $this->command->info('║  Cycle       │ Classe(s)                   │ Examen     ║');
        $this->command->info('║  Primaire    │ CM2                         │ CFEE       ║');
        $this->command->info('║  Moyen       │ 6è · 5è · 4è · 3è           │ BFEM (3è)  ║');
        $this->command->info('║  Secondaire  │ Seconde · Terminale         │ BAC (Ter.) ║');
        $this->command->info('╠══════════════════════════════════════════════════════════╣');
        $this->command->info('║  Total élèves  : 14  (2 par classe exactement)          ║');
        $this->command->info('║  Enseignants   : 8   (isolation stricte par cycle)      ║');
        $this->command->info('║  Directeurs    : 3   (1 par cycle, role=admin)          ║');
        $this->command->info('║  Surveillants  : 3   (1 par cycle, role=surveillant)    ║');
        $this->command->info('╠══════════════════════════════════════════════════════════╣');
        $this->command->info('║  Mot de passe universel : Passer01                      ║');
        $this->command->info('║  Domaine email          : @edumanager.com               ║');
        $this->command->info('╠══════════════════════════════════════════════════════════╣');
        $this->command->info('║  EXEMPLES DE CONNEXION                                  ║');
        $this->command->info('║  Dir. Primaire   : modou.sarr@edumanager.com            ║');
        $this->command->info('║  Prof. Collège 1 : khady.ndiaye.college@edumanager.com ║');
        $this->command->info('║  Élève CM2       : aminata.diop.cm2@edumanager.com      ║');
        $this->command->info('║  Élève 3ème      : rama.diouf.3e@edumanager.com         ║');
        $this->command->info('║  Élève Terminale : bineta.wade.terminale@edumanager.com ║');
        $this->command->info('╚══════════════════════════════════════════════════════════╝');
    }
}
