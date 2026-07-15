<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class PendingTeachersSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        $schoolId = School::withoutGlobalScopes()->value('id');
        
        // Liste de prénoms et noms africains pour les professeurs
        $africanFirstNames = [
            'Mamadou', 'Aminata', 'Ibrahima', 'Fatou', 'Cheikh', 'Aïssatou', 'Ousmane', 'Khadija', 'Moussa', 'Ramatoulaye',
            'Boubacar', 'Aminou', 'Seydou', 'Mariama', 'Amadou', 'Djenaba', 'Abdoulaye', 'Oumou', 'Mamady', 'Awa'
        ];

        $africanLastNames = [
            'Diop', 'Ndiaye', 'Sow', 'Diallo', 'Gueye', 'Fall', 'Diagne', 'Sarr', 'Sy', 'Ba',
            'Mbaye', 'Kane', 'Niang', 'Faye', 'Diouf', 'Thiam', 'Samb', 'Sene', 'Sakho', 'Ndiaye'
        ];
        
        $this->command->info('Début de la création des 20 professeurs en attente...');

        for ($i = 1; $i <= 20; $i++) {
            $teacherNumber = str_pad($i, 5, '0', STR_PAD_LEFT);
            $user_id = 'P' . $teacherNumber;
            
            // Vérifier si le professeur existe déjà
            if (User::where('user_id', $user_id)->exists()) {
                $this->command->info("Le professeur {$user_id} existe déjà, passage au suivant...");
                continue;
            }

            // Générer un nom et prénom africain
            $firstName = $faker->randomElement($africanFirstNames);
            $lastName = $faker->randomElement($africanLastNames);
            $email = Str::slug($firstName . '.' . $lastName) . $i . '@example.com';

            // Créer le professeur en attente
            User::create([
                'user_id' => $user_id,
                'identifier' => $user_id,
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'teacher',
                'status' => 'pending',
                'subject' => null, // Aucune matière affectée pour l'instant
                'email_verified_at' => now(),
                'school_id' => $schoolId,
            ]);
        }

        $this->command->info('Création des 20 professeurs en attente terminée avec succès !');
        $this->command->info('Les professeurs sont en attente d\'approbation par l\'administrateur.');
    }
}
