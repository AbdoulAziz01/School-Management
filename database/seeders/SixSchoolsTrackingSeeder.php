<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Support\LoadTest\LoadTestSchoolFactory;
use Illuminate\Database\Seeder;

/**
 * 6 établissements de suivi (sans purge) — 15 élèves/classe, profs sur toutes les matières.
 *
 * Primaire · Collège · Lycée · Mixte · Formation LMD · Formation sans LMD
 *
 * php artisan db:seed --class=SixSchoolsTrackingSeeder
 *
 * Mot de passe : password
 */
class SixSchoolsTrackingSeeder extends Seeder
{
    private const STUDENTS_PER_CLASS = 15;

    /** @var list<array{slug: string, label: string, seed: string, config: array<string, mixed>}> */
    private const SCHOOLS = [
        [
            'slug' => 'suivi-primaire',
            'label' => 'Primaire',
            'seed' => 'classic',
            'config' => [
                'slug' => 'suivi-primaire',
                'name' => 'École primaire Suivi — CI à CM2',
                'type' => School::TYPE_PRIMAIRE,
                'city' => 'Dakar',
                'address' => 'Médina, Dakar',
                'admin_email' => 'admin@suivi-primaire.edu.sn',
            ],
        ],
        [
            'slug' => 'suivi-college',
            'label' => 'Collège',
            'seed' => 'classic',
            'config' => [
                'slug' => 'suivi-college',
                'name' => 'CEM Suivi — 6ème à 3ème',
                'type' => School::TYPE_COLLEGE,
                'city' => 'Dakar',
                'admin_email' => 'admin@suivi-college.edu.sn',
            ],
        ],
        [
            'slug' => 'suivi-lycee',
            'label' => 'Lycée',
            'seed' => 'classic',
            'config' => [
                'slug' => 'suivi-lycee',
                'name' => 'Lycée Suivi — Seconde à Terminale',
                'type' => School::TYPE_LYCEE,
                'city' => 'Dakar',
                'admin_email' => 'admin@suivi-lycee.edu.sn',
            ],
        ],
        [
            'slug' => 'suivi-mixte',
            'label' => 'Mixte',
            'seed' => 'classic',
            'config' => [
                'slug' => 'suivi-mixte',
                'name' => 'École mixte Suivi — primaire + collège + lycée',
                'type' => School::TYPE_MIXTE,
                'city' => 'Thiès',
                'admin_email' => 'admin@suivi-mixte.edu.sn',
            ],
        ],
        [
            'slug' => 'suivi-formation-lmd',
            'label' => 'Formation LMD',
            'seed' => 'formation_lmd',
            'config' => [
                'slug' => 'suivi-formation-lmd',
                'name' => 'ESP Suivi — Formation LMD',
                'city' => 'Dakar',
                'admin_email' => 'admin@suivi-formation-lmd.edu.sn',
                'dept' => 'Génie informatique',
                'filiere' => 'Licence Génie Logiciel',
                'promotions' => ['L1 GL Suivi', 'L2 GL Suivi'],
            ],
        ],
        [
            'slug' => 'suivi-formation-classique',
            'label' => 'Formation sans LMD',
            'seed' => 'formation_classic',
            'config' => [
                'slug' => 'suivi-formation-classique',
                'name' => 'CFP Suivi — Formation sans LMD',
                'city' => 'Dakar',
                'admin_email' => 'admin@suivi-formation-classique.edu.sn',
            ],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Création des 6 établissements de suivi (sans suppression des données existantes)…');
        $this->command->line('  · '.self::STUDENTS_PER_CLASS.' élèves max par classe');
        $this->command->line('  · Professeurs assignés sur toutes les matières');
        $this->command->newLine();

        $factory = (new LoadTestSchoolFactory)
            ->setOutput($this->command->getOutput())
            ->setStudentsPerClass(self::STUDENTS_PER_CLASS)
            ->setFullTeacherCoverage(true);

        $created = 0;
        $skipped = 0;

        foreach (self::SCHOOLS as $def) {
            if (School::query()->where('slug', $def['slug'])->exists()) {
                $this->command->warn("  ⊘ {$def['label']} ({$def['slug']}) — déjà présent, ignoré.");
                $skipped++;

                continue;
            }

            $this->command->info("  → {$def['label']} : {$def['config']['name']}");

            match ($def['seed']) {
                'formation_lmd' => $factory->seedFormationLmd($def['config']),
                'formation_classic' => $factory->seedFormationClassic($def['config']),
                default => $factory->seedClassic($def['config']),
            };

            $created++;
        }

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Jeu de suivi — terminé');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->line("  Créés : {$created} · Ignorés (déjà là) : {$skipped}");
        $this->command->line('  Total établissements en base : '.School::count());
        $this->command->line('  Élèves : '.User::withoutGlobalScopes()->whereIn('role', User::ROLE_STUDENT_ALIASES)->count());
        $this->command->line('  Enseignants : '.User::withoutGlobalScopes()->whereIn('role', User::ROLE_TEACHER_ALIASES)->count());
        $this->command->newLine();
        $this->command->info('  Admins : admin@{slug}.edu.sn — mot de passe `password`');
        $this->command->info('  Année avec notes : 2024-2025 · Année courante : 2025-2026');
    }
}
