<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Support\LoadTest\LoadTestDataPurge;
use App\Support\LoadTest\LoadTestSchoolFactory;
use Illuminate\Database\Seeder;

/**
 * Jeu de données charge — 10 établissements sénégalais (démo pro).
 *
 * Conserve uniquement le super administrateur (.env).
 * Supprime toutes les écoles et données métier existantes.
 *
 * php artisan db:seed --class=SuperAdminSeeder
 * php artisan db:seed --class=SenegalTenSchoolsLoadTestSeeder
 *
 * Mot de passe commun : password
 */
class SenegalTenSchoolsLoadTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('⚠️  Purge complète : établissements, élèves, enseignants, notes, emplois du temps…');
        $this->command->warn('    Le super administrateur (SUPER_ADMIN_EMAIL) est conservé.');
        $this->command->newLine();

        LoadTestDataPurge::run();

        $factory = (new LoadTestSchoolFactory)->setOutput($this->command->getOutput());

        $this->command->info('Création des 5 établissements scolaires classiques…');

        $factory->seedClassic([
            'slug' => 'ep-blaise-diagne',
            'name' => 'École primaire Blaise Diagne',
            'type' => School::TYPE_PRIMAIRE,
            'city' => 'Dakar',
            'address' => 'Médina, Dakar',
            'admin_email' => 'admin@ep-blaise-diagne.edu.sn',
        ]);

        $factory->seedClassic([
            'slug' => 'cem-liberte-6',
            'name' => 'CEM Liberté 6',
            'type' => School::TYPE_COLLEGE,
            'city' => 'Dakar',
            'admin_email' => 'admin@cem-liberte-6.edu.sn',
        ]);

        $factory->seedClassic([
            'slug' => 'lycee-limamou-laye',
            'name' => 'Lycée Limamou Laye',
            'type' => School::TYPE_LYCEE,
            'city' => 'Dakar',
            'admin_email' => 'admin@lycee-limamou-laye.edu.sn',
        ]);

        $factory->seedClassic([
            'slug' => 'ecole-seydou-nourou-tall',
            'name' => 'École Mixte Seydou Nourou Tall',
            'type' => School::TYPE_MIXTE,
            'city' => 'Thiès',
            'admin_email' => 'admin@ecole-seydou-nourou-tall.edu.sn',
        ]);

        $factory->seedFormationClassic([
            'slug' => 'cfp-senegal-numerique',
            'name' => 'CFP Sénégal Numérique',
            'city' => 'Dakar',
            'admin_email' => 'admin@cfp-senegal-numerique.edu.sn',
        ]);

        $this->command->newLine();
        $this->command->info('Création des 5 établissements de formation LMD…');

        $lmdSchools = [
            [
                'slug' => 'esp-dakar-demo',
                'name' => 'École Supérieure Polytechnique de Dakar',
                'city' => 'Dakar',
                'admin_email' => 'admin@esp-dakar-demo.edu.sn',
                'dept' => 'Génie informatique',
                'filiere' => 'Licence Génie Logiciel',
                'promotions' => ['L1 GL 2024', 'L2 GL 2024'],
            ],
            [
                'slug' => 'ism-dakar-demo',
                'name' => 'Institut Supérieur de Management',
                'city' => 'Dakar',
                'admin_email' => 'admin@ism-dakar-demo.edu.sn',
                'dept' => 'Management',
                'filiere' => 'Licence Marketing',
                'promotions' => ['L1 Marketing 2024', 'L2 Marketing 2024'],
            ],
            [
                'slug' => 'iam-dakar-demo',
                'name' => 'Institut Africain de Management',
                'city' => 'Dakar',
                'admin_email' => 'admin@iam-dakar-demo.edu.sn',
                'dept' => 'Finance',
                'filiere' => 'Licence Finance',
                'promotions' => ['L1 Finance 2024', 'L2 Finance 2024'],
            ],
            [
                'slug' => 'ufr-ugb-demo',
                'name' => 'UFR Sciences et Technologies — UGB (démo)',
                'city' => 'Saint-Louis',
                'admin_email' => 'admin@ufr-ugb-demo.edu.sn',
                'dept' => 'Sciences',
                'filiere' => 'Licence Mathématiques',
                'promotions' => ['L1 Maths 2024', 'L2 Maths 2024'],
            ],
            [
                'slug' => 'ucad-fst-demo',
                'name' => 'UCAD — Faculté des Sciences et Techniques (démo)',
                'city' => 'Dakar',
                'admin_email' => 'admin@ucad-fst-demo.edu.sn',
                'dept' => 'Informatique',
                'filiere' => 'Licence Informatique',
                'promotions' => ['L1 Info 2024', 'L2 Info 2024'],
            ],
        ];

        foreach ($lmdSchools as $cfg) {
            $factory->seedFormationLmd($cfg);
        }

        $schoolCount = School::count();
        $studentCount = User::withoutGlobalScopes()->whereIn('role', User::ROLE_STUDENT_ALIASES)->count();
        $teacherCount = User::withoutGlobalScopes()->whereIn('role', User::ROLE_TEACHER_ALIASES)->count();
        $gradeCount = \App\Models\Grade::withoutGlobalScopes()->count();

        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  Jeu de données charge installé');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->line("  Établissements : {$schoolCount}");
        $this->command->line("  Élèves / étudiants : {$studentCount}");
        $this->command->line("  Enseignants : {$teacherCount}");
        $this->command->line("  Notes : {$gradeCount}");
        $this->command->line('  Année complète (notes + élèves) : 2024-2025 (clôturée)');
        $this->command->line('  Année courante : 2025-2026 (classes + profs, élèves via « Passages en classe supérieure »)');
        $this->command->newLine();
        $this->command->info('  Super admin : '.env('SUPER_ADMIN_EMAIL').' / (mot de passe .env)');
        $this->command->info('  Admins établissement & élèves : mot de passe `password`');
        $this->command->info('  Exemple admin primaire : admin@ep-blaise-diagne.edu.sn');
        $this->command->info('  Emails élèves : prenom.nom.{id}@{slug-ecole}.edu.sn');
    }
}
