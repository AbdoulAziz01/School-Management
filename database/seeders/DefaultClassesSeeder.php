<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\SchoolClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver la vérification des clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SchoolClass::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Récupérer l'année scolaire actuelle
        $currentYear = AcademicYear::where('is_current', true)->first();

        if (!$currentYear) {
            $this->command->error('Aucune année scolaire actuelle trouvée. Veuillez d\'abord créer une année scolaire et la marquer comme actuelle.');
            return;
        }

        // Récupérer tous les niveaux
        $levels = Level::all();

        if ($levels->isEmpty()) {
            $this->command->error('Aucun niveau trouvé. Veuillez d\'abord créer des niveaux.');
            return;
        }

        // Noms de classes par niveau (chaque niveau a les sections A et B)
        $classesByLevel = [
            // Collège
            '6ème' => ['A', 'B'],
            '5ème' => ['A', 'B'],
            '4ème' => ['A', 'B'],
            '3ème' => ['A', 'B'],
            // Lycée - Seconde
            '2nde S' => ['A', 'B'],
            '2nde L' => ['A', 'B'],
            '2nde G' => ['A', 'B'],
            // Lycée - Première
            '1ère S' => ['A', 'B'],
            '1ère L' => ['A', 'B'],
            '1ère G' => ['A', 'B'],
            // Lycée - Terminale
            'Terminale S' => ['A', 'B'],
            'Terminale L' => ['A', 'B'],
            'Terminale G' => ['A', 'B'],
        ];

        $created = 0;

        foreach ($levels as $level) {
            $levelName = $level->name;
            
            // Vérifier si le niveau a des classes prédéfinies
            if (array_key_exists($levelName, $classesByLevel)) {
                foreach ($classesByLevel[$levelName] as $classSuffix) {
                    $className = $levelName . ' ' . $classSuffix;
                    
                    // Vérifier si la classe existe déjà
                    $existingClass = SchoolClass::where('name', $className)
                        ->where('academic_year_id', $currentYear->id)
                        ->exists();
                    
                    if (!$existingClass) {
                        SchoolClass::create([
                            'name' => $className,
                            'level_id' => $level->id,
                            'academic_year_id' => $currentYear->id,
                            'school_id' => $level->school_id,
                            'capacity' => rand(25, 40)
                        ]);
                        
                        $created++;
                    }
                }
            } else {
                // Pour les niveaux non définis, créer les classes A et B
                foreach (['A', 'B'] as $classSuffix) {
                    $className = $levelName . ' ' . $classSuffix;
                    
                    $existingClass = SchoolClass::where('name', $className)
                        ->where('academic_year_id', $currentYear->id)
                        ->exists();
                    
                    if (!$existingClass) {
                        SchoolClass::create([
                            'name' => $className,
                            'level_id' => $level->id,
                            'academic_year_id' => $currentYear->id,
                            'school_id' => $level->school_id,
                            'capacity' => rand(25, 40)
                        ]);
                        
                        $created++;
                    }
                }
            }
        }

        $this->command->info("$created classes ont été créées pour l'année scolaire {$currentYear->name}.");
    }
}
