<?php

namespace App\Support;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class DashboardAcademicYearContext
{
    public const SESSION_KEYS = [
        'admin' => 'admin_dashboard_academic_year_id',
        'teacher' => 'teacher_dashboard_academic_year_id',
        'student' => 'student_dashboard_academic_year_id',
    ];

    public static function sessionKey(string $scope = 'admin'): string
    {
        return self::SESSION_KEYS[$scope] ?? self::SESSION_KEYS['admin'];
    }

    public static function resolve(?Request $request = null, string $scope = 'admin'): ?AcademicYear
    {
        $request ??= request();
        $sessionKey = self::sessionKey($scope);

        if ($request->filled('academic_year_id')) {
            $year = AcademicYear::find($request->integer('academic_year_id'));
            if ($year) {
                Session::put($sessionKey, $year->id);

                return $year;
            }
        }

        $sessionId = Session::get($sessionKey);
        if ($sessionId) {
            $year = AcademicYear::find($sessionId);
            if ($year) {
                return $year;
            }
        }

        $current = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderByDesc('start_date')->first();

        if ($current) {
            Session::put($sessionKey, $current->id);
        }

        return $current;
    }

    public static function select(AcademicYear $year, string $scope = 'admin'): void
    {
        Session::put(self::sessionKey($scope), $year->id);
    }

    /** @return Collection<int, AcademicYear> */
    public static function allYears(): Collection
    {
        return AcademicYear::orderByDesc('start_date')->get();
    }
}
