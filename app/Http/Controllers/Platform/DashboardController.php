<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Support\PlatformMetrics;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = PlatformMetrics::globalStats();

        $recentSchools = PlatformMetrics::schoolWithCountsQuery()
            ->latest()
            ->take(5)
            ->get();

        $currentYears = PlatformMetrics::currentAcademicYearsBySchool();

        $watchlist = PlatformMetrics::watchlistSchools(8);

        return view('platform.dashboard', compact(
            'stats',
            'recentSchools',
            'currentYears',
            'watchlist',
        ));
    }
}
