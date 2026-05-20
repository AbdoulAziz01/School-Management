<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'schools_total' => School::count(),
            'schools_active' => School::where('is_active', true)->count(),
            'users_total' => User::withoutGlobalScopes()->whereNotNull('school_id')->count(),
            'students_total' => User::withoutGlobalScopes()
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->whereNotNull('school_id')
                ->count(),
        ];

        $recentSchools = School::latest()->take(5)->get();

        return view('platform.dashboard', compact('stats', 'recentSchools'));
    }
}
