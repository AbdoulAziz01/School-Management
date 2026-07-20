<?php

namespace App\Support;

use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Matières qu'un enseignant peut réellement utiliser (tableaux de bord,
 * saisie de notes) pour une classe donnée : croise l'affectation de
 * l'enseignant (TeacherAssignment actif, ou à défaut le pivot global
 * teacher_subjects) avec l'activation de la matière au niveau
 * (level_subject.is_active — voir migration
 * add_is_active_to_level_subject_table). Une matière désactivée par
 * l'admin pour un niveau (typiquement le primaire, via la grille
 * "Notation primaire") doit disparaître partout côté enseignant, même si
 * elle reste dans teacher_subjects ou une ancienne TeacherAssignment.
 *
 * Les niveaux qui n'ont aucune ligne level_subject (secondaire aujourd'hui)
 * ne sont pas filtrés : cette restriction ne s'applique qu'aux niveaux
 * explicitement configurés.
 */
class TeacherSubjectResolver
{
    /** @return Collection<int, \App\Models\Subject> */
    public static function forClass(User $teacher, SchoolClass $class, ?int $academicYearId = null): Collection
    {
        $class->loadMissing('level');

        $assignments = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('class_id', $class->id)
            ->active()
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->with('subject')
            ->get();

        $subjects = $assignments->isNotEmpty()
            ? $assignments->pluck('subject')->filter()->unique('id')->values()
            : $teacher->subjects()->get();

        return self::filterActiveForLevel($subjects, $class);
    }

    /**
     * @param  Collection<int, \App\Models\Subject>  $subjects
     * @return Collection<int, \App\Models\Subject>
     */
    public static function filterActiveForLevel(Collection $subjects, SchoolClass $class): Collection
    {
        $class->loadMissing('level');
        if (! $class->level) {
            return $subjects;
        }

        $levelSubjectRows = $class->level->subjects();
        if ($levelSubjectRows->count() === 0) {
            // Niveau jamais configuré (secondaire aujourd'hui) : aucune restriction.
            return $subjects;
        }

        $activeIds = $class->level->subjects()->wherePivot('is_active', true)->pluck('subjects.id');

        return $subjects->filter(fn ($s) => $activeIds->contains($s->id))->values();
    }

    public static function hasAccess(User $teacher, SchoolClass $class, int $subjectId, ?int $academicYearId = null): bool
    {
        return self::forClass($teacher, $class, $academicYearId)->contains('id', $subjectId);
    }

    /**
     * true si la matière n'est pas explicitement désactivée pour ce niveau
     * (level_subject.is_active) — true aussi si le niveau n'a aucune ligne
     * level_subject (secondaire aujourd'hui, jamais configuré).
     */
    public static function isSubjectActiveForLevel(int $subjectId, SchoolClass $class): bool
    {
        $class->loadMissing('level');
        if (! $class->level) {
            return true;
        }

        $levelSubjectRows = $class->level->subjects();
        if ($levelSubjectRows->count() === 0) {
            return true;
        }

        return $class->level->subjects()
            ->wherePivot('subject_id', $subjectId)
            ->wherePivot('is_active', true)
            ->exists();
    }
}
