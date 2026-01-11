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
        // User::factory(10)->create();

        // Création d'un administrateur
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'identifier' => 'A' . str_pad(1, 5, '0', STR_PAD_LEFT),
            'role' => 'admin',
            'status' => 'approved',
            'password' => bcrypt('password'),
        ]);

        // Création d'un enseignant
        User::factory()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'identifier' => 'P' . str_pad(1, 5, '0', STR_PAD_LEFT),
            'role' => 'teacher',
            'status' => 'approved',
            'password' => bcrypt('password'),
        ]);

        // Création d'un étudiant
        User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'identifier' => 'E' . str_pad(1, 5, '0', STR_PAD_LEFT),
            'role' => 'student',
            'status' => 'approved',
            'password' => bcrypt('password'),
        ]);
    }
}
