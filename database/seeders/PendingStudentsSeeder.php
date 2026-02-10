<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class PendingStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        
        // Liste de prénoms et noms africains
        $africanFirstNames = [
            'Aïssatou', 'Aminata', 'Fatou', 'Khady', 'Mariama', 'Ramatoulaye', 'Awa', 'Djenaba', 'Oumou', 'Aminou',
            'Abdou', 'Mamadou', 'Ibrahima', 'Ousmane', 'Cheikh', 'Moussa', 'Amadou', 'Seydou', 'Boubacar', 'Mamady'
        ];

        $africanLastNames = [
            'Diop', 'Ndiaye', 'Sow', 'Diallo', 'Gueye', 'Fall', 'Diagne', 'Ndiaye', 'Sarr', 'Sy',
            'Ba', 'Mbaye', 'Kane', 'Niang', 'Faye', 'Diouf', 'Thiam', 'Samb', 'Sene', 'Sakho'
        ];
        
        $this->command->info('Début de la création des 100 élèves en attente...');

        for ($i = 1; $i <= 100; $i++) {
            $studentNumber = str_pad($i, 5, '0', STR_PAD_LEFT);
            $user_id = 'E' . $studentNumber;
            
            // Vérifier si l'élève existe déjà
            if (User::where('user_id', $user_id)->exists()) {
                $this->command->info("L'élève {$user_id} existe déjà, passage au suivant...");
                continue;
            }

            // Générer un nom et prénom africain
            $firstName = $faker->randomElement($africanFirstNames);
            $lastName = $faker->randomElement($africanLastNames);
            $email = Str::slug($firstName . '.' . $lastName) . $i . '@example.com';

            // Créer l'élève en attente
            User::create([
                'user_id' => $user_id,
                'identifier' => $user_id,
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'eleve',
                'status' => 'pending',
                'class_id' => null, // Pas de classe affectée pour l'instant
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('Création des 100 élèves en attente terminée avec succès !');
        $this->command->info('Les élèves sont en attente d\'approbation par l\'administrateur.');
    }
}
