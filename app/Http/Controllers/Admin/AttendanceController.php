<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\DashboardAcademicYearContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Consultation (lecture seule) des présences pour l'administration —
 * transparence sur l'appel fait par les enseignants, toutes classes de
 * l'établissement confondues. Symétrique de TeacherAttendanceController,
 * mais sans saisie : l'admin ne fait jamais l'appel lui-même.
 */
class AttendanceController extends Controller
{
    /**
     * Détail d'un appel (classe + date) : statut de chaque élève.
     */
    public function index(Request $request)
    {
        $selectedYear = DashboardAcademicYearContext::resolve($request, 'admin');

        $classes = $selectedYear
            ? SchoolClass::with('level')
                ->where('academic_year_id', $selectedYear->id)
                ->orderBy('name')
                ->get()
            : collect();

        $selectedClassId = $request->get('class_id');
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $students = collect();
        $attendances = collect();

        if ($selectedClassId) {
            $hasClass = $classes->contains('id', (int) $selectedClassId);
            if (! $hasClass) {
                return redirect()->route('admin.attendance.index')
                    ->with('error', 'Classe introuvable pour cette année scolaire.');
            }

            $students = User::where('class_id', $selectedClassId)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->orderBy('name')
                ->get();

            $date = Carbon::parse($selectedDate)->toDateString();
            $attendances = Attendance::with('teacher')
                ->whereIn('user_id', $students->pluck('id'))
                ->whereDate('date', $date)
                ->orderByDesc('updated_at')
                ->get()
                ->unique('user_id')
                ->keyBy(fn (Attendance $row) => (int) $row->user_id);
        }

        return view('admin.attendance.index', compact(
            'classes',
            'students',
            'attendances',
            'selectedClassId',
            'selectedDate',
            'selectedYear',
        ));
    }

    /**
     * Historique des appels d'une classe : un jour par ligne, avec le
     * décompte présents/absents/retards/excusés, pour repérer d'un coup
     * d'œil les jours où l'appel n'a pas été fait.
     */
    public function history(Request $request)
    {
        $selectedYear = DashboardAcademicYearContext::resolve($request, 'admin');

        $classes = $selectedYear
            ? SchoolClass::with('level')
                ->where('academic_year_id', $selectedYear->id)
                ->orderBy('name')
                ->get()
            : collect();

        $selectedClassId = $request->get('class_id');
        $days = collect();
        $selectedClass = null;

        if ($selectedClassId) {
            $selectedClass = $classes->firstWhere('id', (int) $selectedClassId);
            if (! $selectedClass) {
                return redirect()->route('admin.attendance.history')
                    ->with('error', 'Classe introuvable pour cette année scolaire.');
            }

            $days = Attendance::with('teacher')
                ->where('class_id', $selectedClassId)
                ->get()
                ->groupBy(fn (Attendance $a) => $a->date->toDateString())
                ->map(function ($rows, $date) {
                    return [
                        'date' => $date,
                        'total' => $rows->count(),
                        'present' => $rows->where('status', 'present')->count(),
                        'absent' => $rows->where('status', 'absent')->count(),
                        'late' => $rows->where('status', 'late')->count(),
                        'excused' => $rows->where('status', 'excused')->count(),
                        'teacher' => $rows->first()->teacher?->name,
                    ];
                })
                ->sortByDesc('date')
                ->values();
        }

        return view('admin.attendance.history', compact(
            'classes',
            'selectedClassId',
            'selectedClass',
            'days',
            'selectedYear',
        ));
    }
}
