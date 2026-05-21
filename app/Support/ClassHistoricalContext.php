<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Builder;

class ClassHistoricalContext
{
    /**
     * Filtre les notes d'une année scolaire (inclut les doublons d'année et les notes par date).
     *
     * @param  Builder<\App\Models\Grade>  $query
     */
    public static function applyGradeYearFilter(Builder $query, AcademicYear $academicYear, ?int $schoolId = null): Builder
    {
        $yearIds = self::relatedYearIds($academicYear, $schoolId);

        return $query->where(function ($yearQuery) use ($yearIds, $academicYear) {
            $yearQuery->whereIn('academic_year_id', $yearIds);

            if ($academicYear->start_date) {
                $end = $academicYear->end_date
                    ?? $academicYear->start_date->copy()->addMonths(10);

                $yearQuery->orWhere(function ($dateQuery) use ($academicYear, $end) {
                    $dateQuery->whereNull('academic_year_id')
                        ->whereBetween('date', [$academicYear->start_date, $end]);
                });
            }
        });
    }

    /** @return list<int> */
    public static function relatedYearIds(AcademicYear $academicYear, ?int $schoolId = null): array
    {
        $schoolId ??= $academicYear->school_id;

        $query = AcademicYear::withoutGlobalScopes()->where('id', $academicYear->id);

        if ($schoolId) {
            $query = AcademicYear::withoutGlobalScopes()
                ->where('school_id', $schoolId)
                ->where(function ($q) use ($academicYear) {
                    $q->where('id', $academicYear->id);

                    if ($academicYear->start_date) {
                        $q->orWhereBetween('start_date', [
                            $academicYear->start_date->copy()->subYear(),
                            $academicYear->start_date->copy()->addYear(),
                        ]);
                    }
                });
        }

        return $query->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /** Classe archivée du même niveau pour l'année précédente. */
    public static function findArchivedMirrorClass(SchoolClass $class): ?SchoolClass
    {
        $class->loadMissing(['academicYear', 'level']);
        $year = $class->academicYear;

        if (! $year || $year->isReadOnly() || ! $class->level_id) {
            return null;
        }

        $previousYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $class->school_id)
            ->where('start_date', '<', $year->start_date ?? now())
            ->orderByDesc('start_date')
            ->first();

        if (! $previousYear) {
            return null;
        }

        return SchoolClass::withoutGlobalScopes()
            ->where('school_id', $class->school_id)
            ->where('academic_year_id', $previousYear->id)
            ->where('level_id', $class->level_id)
            ->first();
    }
}
