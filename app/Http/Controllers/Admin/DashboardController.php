<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\DashboardAcademicYearContext;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = DashboardAcademicYearContext::resolve($request);
        $currentYear = AcademicYear::where('is_current', true)->first();
        $academicYears = DashboardAcademicYearContext::allYears();

        $pendingCount = User::where('status', 'pending')->count();

        $classesCount = 0;
        $totalStudentsCount = 0;

        if ($selectedYear) {
            $classIds = SchoolClass::where('academic_year_id', $selectedYear->id)->pluck('id');
            $classesCount = $classIds->count();
            $totalStudentsCount = User::whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->whereIn('class_id', $classIds)
                ->count();
        }

        $isSelectedYearCurrent = $selectedYear && $currentYear
            && (int) $selectedYear->id === (int) $currentYear->id;

        return view('admin.dashboard', compact(
            'pendingCount',
            'classesCount',
            'totalStudentsCount',
            'selectedYear',
            'currentYear',
            'academicYears',
            'isSelectedYearCurrent',
        ));
    }
}
