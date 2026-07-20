<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\User;

/**
 * Affectation matières/classes d'un enseignant — extrait de
 * TeacherController::syncTeacherTeaching() pour être réutilisable depuis
 * l'import en masse (TeacherImportService) sans dupliquer la logique.
 */
class TeacherTeachingService
{
    public function sync(User $teacher, array $subjectIds, array $classIds): void
    {
        $subjectIds = array_values(array_unique(array_map('intval', $subjectIds)));
        $classIds = array_values(array_unique(array_map('intval', $classIds)));

        $teacher->subjects()->sync($subjectIds);
        $teacher->assignedClasses()->sync($classIds);

        $currentYear = AcademicYear::where('is_current', true)->first();

        if (! $currentYear) {
            return;
        }

        $classes = SchoolClass::whereIn('id', $classIds)->get();

        $desired = [];
        foreach ($classes as $class) {
            $yearId = $class->academic_year_id ?? $currentYear->id;
            foreach ($subjectIds as $subjectId) {
                $desired["{$class->id}:{$subjectId}:{$yearId}"] = [$class->id, $subjectId, $yearId];
            }
        }

        // Supprime uniquement les affectations qui sortent de la nouvelle
        // sélection (classe/matière retirée) ; les affectations conservées
        // ne sont jamais recréées, pour ne pas écraser un `is_active=false`
        // que l'enseignant aurait défini lui-même (voir TeacherAssignment::scopeActive()).
        TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $currentYear->id)
            ->get(['id', 'class_id', 'subject_id', 'academic_year_id'])
            ->each(function (TeacherAssignment $row) use ($desired) {
                if (! isset($desired["{$row->class_id}:{$row->subject_id}:{$row->academic_year_id}"])) {
                    $row->delete();
                }
            });

        foreach ($desired as [$classId, $subjectId, $yearId]) {
            TeacherAssignment::firstOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'academic_year_id' => $yearId,
                ],
                ['school_id' => $teacher->school_id, 'is_active' => true]
            );
        }
    }
}
