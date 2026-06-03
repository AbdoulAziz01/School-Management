<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Support\AcademicYearProvisioner;
use Illuminate\Console\Command;

class ProvisionCurrentAcademicYear extends Command
{
    protected $signature = 'schools:provision-current-year {--school= : ID établissement (sinon tous)}';

    protected $description = 'Génère les classes de l\'année courante à partir de la dernière année terminée (sans déplacer les élèves)';

    public function handle(): int
    {
        $query = School::query()->where('is_active', true);
        if ($id = $this->option('school')) {
            $query->where('id', $id);
        }

        foreach ($query->get() as $school) {
            $current = AcademicYear::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('is_current', true)
                ->first();

            if (! $current) {
                $this->warn("{$school->name} : aucune année courante.");

                continue;
            }

            if (SchoolClass::withoutGlobalScopes()->where('academic_year_id', $current->id)->exists()) {
                $count = SchoolClass::withoutGlobalScopes()->where('academic_year_id', $current->id)->count();
                $this->line("{$school->name} : {$current->name} a déjà {$count} classe(s).");

                continue;
            }

            $closed = AcademicYear::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('is_closed', true)
                ->orderByDesc('start_date')
                ->first();

            if (! $closed) {
                $this->warn("{$school->name} : aucune année terminée source.");

                continue;
            }

            $result = AcademicYearProvisioner::provision($current, $closed);
            $this->info("{$school->name} : {$result['message']}");
        }

        return self::SUCCESS;
    }
}
