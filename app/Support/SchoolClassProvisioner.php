<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\School;
use App\Models\SchoolClass;

/**
 * Crée les classes par défaut (une par niveau) pour une année scolaire.
 * Primaire : CI … CM2 · Collège : 6ème … 3ème · Lycée : Seconde … Terminale · Mixte : tout.
 */
class SchoolClassProvisioner
{
    /** @var list<string> */
    public const CLASS_CYCLES = ['primaire', 'college', 'lycee'];

    /** @var array<string, int> */
    private const CYCLE_ORDER = [
        'primaire' => 1,
        'college' => 2,
        'lycee' => 3,
    ];

    /**
     * Crée les classes manquantes pour l'année (une classe par niveau, nom = nom du niveau).
     */
    public static function createDefaultsForYear(AcademicYear $year, ?School $school = null): int
    {
        $school ??= $year->school_id ? School::find($year->school_id) : null;

        if (! $school || $school->isFormation()) {
            return 0;
        }

        SchoolLevelProvisioner::syncLevelsForSchool($school);

        $levels = Level::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->whereIn('cycle', self::CLASS_CYCLES)
            ->get()
            ->sortBy(fn (Level $level) => (self::CYCLE_ORDER[$level->cycle] ?? 99) * 100 + (int) $level->order)
            ->values();

        $created = 0;

        foreach ($levels as $level) {
            if (self::classExistsForLevel($school->id, $year->id, $level)) {
                continue;
            }

            SchoolClass::withoutGlobalScopes()->create([
                'name' => self::defaultClassNameForLevel($level),
                'level_id' => $level->id,
                'academic_year_id' => $year->id,
                'school_id' => $school->id,
                'capacity' => 40,
            ]);

            $created++;
        }

        return $created;
    }

    public static function defaultClassNameForLevel(Level $level): string
    {
        return trim($level->name);
    }

    private static function classExistsForLevel(int $schoolId, int $academicYearId, Level $level): bool
    {
        $defaultName = self::defaultClassNameForLevel($level);

        return SchoolClass::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('level_id', $level->id)
            ->where('name', $defaultName)
            ->exists();
    }
}
