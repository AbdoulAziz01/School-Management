<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SalaryPayment;
use Illuminate\Support\Facades\Auth;

/**
 * L'enseignant voit simplement si son salaire du mois est payé ou non, plus
 * un historique — lecture seule, module Comptabilité (établissements
 * privés uniquement, voir la garde de route module:accounting).
 */
class TeacherSalaryController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();

        $currentPeriod = now()->startOfMonth();

        $currentPayment = SalaryPayment::where('user_id', $teacher->id)
            ->where('period', $currentPeriod->format('Y-m-d'))
            ->first();

        $history = SalaryPayment::where('user_id', $teacher->id)
            ->orderByDesc('period')
            ->get();

        return view('teacher.salary', [
            'currentPeriod' => $currentPeriod,
            'currentPayment' => $currentPayment,
            'history' => $history,
        ]);
    }
}
