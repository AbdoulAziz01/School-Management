<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\TeacherAssignment;
use App\Services\SchoolBot\BulletinComputation;
use App\Support\DashboardAcademicYearContext;
use App\Support\Grading\GradeSequence;
use App\Support\StudentClassContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentGradesController extends Controller
{
    public function __construct(
        private BulletinComputation $bulletinComputation
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $selectedYear = DashboardAcademicYearContext::resolve($request, 'student');
        $currentYear = AcademicYear::where('is_current', true)->first();
        $academicYears = DashboardAcademicYearContext::allYears();
        $isSelectedYearCurrent = $selectedYear && $currentYear
            && (int) $selectedYear->id === (int) $currentYear->id;

        $teachersBySubject = $this->getTeachersBySubject($user, $selectedYear);

        $realGrades = $user->grades()
            ->with('subject')
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->get();

        $studentClass = StudentClassContext::resolveForYear($user, $selectedYear);
        $isPrimaireStudent = $studentClass?->level?->isPrimaireCycle() ?? false;

        // Afficher uniquement les vraies notes (pas de données simulées)
        if ($realGrades->isEmpty()) {
            $grades = collect();
        } else {
            $grades = $realGrades->groupBy('subject.name')
                ->map(function ($subjectGrades) use ($teachersBySubject, $studentClass) {
                    $avg = $subjectGrades->avg('grade');
                    $subject = $subjectGrades->first()->subject;
                    $teacherName = $teachersBySubject[$subject->id] ?? 'Professeur';
                    $maxGrade = $studentClass ? GradeSequence::maxGradeFor($studentClass, $subject->id) : 20.0;

                    return [
                        'subject' => $subject->name,
                        'subject_color' => $subject->color ?? '#f59e0b',
                        'grades' => $subjectGrades->sortByDesc('date'),
                        'average' => round($avg, 2),
                        'max_grade' => $maxGrade,
                        'coefficient' => $subject->coefficient ?? 1,
                        'teacher' => $teacherName,
                        'appreciation' => $this->getAppreciation($avg, $maxGrade)
                    ];
                });
        }

        // Moyenne générale normalisée sur le barème de référence du
        // niveau (10 par défaut en primaire, sauf configuration admin
        // différente ; 20 en secondaire, inchangé — voir
        // BulletinComputation::referenceMaxGradeForLevel()). Les matières
        // primaire peuvent avoir des barèmes différents entre elles, d'où
        // la normalisation avant moyenne.
        $overallMaxGrade = $this->bulletinComputation->referenceMaxGradeForLevel($studentClass?->level);
        $generalAverage = null;
        if ($grades->isNotEmpty()) {
            $normalized = $grades->filter(fn ($g) => $g['max_grade'] > 0)
                ->map(fn ($g) => ($g['average'] / $g['max_grade']) * $overallMaxGrade);
            $generalAverage = $normalized->isNotEmpty() ? round($normalized->avg(), 2) : null;
        }

        $studentInfo = [
            'name' => $user->name,
            'class' => StudentClassContext::labelForYear($user, $selectedYear),
            'identifier' => $user->identifier ?? '-',
            'academic_year' => $selectedYear?->name ?? '—',
            'trimester' => $this->getCurrentTrimester(),
        ];

        $gradesEvolution = $this->getGradesEvolution($user, $selectedYear, $overallMaxGrade);

        return view('student.grades', compact(
            'grades',
            'generalAverage',
            'overallMaxGrade',
            'studentInfo',
            'gradesEvolution',
            'selectedYear',
            'currentYear',
            'academicYears',
            'isSelectedYearCurrent',
            'isPrimaireStudent',
        ));
    }
    
    /**
     * Récupérer les enseignants par matière pour la classe de l'élève
     */
    private function getTeachersBySubject($user, ?AcademicYear $year = null): array
    {
        $class = StudentClassContext::resolveForYear($user, $year);
        if (! $class) {
            return [];
        }

        $yearId = $year?->id;
        $teachersBySubject = [];

        $assignments = TeacherAssignment::with('teacher')
            ->where('class_id', $class->id)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->get();

        foreach ($assignments as $assignment) {
            if ($assignment->teacher && ! isset($teachersBySubject[$assignment->subject_id])) {
                $teachersBySubject[$assignment->subject_id] = $assignment->teacher->name;
            }
        }

        if ($teachersBySubject !== []) {
            return $teachersBySubject;
        }

        $classTeachers = DB::table('class_teacher')
            ->join('users', 'class_teacher.teacher_id', '=', 'users.id')
            ->where('class_teacher.class_id', $class->id)
            ->select('users.id', 'users.name')
            ->get();

        foreach ($classTeachers as $teacher) {
            $subjectIds = DB::table('teacher_subjects')
                ->where('teacher_id', $teacher->id)
                ->pluck('subject_id');

            foreach ($subjectIds as $subjectId) {
                if (! isset($teachersBySubject[$subjectId])) {
                    $teachersBySubject[$subjectId] = $teacher->name;
                }
            }
        }

        return $teachersBySubject;
    }

    /**
     * Obtenir le trimestre actuel
     */
    private function getCurrentTrimester()
    {
        $month = now()->month;
        if ($month >= 9 && $month <= 12) return 1;
        if ($month >= 1 && $month <= 3) return 2;
        return 3;
    }

    /**
     * Générer une appréciation basée sur le pourcentage de réussite
     * (jamais un seuil absolu, qui n'a de sens que sur /20).
     */
    private function getAppreciation($average, float $maxGrade = 20.0)
    {
        $ratio = $maxGrade > 0 ? $average / $maxGrade : 0;

        if ($ratio >= 16 / 20) return 'Excellent travail. Continuez ainsi !';
        if ($ratio >= 14 / 20) return 'Très bon travail. Félicitations.';
        if ($ratio >= 12 / 20) return 'Bon travail. Peut encore progresser.';
        if ($ratio >= 10 / 20) return 'Travail satisfaisant. Des efforts à fournir.';
        if ($ratio >= 8 / 20) return 'Insuffisant. Doit travailler davantage.';
        return 'Travail très insuffisant. Ressaisissez-vous.';
    }

    /**
     * Évolution mensuelle des moyennes de l'élève sur toute l'année scolaire.
     */
    private function getGradesEvolution($user, ?AcademicYear $selectedYear = null, float $overallMaxGrade = 20.0): array
    {
        $grades = $user->grades()
            ->with('subject')
            ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        if ($grades->isEmpty()) {
            return [];
        }

        $user->loadMissing('schoolClass');
        $academicYear = $selectedYear ?? $user->schoolClass?->academicYear;
        $studentClass = $user->schoolClass;
        [$periodStart, $periodEnd] = $this->getAcademicYearMonthRange($academicYear);

        $evolution = [];
        $currentMonth = $periodStart->copy();

        while ($currentMonth <= $periodEnd) {
            $monthEnd = $currentMonth->copy()->endOfMonth();

            $gradesUpToMonth = $grades->filter(function ($grade) use ($monthEnd) {
                $date = $grade->date ?? $grade->created_at;

                return $date && $date->lte($monthEnd);
            });

            $monthAverage = $this->weightedMonthlyAverage($gradesUpToMonth, $studentClass, $overallMaxGrade);

            $evolution[] = [
                'month' => $currentMonth->translatedFormat('M Y'),
                'grade' => $monthAverage,
                'count' => $gradesUpToMonth->count(),
            ];

            $currentMonth->addMonth();
        }

        $hasChartData = collect($evolution)->contains(fn ($row) => $row['grade'] !== null);

        if (! $hasChartData && $grades->isNotEmpty()) {
            $gradeStart = Carbon::parse($grades->min('date'))->startOfMonth();
            $gradeEnd = Carbon::parse($grades->max('date'))->endOfMonth();
            if ($gradeEnd->gt(now())) {
                $gradeEnd = now()->copy()->endOfMonth();
            }

            $evolution = [];
            $currentMonth = $gradeStart->copy();

            while ($currentMonth <= $gradeEnd) {
                $monthEnd = $currentMonth->copy()->endOfMonth();
                $gradesUpToMonth = $grades->filter(function ($grade) use ($monthEnd) {
                    $date = $grade->date ?? $grade->created_at;

                    return $date && $date->lte($monthEnd);
                });

                $monthAverage = $this->weightedMonthlyAverage($gradesUpToMonth, $studentClass, $overallMaxGrade);

                $evolution[] = [
                    'month' => $currentMonth->translatedFormat('M Y'),
                    'grade' => $monthAverage,
                    'count' => $gradesUpToMonth->count(),
                ];
                $currentMonth->addMonth();
            }
        }

        return $evolution;
    }

    /**
     * Moyenne pondérée par coefficient, chaque matière étant d'abord
     * normalisée sur le barème de référence du niveau ($overallMaxGrade —
     * 10 par défaut en primaire, 20 en secondaire) avant d'être moyennée —
     * sinon une matière /10 fausserait la moyenne mensuelle du graphique.
     */
    private function weightedMonthlyAverage($gradesUpToMonth, $studentClass, float $overallMaxGrade = 20.0): ?float
    {
        if ($gradesUpToMonth->isEmpty()) {
            return null;
        }

        $weightedSum = 0;
        $totalCoef = 0;

        foreach ($gradesUpToMonth->groupBy('subject_id') as $subjectId => $subjectGrades) {
            $subjectAvg = $subjectGrades->avg('grade');
            if ($subjectAvg === null) {
                continue;
            }
            $maxGrade = $studentClass ? GradeSequence::maxGradeFor($studentClass, (int) $subjectId) : 20.0;
            $normalizedAvg = $maxGrade > 0 ? ($subjectAvg / $maxGrade) * $overallMaxGrade : $subjectAvg;
            $coefficient = $subjectGrades->first()->subject->coefficient ?? 1;
            $weightedSum += $normalizedAvg * $coefficient;
            $totalCoef += $coefficient;
        }

        return $totalCoef > 0 ? round($weightedSum / $totalCoef, 2) : null;
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function getAcademicYearMonthRange($academicYear): array
    {
        $now = Carbon::now();

        if ($academicYear?->start_date && $academicYear?->end_date) {
            $start = Carbon::parse($academicYear->start_date)->startOfMonth();
            $end = Carbon::parse($academicYear->end_date)->endOfMonth();

            if ($now->lt($start)) {
                $start->subYear();
                $end->subYear();
            }
        } else {
            $start = $now->copy()->month(9)->day(1)->startOfMonth();
            if ($now->month < 9) {
                $start->subYear();
            }
            $end = $start->copy()->addMonths(9)->endOfMonth();
        }

        if ($end->gt($now)) {
            $end = $now->copy()->endOfMonth();
        }

        if ($start->gt($end)) {
            $start = $end->copy()->startOfMonth();
        }

        return [$start, $end];
    }
}
