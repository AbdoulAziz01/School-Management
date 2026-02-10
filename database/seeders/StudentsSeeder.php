<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentsSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer tous les IDs de classes existantes
        $classIds = SchoolClass::pluck('id')->toArray();
        
        if (empty($classIds)) {
            $this->command->error('Aucune classe trouvée dans la base de données. Veuillez d\'abord créer des classes.');
            return;
        }

        $this->command->info('Début de la création des 100 élèves...');

        for ($i = 1; $i <= 100; $i++) {
            $studentNumber = str_pad($i, 5, '0', STR_PAD_LEFT);
            $email = "eleve{$i}@example.com";
            
            // Vérifier si l'étudiant existe déjà
            if (User::where('email', $email)->exists()) {
                $this->command->info("L'élève {$email} existe déjà, passage au suivant...");
                continue;
            }

            // Créer l'élève
            User::create([
                'user_id' => 'E' . $studentNumber,
                'identifier' => 'E' . $studentNumber,
                'name' => 'Élève ' . $i,
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'eleve',
                'status' => User::STATUS_APPROVED,
                'class_id' => $classIds[array_rand($classIds)],
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('Création des 100 élèves terminée avec succès !');
    }
}
