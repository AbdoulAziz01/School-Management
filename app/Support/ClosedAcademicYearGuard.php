<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;

class ClosedAcademicYearGuard
{
    public static function isLocked(?AcademicYear $year): bool
    {
        return $year?->isReadOnly() ?? false;
    }

    public static function areGradesLocked(?AcademicYear $year): bool
    {
        return self::isLocked($year);
    }

    public static function gradesLockedMessage(?AcademicYear $year): string
    {
        if (! $year) {
            return 'Aucune année scolaire active — saisie des notes impossible.';
        }

        if ($year->isClosed()) {
            return "L'année scolaire « {$year->name} » est terminée — consultation seule, aucune saisie de notes possible.";
        }

        return "L'année scolaire « {$year->name} » n'est plus active — saisie des notes impossible.";
    }

    public static function isClassLocked(SchoolClass $class): bool
    {
        $class->loadMissing('academicYear');

        return self::isLocked($class->academicYear);
    }

    public static function denyClassMutation(SchoolClass $class, ?string $message = null): ?RedirectResponse
    {
        if (! self::isClassLocked($class)) {
            return null;
        }

        $yearName = $class->academicYear?->name ?? 'cette année';

        return back()->with(
            'error',
            $message ?? "L'année scolaire {$yearName} n'est plus active — consultation seule, aucune modification possible."
        );
    }

    public static function allowsStudentAssignment(SchoolClass $class): bool
    {
        $class->loadMissing('academicYear');

        return $class->academicYear?->allowsStudentAssignment() ?? false;
    }

    public static function denyStudentAssignment(SchoolClass $class, ?string $message = null): ?RedirectResponse
    {
        if (self::allowsStudentAssignment($class)) {
            return null;
        }

        $year = $class->academicYear;
        $yearName = $year?->name ?? 'inconnue';

        if ($year?->isClosed()) {
            $detail = "L'année « {$yearName} » est terminée.";
        } elseif ($year && ! $year->is_current) {
            $detail = "L'année « {$yearName} » est passée (non courante).";
        } else {
            $detail = 'Aucune année scolaire courante active pour cet établissement.';
        }

        return back()->with(
            'error',
            $message ?? "Affectation impossible : {$detail} Seules les classes de l'année courante sont autorisées."
        );
    }

    public static function denyYearMutation(?AcademicYear $year, ?string $message = null): ?RedirectResponse
    {
        if (! self::isLocked($year)) {
            return null;
        }

        return back()->with(
            'error',
            $message ?? "L'année scolaire {$year->name} n'est plus active — consultation seule, aucune modification possible."
        );
    }

    public static function denyYearIdMutation(?int $yearId, ?string $message = null): ?RedirectResponse
    {
        if (! $yearId) {
            return null;
        }

        return self::denyYearMutation(AcademicYear::find($yearId), $message);
    }
}
