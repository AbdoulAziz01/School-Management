<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'année scolaire en cours ou en créer une si elle n'existe pas
        $academicYear = AcademicYear::firstOrCreate(
            ['is_current' => true],
            [
                'name' => '2025-2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-06-30',
                'is_current' => true
            ]
        );

        // Récupérer tous les niveaux
        $levels = \App\Models\Level::all();
        
        if ($levels->isEmpty()) {
            $this->command->info('Aucun niveau trouvé. Veuillez d\'abord exécuter le seeder LevelsAndSubjectsSeeder.');
            return;
        }

        // Créer 1-3 classes par niveau
        foreach ($levels as $level) {
            $classCount = rand(1, 3); // 1 à 3 classes par niveau
            
            for ($i = 1; $i <= $classCount; $i++) {
                SchoolClass::firstOrCreate(
                    [
                        'name' => $level->name . ' ' . $i,
                        'academic_year_id' => $academicYear->id
                    ],
                    [
                        'level_id' => $level->id,
                        'capacity' => 30 // Capacité par défaut
                    ]
                );
            }
        }
        
        $this->command->info('Classes créées avec succès !');
    }
}
