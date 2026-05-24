<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Support\SchoolLevelProvisioner;
use Illuminate\Console\Command;

class SyncSchoolLevels extends Command
{
    protected $signature = 'schools:sync-levels {--school= : ID d\'un établissement}';

    protected $description = 'Crée les niveaux manquants (primaire CI–CM2, collège, lycée) selon le type d\'établissement';

    public function handle(): int
    {
        $query = School::query()->where('establishment_type', '!=', School::TYPE_FORMATION);

        if ($this->option('school')) {
            $query->where('id', $this->option('school'));
        }

        $count = 0;
        foreach ($query->get() as $school) {
            SchoolLevelProvisioner::syncLevelsForSchool($school);
            $count++;
            $this->line("Niveaux synchronisés : {$school->name}");
        }

        $this->info("{$count} établissement(s) traité(s).");

        return self::SUCCESS;
    }
}
