<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;

class DirecteurDashboardController extends Controller
{
    public function index()
    {
        return view('accounting.directeur.dashboard');
    }
}
