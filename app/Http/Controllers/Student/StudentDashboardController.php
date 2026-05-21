<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\Attendance;
use App\Support\DashboardAcademicYearContext;
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

            $average = (clone $gradesQuery)->count() > 0
                ? round((clone $gradesQuery)->avg('grade'), 2)
                : null;

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
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur tableau de bord élève', ['error' => $e->getMessage()]);

            return back()->with('error', 'Impossible de charger le tableau de bord.');
        }
    }
}
