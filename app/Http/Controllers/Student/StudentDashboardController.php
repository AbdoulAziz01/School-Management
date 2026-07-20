<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Attendance;
use App\Support\DashboardAcademicYearContext;
use App\Support\Grading\GradeSequence;
use App\Support\StudentClassContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            if (! Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();
            $selectedYear = DashboardAcademicYearContext::resolve($request, 'student');
            $currentYear = AcademicYear::where('is_current', true)->first();
            $academicYears = DashboardAcademicYearContext::allYears();
            $isSelectedYearCurrent = $selectedYear && $currentYear
                && (int) $selectedYear->id === (int) $currentYear->id;

            $user->loadMissing(['schoolClass.level', 'schoolClass.academicYear']);
            $studentClass = StudentClassContext::resolveForYear($user, $selectedYear);
            $classLabel = StudentClassContext::labelForYear($user, $selectedYear);

            $gradesQuery = Grade::where('user_id', $user->id)
                ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id));

            // Moyenne toujours normalisée sur 20 : chaque matière est
            // ramenée à son propre barème avant d'être moyennée (une
            // matière primaire /10 ne doit pas fausser la moyenne affichée
            // "/20", voir StudentGradesController pour le même principe).
            $average = null;
            $allGrades = (clone $gradesQuery)->with('subject')->get();
            if ($allGrades->isNotEmpty()) {
                $normalized = $allGrades->groupBy('subject_id')
                    ->map(function ($subjectGrades) use ($studentClass) {
                        $subjectAvg = $subjectGrades->avg('grade');
                        $maxGrade = $studentClass ? GradeSequence::maxGradeFor($studentClass, (int) $subjectGrades->first()->subject_id) : 20.0;

                        return $maxGrade > 0 ? ($subjectAvg / $maxGrade) * 20 : null;
                    })
                    ->filter(fn ($v) => $v !== null);

                $average = $normalized->isNotEmpty() ? round($normalized->avg(), 2) : null;
            }

            $attendancesQuery = Attendance::where('user_id', $user->id);
            if ($selectedYear?->start_date) {
                $attendancesQuery->whereDate('date', '>=', $selectedYear->start_date);
            }
            if ($selectedYear?->end_date) {
                $attendancesQuery->whereDate('date', '<=', $selectedYear->end_date);
            }

            $totalAttendances = (clone $attendancesQuery)->count();
            $presentCount = (clone $attendancesQuery)->where('status', 'present')->count();
            $attendanceRate = $totalAttendances > 0
                ? round(($presentCount / $totalAttendances) * 100, 1)
                : null;

            $recentGrades = Grade::with('subject')
                ->where('user_id', $user->id)
                ->when($selectedYear, fn ($q) => $q->where('academic_year_id', $selectedYear->id))
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $isPrimaireStudent = $studentClass?->level?->isPrimaireCycle() ?? false;
            foreach ($recentGrades as $grade) {
                $grade->max_grade_display = $studentClass
                    ? GradeSequence::maxGradeFor($studentClass, (int) $grade->subject_id)
                    : 20.0;
            }

            return view('student.dashboard', [
                'user' => $user,
                'average' => $average,
                'attendanceRate' => $attendanceRate,
                'upcomingCourses' => collect(),
                'recentGrades' => $recentGrades,
                'selectedYear' => $selectedYear,
                'currentYear' => $currentYear,
                'academicYears' => $academicYears,
                'isSelectedYearCurrent' => $isSelectedYearCurrent,
                'studentClass' => $studentClass,
                'classLabel' => $classLabel,
                'isPrimaireStudent' => $isPrimaireStudent,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur tableau de bord élève', ['error' => $e->getMessage()]);

            return back()->with('error', 'Impossible de charger le tableau de bord.');
        }
    }
}
