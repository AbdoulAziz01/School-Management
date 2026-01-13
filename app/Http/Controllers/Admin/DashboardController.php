<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\AcademicYear;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingCount = User::where('status', 'pending')->count();
        $classesCount = SchoolClass::count();
        $unassignedStudentsCount = User::where('role', 'student')
            ->whereNull('class_id')
            ->count();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        return view('admin.dashboard', compact(
            'pendingCount', 
            'classesCount', 
            'unassignedStudentsCount', 
            'currentYear'
        ));
    }
}
