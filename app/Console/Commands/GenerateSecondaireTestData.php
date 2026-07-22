<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Support\SenegalGradeSequence;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Génère un jeu de données complet et réaliste pour le collège/lycée
 * (6ème → Terminale) d'un établissement déjà passé en type Mixte/Collège/
 * Lycée (voir SchoolSubjectProvisioner pour le provisioning niveaux/
 * matières) — classes, élèves, enseignants, affectations, notes D1/D2/
 * Composition sur les 2 semestres, pour tester bulletins/classements/
 * moyennes sans rien remplir à la main.
 *
 * N'écrase jamais rien : classes/élèves/affectations/notes déjà présents
 * sont détectés et laissés intacts (firstOrCreate / skip si des notes
 * existent déjà pour la combinaison élève+matière+semestre+type).
 */
class GenerateSecondaireTestData extends Command
{
    protected $signature = 'school:generate-secondaire-test-data
                            {school_id=1 : ID de l\'établissement}
                            {--students=20 : Élèves par classe}
                            {--dry-run : Affiche le résumé sans rien enregistrer}';

    protected $description = 'Génère classes, élèves, enseignants et notes complètes pour le collège/lycée (6ème à Terminale) d\'un établissement de test';

    /** @var list<string> */
    private const LEVEL_NAMES = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale'];

    /** Âge approximatif en début de 6ème ; +1 an par niveau. */
    private const BASE_AGE = 11;

    /** @var list<string> */
    private array $firstNamesMale = [
        'Mamadou', 'Ibrahima', 'Ousmane', 'Moussa', 'Cheikh', 'Abdoulaye', 'Modou', 'Alioune',
        'Assane', 'Babacar', 'Serigne', 'Pape', 'Malick', 'Youssou', 'Lamine', 'Amadou', 'Elie',
        'Souleymane', 'Ndiaga', 'El Hadji', 'Mor', 'Demba', 'Boubacar', 'Aliou', 'Thierno', 'Abdoul Aziz' , 'Pape Samba',
    ];

    /** @var list<string> */
    private array $firstNamesFemale = [
        'Fatou', 'Aminata', 'Aïssatou', 'Khadija', 'Awa', 'Bineta', 'Seynabou', 'Ndeye',
        'Mariama', 'Coumba', 'Adja', 'Astou', 'Rokhaya', 'Sokhna', 'Dieynaba', 'Yacine', 
        'Marième', 'Ramatoulaye', 'Aida', 'Penda', 'Anta', 'Fatimata', 'Absa', 'Khady', 'Ngoné',
    ];

    /** @var list<string> */
    private array $lastNames = [
        'Diop', 'Ndiaye', 'Fall', 'Gueye', 'Ba', 'Sarr', 'Diagne', 'Sy', 'Cissé', 'Faye',
        'Niang', 'Mbaye', 'Sow', 'Kane', 'Diallo', 'Camara', 'Diouf', 'Thiam', 'Seck', 'Wade',
        'Sène', 'Ngom', 'Diatta', 'Dieng', 'Sagna', 'Toure', 'Coly', 'Badji', 'Tine', 'Lo', 'Reyara',
    ];

    private int $studentSeq = 0;

    private int $teacherSeq = 0;

    public function handle(): int
    {
        $schoolId = (int) $this->argument('school_id');
        $studentsPerClass = (int) $this->option('students');
        $dryRun = (bool) $this->option('dry-run');

        $school = School::find($schoolId);
        if (! $school) {
            $this->error('Établissement introuvable.');

            return self::FAILURE;
        }

        if (! in_array($school->establishment_type, [School::TYPE_MIXTE, School::TYPE_COLLEGE, School::TYPE_LYCEE], true)) {
            $this->error(
                "Le type d'établissement actuel ({$school->establishment_type}) ne comprend pas de collège/lycée. ".
                'Passez d\'abord l\'établissement en Mixte (ou Collège/Lycée) depuis Super Admin pour provisionner les niveaux et matières.'
            );

            return self::FAILURE;
        }

        $academicYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        if (! $academicYear) {
            $this->error('Aucune année scolaire courante pour cet établissement.');

            return self::FAILURE;
        }

        $levels = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->whereIn('name', self::LEVEL_NAMES)
            ->get()
            ->keyBy('name');

        $missingLevels = array_diff(self::LEVEL_NAMES, $levels->keys()->all());
        if ($missingLevels !== []) {
            $this->error('Niveaux manquants : '.implode(', ', $missingLevels).' — provisionnez le type Mixte/Collège/Lycée d\'abord.');

            return self::FAILURE;
        }

        $this->studentSeq = (int) User::withoutGlobalScopes()
            ->where('identifier', 'like', 'E20__%')
            ->get()
            ->map(fn ($u) => (int) preg_replace('/\D/', '', $u->identifier))
            ->max();
        $this->teacherSeq = (int) User::withoutGlobalScopes()
            ->where('identifier', 'like', 'P20__%')
            ->get()
            ->map(fn ($u) => (int) preg_replace('/\D/', '', $u->identifier))
            ->max();

        $this->info("Établissement : {$school->name} — année {$academicYear->name}");
        $this->info('Génération : '.count(self::LEVEL_NAMES)." classes × {$studentsPerClass} élèves.");

        $stats = [
            'classes_created' => 0,
            'classes_existing' => 0,
            'students_created' => 0,
            'teachers_created' => 0,
            'assignments_created' => 0,
            'grades_created' => 0,
            'grades_skipped' => 0,
        ];

        try {
            DB::transaction(function () use ($school, $academicYear, $levels, $studentsPerClass, $dryRun, &$stats) {
            $teacherBySubject = [];

            foreach (self::LEVEL_NAMES as $levelIndex => $levelName) {
                $level = $levels[$levelName];
                $subjects = $level->subjects()->get();

                $class = SchoolClass::withoutGlobalScopes()->firstOrNew([
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'level_id' => $level->id,
                    'name' => $levelName,
                ]);
                $wasNewClass = ! $class->exists;
                if ($wasNewClass) {
                    $class->capacity = 35;
                    $class->save();
                    $stats['classes_created']++;
                } else {
                    $stats['classes_existing']++;
                }

                $students = $this->studentsForClass($class, $school, $levelIndex, $studentsPerClass, $stats);

                foreach ($subjects as $subject) {
                    $teacher = $teacherBySubject[$subject->id] ??= $this->teacherForSubject($subject, $school, $stats);

                    $assignment = TeacherAssignment::firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $academicYear->id,
                    ], [
                        'school_id' => $school->id,
                        'is_active' => true,
                    ]);
                    if ($assignment->wasRecentlyCreated) {
                        $stats['assignments_created']++;
                    }
                }

                $this->generateGrades($students, $subjects, $level, $academicYear, $school, $stats);

                $this->line("  {$levelName} : ".($wasNewClass ? 'classe créée' : 'classe existante')." — {$students->count()} élève(s), {$subjects->count()} matière(s).");
            }

                if ($dryRun) {
                    throw new \RuntimeException('dry-run-rollback');
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'dry-run-rollback') {
                throw $e;
            }
        }

        $this->newLine();
        $this->info($dryRun ? 'Mode --dry-run : rien n\'a été enregistré.' : 'Terminé.');
        $this->table(['Élément', 'Résultat'], [
            ['Classes créées', $stats['classes_created'].' (+ '.$stats['classes_existing'].' déjà existantes)'],
            ['Élèves créés', $stats['students_created']],
            ['Enseignants créés', $stats['teachers_created']],
            ['Affectations créées', $stats['assignments_created']],
            ['Notes créées', $stats['grades_created'].' (+ '.$stats['grades_skipped'].' déjà existantes, conservées)'],
            ['Bulletins disponibles', $stats['students_created'] > 0 || $stats['grades_created'] > 0 ? (count(self::LEVEL_NAMES) * $studentsPerClass).' (1 par élève, 2 semestres + annuel)' : 'voir élèves existants'],
        ]);

        return self::SUCCESS;
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function studentsForClass(SchoolClass $class, School $school, int $levelIndex, int $count, array &$stats): \Illuminate\Support\Collection
    {
        $existing = User::withoutGlobalScopes()
            ->where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->get();

        if ($existing->count() >= $count) {
            return $existing;
        }

        $toCreate = $count - $existing->count();
        $birthYear = now()->year - (self::BASE_AGE + $levelIndex);
        $created = collect();

        for ($i = 0; $i < $toCreate; $i++) {
            $isMale = random_int(0, 1) === 1;
            $firstName = $isMale
                ? $this->firstNamesMale[array_rand($this->firstNamesMale)]
                : $this->firstNamesFemale[array_rand($this->firstNamesFemale)];
            $lastName = $this->lastNames[array_rand($this->lastNames)];
            $seq = ++$this->studentSeq;
            $identifier = 'E'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);

            // school_id/role/user_id ne sont pas dans $fillable (protection
            // volontaire du modèle) : affectation directe pour contourner
            // le mass-assignment guard, comme LoadTestSchoolFactory.
            $student = new User;
            $student->forceFill([
                'school_id' => $school->id,
                'name' => $firstName.' '.$lastName,
                'password' => Hash::make('password'),
                'identifier' => $identifier,
                'user_id' => $identifier,
                'role' => User::ROLE_STUDENT,
                'status' => User::STATUS_APPROVED,
                'class_id' => $class->id,
                'gender' => $isMale ? 'masculin' : 'féminin',
                'date_of_birth' => sprintf('%d-%02d-%02d', $birthYear, random_int(1, 12), random_int(1, 28)),
                'city' => 'Dakar',
                'country' => 'Sénégal',
            ])->save();
            $created->push($student);
            $stats['students_created']++;
        }

        return $existing->merge($created);
    }

    private function teacherForSubject(Subject $subject, School $school, array &$stats): User
    {
        $existing = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('role', User::ROLE_TEACHER)
            ->whereHas('subjects', fn ($q) => $q->where('subjects.id', $subject->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        $isMale = random_int(0, 1) === 1;
        $firstName = $isMale
            ? $this->firstNamesMale[array_rand($this->firstNamesMale)]
            : $this->firstNamesFemale[array_rand($this->firstNamesFemale)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        $seq = ++$this->teacherSeq;
        $identifier = 'P'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
        $emailLocal = Str::slug($firstName.'.'.$lastName, '.');

        $teacher = new User;
        $teacher->forceFill([
            'school_id' => $school->id,
            'name' => $firstName.' '.$lastName,
            'email' => "{$emailLocal}.{$seq}@ecole.sn",
            'password' => Hash::make('password'),
            'identifier' => $identifier,
            'user_id' => $identifier,
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'city' => 'Dakar',
            'country' => 'Sénégal',
        ])->save();

        $teacher->subjects()->syncWithoutDetaching([$subject->id]);
        $stats['teachers_created']++;

        return $teacher;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $students
     * @param  \Illuminate\Support\Collection<int, Subject>  $subjects
     */
    private function generateGrades(
        \Illuminate\Support\Collection $students,
        \Illuminate\Support\Collection $subjects,
        Level $level,
        AcademicYear $academicYear,
        School $school,
        array &$stats
    ): void {
        if ($students->isEmpty() || $subjects->isEmpty()) {
            return;
        }

        $coefficients = DB::table('level_subject')
            ->where('level_id', $level->id)
            ->pluck('coefficient', 'subject_id');

        // Profil de performance par élève (0-20), réparti par quartile
        // pour garantir des excellents / bons / moyens / faibles dans
        // chaque classe — cf. GenerateFakeGradesForClass pour le primaire.
        $shuffled = $students->shuffle()->values();
        $count = $shuffled->count();
        $excellentCut = (int) ceil($count * 0.2);
        $bonCut = $excellentCut + (int) ceil($count * 0.3);
        $moyenCut = $bonCut + (int) ceil($count * 0.3);

        $profiles = [];
        foreach ($shuffled as $index => $student) {
            [$min, $max] = match (true) {
                $index < $excellentCut => [15.5, 18.5],
                $index < $bonCut => [13.0, 15.4],
                $index < $moyenCut => [9.5, 12.9],
                default => [4.0, 9.4],
            };
            $profiles[$student->id] = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        }

        $existingKeys = Grade::where('academic_year_id', $academicYear->id)
            ->whereIn('user_id', $students->pluck('id'))
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->get(['user_id', 'subject_id', 'semester', 'type'])
            ->map(fn ($g) => "{$g->user_id}-{$g->subject_id}-{$g->semester}-{$g->type}")
            ->flip();

        $today = now();

        foreach ($students as $student) {
            $center = $profiles[$student->id];

            foreach ($subjects as $subject) {
                $coefficient = (float) ($coefficients[$subject->id] ?? 1);
                $subjectJitter = $this->randomFloat(-1.5, 1.5);

                foreach ([1, 2] as $semester) {
                    $semesterShift = $semester === 2 ? $this->randomFloat(-0.6, 0.8) : 0.0;

                    foreach (SenegalGradeSequence::ORDER as $i => $type) {
                        $key = "{$student->id}-{$subject->id}-{$semester}-{$type}";
                        if (isset($existingKeys[$key])) {
                            $stats['grades_skipped']++;

                            continue;
                        }

                        $noise = $this->randomFloat(-1.0, 1.0);
                        $value = $center + $subjectJitter + $semesterShift + $noise;
                        $value = max(0, min(20, $value));
                        $value = round($value * 4) / 4;

                        $daysAgo = (2 - $semester) * 100 + (2 - $i) * 20;

                        Grade::create([
                            'user_id' => $student->id,
                            'subject_id' => $subject->id,
                            'grade' => $value,
                            'type' => $type,
                            'date' => $today->copy()->subDays(max($daysAgo, 0))->toDateString(),
                            'coefficient' => $coefficient,
                            'semester' => $semester,
                            'academic_year_id' => $academicYear->id,
                            'school_id' => $school->id,
                        ]);
                        $stats['grades_created']++;
                    }
                }
            }
        }
    }

    private function randomFloat(float $min, float $max): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}
