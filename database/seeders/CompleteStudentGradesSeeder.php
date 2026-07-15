<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\SchoolBot\BulletinComputation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Crée UN élève de test avec un bulletin complet (Devoir 1 + Devoir 2 +
 * Composition, sur les 2 semestres, pour toutes les matières de sa classe) —
 * pour vérifier visuellement dans l'appli que la moyenne matière suit bien
 * la pondération 50 % devoirs / 50 % composition (voir BulletinComputation).
 *
 * Usage :
 *   php artisan db:seed --class=CompleteStudentGradesSeeder
 */
class CompleteStudentGradesSeeder extends Seeder
{
    /** Matières et coefficients utilisés pour le niveau de test (6ème). */
    private array $subjectsWithCoefficients = [
        'Français' => 4,
        'Mathématiques' => 4,
        'Histoire-Géographie' => 2,
        'Sciences de la Vie et de la Terre' => 2,
        'Physique-Chimie' => 2,
        'Anglais' => 2,
        'Éducation Physique et Sportive' => 1,
    ];

    public function run(): void
    {
        $school = School::withoutGlobalScopes()->first();
        if (! $school) {
            $this->command->error('Aucun établissement trouvé. Lancez d\'abord un seeder de base (ex: SoutenanceCollegeDemoSeeder).');

            return;
        }

        $academicYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->first();
        if (! $academicYear) {
            $this->command->error('Aucune année académique courante pour cet établissement.');

            return;
        }

        $level = Level::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('order')->first();
        if (! $level) {
            $this->command->error('Aucun niveau trouvé pour cet établissement.');

            return;
        }

        $class = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('level_id', $level->id)
            ->where('academic_year_id', $academicYear->id)
            ->orderBy('id')
            ->first();
        if (! $class) {
            $this->command->error("Aucune classe trouvée pour le niveau {$level->name}.");

            return;
        }

        $this->command->info("École : {$school->name} | Classe : {$class->name} | Année : {$academicYear->name}");

        // 1. Matières + coefficients (idempotent : n'écrase rien si déjà en place)
        $subjects = collect();
        foreach ($this->subjectsWithCoefficients as $name => $coefficient) {
            $subject = Subject::withoutGlobalScopes()->firstOrCreate(
                ['name' => $name, 'school_id' => $school->id],
                [
                    'code' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 4)),
                    'coefficient' => 1,
                    'is_active' => true,
                    'is_core_subject' => true,
                ]
            );
            $subjects->put($name, $subject);

            DB::table('level_subject')->updateOrInsert(
                ['level_id' => $level->id, 'subject_id' => $subject->id],
                ['coefficient' => $coefficient, 'is_compulsory' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            DB::table('class_subject')->updateOrInsert(
                ['class_id' => $class->id, 'subject_id' => $subject->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
        $this->command->info('✓ ' . $subjects->count() . ' matières rattachées au niveau ' . $level->name . ' avec coefficients.');

        // 2. Élève de test
        $student = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'bulletin.test@ecole-demo.sn'],
            [
                'name' => 'Bulletin Test',
                'first_name' => 'Bulletin',
                'last_name' => 'Test',
                'password' => Hash::make('Passer01'),
                'identifier' => 'BULLETIN-TEST-001',
                'role' => User::ROLE_STUDENT,
                'status' => User::STATUS_APPROVED,
                'class_id' => $class->id,
                'school_id' => $school->id,
                'date_of_birth' => now()->subYears(12),
                'email_verified_at' => now(),
            ]
        );
        $this->command->info("✓ Élève de test : {$student->name} ({$student->email} / Passer01), classe {$class->name}");

        // 3. Notes complètes : Devoir 1 + Devoir 2 + Composition, semestres 1 et 2
        Grade::where('user_id', $student->id)->delete();

        // Valeurs volontairement variées par matière (mais déterministes) pour
        // ressembler à un vrai bulletin plutôt qu'à des notes toutes identiques.
        $pattern = [
            1 => ['devoir1' => 12, 'devoir2' => 14, 'composition' => 16],
            2 => ['devoir1' => 9,  'devoir2' => 11, 'composition' => 13],
        ];
        $variation = 0;

        foreach ($subjects as $subject) {
            foreach ([1, 2] as $semester) {
                foreach ($pattern[$semester] as $type => $baseGrade) {
                    $grade = max(0, min(20, $baseGrade + ($variation % 3) - 1));

                    Grade::create([
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'school_id' => $school->id,
                        'academic_year_id' => $academicYear->id,
                        'semester' => $semester,
                        'type' => $type,
                        'grade' => $grade,
                        'coefficient' => 1,
                        'date' => $semester === 1 ? '2025-12-01' : '2026-04-01',
                        'comments' => ucfirst($type) . ' — ' . $subject->name,
                    ]);
                }
                $variation++;
            }
        }
        $this->command->info('✓ Notes créées : ' . (count($subjects) * 2 * 3) . ' notes (Devoir 1 + Devoir 2 + Composition × 2 semestres × ' . count($subjects) . ' matières).');

        // 4. Aperçu de la moyenne attendue, calculée par le VRAI service de
        // l'application (BulletinComputation) — pas un calcul parallèle qui
        // pourrait diverger silencieusement.
        $this->printPreview($student, $level, $school, $academicYear);
    }

    private function printPreview(User $student, Level $level, School $school, AcademicYear $academicYear): void
    {
        $bulletinComputation = app(BulletinComputation::class);
        $coefficients = $bulletinComputation->fetchLevelCoefficients($level);

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  APERÇU BULLETIN (calculé par BulletinComputation — le même');
        $this->command->info('  service utilisé par l\'application)');
        $this->command->info('═══════════════════════════════════════════════════════════');

        foreach ([1, 2] as $semester) {
            $grades = Grade::where('user_id', $student->id)
                ->where('semester', $semester)
                ->where('academic_year_id', $academicYear->id)
                ->with('subject')
                ->get();

            $bulletinData = $bulletinComputation->calculateBulletinData($grades, $level, $school, $coefficients);
            $generalAverage = $bulletinComputation->calculateWeightedAverage($bulletinData);

            $this->command->info("Semestre {$semester} :");
            foreach ($bulletinData as $row) {
                $this->command->line(sprintf(
                    '   %-40s D1=%-5s D2=%-5s Compo=%-5s → Moy. devoirs=%-5s Moy. matière=%s/20 (coef %s)',
                    $row['subject'],
                    $row['devoir1'],
                    $row['devoir2'],
                    $row['composition'],
                    $row['moyenne_devoirs'],
                    $row['moyenne_matiere'],
                    $row['coefficient']
                ));
            }
            $this->command->info("   → Moyenne générale attendue, semestre {$semester} : {$generalAverage}/20");
            $this->command->info('');
        }

        $this->command->info('Connectez-vous avec bulletin.test@ecole-demo.sn / Passer01 et comparez');
        $this->command->info('avec la moyenne affichée sur /student/bulletin.');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}
