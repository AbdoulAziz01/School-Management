<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Classes qu'un enseignant peut réellement voir/utiliser (tableau de bord,
 * présences, notes...) : union de deux modèles d'affectation distincts —
 * class_teacher (primaire, un seul titulaire par classe, via
 * User::assignedClasses()) et TeacherAssignment (collège/lycée, par classe
 * + matière, plusieurs enseignants possibles par classe).
 *
 * Avant l'introduction de cette classe, plusieurs contrôleurs enseignant
 * (dashboard, classes, présences, notes) ne lisaient que assignedClasses()
 * : un enseignant de collège/lycée, affecté uniquement via TeacherAssignment,
 * voyait 0 classe partout alors que ses affectations existaient bien.
 */
class TeacherClassResolver
{
    /** @return Collection<int, SchoolClass> */
    public static function forTeacher(User $teacher, ?AcademicYear $year = null): Collection
    {
        $primaireClasses = $teacher->assignedClasses()
            ->with(['level', 'academicYear'])
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->get();

        $secondaireClassIds = TeacherAssignment::where('teacher_id', $teacher->id)
            ->active()
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->pluck('class_id')
            ->unique();

        $secondaireClasses = SchoolClass::with(['level', 'academicYear'])
            ->whereIn('id', $secondaireClassIds)
            ->get();

        return $primaireClasses->concat($secondaireClasses)->unique('id')->values();
    }

    public static function hasAccess(User $teacher, int $classId, ?AcademicYear $year = null): bool
    {
        return $teacher->assignedClasses()->where('classes.id', $classId)->exists()
            || TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $classId)
                ->active()
                ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
                ->exists();
    }
}
