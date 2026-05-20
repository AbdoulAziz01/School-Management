<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelsAndSubjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolId = School::query()->value('id');
        if (! $schoolId) {
            $this->command?->error('Aucun établissement trouvé. Exécutez d\'abord les migrations multi-écoles.');

            return;
        }

        // Niveaux du collège
        $collegeLevels = [
            ['name' => '6ème', 'order' => 1, 'cycle' => 'college'],
            ['name' => '5ème', 'order' => 2, 'cycle' => 'college'],
            ['name' => '4ème', 'order' => 3, 'cycle' => 'college'],
            ['name' => '3ème', 'order' => 4, 'cycle' => 'college'],
        ];

        // Niveaux du lycée - avec spécialisations S, L, G
        $lyceeLevels = [
            ['name' => '2nde S', 'order' => 5, 'cycle' => 'lycee'],
            ['name' => '2nde L', 'order' => 6, 'cycle' => 'lycee'],
            ['name' => '2nde G', 'order' => 7, 'cycle' => 'lycee'],
            ['name' => '1ère S', 'order' => 8, 'cycle' => 'lycee'],
            ['name' => '1ère L', 'order' => 9, 'cycle' => 'lycee'],
            ['name' => '1ère G', 'order' => 10, 'cycle' => 'lycee'],
            ['name' => 'Terminale S', 'order' => 11, 'cycle' => 'lycee'],
            ['name' => 'Terminale L', 'order' => 12, 'cycle' => 'lycee'],
            ['name' => 'Terminale G', 'order' => 13, 'cycle' => 'lycee'],
        ];

        // Matières communes avec leurs codes
        $commonSubjects = [
            ['name' => 'Français', 'code' => 'FR'],
            ['name' => 'Mathématiques', 'code' => 'MATH'],
            ['name' => 'Histoire-Géographie', 'code' => 'HIST-GEO'],
            ['name' => 'Sciences de la Vie et de la Terre', 'code' => 'SVT'],
            ['name' => 'Physique-Chimie', 'code' => 'PHYS-CHIM'],
            ['name' => 'Anglais', 'code' => 'ANG'],
            ['name' => 'Espagnol', 'code' => 'ESP'],
            ['name' => 'Allemand', 'code' => 'ALL'],
            ['name' => 'Éducation Physique et Sportive', 'code' => 'EPS'],
            ['name' => 'Enseignement Moral et Civique', 'code' => 'EMC'],
            ['name' => 'Sciences Économiques et Sociales', 'code' => 'SES'],
            ['name' => 'Philosophie', 'code' => 'PHILO']
        ];

        // Matières spécifiques au collège
        $collegeSubjects = [
            ['name' => 'Technologie', 'code' => 'TECHNO'],
            ['name' => 'Arts Plastiques', 'code' => 'ARTS-PLAST'],
            ['name' => 'Éducation Musicale', 'code' => 'MUSIQUE'],
            ['name' => 'Latin', 'code' => 'LATIN'],
            ['name' => 'Grec', 'code' => 'GREC'],
            ['name' => 'Histoire-Géographie, Enseignement Moral et Civique', 'code' => 'HG-EMC'],
            ['name' => 'Langue Vivante 2 (LV2)', 'code' => 'LV2']
        ];

        // Matières spécifiques au lycée
        $lyceeSubjects = [
            ['name' => 'Littérature', 'code' => 'LITTERATURE'],
            ['name' => 'Littérature Étrangère en Langue Étrangère', 'code' => 'LELE'],
            ['name' => 'Mathématiques Spécialité', 'code' => 'MATH-SPE'],
            ['name' => 'Physique-Chimie Spécialité', 'code' => 'PHYS-CHIM-SPE'],
            ['name' => 'Sciences de la Vie et de la Terre Spécialité', 'code' => 'SVT-SPE'],
            ['name' => 'Sciences de l\'Ingénieur', 'code' => 'SI'],
            ['name' => 'Numérique et Sciences Informatiques', 'code' => 'NSI'],
            ['name' => 'Histoire-Géographie, Géopolitique et Sciences Politiques', 'code' => 'HGGSP'],
            ['name' => 'Humanités, Littérature et Philosophie', 'code' => 'HLP'],
            ['name' => 'Langues, Littératures et Cultures Étrangères', 'code' => 'LLCE'],
            ['name' => 'Arts Plastiques Spécialité', 'code' => 'ARTS-SPE'],
            ['name' => 'Musique Spécialité', 'code' => 'MUSIQUE-SPE'],
            ['name' => 'Théâtre Spécialité', 'code' => 'THEATRE-SPE'],
            ['name' => 'Cinéma-Audiovisuel Spécialité', 'code' => 'CINE-SPE'],
            ['name' => 'Éducation Physique, Pratiques et Culture Sportives', 'code' => 'EPPCS']
        ];

        // Désactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Vider les tables
        DB::table('levels')->truncate();
        DB::table('subjects')->truncate();
        
        // Réactiver les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Insérer les niveaux
        foreach (array_merge($collegeLevels, $lyceeLevels) as $level) {
            Level::create(array_merge($level, ['school_id' => $schoolId]));
        }

        // Insérer les matières
        $allSubjects = array_merge($commonSubjects, $collegeSubjects, $lyceeSubjects);
        
        // Supprimer les doublons basés sur le code
        $uniqueSubjects = [];
        foreach ($allSubjects as $subject) {
            $uniqueSubjects[$subject['code']] = $subject;
        }
        
        foreach ($uniqueSubjects as $subject) {
            Subject::create([
                'name' => $subject['name'],
                'code' => $subject['code'],
                'coefficient' => 1,
                'description' => $subject['name'],
                'school_id' => $schoolId,
            ]);
        }
    }
}
