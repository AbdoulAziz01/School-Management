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

        $subjects = $teacher->subjects()->get();
        $subjectsCount = $subjects->count();

        $assignments = TeacherAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->get();

        $subjectIds = $subjects->pluck('id')->merge($assignments->pluck('subject_id'))->unique()->toArray();

        $recentGrades = Grade::with(['user', 'subject'])
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

        $classAverages = [];
        foreach ($assignedClasses as $class) {
            $studentIds = User::where('class_id', $class->id)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->pluck('id');

            $classSubjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $class->id)
                ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
                ->pluck('subject_id')
                ->unique();

            if ($classSubjectIds->isEmpty()) {
                $classSubjectIds = collect($subjectIds);
            }

            $subjectAverages = [];

            foreach ($classSubjectIds as $subjectId) {
                $subject = Subject::find($subjectId);
                if (! $subject) {
                    continue;
                }

                $evaluations = $this->evaluationAverages($studentIds, $subjectId, $selectedYear);

                $gradesQuery = Grade::whereIn('user_id', $studentIds)
                    ->where('subject_id', $subjectId)
                    ->whereIn('type', SenegalGradeSequence::ORDER)
                    ->whereIn('semester', [1, 2])
                    ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id));

                $avg   = $gradesQuery->avg('grade');
                $count = $gradesQuery->count();

                $subjectAverages[] = [
                    'subject'     => $subject,
                    'average'     => $avg !== null ? round((float) $avg, 2) : null,
                    'count'       => $count,
                    'evaluations' => $evaluations,
                ];
            }

            $overall = Grade::whereIn('user_id', $studentIds)
                ->whereIn('subject_id', $classSubjectIds)
                ->whereIn('type', SenegalGradeSequence::ORDER)
                ->whereIn('semester', [1, 2])
                ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
                ->avg('grade');

            $classAverages[] = [
                'class'    => $class,
                'average'  => $overall !== null ? round((float) $overall, 2) : null,
                'subjects' => $subjectAverages,
            ];
        }

        $classPerformanceJson = collect($classAverages)->map(fn ($item) => [
            'class'    => $item['class']->display_name ?? ($item['class']->name ?? 'N/A'),
            'average'  => $item['average'],
            'subjects' => collect($item['subjects'])->map(fn ($s) => [
                'name'        => $s['subject']->name ?? '—',
                'average'     => $s['average'],
                'count'       => $s['count'],
                'evaluations' => $s['evaluations'],
            ])->values()->all(),
        ])->values()->all();

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
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>|array<int, int>  $studentIds
     * @return array<int, array{label: string, semester: int, type: string, average: float|null, count: int}>
     */
    private function evaluationAverages($studentIds, int $subjectId, ?AcademicYear $selectedYear): array
    {
        $shortLabels = [
            'devoir1'     => 'D1',
            'devoir2'     => 'D2',
            'composition' => 'Compo',
        ];

        $evaluations = [];

        foreach ([1, 2] as $semester) {
            foreach (SenegalGradeSequence::ORDER as $type) {
                $query = Grade::whereIn('user_id', $studentIds)
                    ->where('subject_id', $subjectId)
                    ->where('semester', $semester)
                    ->where('type', $type)
                    ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id));

                $avg   = $query->avg('grade');
                $count = $query->count();

                $evaluations[] = [
                    'label'    => 'S'.$semester.' · '.$shortLabels[$type],
                    'semester' => $semester,
                    'type'     => $type,
                    'average'  => $avg !== null ? round((float) $avg, 2) : null,
                    'count'    => $count,
                ];
            }
        }

        return $evaluations;
    }
}
