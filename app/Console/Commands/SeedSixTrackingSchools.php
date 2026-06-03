<?php

namespace App\Console\Commands;

use Database\Seeders\SixSchoolsTrackingSeeder;
use Illuminate\Console\Command;

class SeedSixTrackingSchools extends Command
{
    protected $signature = 'schools:seed-tracking-demo';

    protected $description = 'Crée 6 établissements de suivi (15 élèves/classe) sans supprimer les données existantes';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => SixSchoolsTrackingSeeder::class]);

        return self::SUCCESS;
    }
}
