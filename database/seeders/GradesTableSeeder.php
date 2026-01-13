<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradesTableSeeder extends Seeder
{
    /**
     * Exécuter le seeder.
     */
    public function run(): void
    {
        // Vérifier s'il y a déjà des données
        if (Grade::count() > 0) {
            return;
        }

        // Récupérer les étudiants
        $students = User::where('role', User::ROLE_STUDENT)->get();
        $subjects = Subject::all();
        $types = ['Examen', 'Devoir', 'Interrogation', 'Projet'];
        $appreciations = [
            'Très bien', 'Bien', 'Assez bien', 'Passable', 'Insuffisant'
        ];

        if ($students->isEmpty() || $subjects->isEmpty()) {
            $this->command->info('Aucun étudiant ou matière trouvé. Veuillez exécuter les seeders nécessaires.');
            return;
        }

        $grades = [];
        $now = now();
        $startDate = Carbon::now()->subMonths(3);
        
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                // Ajouter 2 à 5 notes par matière
                $numGrades = rand(2, 5);
                
                // S'assurer que chaque note a une date unique pour éviter les doublons
                $usedDates = [];
                
                for ($i = 0; $i < $numGrades; $i++) {
                    // Générer une date unique pour cette combinaison étudiant/matière/type
                    do {
                        $date = $startDate->copy()->addDays(rand(0, 90));
                        $type = $types[array_rand($types)];
                        $dateTypeKey = $student->id . '-' . $subject->id . '-' . $date->format('Y-m-d') . '-' . $type;
                    } while (in_array($dateTypeKey, $usedDates));
                    
                    $usedDates[] = $dateTypeKey;
                    $grade = rand(5, 20) + (rand(0, 9) / 10); // Note entre 5.0 et 20.0
                    
                    $grades[] = [
                        'user_id' => $student->id,
                        'subject_id' => $subject->id,
                        'grade' => $grade,
                        'comments' => 'Commentaire pour la note de ' . $subject->name . ' (' . $type . ' du ' . $date->format('d/m/Y') . ')' ,
                        'appreciation' => $appreciations[array_rand($appreciations)],
                        'date' => $date,
                        'type' => $type,
                        'coefficient' => $type === 'Examen' ? 3 : ($type === 'Devoir' ? 2 : 1),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Insérer les notes par lots pour des performances optimales
        foreach (array_chunk($grades, 100) as $chunk) {
            DB::table('grades')->insert($chunk);
        }
    }
}
