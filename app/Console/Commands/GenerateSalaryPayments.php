<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\SalaryPaymentService;
use App\Support\SchoolModules;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Génère les paiements de salaire du mois pour chaque établissement privé
 * ayant le module Comptabilité activé — idempotent (unique(user_id,
 * period)), destinée à être planifiée en début de mois.
 */
class GenerateSalaryPayments extends Command
{
    protected $signature = 'accounting:generate-salary-payments {--school= : Limiter à un établissement (ID)} {--period= : Mois cible (YYYY-MM), par défaut le mois courant}';

    protected $description = 'Génère les paiements de salaire du personnel pour une période donnée';
     
    public function handle(SalaryPaymentService $salaries): int
    {
        $period = $this->option('period')
            ? Carbon::createFromFormat('Y-m', $this->option('period'))->startOfMonth()
            : now()->startOfMonth();

        $schools = School::withoutGlobalScopes()
            ->when($this->option('school'), fn ($q, $id) => $q->where('id', $id))
            ->get()
            ->filter(fn (School $school) => SchoolModules::isEnabled($school, SchoolModules::ACCOUNTING));

        if ($schools->isEmpty()) {
            $this->info('Aucun établissement avec le module Comptabilité activé.');

            return self::SUCCESS;
        }

        foreach ($schools as $school) {
            $created = $salaries->generateForPeriod($school, $period);
            $this->info("« {$school->name} » : {$created->count()} paiement(s) de salaire généré(s)/déjà existant(s) pour {$period->locale('fr')->translatedFormat('F Y')}.");
        }

        return self::SUCCESS;
    }
}
