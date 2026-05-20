<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Création des utilisateurs de test
        $this->createTestUsers();

        // Appel des autres seeders
        $this->call([
            \Database\Seeders\DefaultClassesSeeder::class,
            \Database\Seeders\LevelsAndSubjectsSeeder::class,
            \Database\Seeders\SchoolClassesSeeder::class, // Création des classes
            \Database\Seeders\GradesTableSeeder::class, // Ajout du seeder des notes
            \Database\Seeders\AttendancesTableSeeder::class, // Ajout du seeder des présences
        ]);

        // Démo complète SN (7 classes, ~330 élèves, profs, notes 2 semestres) — destructif :
        // php artisan db:seed --class=SenegalSevenClassesDemoSeeder
    }

    /**
     * Création des utilisateurs de test
     */
    protected function createTestUsers(): void
    {
        $schoolId = School::query()->value('id');

        if (User::withoutGlobalScopes()->where('email', 'admin@example.com')->doesntExist()) {
            User::withoutGlobalScopes()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'identifier' => 'A' . str_pad(1, 5, '0', STR_PAD_LEFT),
                'role' => 'admin',
                'status' => 'approved',
                'school_id' => $schoolId,
                'password' => bcrypt('password'),
            ]);
        }

        if (User::withoutGlobalScopes()->where('email', 'enseignant@example.com')->doesntExist()) {
            User::withoutGlobalScopes()->create([
                'name' => 'Enseignant Test',
                'email' => 'enseignant@example.com',
                'identifier' => 'T' . str_pad(1, 5, '0', STR_PAD_LEFT),
                'role' => 'teacher',
                'status' => 'approved',
                'school_id' => $schoolId,
                'password' => bcrypt('password'),
            ]);
        }

        if (User::withoutGlobalScopes()->where('email', 'eleve@example.com')->doesntExist()) {
            User::withoutGlobalScopes()->create([
                'name' => 'Élève Test',
                'email' => 'eleve@example.com',
                'identifier' => 'E' . str_pad(1, 5, '0', STR_PAD_LEFT),
                'role' => 'eleve',
                'status' => 'approved',
                'school_id' => $schoolId,
                'password' => bcrypt('password'),
            ]);
        }

        // Autres utilisateurs de test uniques
        $testUsers = [
            [
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'identifier' => 'T' . str_pad(2, 5, '0', STR_PAD_LEFT),
                'role' => 'teacher',
                'status' => 'approved',
                'password' => bcrypt('password'),
            ],
            [
                'name' => 'Student User',
                'email' => 'student@example.com',
                'identifier' => 'E' . str_pad(2, 5, '0', STR_PAD_LEFT), // Changement de E00001 à E00002
                'role' => 'eleve',
                'status' => 'approved',
                'password' => bcrypt('password'),
            ]
        ];

        foreach ($testUsers as $userData) {
            if (User::withoutGlobalScopes()->where('email', $userData['email'])->doesntExist()) {
                User::withoutGlobalScopes()->create(array_merge($userData, ['school_id' => $schoolId]));
            }
        }
    }
}
