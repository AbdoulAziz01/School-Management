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
        // Récupérer tous les niveaux
        $levels = \App\Models\Level::all();

        if ($levels->isEmpty()) {
            $this->command->info('Aucun niveau trouvé. Veuillez d\'abord exécuter le seeder LevelsAndSubjectsSeeder.');
            return;
        }

        // Année scolaire courante par établissement (un niveau peut appartenir
        // à une école différente ; on ne suppose pas une seule année partagée).
        $academicYearsBySchool = [];

        // Créer 1-3 classes par niveau
        foreach ($levels as $level) {
            $schoolId = $level->school_id;

            $academicYear = $academicYearsBySchool[$schoolId] ??= AcademicYear::firstOrCreate(
                ['is_current' => true, 'school_id' => $schoolId],
                [
                    'name' => '2025-2026',
                    'start_date' => '2025-09-01',
                    'end_date' => '2026-06-30',
                    'is_current' => true,
                    'school_id' => $schoolId,
                ]
            );

            $classCount = rand(1, 3); // 1 à 3 classes par niveau

            for ($i = 1; $i <= $classCount; $i++) {
                SchoolClass::firstOrCreate(
                    [
                        'name' => $level->name . ' ' . $i,
                        'academic_year_id' => $academicYear->id
                    ],
                    [
                        'level_id' => $level->id,
                        'school_id' => $schoolId,
                        'capacity' => 30 // Capacité par défaut
                    ]
                );
            }
        }

        $this->command->info('Classes créées avec succès !');
    }
}
