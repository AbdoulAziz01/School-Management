<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;

class ComptableDashboardController extends Controller
{
    public function index()
    {
        return view('accounting.comptable.dashboard');
    }
}
