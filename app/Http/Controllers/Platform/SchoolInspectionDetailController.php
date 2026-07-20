<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentClassPromotionService;
use App\Support\ClassHistoricalContext;
use App\Support\Grading\GradeSequence;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SchoolInspectionDetailController extends Controller
{
    public function showClass(School $school, SchoolClass $class): View
    {
        abort_unless($class->school_id === $school->id, 404);

        $class->load([
            'academicYear',
            'level',
            'teacherAssignments.teacher',
            'teacherAssignments.subject',
        ]);

        $academicYear = $class->academicYear;
        $isReadOnly = $academicYear?->isReadOnly() ?? false;
        $promotionService = app(StudentClassPromotionService::class);

        $students = $this->studentsForClass($class, $academicYear, $isReadOnly, $promotionService);

        $studentAverages = $this->studentAveragesForYear($students->pluck('id')->all(), $academicYear, $class->school_id, $class);

        return view('platform.schools.classes.show', compact(
            'school',
            'class',
            'academicYear',
            'isReadOnly',
            'students',
            'studentAverages',
        ));
    }

    public function showUser(School $school, User $user): View
    {
        abort_unless($user->school_id === $school->id, 404);

        $user->load([
            'class.level',
            'class.academicYear',
            'teacherAssignments.schoolClass.academicYear',
            'teacherAssignments.subject',
        ]);

        $studentAverage = null;
        if ($user->isStudent() && $user->class?->academicYear) {
            $averages = $this->studentAveragesForYear(
                [$user->id],
                $user->class->academicYear,
                $school->id,
                $user->class
            );
            $studentAverage = $averages[$user->id] ?? null;
        }

        return view('platform.schools.users.show', compact('school', 'user', 'studentAverage'));
    }

    public function showSubject(School $school, Subject $subject): View
    {
        abort_unless($subject->school_id === $school->id, 404);

        $subject->load(['levels' => fn ($q) => $q->orderBy('order')]);

        return view('platform.schools.subjects.show', compact('school', 'subject'));
    }

    private function studentsForClass(
        SchoolClass $class,
        ?\App\Models\AcademicYear $academicYear,
        bool $isReadOnly,
        StudentClassPromotionService $promotionService,
    ): Collection {
        if ($isReadOnly && $academicYear) {
            return $promotionService->resolveClassCohort($class, $academicYear)->sortBy('name')->values();
        }

        return User::withoutGlobalScopes()
            ->where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, float>
     *
     * Moyenne pondérée normalisée sur 20 : chaque matière est ramenée à son
     * propre barème (primaire /10 par défaut, secondaire /20) avant d'être
     * pondérée par son coefficient.
     */
    private function studentAveragesForYear(array $studentIds, ?\App\Models\AcademicYear $academicYear, int $schoolId, SchoolClass $class): array
    {
        if ($studentIds === [] || ! $academicYear) {
            return [];
        }

        $query = Grade::query()
            ->whereIn('user_id', $studentIds)
            ->whereHas('subject');

        $query = ClassHistoricalContext::applyGradeYearFilter($query, $academicYear, $schoolId);
        $allGrades = $query->with('subject')->get();

        $averages = [];

        foreach ($studentIds as $studentId) {
            $grades = $allGrades->where('user_id', $studentId);
            if ($grades->isEmpty()) {
                continue;
            }

            $weightedSum = 0;
            $totalCoef = 0;

            foreach ($grades->groupBy('subject_id') as $subjectId => $subjectGrades) {
                $subjectAvg = $subjectGrades->avg('grade');
                if ($subjectAvg === null) {
                    continue;
                }

                $maxGrade = GradeSequence::maxGradeFor($class, (int) $subjectId);
                if ($maxGrade <= 0) {
                    continue;
                }

                $normalizedAvg = ($subjectAvg / $maxGrade) * 20;
                $coefficient = $subjectGrades->first()->subject->coefficient ?? 1;
                $weightedSum += $normalizedAvg * $coefficient;
                $totalCoef += $coefficient;
            }

            if ($totalCoef > 0) {
                $averages[$studentId] = round($weightedSum / $totalCoef, 2);
            }
        }

        return $averages;
    }
}
