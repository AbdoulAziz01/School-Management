<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Timetable;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Event;
use App\Models\StudentClass;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        \Log::info('DashboardController@index - Début de la requête');
        
        try {
            $user = Auth::user();
            
            if (!$user) {
                \Log::warning('Aucun utilisateur connecté, redirection vers la page de connexion');
                return redirect()->route('login');
            }
            
            \Log::info('Utilisateur connecté', [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role
            ]);
            
            // Vérifier que l'utilisateur a bien le rôle étudiant
            if ($user->role !== 'eleve') {
                \Log::warning("L'utilisateur n'a pas le rôle étudiant", [
                    'user_id' => $user->id,
                    'role' => $user->role
                ]);
                
                // Rediriger selon le rôle
                switch ($user->role) {
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                    default:
                        Auth::logout();
                        return redirect()->route('login')->with('error', 'Accès non autorisé.');
                }
            }
            
            // Récupérer la classe de l'étudiant avec une relation chargée
            $class = $user->load('studentClass')->studentClass;
            \Log::info('Classe de l\'étudiant', [
                'class_id' => $class ? $class->id : null,
                'class_name' => $class ? $class->name : null
            ]);
            
            // Initialiser les collections vides par défaut
            $upcomingClasses = collect();
            $pendingAssignments = collect();
            $recentGrades = collect();
            $events = collect();
            $timetable = collect();
            $average = null;
            
            // Récupérer les données uniquement si la classe existe
            if ($class) {
                // Prochains cours (pour aujourd'hui et demain)
                $today = strtolower(now()->englishDayOfWeek);
                $tomorrow = strtolower(now()->addDay()->englishDayOfWeek);
                
                $upcomingClasses = Timetable::where('class_id', $class->id)
                    ->whereIn('day_of_week', [$today, $tomorrow])
                    ->orderByRaw("CASE WHEN day_of_week = '{$today}' THEN 0 ELSE 1 END")
                    ->orderBy('start_time')
                    ->with('subject')
                    ->get();
                
                // Devoirs à rendre
                $pendingAssignments = Assignment::where('class_id', $class->id)
                    ->where('due_date', '>=', now())
                    ->orderBy('due_date')
                    ->get()
                    ->map(function($assignment) {
                        $assignment->is_late = $assignment->due_date < now();
                        return $assignment;
                    });
                
                // Récupérer l'emploi du temps d'aujourd'hui
                $today = Carbon::today();
                $timetable = Timetable::where('class_id', $class->id)
                    ->where('day_of_week', strtolower($today->englishDayOfWeek))
                    ->orderBy('start_time')
                    ->with('subject')
                    ->get();
            }
            
            // Récupérer les notes de l'étudiant (indépendamment de la classe)
            $recentGrades = Grade::where('student_id', $user->id)
                ->with('subject')
                ->latest('date')
                ->take(5)
                ->get();
            
            // Calculer la moyenne uniquement s'il y a des notes
            if ($recentGrades->isNotEmpty()) {
                $average = number_format($recentGrades->avg('grade'), 2);
            }
            
            // Récupérer les événements à venir
            $events = Event::where('end_date', '>=', now())
                ->orderBy('start_date')
                ->take(5)
                ->get();
            
            // Préparer les données pour la vue
            $viewData = [
                'class' => $class ? $class->name : 'Non assigné',
                'upcomingClasses' => $upcomingClasses,
                'pendingAssignments' => $pendingAssignments,
                'recentGrades' => $recentGrades,
                'events' => $events,
                'average' => $average,
                'timetable' => $timetable,
                'user' => $user
            ];
            
            \Log::info('Données du tableau de bord préparées', [
                'classes_count' => $upcomingClasses->count(),
                'assignments_count' => $pendingAssignments->count(),
                'grades_count' => $recentGrades->count(),
                'events_count' => $events->count(),
                'timetable_count' => $timetable->count()
            ]);
            
            return view('student.dashboard', $viewData);

        } catch (\Exception $e) {
            \Log::error('Erreur dans le tableau de bord étudiant', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Rediriger vers la page de connexion avec un message d'erreur
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Une erreur est survenue lors du chargement de votre tableau de bord. Veuillez vous reconnecter.');
        }
    }
}