<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\SchoolBot\BulletinComputation;
use App\Support\Grading\GradeSequence;
use Illuminate\Support\Collection;

/**
 * Indicateurs non-financiers du dashboard directeur ("Centre de Commande") —
 * effectifs, présence du jour, moyenne générale de l'école. Même esprit que
 * AccountingDashboardService (lecture seule, scope school_id), pour que le
 * directeur voie l'établissement dans son ensemble et pas uniquement ses
 * finances (voir AccountingDashboardService, qui reste inchangé).
 */
class SchoolOverviewService
{
    public function __construct(
        private BulletinComputation $bulletinComputation
    ) {}

    /** @return array<string, int> */
    public function headcounts(School $school): array
    {
        $countByRole = fn (array $roles) => User::where('school_id', $school->id)
            ->whereIn('role', $roles)
            ->count();

        return [
            'students' => $countByRole(User::ROLE_STUDENT_ALIASES),
            'classes' => SchoolClass::where('school_id', $school->id)->count(),
            'teachers' => $countByRole(User::ROLE_TEACHER_ALIASES),
            'surveillants' => $countByRole([User::ROLE_SURVEILLANT]),
            'admins' => $countByRole([User::ROLE_ADMIN]),
            'comptables' => $countByRole([User::ROLE_COMPTABLE, User::ROLE_CAISSIER]),
            // Pas de compte "parent" distinct dans ce système (juste des
            // champs sur la fiche élève) : on compte les élèves ayant au
            // moins un contact parent renseigné, à défaut d'un vrai
            // dénombrement de parents.
            'parents' => User::where('school_id', $school->id)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where(fn ($q) => $q->whereNotNull('parent_name')->orWhereNotNull('parent_whatsapp'))
                ->count(),
        ];
    }

    /** @return array<string, int> */
    public function attendanceToday(School $school): array
    {
        $studentIds = User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->pluck('id');

        $todayAttendance = Attendance::where('school_id', $school->id)
            ->whereDate('date', today())
            ->whereIn('user_id', $studentIds)
            ->get()
            ->unique('user_id');

        $absentStudents = $todayAttendance->where('status', 'absent')->count();
        $presentStudents = $todayAttendance->whereIn('status', ['present', 'late'])->count();

        // Un enseignant est considéré absent aujourd'hui s'il n'a fait
        // l'appel dans AUCUNE de ses classes alors qu'il a un emploi du
        // temps ce jour — approximation raisonnable en l'absence d'un vrai
        // pointage enseignant.
        $teacherIds = User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_TEACHER_ALIASES)
            ->pluck('id');

        $teachersWhoTookAttendanceToday = Attendance::where('school_id', $school->id)
            ->whereDate('date', today())
            ->whereIn('teacher_id', $teacherIds)
            ->distinct()
            ->pluck('teacher_id');

        $dayOfWeek = (int) now()->dayOfWeekIso; // 1 (lundi) .. 7
        $teachersScheduledToday = \App\Models\Schedule::where('school_id', $school->id)
            ->where('day_of_week', $dayOfWeek)
            ->whereIn('teacher_id', $teacherIds)
            ->distinct()
            ->pluck('teacher_id');

        $absentTeachers = $teachersScheduledToday->diff($teachersWhoTookAttendanceToday)->count();

