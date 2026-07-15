<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\AccountingDashboardService;
use Illuminate\Http\Request;

class ComptableDashboardController extends Controller
{
    public function __construct(
        private AccountingDashboardService $dashboard
    ) {}

    public function index(Request $request)
    {
        $school = School::find($request->user()->school_id);

        return view('accounting.comptable.dashboard', [
            'summary' => $this->dashboard->summary($school),
            'recentOperations' => $this->dashboard->recentOperations($school, 8),
        ]);
    }
}
