<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SenegalSchoolSystemSeeder extends Seeder
{
    /**
     * Coefficients officiels du Sénégal par série
     */
    private array $coefficientsBySerie = [
        'L' => [
            'Français' => 5,
            'Philosophie' => 5,
            'Histoire-Géographie' => 4,
            'Littérature' => 3,
            'Mathématiques' => 2,
            'LV1 (Anglais)' => 2,
            'LV2 (Espagnol)' => 2,
            'Sciences Économiques et Sociales' => 2,
            'Éducation Physique et Sportive' => 1,
        ],
        'S' => [
            'Mathématiques' => 5,
            'Physique-Chimie' => 5,
            'Sciences de la Vie et de la Terre' => 5,
            'Sciences de l\'Ingénieur' => 3,
            'Français' => 2,
            'LV1 (Anglais)' => 2,
            'LV2 (Espagnol)' => 2,
            'Philosophie' => 2,
            'Éducation Physique et Sportive' => 1,
        ],
        'ES' => [
            'Mathématiques' => 4,
            'Sciences Économiques et Sociales' => 5,
            'Économie' => 4,
            'Histoire-Géographie' => 4,
            'Français' => 3,
            'LV1 (Anglais)' => 2,
            'LV2 (Espagnol)' => 2,
            'Philosophie' => 2,
            'Éducation Physique et Sportive' => 1,
        ],
    ];

    /**
     * Matières par série
     */
    private array $subjectsBySerie = [
        'L' => [
            'Français', 'Philosophie', 'Histoire-Géographie', 'Littérature',
            'Mathématiques', 'LV1 (Anglais)', 'LV2 (Espagnol)', 
            'Sciences Économiques et Sociales', 'Éducation Physique et Sportive'
        ],
        'S' => [
            'Mathématiques', 'Physique-Chimie', 'Sciences de la Vie et de la Terre',
            'Sciences de l\'Ingénieur', 'Français', 'LV1 (Anglais)', 'LV2 (Espagnol)',
            'Philosophie', 'Éducation Physique et Sportive'
        ],
        'ES' => [
            'Mathématiques', 'Sciences Économiques et Sociales', 'Économie',
            'Histoire-Géographie', 'Français', 'LV1 (Anglais)', 'LV2 (Espagnol)',
            'Philosophie', 'Éducation Physique et Sportive'
        ],
    ];

    /**
     * Prénoms sénégalais
     */
    private array $senegalFirstNames = [
        // Prénoms masculins
        'Mamadou', 'Abdoulaye', 'Ibrahima', 'Ousmane', 'Moussa', 'Amadou', 'Cheikh', 
        'Pape', 'Modou', 'Aliou', 'Babacar', 'Assane', 'Daouda', 'El Hadji', 'Malick',
        'Seydou', 'Boubacar', 'Lamine', 'Souleymane', 'Thierno', 'Djibril', 'Youssou',
        'Mbaye', 'Saliou', 'Alioune', 'Demba', 'Fallou', 'Mor', 'Serigne', 'Birame',
        // Prénoms féminins
        'Fatou', 'Aminata', 'Awa', 'Mariama', 'Khady', 'Aissatou', 'Ndeye', 'Rama',
        'Coumba', 'Oumou', 'Bineta', 'Astou', 'Mame', 'Adja', 'Rokhaya', 'Dieynaba',
        'Sokhna', 'Amy', 'Soda', 'Aby', 'Dado', 'Seynabou', 'Ndella', 'Binta', 'Maty',
        'Kine', 'Ndey', 'Codou', 'Arame', 'Thioro'
    ];

    /**
     * Noms de famille sénégalais
     */
    private array $senegalLastNames = [
        'Diop', 'Ndiaye', 'Fall', 'Sow', 'Diallo', 'Sy', 'Ba', 'Mbaye', 'Gueye', 'Faye',
        'Sarr', 'Thiam', 'Seck', 'Niang', 'Kane', 'Dieng', 'Cissé', 'Wade', 'Tall', 'Diouf',
        'Bâ', 'Sène', 'Ndoye', 'Mbow', 'Ly', 'Camara', 'Samb', 'Ngom', 'Diaw', 'Badiane',
        'Touré', 'Drame', 'Barry', 'Sock', 'Dièye', 'Baldé', 'Guissé', 'Niasse', 'Lo', 'Sagna'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🇸🇳 Initialisation du système scolaire sénégalais...');

        // Désactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Nettoyer les tables existantes
        $this->cleanTables();

        // Réactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 1. Créer l'année académique 2025-2026 (terminée)
        $academicYear = $this->createAcademicYear();
        $this->command->info('✅ Année académique créée');

        // 2. Créer les niveaux avec séries
        $levels = $this->createLevels();
        $this->command->info('✅ Niveaux créés');

        // 3. Créer les matières
        $subjects = $this->createSubjects();
        $this->command->info('✅ Matières créées avec coefficients sénégalais');

        // 4. Associer les matières aux niveaux
        $this->attachSubjectsToLevels($levels, $subjects);
        $this->command->info('✅ Matières associées aux niveaux');

        // 5. Créer l'administrateur
        $this->createAdmin();
        $this->command->info('✅ Administrateur créé');

        // 6. Créer les professeurs (un par matière minimum)
        $teachers = $this->createTeachers($subjects);
        $this->command->info('✅ ' . count($teachers) . ' professeurs créés');

        // 7. Créer 14 classes avec 50 élèves chacune = 700 élèves
        $classes = $this->createClasses($academicYear, $levels);
        $this->command->info('✅ ' . count($classes) . ' classes créées');

        // 8. Créer 700 élèves répartis dans les classes
        $students = $this->createStudents($classes);
        $this->command->info('✅ ' . count($students) . ' élèves créés');

        // 9. Assigner les professeurs aux classes
        $this->assignTeachersToClasses($teachers, $classes, $subjects);
        $this->command->info('✅ Professeurs assignés aux classes');

        // 10. Associer les matières aux classes
        $this->attachSubjectsToClasses($classes, $subjects, $levels);
        $this->command->info('✅ Matières associées aux classes');

        // 11. Générer les notes pour les 2 semestres
        $this->generateGrades($students, $subjects, $classes, $academicYear, $levels);
        $this->command->info('✅ Notes générées pour les 2 semestres');

        $this->command->info('');
        $this->command->info('🎓 Système scolaire sénégalais initialisé avec succès!');
        $this->command->info('');
        $this->command->info('📊 Résumé:');
        $this->command->info('   - 1 année académique (2025-2026)');
        $this->command->info('   - ' . count($levels) . ' niveaux (Seconde, Première, Terminale x 3 séries)');
        $this->command->info('   - ' . count($subjects) . ' matières avec coefficients');
        $this->command->info('   - ' . count($classes) . ' classes');
        $this->command->info('   - ' . count($students) . ' élèves (50 par classe)');
        $this->command->info('   - ' . count($teachers) . ' professeurs');
        $this->command->info('   - Notes complètes sur 2 semestres (2 devoirs + 1 composition par semestre)');
        $this->command->info('');
        $this->command->info('🔐 Identifiants de connexion:');
        $this->command->info('   Admin: admin@ecole-senegal.sn / password');
        $this->command->info('   Prof exemple: prof.francais@ecole-senegal.sn / password');
        $this->command->info('   Élève exemple: E00001@ecole-senegal.sn / password');
    }

    /**
     * Nettoyer les tables
     */
    private function cleanTables(): void
    {
        DB::table('grades')->truncate();
        DB::table('attendances')->truncate();
        DB::table('teacher_subjects')->truncate();
        DB::table('class_teacher')->truncate();
        DB::table('class_subject')->truncate();
        DB::table('level_subject')->truncate();
        DB::table('teacher_assignments')->truncate();
        DB::table('users')->truncate();
        DB::table('classes')->truncate();
        DB::table('subjects')->truncate();
        DB::table('levels')->truncate();
        DB::table('academic_years')->truncate();
    }

    /**
     * Créer l'année académique 2025-2026 (terminée)
     */
    private function createAcademicYear(): AcademicYear
    {
        return AcademicYear::create([
            'name' => '2025-2026',
            'start_date' => '2025-10-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ]);
    }

    /**
     * Créer les niveaux du système sénégalais
     */
    private function createLevels(): array
    {
        $levels = [];
        $order = 1;

        $series = ['L', 'S', 'ES'];
        $niveaux = ['Seconde', 'Première', 'Terminale'];

        foreach ($niveaux as $niveau) {
            foreach ($series as $serie) {
                $levels[] = Level::create([
                    'name' => $niveau . ' ' . $serie,
                    'order' => $order++,
                    'cycle' => 'lycee',
                    'serie' => $serie,
                ]);
            }
        }

        return $levels;
    }

    /**
     * Créer toutes les matières avec leurs codes
     */
    private function createSubjects(): array
    {
        $allSubjects = [
            ['name' => 'Français', 'code' => 'FR', 'department' => 'Lettres'],
            ['name' => 'Philosophie', 'code' => 'PHILO', 'department' => 'Lettres'],
            ['name' => 'Histoire-Géographie', 'code' => 'HG', 'department' => 'Sciences Humaines'],
            ['name' => 'Littérature', 'code' => 'LIT', 'department' => 'Lettres'],
            ['name' => 'Mathématiques', 'code' => 'MATH', 'department' => 'Sciences'],
            ['name' => 'LV1 (Anglais)', 'code' => 'ANG', 'department' => 'Langues'],
            ['name' => 'LV2 (Espagnol)', 'code' => 'ESP', 'department' => 'Langues'],
            ['name' => 'Sciences Économiques et Sociales', 'code' => 'SES', 'department' => 'Sciences Humaines'],
            ['name' => 'Physique-Chimie', 'code' => 'PC', 'department' => 'Sciences'],
            ['name' => 'Sciences de la Vie et de la Terre', 'code' => 'SVT', 'department' => 'Sciences'],
            ['name' => 'Sciences de l\'Ingénieur', 'code' => 'SI', 'department' => 'Sciences'],
            ['name' => 'Économie', 'code' => 'ECO', 'department' => 'Sciences Humaines'],
            ['name' => 'Éducation Physique et Sportive', 'code' => 'EPS', 'department' => 'Sport'],
        ];

        $subjects = [];
        foreach ($allSubjects as $subjectData) {
            $subjects[$subjectData['name']] = Subject::create([
                'name' => $subjectData['name'],
                'code' => $subjectData['code'],
                'coefficient' => 1, // Sera mis à jour par niveau/série
                'description' => 'Matière: ' . $subjectData['name'],
                'department' => $subjectData['department'],
                'is_active' => true,
                'hours_per_week' => rand(2, 5),
                'is_core_subject' => true,
            ]);
        }

        return $subjects;
    }

    /**
     * Associer les matières aux niveaux avec les bons coefficients
     */
    private function attachSubjectsToLevels(array $levels, array $subjects): void
    {
        foreach ($levels as $level) {
            $serie = $level->serie;
            
            if (!isset($this->subjectsBySerie[$serie])) {
                continue;
            }

            foreach ($this->subjectsBySerie[$serie] as $subjectName) {
                if (isset($subjects[$subjectName])) {
                    $coefficient = $this->coefficientsBySerie[$serie][$subjectName] ?? 1;
                    
                    $level->subjects()->attach($subjects[$subjectName]->id, [
                        'coefficient' => $coefficient,
                        'is_compulsory' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Créer l'administrateur
     */
    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Administrateur Système',
            'email' => 'admin@ecole-senegal.sn',
            'password' => Hash::make('password'),
            'identifier' => 'ADMIN001',
            'role' => 'admin',
            'status' => 'approved',
            'phone' => '+221 77 000 00 00',
            'address' => 'Dakar, Sénégal',
        ]);
    }

    /**
     * Créer les professeurs
     */
    private function createTeachers(array $subjects): array
    {
        $teachers = [];
        $index = 1;

        // Un professeur principal par matière
        foreach ($subjects as $subjectName => $subject) {
            $firstName = $this->senegalFirstNames[array_rand($this->senegalFirstNames)];
            $lastName = $this->senegalLastNames[array_rand($this->senegalLastNames)];
            
            $emailSlug = strtolower(str_replace([' ', '\'', '(', ')', '-'], '', $subject->code));
            
            $teacher = User::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => 'prof.' . $emailSlug . '@ecole-senegal.sn',
                'password' => Hash::make('password'),
                'identifier' => 'P' . str_pad($index, 5, '0', STR_PAD_LEFT),
                'role' => 'teacher',
                'status' => 'approved',
                'phone' => '+221 77 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'address' => 'Dakar, Sénégal',
                'date_of_birth' => now()->subYears(rand(30, 55))->subDays(rand(1, 365)),
            ]);

            // Associer la matière au professeur
            $teacher->subjects()->attach($subject->id);
            
            $teachers[] = [
                'teacher' => $teacher,
                'subject' => $subject,
            ];
            
            $index++;
        }

        // Ajouter des professeurs supplémentaires pour avoir assez de couverture
        for ($i = 0; $i < 10; $i++) {
            $firstName = $this->senegalFirstNames[array_rand($this->senegalFirstNames)];
            $lastName = $this->senegalLastNames[array_rand($this->senegalLastNames)];
            
            $randomSubject = $subjects[array_rand($subjects)];
            
            $teacher = User::create([
                'name' => $firstName . ' ' . $lastName,
                'email' => 'prof' . $index . '@ecole-senegal.sn',
                'password' => Hash::make('password'),
                'identifier' => 'P' . str_pad($index, 5, '0', STR_PAD_LEFT),
                'role' => 'teacher',
                'status' => 'approved',
                'phone' => '+221 77 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'address' => 'Dakar, Sénégal',
                'date_of_birth' => now()->subYears(rand(30, 55))->subDays(rand(1, 365)),
            ]);

            $teacher->subjects()->attach($randomSubject->id);
            
            $teachers[] = [
                'teacher' => $teacher,
                'subject' => $randomSubject,
            ];
            
            $index++;
        }

        return $teachers;
    }

    /**
     * Créer les classes - 14 classes pour 700 élèves (50 par classe)
     */
    private function createClasses(AcademicYear $academicYear, array $levels): array
    {
        $classes = [];
        
        // Distribution des classes par niveau/série
        // Terminale: 2 classes par série = 6 classes
        // Première: 2 classes par série = 6 classes  
        // Seconde: 1 classe par série + 1 extra = 3-4 classes
        // Total adapté pour 14 classes
        
        $classDistribution = [
            'Terminale L' => 2,
            'Terminale S' => 2,
            'Terminale ES' => 2,
            'Première L' => 2,
            'Première S' => 2,
            'Première ES' => 2,
            'Seconde L' => 1,
            'Seconde S' => 1,
        ];

        foreach ($levels as $level) {
            $numClasses = $classDistribution[$level->name] ?? 0;
            
            for ($i = 1; $i <= $numClasses; $i++) {
                $letter = chr(64 + $i); // A, B, C...
                
                $classes[] = SchoolClass::create([
                    'name' => $level->name . ' ' . $letter,
                    'academic_year_id' => $academicYear->id,
                    'level_id' => $level->id,
                    'capacity' => 50,
                ]);
            }
        }

        return $classes;
    }

    /**
     * Créer 700 élèves répartis dans les classes
     */
    private function createStudents(array $classes): array
    {
        $students = [];
        $studentIndex = 1;

        foreach ($classes as $class) {
            for ($i = 0; $i < 50; $i++) {
                $firstName = $this->senegalFirstNames[array_rand($this->senegalFirstNames)];
                $lastName = $this->senegalLastNames[array_rand($this->senegalLastNames)];
                
                $identifier = 'E' . str_pad($studentIndex, 5, '0', STR_PAD_LEFT);
                
                $students[] = User::create([
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $identifier . '@ecole-senegal.sn',
                    'password' => Hash::make('password'),
                    'identifier' => $identifier,
                    'role' => 'eleve',
                    'status' => 'approved',
                    'class_id' => $class->id,
                    'phone' => '+221 78 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                    'address' => $this->getRandomSenegalCity() . ', Sénégal',
                    'date_of_birth' => now()->subYears(rand(15, 19))->subDays(rand(1, 365)),
                ]);
                
                $studentIndex++;
            }
        }

        return $students;
    }

    /**
     * Obtenir une ville sénégalaise aléatoire
     */
    private function getRandomSenegalCity(): string
    {
        $cities = [
            'Dakar', 'Thiès', 'Rufisque', 'Kaolack', 'Mbour', 'Saint-Louis',
            'Ziguinchor', 'Diourbel', 'Louga', 'Tambacounda', 'Kolda', 'Matam',
            'Fatick', 'Kaffrine', 'Kédougou', 'Sédhiou', 'Pikine', 'Guédiawaye'
        ];
        return $cities[array_rand($cities)];
    }

    /**
     * Assigner les professeurs aux classes
     */
    private function assignTeachersToClasses(array $teachers, array $classes, array $subjects): void
    {
        foreach ($classes as $class) {
            $level = Level::find($class->level_id);
            $serie = $level->serie;
            
            if (!isset($this->subjectsBySerie[$serie])) {
                continue;
            }

            foreach ($this->subjectsBySerie[$serie] as $subjectName) {
                if (!isset($subjects[$subjectName])) {
                    continue;
                }
                
                $subject = $subjects[$subjectName];
                
                // Trouver un professeur pour cette matière
                $teacherFound = null;
                foreach ($teachers as $teacherData) {
                    if ($teacherData['subject']->id === $subject->id) {
                        $teacherFound = $teacherData['teacher'];
                        break;
                    }
                }
                
                if ($teacherFound) {
                    // Éviter les doublons
                    $exists = DB::table('class_teacher')
                        ->where('class_id', $class->id)
                        ->where('teacher_id', $teacherFound->id)
                        ->exists();
                    
                    if (!$exists) {
                        $class->teachers()->attach($teacherFound->id);
                    }
                }
            }
        }
    }

    /**
     * Associer les matières aux classes
     */
    private function attachSubjectsToClasses(array $classes, array $subjects, array $levels): void
    {
        foreach ($classes as $class) {
            $level = Level::find($class->level_id);
            $serie = $level->serie;
            
            if (!isset($this->subjectsBySerie[$serie])) {
                continue;
            }

            foreach ($this->subjectsBySerie[$serie] as $subjectName) {
                if (isset($subjects[$subjectName])) {
                    $class->subjects()->attach($subjects[$subjectName]->id);
                }
            }
        }
    }

    /**
     * Générer les notes pour tous les élèves sur les 2 semestres
     * Système sénégalais: 2 devoirs + 1 composition par semestre
     */
    private function generateGrades(array $students, array $subjects, array $classes, AcademicYear $academicYear, array $levels): void
    {
        $this->command->info('   Génération des notes en cours...');
        
        $evaluationTypes = ['devoir1', 'devoir2', 'composition'];
        $semesters = [1, 2];
        
        // Dates pour chaque évaluation par semestre
        $evaluationDates = [
            1 => [
                'devoir1' => '2025-11-15',
                'devoir2' => '2025-12-15',
                'composition' => '2026-01-20',
            ],
            2 => [
                'devoir1' => '2026-03-15',
                'devoir2' => '2026-04-15',
                'composition' => '2026-06-01',
            ],
        ];

        $gradesData = [];
        $progressCount = 0;
        $totalCount = count($students);

        foreach ($students as $student) {
            $class = SchoolClass::find($student->class_id);
            $level = Level::find($class->level_id);
            $serie = $level->serie;
            
            if (!isset($this->subjectsBySerie[$serie])) {
                continue;
            }

            foreach ($this->subjectsBySerie[$serie] as $subjectName) {
                if (!isset($subjects[$subjectName])) {
                    continue;
                }
                
                $subject = $subjects[$subjectName];
                $coefficient = $this->coefficientsBySerie[$serie][$subjectName] ?? 1;
                
                // Générer une base de performance pour cet élève dans cette matière
                // Cela crée des profils d'élèves cohérents
                $basePerformance = rand(8, 16);
                $variance = rand(-3, 3);
                
                foreach ($semesters as $semester) {
                    foreach ($evaluationTypes as $type) {
                        // Variation autour de la performance de base
                        $grade = $basePerformance + $variance + (rand(-20, 20) / 10);
                        
                        // Les compositions sont souvent un peu plus difficiles
                        if ($type === 'composition') {
                            $grade -= rand(0, 2);
                        }
                        
                        // Assurer que la note est entre 0 et 20
                        $grade = max(0, min(20, $grade));
                        $grade = round($grade, 2);
                        
                        $appreciation = $this->getAppreciation($grade);
                        
                        $gradesData[] = [
                            'user_id' => $student->id,
                            'subject_id' => $subject->id,
                            'grade' => $grade,
                            'comments' => $this->getComment($grade, $type),
                            'appreciation' => $appreciation,
                            'date' => $evaluationDates[$semester][$type],
                            'type' => $type,
                            'coefficient' => $coefficient,
                            'semester' => $semester,
                            'academic_year_id' => $academicYear->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
            
            $progressCount++;
            if ($progressCount % 100 === 0) {
                $this->command->info("   ... {$progressCount}/{$totalCount} élèves traités");
            }
        }

        // Insérer par lots pour la performance
        $chunks = array_chunk($gradesData, 1000);
        foreach ($chunks as $chunk) {
            DB::table('grades')->insert($chunk);
        }
        
        $this->command->info("   ... {$totalCount}/{$totalCount} élèves traités - Terminé!");
    }

    /**
     * Obtenir une appréciation basée sur la note
     */
    private function getAppreciation(float $grade): string
    {
        if ($grade >= 16) return 'Très Bien';
        if ($grade >= 14) return 'Bien';
        if ($grade >= 12) return 'Assez Bien';
        if ($grade >= 10) return 'Passable';
        if ($grade >= 8) return 'Insuffisant';
        return 'Très Insuffisant';
    }

    /**
     * Obtenir un commentaire basé sur la note et le type d'évaluation
     */
    private function getComment(float $grade, string $type): string
    {
        $typeLabels = [
            'devoir1' => 'Premier devoir',
            'devoir2' => 'Deuxième devoir',
            'composition' => 'Composition',
        ];
        
        $comments = [
            'excellent' => [
                'Excellent travail! Continue ainsi.',
                'Très bonne maîtrise du sujet.',
                'Félicitations pour ce travail remarquable.',
                'Travail exemplaire.',
            ],
            'bien' => [
                'Bon travail dans l\'ensemble.',
                'De bons efforts fournis.',
                'Travail satisfaisant.',
                'Bonne progression.',
            ],
            'moyen' => [
                'Des efforts à fournir.',
                'Travail à approfondir.',
                'Peut mieux faire avec plus de travail.',
                'Des lacunes à combler.',
            ],
            'faible' => [
                'Travail insuffisant.',
                'Doit impérativement se ressaisir.',
                'Résultats décevants.',
                'Un travail plus régulier est nécessaire.',
            ],
        ];
        
        if ($grade >= 16) {
            return $comments['excellent'][array_rand($comments['excellent'])];
        } elseif ($grade >= 12) {
            return $comments['bien'][array_rand($comments['bien'])];
        } elseif ($grade >= 8) {
            return $comments['moyen'][array_rand($comments['moyen'])];
        } else {
            return $comments['faible'][array_rand($comments['faible'])];
        }
    }
}
