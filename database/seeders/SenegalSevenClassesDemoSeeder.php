<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Démo système éducatif sénégalais : 7 classes (collège → lycée),
 * élèves 45–50 par classe, professeurs par matière, notes 2 semestres (devoirs + compositions…),
 * absences / retards synthétiques, emplois du temps.
 *
 * ⚠️ Usage destructif local / staging : purge la plupart des données métier puis recrée.
 * Exemple :
 * php artisan migrate
 * php artisan db:seed --class=SenegalSevenClassesDemoSeeder
 *
 * Connexion après seed :
 * Admin : admin-sn@demo.ecole.sn / password
 */
class SenegalSevenClassesDemoSeeder extends Seeder
{
    private const ADMIN_EMAIL = 'admin-sn@demo.ecole.sn';

    /** Libellés de classes officiellement demandées (une section par niveau). */
    private array $classLabels = ['6e', '5e', '4e', '3e', 'Seconde', 'Première', 'Terminale'];

    private array $firstNamesMale = ['Mamadou', 'Abdoulaye', 'Ibrahima', 'Cheikh', 'Pape', 'Modou', 'Ousmane', 'Mor', 'Babacar', 'Djibril', 'Lamine', 'Souleymane'];

    private array $firstNamesFemale = ['Fatou', 'Aminata', 'Awa', 'Mariama', 'Khady', 'Ndèye', 'Bineta', 'Coumba', 'Rama', 'Aissatou', 'Soda', 'Mame Diarra'];

    private array $lastNames = ['Diop', 'Ndiaye', 'Fall', 'Sy', 'Gueye', 'Sow', 'Diallo', 'Thiam', 'Sarr', 'Mbaye', 'Faye', 'Kane'];

    /** Matière => [code, service] selon nomenclature proche officielle Sénégal — ICE = Initiations / compléments scientifiques & technologiques. */
    private array $subjectBlueprint = [
        'Français' => ['FR', 'Lettres'],
        'Anglais' => ['ANG', 'Langues vivantes'],
        'Mathématiques' => ['MATH', 'Sciences'],
        'Histoire-Géographie' => ['HG', 'SHS'],
        'PC' => ['PC', 'Sciences'],
        'SVT' => ['SVT', 'Sciences'],
        'ICE' => ['ICE', 'Technologie'],
        'Philosophie' => ['PHI', 'Lettres'],
        'EPS' => ['EPS', 'EPS'],
        'Arabe' => ['AR', 'Langues vivantes'],
        'Espagnol' => ['ES', 'Langues vivantes'],
    ];

    /** Coefficients par cycle (bulletin pondéré niveau/college vs lycée). */
    private array $coefCollege = [
        'Français' => 5,
        'Anglais' => 2,
        'Mathématiques' => 5,
        'Histoire-Géographie' => 4,
        'PC' => 4,
        'SVT' => 3,
        'ICE' => 2,
        'Philosophie' => 1,
        'EPS' => 1,
        'Arabe' => 2,
        'Espagnol' => 2,
    ];

    private array $coefLycee = [
        'Français' => 4,
        'Anglais' => 2,
        'Mathématiques' => 5,
        'Histoire-Géographie' => 3,
        'PC' => 4,
        'SVT' => 4,
        'ICE' => 2,
        'Philosophie' => 3,
        'EPS' => 1,
        'Arabe' => 2,
        'Espagnol' => 2,
    ];

    /** @var array<string, Subject> */
    private array $subjectByName = [];

    /** @var array<int, Level> order => model */
    private array $levels = [];

    /** @var array<string, User> keyed by teacher email */
    private array $teacherBySubjectName = [];

