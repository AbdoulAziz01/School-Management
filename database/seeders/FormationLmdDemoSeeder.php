<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\FormationDepartment;
use App\Models\FormationPromotion;
use App\Models\Grade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Support\FormationLevelResolver;
use App\Support\FormationLmdSettings;
use App\Support\SenegalGradeSequence;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Démo LMD — école de formation uniquement.
 *
 * php artisan db:seed --class=SuperAdminSeeder
 * php artisan db:seed --class=FormationLmdDemoSeeder
 *
 * Admin établissement : admin-formation@demo.sn / password
 */
class FormationLmdDemoSeeder extends Seeder
{
    private const ADMIN_EMAIL = 'admin-formation@demo.sn';

    private ?int $schoolId = null;

    public function run(): void
    {
        $this->purgeLabData();

        $school = School::query()->updateOrCreate(
            ['slug' => 'demo-formation-lmd'],
            [
                'name' => 'Institut Démo LMD',
                'code' => School::generateUniqueCode(),
                'establishment_type' => School::TYPE_FORMATION,
                'is_active' => true,
                'email' => 'contact@demo-formation.sn',
                'city' => 'Dakar',
                'address' => 'Dakar',
            ]
        );
        $this->schoolId = $school->id;

        FormationLmdSettings::defaults()->persistToSchool($school);

        $year2025 = AcademicYear::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'name' => '2025-2026',
            'start_date' => '2025-10-01',
            'end_date' => '2026-06-30',
            'is_current' => false,
            'is_closed' => true,
        ]);

        $year2026 = AcademicYear::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'name' => '2026-2027',
            'start_date' => '2026-10-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
            'is_closed' => false,
        ]);

        $school->update(['default_academic_year_id' => $year2026->id]);

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'school_id' => $this->schoolId,
                'name' => 'Admin Formation LMD',
                'identifier' => 'ADM-FORM-01',
                'user_id' => 'ADM-FORM-01',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $teacher = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'prof-formation@demo.sn'],
            [
                'school_id' => $this->schoolId,
                'name' => 'Professeur Démo',
                'identifier' => 'PROF-FORM-01',
                'user_id' => 'PROF-FORM-01',
                'role' => User::ROLE_TEACHER,
                'status' => User::STATUS_APPROVED,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $dept = FormationDepartment::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'name' => 'Informatique',
        ]);

        $filiere = 'Licence Informatique de Gestion';
        $formationYear = 'Licence 1';

        $level = FormationLevelResolver::resolve(
            $this->schoolId,
            $formationYear,
            $filiere,
            'licence'
        );

        $promo = FormationPromotion::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'formation_department_id' => $dept->id,
            'academic_year_id' => $year2025->id,
            'name' => 'Licence IG 2025',
            'filiere' => $filiere,
            'diploma_type' => 'licence',
            'formation_year' => $formationYear,
        ]);

        $class = SchoolClass::withoutGlobalScopes()->create([
            'school_id' => $this->schoolId,
            'academic_year_id' => $year2025->id,
            'formation_promotion_id' => $promo->id,
            'formation_department_id' => $dept->id,
            'name' => 'LIG1-1',
            'promotion_name' => $promo->name,
            'filiere' => $filiere,
            'diploma_type' => 'licence',
            'formation_year' => $formationYear,
            'level_id' => $level->id,
            'capacity' => 40,
        ]);

        $modules = [];
        foreach (
            [
                ['Algorithmique', 'ALGO', 30],
                ['Base de données', 'BDD', 40],
                ['Réseaux', 'RES', 25],
            ] as [$name, $code, $ccPercent]
        ) {
            $lmd = FormationLmdSettings::fromValidated([
                'cc_weight_percent' => $ccPercent,
                'exam_weight_percent' => 100 - $ccPercent,
                'passing_grade_min' => 10,
                'cc_grade_types' => ['devoir1', 'devoir2'],
                'exam_grade_types' => ['composition'],
            ]);

            $modules[] = Subject::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'name' => $name,
                'code' => $code,
                'coefficient' => 2,
                'lmd_settings' => $lmd->toArray(),
                'is_active' => true,
                'hours_per_week' => 4,
            ]);
        }

        $class->subjects()->sync(collect($modules)->pluck('id'));
        $class->teachers()->syncWithoutDetaching([$teacher->id]);
        $teacher->subjects()->syncWithoutDetaching(collect($modules)->pluck('id'));

        foreach ($modules as $subject) {
            TeacherAssignment::withoutGlobalScopes()->firstOrCreate([
                'teacher_id' => $teacher->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $year2025->id,
            ], [
                'school_id' => $this->schoolId,
            ]);
        }

        $students = [];
        for ($i = 1; $i <= 8; $i++) {
            $students[] = User::withoutGlobalScopes()->create([
                'school_id' => $this->schoolId,
                'name' => "Étudiant LMD {$i}",
                'email' => "etudiant-lmd-{$i}@demo.sn",
                'identifier' => 'ELMD'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'user_id' => 'ELMD'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'role' => User::ROLE_STUDENT,
                'status' => User::STATUS_APPROVED,
                'class_id' => $class->id,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        $gradesCreated = $this->seedRandomGrades($students, $modules, $year2025);

        $this->command?->info('Formation LMD démo créée.');
        $this->command?->info('École : Institut Démo LMD (formation)');
        $this->command?->info('Admin : '.self::ADMIN_EMAIL.' / password');
        $this->command?->info('Année source (terminée) : 2025-2026 — Licence 1 — groupe LIG1-1 — 8 élèves');
        $this->command?->info("Notes générées : {$gradesCreated} (D1, D2, Compo × S1/S2 par module)");
        $this->command?->info('Année cible (vide)     : 2026-2027 — pour tester le provisionnement');
        $this->command?->info('Élève 8 : moyennes basses (test redoublement). Élèves 1-7 : admissibles (≥ 10/20).');
    }

    /**
     * Notes aléatoires au format officiel (devoir1, devoir2, composition) pour affichage bulletin / passage.
     *
     * @param  list<User>  $students
     * @param  list<Subject>  $modules
     */
    private function seedRandomGrades(array $students, array $modules, AcademicYear $year): int
    {
        $start = Carbon::parse($year->start_date ?? '2025-10-01');
        $schedule = [
            ['sem' => 1, 'type' => 'devoir1', 'month_offset' => 0],
            ['sem' => 1, 'type' => 'devoir2', 'month_offset' => 1],
            ['sem' => 1, 'type' => 'composition', 'month_offset' => 2],
            ['sem' => 2, 'type' => 'devoir1', 'month_offset' => 5],
            ['sem' => 2, 'type' => 'devoir2', 'month_offset' => 6],
            ['sem' => 2, 'type' => 'composition', 'month_offset' => 7],
        ];

        $appreciations = ['Très bien', 'Bien', 'Assez bien', 'Passable', 'Insuffisant'];
        $created = 0;

        foreach ($students as $index => $student) {
            // Dernier élève : moyennes < 10 pour tester l'échec au passage
            $base = $index === count($students) - 1
                ? random_int(6, 9)
                : random_int(10, 16);

            foreach ($modules as $subject) {
                foreach ($schedule as $eval) {
                    if (! in_array($eval['type'], SenegalGradeSequence::ORDER, true)) {
                        continue;
                    }

                    $score = max(0, min(20, $base + random_int(-2, 2) + random_int(0, 1)));
                    $date = $start->copy()
                        ->addMonths($eval['month_offset'])
                        ->addDays(random_int(0, 10));

                    Grade::withoutGlobalScopes()->create([
                        'school_id' => $this->schoolId,
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'grade' => $score,
                        'type' => $eval['type'],
                        'semester' => $eval['sem'],
                        'academic_year_id' => $year->id,
                        'coefficient' => (int) ($subject->coefficient ?? 1),
                        'date' => $date,
                        'appreciation' => $appreciations[min(4, max(0, (int) floor($score / 4)))],
                    ]);

                    $created++;
                }
            }
        }

        return $created;
    }

    private function purgeLabData(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            if (Schema::hasTable('grades')) {
                Grade::query()->delete();
            }
            foreach (['attendances', 'schedules', 'teacher_assignments', 'class_subject', 'class_teacher', 'teacher_subjects'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }
            if (Schema::hasTable('formation_promotions')) {
                DB::table('formation_promotions')->delete();
            }
            if (Schema::hasTable('formation_departments')) {
                DB::table('formation_departments')->delete();
            }
            if (Schema::hasTable('classes')) {
                SchoolClass::query()->delete();
            }
            if (Schema::hasTable('subjects')) {
                Subject::query()->delete();
            }
            if (Schema::hasTable('levels')) {
                DB::table('levels')->delete();
            }
            if (Schema::hasTable('academic_years')) {
                AcademicYear::query()->delete();
            }
            if (Schema::hasTable('schools')) {
                School::query()->delete();
            }

            User::withoutGlobalScopes()
                ->where('role', '!=', User::ROLE_SUPER_ADMIN)
                ->delete();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
