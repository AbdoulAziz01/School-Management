<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TeacherAssignment;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Support\SenegalGradeSequence;
use Illuminate\Support\Facades\Auth;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Récupérer les classes affectées via class_teacher
        $assignedClasses = $teacher->assignedClasses()->with('level')->get();
        $classIds = $assignedClasses->pluck('id')->toArray();
        
        // Nombre de classes
        $classesCount = $assignedClasses->count();
        
        // Nombre total d'élèves dans ces classes
        $studentsCount = User::whereIn('class_id', $classIds)
            ->whereIn('role', ['student', 'eleve'])
            ->where('status', 'approved')
            ->count();
        
        // Matières enseignées (via teacher_subjects ou teacher_assignments)
        $subjects = $teacher->subjects;
        $subjectsCount = $subjects->count();
        
        // Récupérer aussi les affectations TeacherAssignment si disponibles
        $assignments = TeacherAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->when($currentYear, fn($q) => $q->where('academic_year_id', $currentYear->id))
            ->get();
        
        // Notes récentes saisies par cet enseignant
        $subjectIds = $subjects->pluck('id')->merge($assignments->pluck('subject_id'))->unique()->toArray();
        
        $recentGrades = Grade::with(['user', 'subject'])
            ->whereIn('subject_id', $subjectIds)
            ->whereIn('user_id', function($query) use ($classIds) {
                $query->select('id')
                    ->from('users')
                    ->whereIn('class_id', $classIds);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Calculer les moyennes par classe et par matière enseignée
        $classAverages = [];
        foreach ($assignedClasses as $class) {
            $studentIds = User::where('class_id', $class->id)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->pluck('id');

            $classSubjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $class->id)
                ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
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

                $evaluations = $this->evaluationAverages($studentIds, $subjectId, $currentYear);

                $gradesQuery = Grade::whereIn('user_id', $studentIds)
                    ->where('subject_id', $subjectId)
                    ->whereIn('type', SenegalGradeSequence::ORDER)
                    ->whereIn('semester', [1, 2])
                    ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id));

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
                ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
                ->avg('grade');

            $classAverages[] = [
                'class'    => $class,
                'average'  => round($overall ?? 0, 2),
                'subjects' => $subjectAverages,
            ];
        }

        $classPerformanceJson = collect($classAverages)->map(fn ($item) => [
            'class'    => $item['class']->name ?? 'N/A',
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
            'currentYear'
        ));
    }

    /**
     * Moyennes par évaluation (S1 D1 → D2 → Compo, puis S2).
     *
     * @param  \Illuminate\Support\Collection<int, int>|array<int, int>  $studentIds
     * @return array<int, array{label: string, semester: int, type: string, average: float|null, count: int}>
     */
    private function evaluationAverages($studentIds, int $subjectId, ?AcademicYear $currentYear): array
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
                    ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id));

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