        return [
            'students_present' => $presentStudents,
            'students_absent' => $absentStudents,
            'teachers_absent' => $absentTeachers,
        ];
    }

    /** @return array<string, mixed> */
    public function academicSnapshot(School $school): array
    {
        $academicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first();

        $students = User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->whereNotNull('class_id')
            ->with('schoolClass.level')
            ->get();

        $newEnrollments = User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($students->isEmpty() || ! $academicYear) {
            return [
                'general_average' => null,
                'success_rate' => null,
                'new_enrollments' => $newEnrollments,
                'students_in_difficulty' => 0,
            ];
        }

        $averages = collect();
        foreach ($students as $student) {
            $average = $this->studentGeneralAverage($student, $academicYear);
            if ($average !== null) {
                $averages->push($average);
            }
        }

        if ($averages->isEmpty()) {
            return [
                'general_average' => null,
                'success_rate' => null,
                'new_enrollments' => $newEnrollments,
                'students_in_difficulty' => 0,
            ];
        }

        $passing = $averages->filter(fn ($row) => $row['ratio'] >= 0.5);

        // L'établissement peut mélanger primaire (barème /10) et secondaire
        // (/20) : la moyenne générale école se base sur le RATIO (0..1) de
        // chaque élève, ramené à un /20 fixe pour l'affichage — jamais sur
        // 'normalized' (qui reste, lui, sur le barème propre à chaque élève
        // et n'est donc pas comparable/moyennable entre cycles différents).
        return [
            'general_average' => round($averages->avg('ratio') * 20, 2),
            'success_rate' => round(($passing->count() / $averages->count()) * 100, 1),
            'new_enrollments' => $newEnrollments,
            'students_in_difficulty' => $averages->count() - $passing->count(),
        ];
    }

    /**
     * Moyenne générale + taux de réussite d'UNE classe (utilisé par la
     * liste et la fiche classe côté directeur) — sur le barème propre à son
     * niveau ('max_grade' dans le retour, à afficher explicitement côté vue
     * puisque plusieurs classes listées ensemble peuvent avoir des barèmes
     * différents, primaire vs secondaire).
     *
     * @return array{average: float|null, success_rate: float|null, graded_count: int, max_grade: float}
     */
    public function classAcademicSummary(SchoolClass $class, ?AcademicYear $academicYear): array
    {
        $class->loadMissing('level');
        $maxGrade = $this->bulletinComputation->referenceMaxGradeForLevel($class->level);

        if (! $academicYear) {
            return ['average' => null, 'success_rate' => null, 'graded_count' => 0, 'max_grade' => $maxGrade];
        }

        $students = User::where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->get();

        $averages = collect();
        foreach ($students as $student) {
            $student->setRelation('schoolClass', $class);
            $average = $this->studentGeneralAverage($student, $academicYear);
            if ($average !== null) {
                $averages->push($average);
            }
        }

        if ($averages->isEmpty()) {
            return ['average' => null, 'success_rate' => null, 'graded_count' => 0, 'max_grade' => $maxGrade];
        }

        $passing = $averages->filter(fn ($row) => $row['ratio'] >= 0.5);

        return [
            'average' => round($averages->avg('normalized'), 2),
            'success_rate' => round(($passing->count() / $averages->count()) * 100, 1),
            'max_grade' => $maxGrade,
            'graded_count' => $averages->count(),
        ];
    }

    /**
     * Moyenne générale d'un élève, normalisée sur le barème de référence de
     * son niveau (10 en primaire par défaut, 20 en secondaire — voir
     * BulletinComputation::referenceMaxGradeForLevel()), avec le ratio de
     * réussite associé (0..1), comparable lui entre cycles différents.
     *
     * @return array{normalized: float, ratio: float}|null
     */
    public function studentGeneralAverage(User $student, AcademicYear $academicYear): ?array
    {
        $class = $student->schoolClass;
        if (! $class) {
            return null;
        }

        $grades = Grade::where('user_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->get();

        if ($grades->isEmpty()) {
            return null;
        }

        $referenceMax = $this->bulletinComputation->referenceMaxGradeForLevel($class->level);

        $normalized = $grades->groupBy('subject_id')->map(function (Collection $subjectGrades) use ($class) {
            $subjectId = (int) $subjectGrades->first()->subject_id;
            $maxGrade = GradeSequence::maxGradeFor($class, $subjectId);

            return $maxGrade > 0 ? ($subjectGrades->avg('grade') / $maxGrade) : null;
        })->filter(fn ($ratio) => $ratio !== null);

        if ($normalized->isEmpty()) {
            return null;
        }

        $ratio = $normalized->avg();

        return [
            'normalized' => $ratio * $referenceMax,
            'ratio' => $ratio,
        ];
    }
}
