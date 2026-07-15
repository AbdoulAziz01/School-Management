<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;

class CaisseDashboardController extends Controller
{
    public function index()
    {
        return view('accounting.caisse.dashboard');
    }
}
