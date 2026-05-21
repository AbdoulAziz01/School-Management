<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;

class StudentClassContext
{
    public static function resolveForYear(User $user, ?AcademicYear $year): ?SchoolClass
    {
        $user->loadMissing(['schoolClass.level', 'schoolClass.academicYear']);

        if (! $year) {
            return $user->schoolClass;
        }

        if ($user->schoolClass && (int) $user->schoolClass->academic_year_id === (int) $year->id) {
            return $user->schoolClass;
        }

        if ((int) $user->last_promotion_academic_year_id === (int) $year->id && $user->promotion_source_class_id) {
            return SchoolClass::with('level')->find($user->promotion_source_class_id);
        }

        if ($user->promotion_source_class_id) {
            $sourceClass = SchoolClass::with('level')->find($user->promotion_source_class_id);
            if ($sourceClass && (int) $sourceClass->academic_year_id === (int) $year->id) {
                return $sourceClass;
            }
        }

        return null;
    }

    public static function labelForYear(User $user, ?AcademicYear $year): string
    {
        $class = self::resolveForYear($user, $year);

        return $class?->display_name ?? 'Non assigné';
    }
}
