<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\User;
use App\Support\LoadTest\LoadTestDataPurge;
use Illuminate\Console\Command;

class PurgeSchoolData extends Command
{
    protected $signature = 'schools:purge-all {--force : Sans confirmation}';

    protected $description = 'Supprime TOUTES les écoles et données métier — conserve uniquement le super administrateur';

    public function handle(): int
    {
        $superEmail = env('SUPER_ADMIN_EMAIL', 'non configuré');

        if (! $this->option('force') && ! $this->confirm("Tout supprimer (écoles incluses) sauf le super admin ({$superEmail}) ?", true)) {
            $this->info('Annulé.');

            return self::SUCCESS;
        }

        LoadTestDataPurge::run();

        $schools = School::count();
        $users = User::withoutGlobalScopes()->count();
        $super = User::withoutGlobalScopes()->where('role', User::ROLE_SUPER_ADMIN)->count();

        $this->newLine();
        $this->info('Purge terminée.');
        $this->line("  Établissements restants : {$schools}");
        $this->line("  Utilisateurs restants : {$users} (super admin : {$super})");
        $this->line('  Pour recréer les 10 écoles démo : php artisan db:seed --class=SenegalTenSchoolsLoadTestSeeder');

        return self::SUCCESS;
    }
}
