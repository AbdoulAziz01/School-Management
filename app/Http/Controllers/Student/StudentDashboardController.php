<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Grade;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function dashboard()
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();
            
            // Calculer la moyenne réelle des notes (la colonne est 'grade' pas 'value')
            $grades = Grade::where('user_id', $user->id)->get();
            $average = $grades->count() > 0 ? round($grades->avg('grade'), 2) : null;
            
            // Calculer le taux de présence réel
            $attendancesQuery = Attendance::where('user_id', $user->id);
            $totalAttendances = $attendancesQuery->count();
            $presentCount = (clone $attendancesQuery)->where('status', 'present')->count();
            $attendanceRate = $totalAttendances > 0 ? round(($presentCount / $totalAttendances) * 100, 1) : null;
            
            // Récupérer les 5 dernières notes
            $recentGrades = Grade::with('subject')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            return view('student.dashboard', [
                'user' => $user,
                'average' => $average,
                'attendanceRate' => $attendanceRate,
                'upcomingCourses' => collect(),
                'recentGrades' => $recentGrades
            ]);
            
        } catch (\Exception $e) {
            return response('Erreur: ' . $e->getMessage(), 500);
        }
    }
}
