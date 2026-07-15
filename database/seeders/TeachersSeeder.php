<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('Passer01');
        
        // Liste des professeurs à créer avec leurs matières
        $teachers = [
            [
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@school.com',
                'subjects' => [1], // Français
            ],
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@school.com',
                'subjects' => [2], // Mathématiques
            ],
            [
                'name' => 'Pierre Bernard',
                'email' => 'pierre.bernard@school.com',
                'subjects' => [3, 18], // Histoire-Géographie
            ],
            [
                'name' => 'Sophie Petit',
                'email' => 'sophie.petit@school.com',
                'subjects' => [4], // SVT
            ],
            [
                'name' => 'Luc Moreau',
                'email' => 'luc.moreau@school.com',
                'subjects' => [5], // Physique-Chimie
            ],
            [
                'name' => 'Claire Dubois',
                'email' => 'claire.dubois@school.com',
                'subjects' => [6], // Anglais
            ],
            [
                'name' => 'Marc Lefebvre',
                'email' => 'marc.lefebvre@school.com',
                'subjects' => [7], // Espagnol
            ],
            [
                'name' => 'Anne Roux',
                'email' => 'anne.roux@school.com',
                'subjects' => [9], // EPS
            ],
            [
                'name' => 'Philippe Garcia',
                'email' => 'philippe.garcia@school.com',
                'subjects' => [12], // Philosophie
            ],
            [
                'name' => 'Isabelle Thomas',
                'email' => 'isabelle.thomas@school.com',
                'subjects' => [11], // SES
            ],
        ];
        
        // Récupérer toutes les classes, et l'école à laquelle rattacher les profs
        $allClasses = DB::table('classes')->pluck('id')->toArray();
        $schoolId = DB::table('schools')->value('id');
        
        foreach ($teachers as $teacherData) {
            // Vérifier si le professeur existe déjà
            $existingTeacher = User::where('email', $teacherData['email'])->first();
            
            if ($existingTeacher) {
                $this->command->info("Le professeur {$teacherData['name']} existe déjà.");
                $teacher = $existingTeacher;
            } else {
                // Génerer un identifiant unique pour le professeur
                $identifier = 'PROF-' . strtoupper(substr(md5($teacherData['email']), 0, 6));
                
                // Créer le professeur
                $teacher = User::create([
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'password' => $password,
                    'role' => 'teacher',
                    'status' => 'approved',
                    'email_verified_at' => now(),
                    'identifier' => $identifier,
                    'school_id' => $schoolId,
                ]);
                
                $this->command->info("Professeur créé: {$teacherData['name']} ({$teacherData['email']})");
            }
            
            // Associer les matières au professeur
            foreach ($teacherData['subjects'] as $subjectId) {
                DB::table('teacher_subjects')->updateOrInsert(
                    [
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            
            // Affecter le professeur à quelques classes (les 3 premières)
            $classesToAssign = array_slice($allClasses, 0, 3);
            foreach ($classesToAssign as $classId) {
                DB::table('class_teacher')->updateOrInsert(
                    [
                        'teacher_id' => $teacher->id,
                        'class_id' => $classId,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
        
        $this->command->info("\n=== Récapitulatif ===");
        $this->command->info("Mot de passe pour tous les professeurs: Passer01");
        $this->command->info("\nProfesseurs créés:");
        foreach ($teachers as $t) {
            $this->command->info("  - {$t['name']} : {$t['email']}");
        }
    }
}
