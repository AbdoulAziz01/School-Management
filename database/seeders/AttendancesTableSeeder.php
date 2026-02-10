<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Exécuter les seeds de la base de données.
     */
    public function run(): void
    {
        // Récupérer tous les élèves
        $students = User::where('role', 'eleve')->get();
        
        if ($students->isEmpty()) {
            $this->command->info('Aucun élève trouvé. Veuillez d\'abord créer des utilisateurs avec le rôle "eleve".');
            return;
        }
        
        $statuses = ['present', 'absent', 'late', 'excused'];
        $reasons = [
            'Maladie', 
            'Rendez-vous médical', 
            'Problème de transport', 
            'Raisons personnelles',
            null
        ];
        
        // Créer des présences pour les 30 derniers jours
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            
            // Ne pas créer de données pour les week-ends
            if ($date->isWeekend()) {
                continue;
            }
            
            foreach ($students as $student) {
                // 90% de chance d'être présent
                $status = rand(1, 100) <= 90 ? 'present' : $statuses[array_rand($statuses)];
                $reason = $status === 'present' ? null : $reasons[array_rand($reasons)];
                $justified = $status !== 'present' ? (bool)rand(0, 1) : false;
                
                Attendance::create([
                    'user_id' => $student->id,
                    'date' => $date->format('Y-m-d'),
                    'status' => $status,
                    'reason' => $reason,
                    'justified' => $justified,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
            
            $this->command->info("Création des présences pour le " . $date->format('d/m/Y') . " terminée.");
        }
    }
}
