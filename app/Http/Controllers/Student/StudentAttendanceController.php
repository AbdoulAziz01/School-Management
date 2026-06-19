<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StudentAttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Récupérer les présences triées par date décroissante
        $attendances = $user->attendances()
            ->with(['subject', 'teacher'])
            ->orderBy('date', 'desc')
            ->paginate(15);

        // Calculer les statistiques de présence réelles
        $presentDays = $user->attendances()->where('status', 'present')->count();
        $absentDays = $user->attendances()->where('status', 'absent')->count();
        $lateDays = $user->attendances()->where('status', 'late')->count();
        $totalDays = $presentDays + $absentDays + $lateDays;
        
        // Calculer le taux de présence (si aucune donnée, afficher 0)
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

        $stats = [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'attendance_rate' => $attendanceRate
        ];

        // Données du calendrier (vide si pas de données réelles)
        $calendarEvents = $this->getCalendarEvents($user);

        return view('student.attendance', compact('attendances', 'stats', 'calendarEvents'));
    }
    
    /**
     * Récupérer les événements du calendrier basés sur les vraies données de présence
     */
    private function getCalendarEvents($user)
    {
        $events = [];
        
        // Récupérer les vraies présences de l'utilisateur
        $attendances = $user->attendances()
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->get();
        
        foreach ($attendances as $attendance) {
            $color = '#1cc88a'; // present
            if ($attendance->status === 'absent') {
                $color = '#e74a3b';
            } elseif ($attendance->status === 'late') {
                $color = '#f6c23e';
            }
            
            $events[] = [
                'title' => ucfirst($attendance->status),
                'start' => $attendance->date->format('Y-m-d'),
                'color' => $color,
                'display' => 'background'
            ];
        }
        
        return $events;
    }
    
}
