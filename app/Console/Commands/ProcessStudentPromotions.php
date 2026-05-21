<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Services\StudentClassPromotionService;
use Illuminate\Console\Command;

class ProcessStudentPromotions extends Command
{
    protected $signature = 'students:process-promotions
                            {--school= : ID de l\'établissement}
                            {--class= : ID d\'une classe}
                            {--year= : ID de l\'année scolaire}';

    protected $description = 'Passe en classe supérieure les élèves admis (6ème → Terminale, moyenne ≥ seuil, notes complètes)';

    public function handle(StudentClassPromotionService $service): int
    {
        $year = $this->option('year')
            ? AcademicYear::withoutGlobalScopes()->find($this->option('year'))
            : AcademicYear::where('is_current', true)->first();

        if (! $year) {
            $this->error('Aucune année scolaire trouvée.');

            return self::FAILURE;
        }

        $schoolQuery = School::query()->where('establishment_type', '!=', School::TYPE_FORMATION);
        if ($this->option('school')) {
            $schoolQuery->where('id', $this->option('school'));
        }

        $schools = $schoolQuery->get();
        $totalPromoted = 0;

        foreach ($schools as $school) {
            if (! $service->appliesToSchool($school)) {
                continue;
            }

            $classQuery = SchoolClass::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->with('level');

            if ($this->option('class')) {
                $classQuery->where('id', $this->option('class'));
            }

            $classes = $classQuery->get();

            foreach ($classes as $class) {
                if ($class->level && ! in_array($class->level->cycle, ['college', 'lycee'], true)) {
                    continue;
                }

                $results = $service->processClass($class, $year);
                $promoted = collect($results)->where('promoted', true)->count();
                $totalPromoted += $promoted;

                if ($promoted > 0) {
                    $this->info("{$school->name} — {$class->name} : {$promoted} passage(s).");
                }
            }
        }

        $this->info("Terminé. {$totalPromoted} élève(s) promu(s) au total.");

        return self::SUCCESS;
    }
}