    public function run(): void
    {
        $year = (int) date('Y');
        $this->command->info('Nettoyage des données existantes (ordre compatible PostgreSQL / MySQL)…');
        $this->purgeAcademicData();

        $this->command->info('Création année scolaire 2025-2026 & ressources…');
        $academicYear = AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => "{$year}-10-01",
            'end_date' => ($year + 1).'-06-30',
            'is_current' => true,
        ]);

        $this->createSubjects();
        $this->createLevelsAndLinks();
        $this->createAdmin();
        $this->createTeachers($year);

        /** @var array<string, SchoolClass> keyed by level name */
        $classesByLevel = [];

        foreach (collect($this->levels)->sortKeys() as $level) {
            $label = $this->classLabels[$level->order - 1] ?? $level->name;
            $classesByLevel[$level->name] = SchoolClass::create([
                'name' => $label,
                'academic_year_id' => $academicYear->id,
                'level_id' => $level->id,
                'capacity' => 55,
            ]);
        }

        $studentNumber = 1;
        $allStudents = [];

        foreach (collect($this->levels)->sortKeys() as $level) {
            $class = $classesByLevel[$level->name];
            $count = random_int(45, 50);
            for ($i = 0; $i < $count; $i++) {
                $gender = random_int(0, 1) === 1 ? 'M' : 'F';
                $fn = $gender === 'M'
                    ? $this->firstNamesMale[array_rand($this->firstNamesMale)]
                    : $this->firstNamesFemale[array_rand($this->firstNamesFemale)];
                $ln = $this->lastNames[array_rand($this->lastNames)];
                $identifier = 'E'.$year.str_pad((string) $studentNumber, 4, '0', STR_PAD_LEFT);
                $studentNumber++;

                $ageRange = $this->ageRangeForLevelOrder($level->order);
                $dob = now()->subYears(random_int($ageRange[0], $ageRange[1]))->subDays(random_int(0, 300));

                $tier = $this->randomPerformanceTier();
                $conduct = $this->conductForTier($tier);
                $photo = 'https://ui-avatars.com/api/?background=f59e0b&color=fff&name='.urlencode($fn.' '.$ln);

                $student = User::create([
                    'name' => $fn.' '.$ln,
                    'email' => strtolower($identifier).'@demo.ecole.sn',
                    'password' => Hash::make('password'),
                    'identifier' => $identifier,
                    'role' => User::ROLE_STUDENT,
                    'status' => User::STATUS_APPROVED,
                    'class_id' => $class->id,
                    'date_of_birth' => $dob,
                    'phone' => null,
                    'address' => $this->randomQuartierDakar(),
                    'city' => 'Dakar',
                    'postal_code' => 'SN',
                    'country' => 'Sénégal',
                    'gender' => $gender === 'M' ? 'masculin' : 'féminin',
                    'guardian_phone' => '+221 77 '.random_int(100, 999).' '.random_int(10, 99).' '.random_int(10, 99),
                    'conduct_evaluation' => $conduct,
                    'profile_photo_path' => $photo,
                ]);

                $student->setAttribute('performance_tier', $tier);
                $allStudents[] = $student;
            }
        }

        $classCollection = collect($classesByLevel);

        foreach ($classCollection as $class) {
            $class->loadMissing(['level.subjects']);

            $this->linkTeachersToClass($class);

            foreach ($class->level?->subjects ?? [] as $subject) {
                $class->subjects()->syncWithoutDetaching([$subject->id]);
            }

            $this->seedSchedulesForClass($class->fresh(['level']));
        }

        $this->command->info('Notes (2 semestres : devoirs, interrogations, compositions, examens)…');
        $this->bulkGradesForStudents($allStudents, $academicYear);

        $this->command->info('Absences et retards (échantillon)…');
        $this->bulkAttendances($allStudents);

        $this->syncAssiduityFromAttendance($allStudents);

        $this->command->newLine();
        $this->command->info('Terminé. Admin : '.self::ADMIN_EMAIL.' / password');
        $this->command->info('Exemple élève / prof : même mot de passe `password`.');
    }

    private function purgeAcademicData(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            if (Schema::hasTable('grades')) {
                Grade::query()->delete();
            }
            if (Schema::hasTable('attendances')) {
                Attendance::query()->delete();
            }
            if (Schema::hasTable('schedules')) {
                Schedule::query()->delete();
            }
            foreach (['assignments', 'timetables', 'class_group_student', 'class_groups'] as $t) {
                if (Schema::hasTable($t)) {
                    DB::table($t)->delete();
                }
            }
            if (Schema::hasTable('teacher_subjects')) {
                DB::table('teacher_subjects')->delete();
            }
            if (Schema::hasTable('class_teacher')) {
                DB::table('class_teacher')->delete();
            }
            if (Schema::hasTable('class_subject')) {
                DB::table('class_subject')->delete();
            }
            if (Schema::hasTable('level_subject')) {
                DB::table('level_subject')->delete();
            }
            if (Schema::hasTable('teacher_assignments')) {
                DB::table('teacher_assignments')->delete();
            }

            $permTables = config('permission.table_names', []);
            if (! empty($permTables['model_has_roles']) && Schema::hasTable($permTables['model_has_roles'])) {
                DB::table($permTables['model_has_roles'])
                    ->where('model_type', User::class)
                    ->delete();
            }
            if (! empty($permTables['model_has_permissions']) && Schema::hasTable($permTables['model_has_permissions'])) {
                DB::table($permTables['model_has_permissions'])
                    ->where('model_type', User::class)
                    ->delete();
            }

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->delete();
            }

            User::query()->delete();
            if (Schema::hasTable('classes')) {
                SchoolClass::query()->delete();
            }
            if (Schema::hasTable('subjects')) {
                Subject::query()->delete();
            }
            if (Schema::hasTable('levels')) {
                Level::query()->delete();
            }
            if (Schema::hasTable('academic_years')) {
                AcademicYear::query()->delete();
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function createSubjects(): void
    {
        foreach ($this->subjectBlueprint as $name => $meta) {
            $this->subjectByName[$name] = Subject::create([
                'name' => $name,
                'code' => $meta[0],
                'coefficient' => 1,
                'description' => $name.' — programme démo SN',
                'department' => $meta[1],
                'is_active' => true,
                'hours_per_week' => in_array($name, ['EPS', 'ICE'], true) ? 2 : random_int(2, 5),
                'is_core_subject' => true,
            ]);
        }
    }

    private function createLevelsAndLinks(): void
    {
        $defs = [
            ['name' => '6ème', 'order' => 1, 'cycle' => 'college', 'label' => '6e'],
            ['name' => '5ème', 'order' => 2, 'cycle' => 'college', 'label' => '5e'],
            ['name' => '4ème', 'order' => 3, 'cycle' => 'college', 'label' => '4e'],
            ['name' => '3ème', 'order' => 4, 'cycle' => 'college', 'label' => '3e'],
            ['name' => 'Seconde', 'order' => 5, 'cycle' => 'lycee', 'serie' => null],
            ['name' => 'Première', 'order' => 6, 'cycle' => 'lycee', 'serie' => null],
            ['name' => 'Terminale', 'order' => 7, 'cycle' => 'lycee', 'serie' => null],
        ];

        $coefCollege = $this->coefCollege;
        $coefLycee = $this->coefLycee;

        foreach ($defs as $d) {
            $level = Level::create([
                'name' => $d['name'],
                'order' => $d['order'],
                'cycle' => $d['cycle'],
                'serie' => $d['serie'] ?? null,
            ]);
            $this->levels[$d['order']] = $level;

            $coefs = $d['cycle'] === 'college' ? $coefCollege : $coefLycee;
            foreach ($this->subjectByName as $sName => $subject) {
                $level->subjects()->attach($subject->id, [
                    'coefficient' => $coefs[$sName] ?? 1,
                    'is_compulsory' => true,
                ]);
            }
        }
    }

    private function createAdmin(): void
    {
        User::create([
            'name' => 'Administrateur Démonstration SN',
            'email' => self::ADMIN_EMAIL,
            'password' => Hash::make('password'),
            'identifier' => 'ADMIN-SN',
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'phone' => '+221 33 839 94 74',
            'address' => 'Direction, Dakar Plateau',
        ]);
    }

    private function createTeachers(int $year): void
    {
        $i = 1;
        foreach ($this->subjectByName as $name => $subject) {
            $fn = $this->firstNamesMale[array_rand($this->firstNamesMale)];
            $ln = $this->lastNames[array_rand($this->lastNames)];
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $subject->code));
            $identifier = 'P'.$year.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $email = 'prof.'.$slug.'.'.$i.'@demo.ecole.sn';
            $i++;

            $teacher = User::create([
                'name' => 'Professeur '.$fn.' '.$ln,
                'email' => $email,
                'password' => Hash::make('password'),
                'identifier' => $identifier,
                'role' => User::ROLE_TEACHER,
                'status' => User::STATUS_APPROVED,
                'phone' => '+221 76 '.random_int(200, 999).' '.random_int(10, 99).' '.random_int(10, 99),
                'address' => 'Enseignant — '.$subject->department,
                'date_of_birth' => now()->subYears(random_int(28, 55)),
            ]);

            $teacher->subjects()->attach($subject->id);
            $this->teacherBySubjectName[$name] = $teacher;
        }
    }

    private function linkTeachersToClass(SchoolClass $class): void
    {
        $level = $class->level;
        if ($level === null) {
            return;
        }

        foreach ($level->subjects as $subject) {
            $teacher = $this->teacherBySubjectName[$subject->name] ?? null;
            if (! $teacher) {
                continue;
            }

            $exists = DB::table('class_teacher')
                ->where('class_id', $class->id)
                ->where('teacher_id', $teacher->id)
                ->exists();
            if (! $exists) {
                $class->teachers()->attach($teacher->id);
            }

            TeacherAssignment::firstOrCreate([
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $class->academic_year_id,
            ]);
        }
    }

    /**
     * @param  User[]  $students
     */
    private function bulkGradesForStudents(array $students, AcademicYear $yearModel): void
    {
        $start = Carbon::parse($yearModel->start_date);

        $semesterDates = [
            1 => [
                'devoir1' => $start->copy()->addWeeks(4)->format('Y-m-d'),
                'interrogation' => $start->copy()->addWeeks(9)->format('Y-m-d'),
                'devoir2' => $start->copy()->addWeeks(13)->format('Y-m-d'),
                'composition' => $start->copy()->addMonths(4)->format('Y-m-d'),
                'examen' => $start->copy()->addMonths(4)->addWeeks(1)->format('Y-m-d'),
            ],
            2 => [
                'devoir1' => $start->copy()->addMonths(5)->addWeeks(1)->format('Y-m-d'),
                'interrogation' => $start->copy()->addMonths(6)->addWeeks(2)->format('Y-m-d'),
                'devoir2' => $start->copy()->addMonths(7)->addWeeks(1)->format('Y-m-d'),
                'composition' => $start->copy()->addMonths(8)->format('Y-m-d'),
                'examen' => $start->copy()->addMonths(8)->addWeeks(3)->format('Y-m-d'),
            ],
        ];

        $types = ['devoir1', 'interrogation', 'devoir2', 'composition', 'examen'];
        $rows = [];

        foreach ($students as $student) {
            /** @var User $student */
            $tier = $student->getAttribute('performance_tier') ?: 'medium';
            $base = ['strong' => 15.5, 'medium' => 11.8, 'weak' => 7.5][$tier];

            $class = SchoolClass::with('level.subjects')->find($student->class_id);
            if (! $class) {
                continue;
            }

            foreach ($class->level->subjects as $subject) {
                $coef = (int) $subject->pivot->coefficient;
                $subjectVariation = random_int(-6, 6) / 10;

                foreach ([1, 2] as $sem) {
                    foreach ($types as $type) {
                        $g = $base + $subjectVariation + random_int(-12, 12) / 10;
                        if ($type === 'composition' || $type === 'examen') {
                            $g -= random_int(0, 15) / 10;
                        }
                        if ($tier === 'strong' && $type === 'examen') {
                            $g += random_int(0, 10) / 10;
                        }
                        $g = max(4, min(20, round($g, 2)));

                        $rows[] = [
                            'user_id' => $student->id,
                            'subject_id' => $subject->id,
                            'grade' => $g,
                            'comments' => $this->gradeCommentFr($type, $g),
                            'appreciation' => $this->appreciation($g),
                            'date' => $semesterDates[$sem][$type] ?? ($semesterDates[$sem]['devoir2']),
                            'type' => $type === 'interrogation' ? 'interrogation' : $type,
                            'coefficient' => $coef,
                            'semester' => $sem,
                            'academic_year_id' => $yearModel->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        foreach (array_chunk($rows, 800) as $chunk) {
            DB::table('grades')->insert($chunk);
        }
    }

    private function bulkAttendances(array $students): void
    {
        $start = (now()->subMonths(2))->format('Y-m-d');
        $rows = [];
        $seenKeys = [];

        foreach ($students as $student) {
            for ($w = 0; $w < 18; $w++) {
                if (random_int(1, 10) > 6) {
                    continue;
                }
                $dt = date('Y-m-d', strtotime($start.' + '.$w.' week '.random_int(0, 4).' day'));
                $ukey = $student->id.'|'.$dt;
                if (isset($seenKeys[$ukey])) {
                    continue;
                }
                $seenKeys[$ukey] = true;

                $r = random_int(1, 10);
                if ($r <= 7) {
                    $status = Attendance::STATUS_PRESENT;
                    $reason = null;
                } elseif ($r === 8) {
                    $status = Attendance::STATUS_LATE;
                    $reason = 'Retard transport';
                } else {
                    $status = Attendance::STATUS_ABSENT;
                    $reason = random_int(1, 2) === 1 ? 'Maladie' : 'absence familiale';
                }

                $rows[] = [
                    'user_id' => $student->id,
                    'date' => $dt,
                    'status' => $status,
                    'reason' => $reason,
                    'justified' => $status !== Attendance::STATUS_PRESENT ? (bool) random_int(0, 1) : false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($rows, 900) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }

    private function syncAssiduityFromAttendance(array $students): void
    {
        foreach ($students as $student) {
            $total = DB::table('attendances')->where('user_id', $student->id)->count();
            $abs = DB::table('attendances')
                ->where('user_id', $student->id)
                ->where('status', Attendance::STATUS_ABSENT)->count();

            $late = DB::table('attendances')
                ->where('user_id', $student->id)
                ->where('status', Attendance::STATUS_LATE)->count();

            if ($total === 0) {
                continue;
            }
            $absRate = $abs / max(1, $total);
            if ($late >= 5 || $absRate > 0.35) {
                $text = 'Assiduité fragile : retards et/ou absences à surveiller.';
            } elseif ($absRate > 0.15 || $late >= 2) {
                $text = 'Assiduité correcte ; quelques retards signalés.';
            } else {
                $text = 'Très bonne assiduité générale.';
            }

            DB::table('users')->where('id', $student->id)->update(['assiduity_comment' => $text]);
            $student->assiduity_comment = $text;
        }
    }

    private function seedSchedulesForClass(SchoolClass $class): void
    {
        $level = $class->level ?? Level::find($class->level_id);
        if (! $level) {
            return;
        }
        $slots = [
            ['d' => 1, 'start' => '08:00:00', 'end' => '09:00:00'],
            ['d' => 1, 'start' => '09:00:00', 'end' => '10:00:00'],
            ['d' => 2, 'start' => '08:00:00', 'end' => '09:00:00'],
            ['d' => 2, 'start' => '14:00:00', 'end' => '15:00:00'],
            ['d' => 3, 'start' => '10:00:00', 'end' => '11:00:00'],
            ['d' => 4, 'start' => '08:00:00', 'end' => '09:30:00'],
            ['d' => 5, 'start' => '09:00:00', 'end' => '10:30:00'],
        ];

        $subjectsOrdered = $level->subjects()->orderBy('name')->get();
        foreach ($slots as $idx => $slot) {
            $subject = $subjectsOrdered[$idx % $subjectsOrdered->count()];
            $teacher = $this->teacherBySubjectName[$subject->name]
                ?? $this->teacherBySubjectName[array_rand($this->teacherBySubjectName)];

            DB::table('schedules')->insert([
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day_of_week' => $slot['d'],
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'room' => 'Salle '.chr(65 + ($idx % 5)).random_int(1, 40),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function randomPerformanceTier(): string
    {
        $r = random_int(1, 100);
        if ($r <= 18) {
            return 'strong';
        }
        if ($r <= 88) {
            return 'medium';
        }

        return 'weak';
    }

    private function conductForTier(string $tier): string
    {
        $samples = [
            'strong' => ['Comportement exemplaire.', 'Relations sociales très positives.', 'Bon esprit civique dans la classe.'],
            'medium' => ['Comportement correct dans l\'ensemble.', 'À encourager davantage.', 'Quelques bavardages en classe.'],
            'weak' => ['Comportement difficile : rappels au règlement intérieur nécessaires.', 'Doit gagner en maturité et respect.', 'Interactions parfois conflictuelles.'],
        ];

        $arr = $samples[$tier];

        return $arr[array_rand($arr)];
    }

    private function appreciation(float $note): string
    {
        if ($note >= 16) {
            return 'Très bien';
        }
        if ($note >= 14) {
            return 'Bien';
        }
        if ($note >= 12) {
            return 'Assez bien';
        }
        if ($note >= 10) {
            return 'Passable';
        }
        if ($note >= 8) {
            return 'Insuffisant';
        }

        return 'Très insuffisant';
    }

    private function gradeCommentFr(string $type, float $g): string
    {
        $t = ['devoir1' => 'Devoir classe 1', 'devoir2' => 'Devoir classe 2', 'composition' => 'Composition', 'examen' => 'Examen de fin de semestre', 'interrogation' => 'Interrogation écrite', 'exam' => 'Examen'];

        return ($t[$type] ?? ucfirst($type)).' — note '.$g.'/20.';
    }

    private function ageRangeForLevelOrder(int $order): array
    {
        return match ($order) {
            1 => [11, 12],
            2 => [12, 13],
            3 => [13, 14],
            4 => [14, 15],
            5 => [15, 16],
            6 => [16, 17],
            7 => [17, 19],
            default => [15, 18],
        };
    }

    private function randomQuartierDakar(): string
    {
        $parts = ['Médina', 'Plateau', 'HLM Grand Yoff', 'Ouakam', 'Sacré-Cœur', 'Liberté 6'];

        return $parts[array_rand($parts)];
    }

}
