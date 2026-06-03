<?php

namespace App\Support\LoadTest;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\FormationDepartment;
use App\Models\FormationPromotion;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Support\FormationLevelResolver;
use App\Support\FormationLmdSettings;
use App\Support\AcademicYearProvisioner;
use App\Support\PrimaryClassTeacherProvisioner;
use App\Support\SchoolLoginCredentials;
use App\Support\SchoolLevelProvisioner;
use App\Support\SchoolSubjectProvisioner;
use App\Support\SenegalGradeSequence;
use Carbon\Carbon;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoadTestSchoolFactory
{
    public const STUDENTS_PER_CLASS = 50;

    private int $studentsPerClass = self::STUDENTS_PER_CLASS;

    private bool $fullTeacherCoverage = false;

    private const CLOSED_YEAR = '2024-2025';

    private const CURRENT_YEAR = '2025-2026';

    private array $firstNamesMale = ['Mamadou', 'Abdoulaye', 'Ibrahima', 'Cheikh', 'Pape', 'Modou', 'Ousmane', 'Mor', 'Babacar', 'Djibril'];

    private array $firstNamesFemale = ['Fatou', 'Aminata', 'Awa', 'Mariama', 'Khady', 'Ndèye', 'Bineta', 'Coumba', 'Rama', 'Aissatou'];

    private array $lastNames = ['Diop', 'Ndiaye', 'Fall', 'Sy', 'Gueye', 'Sow', 'Diallo', 'Thiam', 'Sarr', 'Mbaye', 'Faye', 'Kane', 'Tall', 'Wade'];

    private ?OutputStyle $output = null;

    private int $globalStudentSeq = 1;

    private int $globalTeacherSeq = 1;

    private ?int $schoolId = null;

    /** @var array<string, User> */
    private array $teacherBySubjectName = [];

    private ?User $primaryClassTeacher = null;

    /** @var array<int, int> */
    private array $classOrderById = [];

    public function setOutput(?OutputStyle $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function setStudentsPerClass(int $count): self
    {
        $this->studentsPerClass = max(1, min(55, $count));

        return $this;
    }

    /** Assigne un professeur à chaque matière de chaque classe (suivi notes / EDT). */
    public function setFullTeacherCoverage(bool $full = true): self
    {
        $this->fullTeacherCoverage = $full;

        return $this;
    }

    private function classCapacity(): int
    {
        return max($this->studentsPerClass + 5, 20);
    }

    /**
     * @param  array{slug: string, name: string, type: string, city: string, address?: string, admin_email: string, formation_use_lmd?: bool}  $config
     */
    public function seedClassic(array $config): School
    {
        $this->resetSchoolState();
        $school = $this->createSchool($config);
        $this->schoolId = $school->id;

        SchoolLevelProvisioner::ensureForSchool($this->schoolId);

        if (! $school->isPrimaireEstablishment()) {
            SchoolSubjectProvisioner::ensureForSchool($this->schoolId);
        }

        if (PrimaryClassTeacherProvisioner::schoolHasPrimaireCycle($school)) {
            PrimaryClassTeacherProvisioner::ensurePrimaireSubjects($this->schoolId);
            $this->primaryClassTeacher = PrimaryClassTeacherProvisioner::ensurePrimaryTeacher($school->fresh());
            $this->mapPrimaryTeacherToSubjectNames();
        }

        $years = $this->createYears($school);
        $closedYear = $years['closed'];
        $currentYear = $years['current'];
        $this->createSchoolAdmin($config['admin_email'], $config['name']);
        $this->createSurveillant();
        $this->createTeachersForSchool($school);

        $levels = Level::withoutGlobalScopes()
            ->where('school_id', $this->schoolId)
            ->orderBy('order')
            ->get();

        $classes = [];
        foreach ($levels as $level) {
            $classes[] = SchoolClass::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'name' => $level->name,
                'academic_year_id' => $closedYear->id,
                'level_id' => $level->id,
                'capacity' => $this->classCapacity(),
            ]);
            $this->classOrderById[end($classes)->id] = $level->order;
        }

        $allStudents = [];
        foreach ($classes as $class) {
            $class->loadMissing('level.subjects');
            $allStudents = array_merge($allStudents, $this->seedStudentsForClass($class, $closedYear));
            if (PrimaryClassTeacherProvisioner::isPrimaireLevel($class->level) && $this->primaryClassTeacher) {
                PrimaryClassTeacherProvisioner::assignToClass($class, $this->primaryClassTeacher);
            } else {
                $this->linkTeachersToClassPartial($class);
            }
            $this->seedSchedulesForClass($class);
        }

        $this->bulkGrades($allStudents, $closedYear);
        $this->bulkAttendances($allStudents);

        $this->provisionCurrentYearFromClosed($school, $closedYear, $currentYear);

        $this->line("  ✓ {$school->name} — ".count($classes).' classes, '.count($allStudents).' élèves');

        return $school;
    }

    /**
     * @param  array{slug: string, name: string, city: string, admin_email: string, formation_use_lmd: false}  $config
     */
    public function seedFormationClassic(array $config): School
    {
        $this->resetSchoolState();
        $config['type'] = School::TYPE_FORMATION;
        $config['formation_use_lmd'] = false;

        $school = $this->createSchool($config);
        $this->schoolId = $school->id;
        $years = $this->createYears($school);
        $closedYear = $years['closed'];
        $currentYear = $years['current'];
        $this->createSchoolAdmin($config['admin_email'], $config['name']);
        $this->createSurveillant();

        $modules = $this->createFormationModules($school, false);
        $teachers = $this->createFormationTeachers($modules, 3);

        $classDefs = [
            ['name' => 'CAP Comptabilité 2024', 'filiere' => 'Comptabilité', 'year' => 'CAP 1'],
            ['name' => 'BTS Informatique 2024', 'filiere' => 'Informatique', 'year' => 'BTS 1'],
        ];

        $allStudents = [];
        foreach ($classDefs as $i => $def) {
            $level = FormationLevelResolver::resolve($this->schoolId, $def['year'], $def['filiere'], 'bts');
            $class = SchoolClass::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'academic_year_id' => $closedYear->id,
                'level_id' => $level->id,
                'name' => $def['name'],
                'filiere' => $def['filiere'],
                'formation_year' => $def['year'],
                'capacity' => $this->classCapacity(),
            ]);
            $class->subjects()->sync(collect($modules)->pluck('id'));
            $teacher = $teachers[$i % 2];
            if ($teacher) {
                $class->teachers()->syncWithoutDetaching([$teacher->id]);
                foreach ($modules as $subject) {
                    if (! $this->fullTeacherCoverage && random_int(1, 100) > 12) {
                        continue;
                    }
                    TeacherAssignment::withoutGlobalScopes()->firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $closedYear->id,
                    ], ['school_id' => $this->schoolId]);
                }
            }
            $allStudents = array_merge($allStudents, $this->seedStudentsForClass($class, $closedYear));
            $this->seedSchedulesForClass($class, $modules);
        }

        $this->bulkGradesClassicTypes($allStudents, $modules, $closedYear);
        $this->bulkAttendances($allStudents);
        $this->provisionCurrentYearFromClosed($school, $closedYear, $currentYear);
        $this->line("  ✓ {$school->name} (formation sans LMD) — ".count($allStudents).' étudiants');

        return $school;
    }

    /**
     * @param  array{slug: string, name: string, city: string, admin_email: string, dept: string, filiere: string, promotions: list<string>}  $config
     */
    public function seedFormationLmd(array $config): School
    {
        $this->resetSchoolState();
        $school = $this->createSchool([
            ...$config,
            'type' => School::TYPE_FORMATION,
            'formation_use_lmd' => true,
        ]);
        $this->schoolId = $school->id;
        FormationLmdSettings::defaults()->persistToSchool($school);

        $years = $this->createYears($school);
        $closedYear = $years['closed'];
        $currentYear = $years['current'];
        $this->createSchoolAdmin($config['admin_email'], $config['name']);

        $dept = FormationDepartment::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'name' => $config['dept'],
        ]);

        $modules = $this->createFormationModules($school, true);
        $teachers = $this->createFormationTeachers($modules, 4);
        $allStudents = [];

        foreach ($config['promotions'] as $pIndex => $promoName) {
            $promo = FormationPromotion::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'formation_department_id' => $dept->id,
                'academic_year_id' => $closedYear->id,
                'name' => $promoName,
                'filiere' => $config['filiere'],
                'diploma_type' => 'licence',
                'formation_year' => 'Licence '.($pIndex + 1),
            ]);

            $level = FormationLevelResolver::resolve(
                $this->schoolId,
                $promo->formation_year,
                $config['filiere'],
                'licence'
            );

            $class = SchoolClass::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'academic_year_id' => $closedYear->id,
                'formation_promotion_id' => $promo->id,
                'formation_department_id' => $dept->id,
                'name' => Str::slug($promoName, '-'),
                'promotion_name' => $promo->name,
                'filiere' => $config['filiere'],
                'diploma_type' => 'licence',
                'formation_year' => $promo->formation_year,
                'level_id' => $level->id,
                'capacity' => $this->classCapacity(),
            ]);

            $class->subjects()->sync(collect($modules)->pluck('id'));
            $teacher = $teachers[$pIndex % count($teachers)];
            if ($teacher) {
                $class->teachers()->syncWithoutDetaching([$teacher->id]);
                foreach ($modules as $subject) {
                    TeacherAssignment::withoutGlobalScopes()->firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $closedYear->id,
                    ], ['school_id' => $this->schoolId]);
                }
            }

            $allStudents = array_merge($allStudents, $this->seedStudentsForClass($class, $closedYear));
            $this->seedSchedulesForClass($class, $modules);
        }

        $this->bulkGradesLmd($allStudents, $modules, $closedYear);
        $this->bulkAttendances($allStudents);
        $this->provisionCurrentYearFromClosed($school, $closedYear, $currentYear);
        $this->line("  ✓ {$school->name} (LMD) — ".count($allStudents).' étudiants');

        return $school;
    }

    /**
     * @return array{closed: AcademicYear, current: AcademicYear}
     */
    private function createYears(School $school): array
    {
        $closed = AcademicYear::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'name' => self::CLOSED_YEAR,
            'start_date' => '2024-10-01',
            'end_date' => '2025-06-30',
            'is_current' => false,
            'is_closed' => true,
        ]);

        $current = AcademicYear::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'name' => self::CURRENT_YEAR,
            'start_date' => '2025-10-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
            'is_closed' => false,
        ]);

        $school->update(['default_academic_year_id' => $current->id]);

        return ['closed' => $closed, 'current' => $current];
    }

    /** Classes + profs + EDT pour l'année courante (sans élèves) — permet le passage depuis l'année clôturée. */
    private function provisionCurrentYearFromClosed(School $school, AcademicYear $closed, AcademicYear $current): void
    {
        if (SchoolClass::withoutGlobalScopes()->where('academic_year_id', $current->id)->exists()) {
            return;
        }

        $result = AcademicYearProvisioner::provision($current, $closed);

        if (($result['classes'] ?? 0) > 0) {
            $this->line("  → {$current->name} : {$result['classes']} classe(s) prêtes (élèves via passage depuis {$closed->name})");
        }
    }

    /**
     * @param  array{slug: string, name: string, type: string, city: string, address?: string, admin_email: string, formation_use_lmd?: bool}  $config
     */
    private function createSchool(array $config): School
    {
        return School::withoutGlobalScopes()->create([
            'name' => $config['name'],
            'slug' => $config['slug'],
            'code' => School::generateUniqueCode(),
            'establishment_type' => $config['type'],
            'formation_use_lmd' => $config['formation_use_lmd'] ?? true,
            'is_active' => true,
            'email' => 'contact@'.$config['slug'].'.edu.sn',
            'secretariat_email' => 'secretariat@'.$config['slug'].'.edu.sn',
            'city' => $config['city'],
            'address' => $config['address'] ?? $config['city'],
            'timezone' => 'Africa/Dakar',
            'locale' => 'fr',
        ]);
    }

    private function createSchoolAdmin(string $email, string $schoolName): void
    {
        $password = (string) config('school.load_test_default_password', 'password');
        $admin = User::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'name' => 'Directeur — '.$schoolName,
            'email' => $email,
            'password' => Hash::make($password),
            'identifier' => 'ADM-'.$this->schoolId,
            'user_id' => 'ADM-'.$this->schoolId,
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'phone' => '+221 33 '.random_int(800, 999).' '.random_int(10, 99).' '.random_int(10, 99),
            'email_verified_at' => now(),
        ]);

        $school = School::find($this->schoolId);
        if ($school) {
            SchoolLoginCredentials::recordLoadTestDefaults($school, $admin, $password);
        }
    }

    private function createSurveillant(): void
    {
        $n = $this->globalTeacherSeq++;
        User::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'name' => 'Surveillant général '.$this->lastNames[array_rand($this->lastNames)],
            'email' => "surveillant.{$this->schoolId}.{$n}@loadtest.edu.sn",
            'password' => Hash::make('password'),
            'identifier' => 'SV'.$this->schoolId.$n,
            'role' => User::ROLE_SURVEILLANT,
            'status' => User::STATUS_APPROVED,
            'email_verified_at' => now(),
        ]);
    }

    private function createTeachersForSchool(School $school): void
    {
        if ($school->isPrimaireEstablishment()) {
            return;
        }

        $this->createTeachersForCollegeLyceeCatalog($school);
    }

    private function mapPrimaryTeacherToSubjectNames(): void
    {
        if (! $this->primaryClassTeacher) {
            return;
        }

        foreach (array_keys(PrimaryClassTeacherProvisioner::SUBJECT_CATALOG) as $name) {
            $this->teacherBySubjectName[$name] = $this->primaryClassTeacher;
        }
    }

    private function createTeachersForCollegeLyceeCatalog(School $school): void
    {
        $primaireCodes = PrimaryClassTeacherProvisioner::primaireSubjectCodes();

        $subjects = Subject::withoutGlobalScopes()
            ->where('school_id', $this->schoolId)
            ->whereNotIn('code', $primaireCodes)
            ->get();

        foreach ($subjects as $subject) {
            $fn = $this->firstNamesMale[array_rand($this->firstNamesMale)];
            $ln = $this->lastNames[array_rand($this->lastNames)];
            $n = $this->globalTeacherSeq++;
            $slug = Str::slug($subject->code);
            $email = "prof.{$slug}.{$this->schoolId}.{$n}@loadtest.edu.sn";

            $teacher = User::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'name' => $fn.' '.$ln,
                'email' => $email,
                'password' => Hash::make('password'),
                'identifier' => 'P'.$this->schoolId.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
                'role' => User::ROLE_TEACHER,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]);
            $teacher->subjects()->syncWithoutDetaching([$subject->id]);
            $this->teacherBySubjectName[$subject->name] = $teacher;
        }

        $n = $this->globalTeacherSeq++;
        User::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'name' => 'Coordinateur pédagogique '.$this->lastNames[array_rand($this->lastNames)],
            'email' => "coordo.{$this->schoolId}.{$n}@loadtest.edu.sn",
            'password' => Hash::make('password'),
            'identifier' => 'CO'.$this->schoolId.$n,
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return list<Subject>
     */
    private function createFormationModules(School $school, bool $lmd): array
    {
        $defs = [
            ['Algorithmique', 'ALGO', 30],
            ['Base de données', 'BDD', 40],
            ['Réseaux', 'RES', 25],
            ['Gestion de projet', 'GP', 35],
        ];

        $modules = [];
        foreach ($defs as [$name, $code, $cc]) {
            $attrs = [
                'school_id' => $school->id,
                'name' => $name,
                'code' => $code.'-'.$school->id,
                'coefficient' => 2,
                'is_active' => true,
                'hours_per_week' => 4,
            ];
            if ($lmd) {
                $attrs['lmd_settings'] = FormationLmdSettings::fromValidated([
                    'cc_weight_percent' => $cc,
                    'exam_weight_percent' => 100 - $cc,
                    'passing_grade_min' => 10,
                    'cc_grade_types' => ['devoir1', 'devoir2'],
                    'exam_grade_types' => ['composition'],
                ])->toArray();
            }
            $modules[] = Subject::withoutGlobalScopes()->create($attrs);
        }

        return $modules;
    }

    /**
     * @param  list<Subject>  $modules
     * @return list<User>
     */
    private function createFormationTeachers(array $modules, int $count): array
    {
        $teachers = [];
        for ($i = 0; $i < $count; $i++) {
            $fn = $this->firstNamesMale[array_rand($this->firstNamesMale)];
            $ln = $this->lastNames[array_rand($this->lastNames)];
            $n = $this->globalTeacherSeq++;
            $teacher = User::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'name' => 'Dr '.$fn.' '.$ln,
                'email' => "enseignant.{$this->schoolId}.{$n}@loadtest.edu.sn",
                'password' => Hash::make('password'),
                'identifier' => 'PF'.$this->schoolId.$n,
                'role' => User::ROLE_TEACHER,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),
            ]);
            if ($i < count($modules)) {
                $teacher->subjects()->syncWithoutDetaching([$modules[$i]->id]);
                $this->teacherBySubjectName[$modules[$i]->name] = $teacher;
            }
            $teachers[] = $teacher;
        }

        return $teachers;
    }

    private function resetSchoolState(): void
    {
        $this->teacherBySubjectName = [];
        $this->primaryClassTeacher = null;
        $this->classOrderById = [];
    }

    /**
     * @return list<User>
     */
    private function seedStudentsForClass(SchoolClass $class, AcademicYear $year): array
    {
        $students = [];
        $slug = School::find($this->schoolId)?->slug ?? 'ecole';

        for ($i = 0; $i < $this->studentsPerClass; $i++) {
            $gender = random_int(0, 1) === 1 ? 'M' : 'F';
            $fn = $gender === 'M'
                ? $this->firstNamesMale[array_rand($this->firstNamesMale)]
                : $this->firstNamesFemale[array_rand($this->firstNamesFemale)];
            $ln = $this->lastNames[array_rand($this->lastNames)];
            $seq = $this->globalStudentSeq++;
            $identifier = 'E'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
            $emailLocal = Str::slug($fn.'.'.$ln, '.');
            $email = "{$emailLocal}.{$seq}@{$slug}.edu.sn";

            $students[] = User::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'name' => $fn.' '.$ln,
                'email' => $email,
                'password' => Hash::make('password'),
                'identifier' => $identifier,
                'user_id' => $identifier,
                'role' => User::ROLE_STUDENT,
                'status' => User::STATUS_APPROVED,
                'class_id' => $class->id,
                'gender' => $gender === 'M' ? 'masculin' : 'féminin',
                'city' => 'Dakar',
                'country' => 'Sénégal',
                'address' => $this->randomQuartier(),
                'guardian_phone' => '+221 77 '.random_int(100, 999).' '.random_int(10, 99).' '.random_int(10, 99),
                'email_verified_at' => now(),
            ]);
        }

        return $students;
    }

    private function linkTeachersToClassPartial(SchoolClass $class): void
    {
        $level = $class->level;
        if (! $level) {
            return;
        }

        foreach ($level->subjects as $subject) {
            if (! $this->fullTeacherCoverage && random_int(1, 100) <= 16) {
                continue;
            }

            $teacher = $this->teacherBySubjectName[$subject->name] ?? null;
            if (! $teacher) {
                continue;
            }

            $class->teachers()->syncWithoutDetaching([$teacher->id]);
            TeacherAssignment::withoutGlobalScopes()->firstOrCreate([
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $class->academic_year_id,
            ], ['school_id' => $this->schoolId]);

            $class->subjects()->syncWithoutDetaching([$subject->id]);
        }
    }

    /**
     * @param  list<Subject>|null  $subjectsOverride
     */
    private function seedSchedulesForClass(SchoolClass $class, ?array $subjectsOverride = null): void
    {
        $subjects = $subjectsOverride
            ? collect($subjectsOverride)
            : ($class->level?->subjects ?? collect());

        if ($subjects->isEmpty()) {
            return;
        }

        $slots = [
            ['d' => 1, 'start' => '08:00:00', 'end' => '09:00:00'],
            ['d' => 1, 'start' => '10:00:00', 'end' => '11:00:00'],
            ['d' => 2, 'start' => '08:00:00', 'end' => '09:30:00'],
            ['d' => 3, 'start' => '14:00:00', 'end' => '15:00:00'],
            ['d' => 4, 'start' => '09:00:00', 'end' => '10:00:00'],
            ['d' => 5, 'start' => '08:00:00', 'end' => '09:00:00'],
        ];

        $ordered = $subjects->values();
        $usePrimaryForAll = PrimaryClassTeacherProvisioner::isPrimaireLevel($class->level)
            && $this->primaryClassTeacher;

        foreach ($slots as $idx => $slot) {
            $subject = $ordered[$idx % $ordered->count()];
            $teacher = $usePrimaryForAll
                ? $this->primaryClassTeacher
                : ($this->teacherBySubjectName[$subject->name] ?? null);
            if (! $teacher) {
                $assignment = TeacherAssignment::withoutGlobalScopes()
                    ->where('class_id', $class->id)
                    ->where('subject_id', $subject->id)
                    ->first();
                $teacher = $assignment ? User::find($assignment->teacher_id) : null;
            }
            if (! $teacher) {
                continue;
            }

            Schedule::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day_of_week' => $slot['d'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'room' => 'Salle '.chr(65 + ($idx % 6)).($class->id % 20 + 1),
            ]);
        }
    }

    /**
     * @param  list<User>  $students
     */
    private function bulkGrades(array $students, AcademicYear $year): void
    {
        $start = Carbon::parse($year->start_date);
        $schedule = [
            ['sem' => 1, 'type' => 'devoir1', 'month' => 0],
            ['sem' => 1, 'type' => 'devoir2', 'month' => 2],
            ['sem' => 1, 'type' => 'composition', 'month' => 3],
            ['sem' => 1, 'type' => 'examen', 'month' => 4],
            ['sem' => 2, 'type' => 'devoir1', 'month' => 5],
            ['sem' => 2, 'type' => 'devoir2', 'month' => 6],
            ['sem' => 2, 'type' => 'composition', 'month' => 7],
            ['sem' => 2, 'type' => 'examen', 'month' => 8],
        ];

        $rows = [];
        foreach ($students as $student) {
            $class = SchoolClass::with('level.subjects')->find($student->class_id);
            if (! $class?->level) {
                continue;
            }
            $base = random_int(85, 155) / 10;

            foreach ($class->level->subjects as $subject) {
                $coef = (int) ($subject->pivot->coefficient ?? $subject->coefficient ?? 1);
                foreach ($schedule as $eval) {
                    $score = max(4, min(20, round($base + random_int(-25, 25) / 10, 2)));
                    $date = $start->copy()->addMonths($eval['month'])->day(random_int(5, 20));

                    $rows[] = [
                        'school_id' => $this->schoolId,
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'grade' => $score,
                        'type' => $eval['type'],
                        'semester' => $eval['sem'],
                        'academic_year_id' => $year->id,
                        'coefficient' => $coef,
                        'appreciation' => $this->appreciation($score),
                        'comments' => 'Évaluation '.$eval['type'].' — '.$score.'/20',
                        'date' => $date->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('grades')->insert($chunk);
        }
    }

    /**
     * @param  list<User>  $students
     * @param  list<Subject>  $modules
     */
    private function bulkGradesLmd(array $students, array $modules, AcademicYear $year): void
    {
        $start = Carbon::parse($year->start_date);
        $schedule = [
            ['sem' => 1, 'type' => 'devoir1', 'month' => 0],
            ['sem' => 1, 'type' => 'devoir2', 'month' => 1],
            ['sem' => 1, 'type' => 'composition', 'month' => 2],
            ['sem' => 2, 'type' => 'devoir1', 'month' => 5],
            ['sem' => 2, 'type' => 'devoir2', 'month' => 6],
            ['sem' => 2, 'type' => 'composition', 'month' => 7],
        ];

        foreach ($students as $student) {
            $base = random_int(100, 160) / 10;
            foreach ($modules as $subject) {
                foreach ($schedule as $eval) {
                    if (! in_array($eval['type'], SenegalGradeSequence::ORDER, true)) {
                        continue;
                    }
                    $score = max(0, min(20, $base + random_int(-20, 20) / 10));
                    Grade::withoutGlobalScopes()->create([
                        'school_id' => $this->schoolId,
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'grade' => $score,
                        'type' => $eval['type'],
                        'semester' => $eval['sem'],
                        'academic_year_id' => $year->id,
                        'coefficient' => (int) ($subject->coefficient ?? 1),
                        'date' => $start->copy()->addMonths($eval['month'])->addDays(random_int(0, 10)),
                        'appreciation' => $this->appreciation($score),
                    ]);
                }
            }
        }
    }

    /**
     * @param  list<User>  $students
     * @param  list<Subject>  $modules
     */
    private function bulkGradesClassicTypes(array $students, array $modules, AcademicYear $year): void
    {
        $this->bulkGradesLmd($students, $modules, $year);
    }

    /**
     * @param  list<User>  $students
     */
    private function bulkAttendances(array $students): void
    {
        $start = Carbon::parse('2025-01-01');
        $rows = [];

        foreach ($students as $student) {
            for ($w = 0; $w < 12; $w++) {
                if (random_int(1, 10) > 4) {
                    continue;
                }
                $dt = $start->copy()->addWeeks($w)->addDays(random_int(0, 4));
                $status = match (random_int(1, 10)) {
                    1, 2 => Attendance::STATUS_ABSENT,
                    3 => Attendance::STATUS_LATE,
                    default => Attendance::STATUS_PRESENT,
                };

                $rows[] = [
                    'school_id' => $this->schoolId,
                    'user_id' => $student->id,
                    'date' => $dt->format('Y-m-d'),
                    'status' => $status,
                    'reason' => $status === Attendance::STATUS_PRESENT ? null : 'Motif démo',
                    'justified' => $status !== Attendance::STATUS_PRESENT && random_int(0, 1) === 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 800) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }

    private function appreciation(float $note): string
    {
        return match (true) {
            $note >= 16 => 'Très bien',
            $note >= 14 => 'Bien',
            $note >= 12 => 'Assez bien',
            $note >= 10 => 'Passable',
            $note >= 8 => 'Insuffisant',
            default => 'Très insuffisant',
        };
    }

    private function randomQuartier(): string
    {
        $q = ['Médina', 'Plateau', 'HLM Grand Yoff', 'Ouakam', 'Liberté 6', 'Pikine', 'Guédiawaye'];

        return $q[array_rand($q)].', Dakar';
    }

    private function line(string $message): void
    {
        $this->output?->info($message);
    }
}
