<?php

namespace Database\Seeders;

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
        ]);
    }

    /**
     * Création des utilisateurs de test
     */
    protected function createTestUsers(): void
    {
        // Vérifier si les utilisateurs existent déjà
        if (User::where('email', 'admin@example.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'identifier' => 'A' . str_pad(1, 5, '0', STR_PAD_LEFT),
                'role' => 'admin',
                'status' => 'approved',
                'password' => bcrypt('password'),
            ]);
        }

        if (User::where('email', 'enseignant@example.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Enseignant Test',
                'email' => 'enseignant@example.com',
                'identifier' => 'T' . str_pad(1, 5, '0', STR_PAD_LEFT),
                'role' => 'teacher',
                'status' => 'approved',
                'password' => bcrypt('password'),
            ]);
        }

        if (User::where('email', 'eleve@example.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Élève Test',
                'email' => 'eleve@example.com',
                'identifier' => 'E' . str_pad(1, 5, '0', STR_PAD_LEFT),
                'role' => 'eleve',
                'status' => 'approved',
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
            if (User::where('email', $userData['email'])->doesntExist()) {
                User::factory()->create($userData);
            }
        }
    }
}
