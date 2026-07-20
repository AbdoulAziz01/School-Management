<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TeacherAssignment;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Support\ClosedAcademicYearGuard;
use App\Support\DashboardAcademicYearContext;
use App\Support\SenegalGradeSequence;
use App\Support\Grading\GradeSequence;
use App\Support\Grading\PrimaryGradeSequence;
use App\Support\TeacherSubjectResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherDashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user();
        $selectedYear = DashboardAcademicYearContext::resolve($request, 'teacher');
        $currentYear = AcademicYear::where('is_current', true)->first();
        $academicYears = DashboardAcademicYearContext::allYears();
        $isSelectedYearCurrent = $selectedYear && $currentYear
            && (int) $selectedYear->id === (int) $currentYear->id;
        $gradesLocked = ClosedAcademicYearGuard::areGradesLocked($selectedYear);
        $gradesLockedMessage = ClosedAcademicYearGuard::gradesLockedMessage($selectedYear);

        $assignedClasses = $teacher->assignedClasses()
            ->with('level')
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->get();
        $classIds = $assignedClasses->pluck('id')->toArray();

        $classesCount = $assignedClasses->count();

        $studentsCount = User::whereIn('class_id', $classIds)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->count();

        $assignments = TeacherAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->active()
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->get();

        // Matières réellement disponibles pour l'enseignant : croise ses
        // affectations actives (ou, à défaut, le pivot global
        // teacher_subjects) avec l'activation de la matière au niveau
        // (level_subject.is_active, voir TeacherSubjectResolver) — une
        // matière désactivée par l'admin pour un niveau (ex. grille
        // "Notation primaire") ne doit plus apparaître ici.
        $subjects = $assignedClasses
            ->flatMap(fn ($class) => TeacherSubjectResolver::forClass($teacher, $class, $selectedYear?->id))
            ->unique('id')
            ->values();
        $subjectsCount = $subjects->count();

        $subjectIds = $subjects->pluck('id')->unique()->toArray();

        $recentGrades = Grade::with(['user.schoolClass.level', 'subject'])
            ->whereIn('subject_id', $subjectIds)
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->whereIn('user_id', function ($query) use ($classIds) {
                $query->select('id')
                    ->from('users')
                    ->whereIn('class_id', $classIds);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Barème réel de chaque note (configurable pour le primaire, /20
        // par défaut) — évite d'afficher "7/20" pour une note saisie sur 10.
        foreach ($recentGrades as $grade) {
            $studentClass = $grade->user?->schoolClass;
            $grade->max_grade_display = $studentClass
                ? GradeSequence::maxGradeFor($studentClass, $grade->subject_id)
                : 20.0;
        }

        $classAverages = [];
        foreach ($assignedClasses as $class) {
            $studentIds = User::where('class_id', $class->id)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->pluck('id');

            $classSubjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $class->id)
                ->active()
                ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
                ->pluck('subject_id')
                ->unique();

            if ($classSubjectIds->isEmpty()) {
                $classSubjectIds = collect($subjectIds);
            }

            $isPrimaireClass = $class->level?->isPrimaireCycle() ?? false;
            $subjectAverages = [];

            foreach ($classSubjectIds as $subjectId) {
                $subject = Subject::find($subjectId);
                if (! $subject) {
                    continue;
                }

                $maxGrade = GradeSequence::maxGradeFor($class, $subjectId);
                $evaluations = $this->evaluationAverages($studentIds, $subjectId, $selectedYear, $class);

                $gradesQuery = Grade::whereIn('user_id', $studentIds)
                    ->where('subject_id', $subjectId)
                    ->whereIn('type', GradeSequence::allTypes())
                    ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id));

                $avg   = $gradesQuery->avg('grade');
                $count = $gradesQuery->count();

                $subjectAverages[] = [
                    'subject'     => $subject,
                    'average'     => $avg !== null ? round((float) $avg, 2) : null,
                    'max_grade'   => $maxGrade,
                    'count'       => $count,
                    'evaluations' => $evaluations,
                ];
            }

            // Barème de référence pour la moyenne globale de la classe : le
            // primaire n'affiche jamais de /20 (barème propre au cycle,
            // 10 par défaut) — on ramène chaque matière au barème le plus
            // représenté dans la classe plutôt que de forcer un /20 qui
            // n'a pas de sens pour ce cycle. Le secondaire garde 20
            // (barème unique, comportement inchangé).
            $referenceMaxGrade = 20.0;
            if ($isPrimaireClass) {
                $maxGradeCounts = collect($subjectAverages)
                    ->filter(fn ($s) => $s['average'] !== null && $s['max_grade'] > 0)
                    ->countBy('max_grade');
                $referenceMaxGrade = $maxGradeCounts->isNotEmpty()
                    ? (float) $maxGradeCounts->sortDesc()->keys()->first()
                    : 10.0;
            }

            $normalized = collect($subjectAverages)
                ->filter(fn ($s) => $s['average'] !== null && $s['max_grade'] > 0)
                ->map(fn ($s) => ($s['average'] / $s['max_grade']) * $referenceMaxGrade);

            $overall = $normalized->isNotEmpty() ? $normalized->avg() : null;

            $classAverages[] = [
                'class'       => $class,
                'is_primaire' => $isPrimaireClass,
                'average'     => $overall !== null ? round((float) $overall, 2) : null,
                'max_grade'   => $referenceMaxGrade,
                'subjects'    => $subjectAverages,
            ];
        }

        $classPerformanceJson = collect($classAverages)->map(fn ($item) => [
            'class'       => $item['class']->display_name ?? ($item['class']->name ?? 'N/A'),
            'is_primaire' => $item['is_primaire'],
            'average'     => $item['average'],
            'max_grade'   => $item['max_grade'] ?? 20.0,
            'subjects'    => collect($item['subjects'])->map(fn ($s) => [
                'name'        => $s['subject']->name ?? '—',
                'average'     => $s['average'],
                'max_grade'   => $s['max_grade'],
                'count'       => $s['count'],
                'evaluations' => $s['evaluations'],
            ])->values()->all(),
        ])->values()->all();

        $singleClassId = $classesCount === 1 ? $assignedClasses->first()?->id : null;

        return view('teacher.dashboard', compact(
            'teacher',
            'assignments',
            'classesCount',
            'studentsCount',
            'subjectsCount',
            'subjects',
            'recentGrades',
            'classAverages',
            'classPerformanceJson',
            'selectedYear',
            'currentYear',
            'academicYears',
            'isSelectedYearCurrent',
            'gradesLocked',
            'gradesLockedMessage',
            'singleClassId',
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int, int>  $studentIds
     * @return array<int, array{label: string, semester: int, type: string, average: float|null, count: int}>
     */
    private function evaluationAverages($studentIds, int $subjectId, ?AcademicYear $selectedYear, $class = null): array
    {
        $isPrimaire = $class?->level?->isPrimaireCycle() ?? false;

        $slots = $isPrimaire
            ? array_map(fn ($type) => [1, $type], PrimaryGradeSequence::orderFor($class, $subjectId))
            : (function () {
                $slots = [];
                foreach ([1, 2] as $semester) {
                    foreach (SenegalGradeSequence::ORDER as $type) {
                        $slots[] = [$semester, $type];
                    }
                }

                return $slots;
            })();

        $evaluations = [];

        foreach ($slots as [$semester, $type]) {
            $query = Grade::whereIn('user_id', $studentIds)
                ->where('subject_id', $subjectId)
                ->where('semester', $semester)
                ->where('type', $type)
                ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id));

            $avg   = $query->avg('grade');
            $count = $query->count();

            $evaluations[] = [
                'label'    => $isPrimaire
                    ? GradeSequence::shortLabel($type)
                    : 'S'.$semester.' · '.GradeSequence::shortLabel($type),
                'semester' => $semester,
                'type'     => $type,
                'average'  => $avg !== null ? round((float) $avg, 2) : null,
                'count'    => $count,
            ];
        }

        return $evaluations;
    }
}
