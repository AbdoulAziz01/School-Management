<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use Illuminate\Support\Facades\DB;

class AcademicYearProvisioner
{
    /**
     * Prépare une nouvelle année : niveaux, matières, classes, affectations, emplois du temps.
     *
     * @return array{classes: int, assignments: int, schedules: int, skipped: bool, message: string}
     */
    public static function provision(AcademicYear $year, ?AcademicYear $sourceYear = null): array
    {
        $summary = [
            'classes' => 0,
            'assignments' => 0,
            'schedules' => 0,
            'skipped' => false,
            'message' => '',
        ];

        $schoolId = $year->school_id;
        if (! $schoolId) {
            $summary['message'] = 'Établissement non défini.';

            return $summary;
        }

        $school = School::find($schoolId);
        if ($school?->isFormation()) {
            return FormationAcademicYearProvisioner::provision($year, $sourceYear);
        }

        SchoolSubjectProvisioner::ensureForSchool($schoolId);

        $sourceYear ??= self::resolveSourceYear($year);

        $yearHasClasses = SchoolClass::withoutGlobalScopes()
            ->where('academic_year_id', $year->id)
            ->exists();

        if ($yearHasClasses && ! $sourceYear) {
            $added = SchoolClassProvisioner::createDefaultsForYear($year, $school);
            if ($added > 0) {
                $summary['classes'] = $added;
                $summary['message'] = "{$added} classe(s) par défaut ajoutée(s) pour les niveaux manquants.";
            } else {
                $summary['skipped'] = true;
                $summary['message'] = 'Des classes existent déjà pour tous les niveaux de cette année.';
            }

            return $summary;
        }

        if ($yearHasClasses && $sourceYear) {
            $summary['skipped'] = true;
            $summary['message'] = 'Des classes existent déjà pour cette année.';

            return $summary;
        }

        DB::transaction(function () use ($year, $sourceYear, $schoolId, &$summary) {
            $classMap = self::replicateClasses($year, $sourceYear, $schoolId, $summary);

            if ($classMap !== []) {
                self::replicateClassRelations($sourceYear, $classMap, $summary);
            }
        });

        if ($summary['classes'] === 0) {
            $summary['message'] = 'Aucune classe créée.';
        } else {
            $summary['message'] = "{$summary['classes']} classe(s), {$summary['assignments']} affectation(s) prof, {$summary['schedules']} créneau(x) EDT générés.";
        }

        return $summary;
    }

    private static function resolveSourceYear(AcademicYear $year): ?AcademicYear
    {
        $query = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $year->school_id)
            ->where('id', '!=', $year->id);

        if ($year->start_date) {
            $previous = (clone $query)
                ->where('start_date', '<', $year->start_date)
                ->orderByDesc('start_date')
                ->first();

            if ($previous && SchoolClass::withoutGlobalScopes()->where('academic_year_id', $previous->id)->exists()) {
                return $previous;
            }
        }

        return $query
            ->whereHas('classes')
            ->orderByDesc('start_date')
            ->first();
    }

    /**
     * @return array<int, int> old_class_id => new_class_id
     */
    private static function replicateClasses(
        AcademicYear $year,
        ?AcademicYear $sourceYear,
        int $schoolId,
        array &$summary
    ): array {
        $classMap = [];

        if ($sourceYear) {
            $sourceClasses = SchoolClass::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where('academic_year_id', $sourceYear->id)
                ->orderBy('name')
                ->get();

            foreach ($sourceClasses as $sourceClass) {
                $newClass = SchoolClass::withoutGlobalScopes()->create([
                    'name' => $sourceClass->getRawOriginal('name') ?? $sourceClass->name,
                    'level_id' => $sourceClass->level_id,
                    'academic_year_id' => $year->id,
                    'school_id' => $schoolId,
                    'capacity' => $sourceClass->capacity,
                    'description' => $sourceClass->description,
                    'room_number' => $sourceClass->room_number,
                ]);

                $classMap[$sourceClass->id] = $newClass->id;
                $summary['classes']++;
            }
        }

        $summary['classes'] += SchoolClassProvisioner::createDefaultsForYear($year, School::find($schoolId));

        return $classMap;
    }

    /**
     * @param  array<int, int>  $classMap
     */
    private static function replicateClassRelations(?AcademicYear $sourceYear, array $classMap, array &$summary): void
    {
        if (! $sourceYear) {
            return;
        }

        $oldClassIds = array_keys($classMap);

        foreach ($classMap as $oldId => $newId) {
            $oldClass = SchoolClass::withoutGlobalScopes()->find($oldId);
            $newClass = SchoolClass::withoutGlobalScopes()->find($newId);
            if (! $oldClass || ! $newClass) {
                continue;
            }

            $subjectIds = $oldClass->subjects()->pluck('subjects.id');
            if ($subjectIds->isNotEmpty()) {
                $newClass->subjects()->syncWithoutDetaching($subjectIds);
            }

            $teacherIds = $oldClass->teachers()->pluck('users.id');
            foreach ($teacherIds as $teacherId) {
                if (! $newClass->teachers()->where('users.id', $teacherId)->exists()) {
                    $newClass->teachers()->attach($teacherId);
                }
            }
        }

        $assignments = TeacherAssignment::withoutGlobalScopes()
            ->where('academic_year_id', $sourceYear->id)
            ->whereIn('class_id', $oldClassIds)
            ->get();

        foreach ($assignments as $assignment) {
            $newClassId = $classMap[$assignment->class_id] ?? null;
            if (! $newClassId) {
                continue;
            }

            $newYearId = SchoolClass::withoutGlobalScopes()->find($newClassId)?->academic_year_id;

            TeacherAssignment::withoutGlobalScopes()->firstOrCreate([
                'teacher_id' => $assignment->teacher_id,
                'class_id' => $newClassId,
                'subject_id' => $assignment->subject_id,
                'academic_year_id' => $newYearId ?? $assignment->academic_year_id,
            ], [
                'school_id' => $assignment->school_id,
            ]);

            $summary['assignments']++;
        }

        $schedules = Schedule::withoutGlobalScopes()
            ->whereIn('class_id', $oldClassIds)
            ->get();

        foreach ($schedules as $schedule) {
            $newClassId = $classMap[$schedule->class_id] ?? null;
            if (! $newClassId) {
                continue;
            }

            $exists = Schedule::withoutGlobalScopes()
                ->where('class_id', $newClassId)
                ->where('day_of_week', $schedule->day_of_week)
                ->where('start_time', $schedule->start_time)
                ->where('subject_id', $schedule->subject_id)
                ->exists();

            if ($exists) {
                continue;
            }

            Schedule::withoutGlobalScopes()->create([
                'class_id' => $newClassId,
                'subject_id' => $schedule->subject_id,
                'teacher_id' => $schedule->teacher_id,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'room' => $schedule->room,
                'school_id' => $schedule->school_id,
            ]);

            $summary['schedules']++;
        }
    }
}
