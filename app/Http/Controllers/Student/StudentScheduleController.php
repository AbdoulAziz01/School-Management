<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Récupérer les paramètres de filtrage
        $classGroupId = $request->query('class_group');
        $week = $request->query('week', now()->weekOfYear);
        $year = $request->query('year', now()->year);

        // Calculer les dates de début et de fin de la semaine
        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        // Pour l'instant, utiliser uniquement des données simulées
        // TODO: Implémenter les vraies données quand les relations seront configurées
        $hasRealData = false;
        $schedule = $this->getSimulatedSchedule();
        $classGroups = collect();

        // Définir les noms des jours
        $days = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi'
        ];

        // Générer les semaines pour le sélecteur
        $currentWeek = now()->weekOfYear;
        $weeks = [];
        for ($i = 1; $i <= 52; $i++) {
            $weekStart = Carbon::now()->setISODate($year, $i)->startOfWeek();
            $weeks[$i] = 'Semaine ' . $i . ' (' . $weekStart->format('d/m') . ' - ' . 
                          $weekStart->copy()->endOfWeek()->format('d/m/Y') . ')';
        }

        return view('student.schedule', compact(
            'schedule', 
            'days', 
            'classGroups',
            'weeks',
            'currentWeek',
            'year',
            'classGroupId',
            'hasRealData'
        ));
    }

    /**
     * Générer un emploi du temps simulé pour démonstration
     */
    private function getSimulatedSchedule()
    {
        $courses = [
            // Lundi
            1 => [
                (object)[
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'subject' => (object)['name' => 'Mathématiques', 'color' => '#3b82f6'],
                    'teacher' => (object)['name' => 'M. Dupont'],
                    'classroom' => (object)['name' => 'Salle 101'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'subject' => (object)['name' => 'Mathématiques', 'color' => '#3b82f6'],
                    'teacher' => (object)['name' => 'M. Dupont'],
                    'classroom' => (object)['name' => 'Salle 101'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '10:15',
                    'end_time' => '11:15',
                    'subject' => (object)['name' => 'Français', 'color' => '#ef4444'],
                    'teacher' => (object)['name' => 'Mme Martin'],
                    'classroom' => (object)['name' => 'Salle 203'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '11:15',
                    'end_time' => '12:15',
                    'subject' => (object)['name' => 'Histoire-Géographie', 'color' => '#f59e0b'],
                    'teacher' => (object)['name' => 'M. Bernard'],
                    'classroom' => (object)['name' => 'Salle 105'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '14:00',
                    'end_time' => '15:00',
                    'subject' => (object)['name' => 'Anglais', 'color' => '#8b5cf6'],
                    'teacher' => (object)['name' => 'Mme Johnson'],
                    'classroom' => (object)['name' => 'Salle 302'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '15:00',
                    'end_time' => '16:00',
                    'subject' => (object)['name' => 'Sciences Physiques', 'color' => '#10b981'],
                    'teacher' => (object)['name' => 'M. Leroy'],
                    'classroom' => (object)['name' => 'Labo 1'],
                    'classGroup' => null
                ],
            ],
            // Mardi
            2 => [
                (object)[
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'subject' => (object)['name' => 'SVT', 'color' => '#22c55e'],
                    'teacher' => (object)['name' => 'Mme Petit'],
                    'classroom' => (object)['name' => 'Labo 2'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'subject' => (object)['name' => 'SVT', 'color' => '#22c55e'],
                    'teacher' => (object)['name' => 'Mme Petit'],
                    'classroom' => (object)['name' => 'Labo 2'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '10:15',
                    'end_time' => '11:15',
                    'subject' => (object)['name' => 'Mathématiques', 'color' => '#3b82f6'],
                    'teacher' => (object)['name' => 'M. Dupont'],
                    'classroom' => (object)['name' => 'Salle 101'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '11:15',
                    'end_time' => '12:15',
                    'subject' => (object)['name' => 'Français', 'color' => '#ef4444'],
                    'teacher' => (object)['name' => 'Mme Martin'],
                    'classroom' => (object)['name' => 'Salle 203'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '14:00',
                    'end_time' => '16:00',
                    'subject' => (object)['name' => 'Éducation Physique', 'color' => '#ec4899'],
                    'teacher' => (object)['name' => 'M. Garcia'],
                    'classroom' => (object)['name' => 'Gymnase'],
                    'classGroup' => null
                ],
            ],
            // Mercredi
            3 => [
                (object)[
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'subject' => (object)['name' => 'Philosophie', 'color' => '#6366f1'],
                    'teacher' => (object)['name' => 'M. Rousseau'],
                    'classroom' => (object)['name' => 'Salle 401'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'subject' => (object)['name' => 'Philosophie', 'color' => '#6366f1'],
                    'teacher' => (object)['name' => 'M. Rousseau'],
                    'classroom' => (object)['name' => 'Salle 401'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '10:15',
                    'end_time' => '11:15',
                    'subject' => (object)['name' => 'Anglais', 'color' => '#8b5cf6'],
                    'teacher' => (object)['name' => 'Mme Johnson'],
                    'classroom' => (object)['name' => 'Salle 302'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '11:15',
                    'end_time' => '12:15',
                    'subject' => (object)['name' => 'Histoire-Géographie', 'color' => '#f59e0b'],
                    'teacher' => (object)['name' => 'M. Bernard'],
                    'classroom' => (object)['name' => 'Salle 105'],
                    'classGroup' => null
                ],
            ],
            // Jeudi
            4 => [
                (object)[
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'subject' => (object)['name' => 'Mathématiques', 'color' => '#3b82f6'],
                    'teacher' => (object)['name' => 'M. Dupont'],
                    'classroom' => (object)['name' => 'Salle 101'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'subject' => (object)['name' => 'Sciences Physiques', 'color' => '#10b981'],
                    'teacher' => (object)['name' => 'M. Leroy'],
                    'classroom' => (object)['name' => 'Labo 1'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '10:15',
                    'end_time' => '11:15',
                    'subject' => (object)['name' => 'Sciences Physiques', 'color' => '#10b981'],
                    'teacher' => (object)['name' => 'M. Leroy'],
                    'classroom' => (object)['name' => 'Labo 1'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '11:15',
                    'end_time' => '12:15',
                    'subject' => (object)['name' => 'Français', 'color' => '#ef4444'],
                    'teacher' => (object)['name' => 'Mme Martin'],
                    'classroom' => (object)['name' => 'Salle 203'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '14:00',
                    'end_time' => '15:00',
                    'subject' => (object)['name' => 'Espagnol', 'color' => '#f97316'],
                    'teacher' => (object)['name' => 'Mme Rodriguez'],
                    'classroom' => (object)['name' => 'Salle 304'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '15:00',
                    'end_time' => '16:00',
                    'subject' => (object)['name' => 'Arts Plastiques', 'color' => '#a855f7'],
                    'teacher' => (object)['name' => 'M. Monet'],
                    'classroom' => (object)['name' => 'Atelier'],
                    'classGroup' => null
                ],
            ],
            // Vendredi
            5 => [
                (object)[
                    'start_time' => '08:00',
                    'end_time' => '09:00',
                    'subject' => (object)['name' => 'Français', 'color' => '#ef4444'],
                    'teacher' => (object)['name' => 'Mme Martin'],
                    'classroom' => (object)['name' => 'Salle 203'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'subject' => (object)['name' => 'Français', 'color' => '#ef4444'],
                    'teacher' => (object)['name' => 'Mme Martin'],
                    'classroom' => (object)['name' => 'Salle 203'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '10:15',
                    'end_time' => '11:15',
                    'subject' => (object)['name' => 'Mathématiques', 'color' => '#3b82f6'],
                    'teacher' => (object)['name' => 'M. Dupont'],
                    'classroom' => (object)['name' => 'Salle 101'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '11:15',
                    'end_time' => '12:15',
                    'subject' => (object)['name' => 'SVT', 'color' => '#22c55e'],
                    'teacher' => (object)['name' => 'Mme Petit'],
                    'classroom' => (object)['name' => 'Labo 2'],
                    'classGroup' => null
                ],
                (object)[
                    'start_time' => '14:00',
                    'end_time' => '16:00',
                    'subject' => (object)['name' => 'Éducation Physique', 'color' => '#ec4899'],
                    'teacher' => (object)['name' => 'M. Garcia'],
                    'classroom' => (object)['name' => 'Stade'],
                    'classGroup' => null
                ],
            ],
        ];

        // Convertir en collection groupée par jour
        return collect($courses)->map(function($dayCourses) {
            return collect($dayCourses);
        });
    }

    /**
     * Récupérer l'emploi du temps au format JSON pour le calendrier
     */
    public function calendarEvents(Request $request)
    {
        // Utiliser uniquement les données simulées pour l'instant
        return response()->json($this->getSimulatedCalendarEvents());
    }

    /**
     * Générer des événements de calendrier simulés
     */
    private function getSimulatedCalendarEvents()
    {
        $events = [];
        $schedule = $this->getSimulatedSchedule();
        $startOfWeek = Carbon::now()->startOfWeek();

        foreach ($schedule as $dayOfWeek => $courses) {
            $date = $startOfWeek->copy()->addDays($dayOfWeek - 1);
            foreach ($courses as $course) {
                $events[] = [
                    'id' => uniqid(),
                    'title' => $course->subject->name,
                    'start' => $date->format('Y-m-d') . ' ' . $course->start_time,
                    'end' => $date->format('Y-m-d') . ' ' . $course->end_time,
                    'allDay' => false,
                    'backgroundColor' => $course->subject->color,
                    'borderColor' => $course->subject->color,
                    'extendedProps' => [
                        'teacher' => $course->teacher->name,
                        'classroom' => $course->classroom->name,
                        'subject' => $course->subject->name,
                        'isRecurring' => true,
                        'classGroup' => null,
                        'color' => $course->subject->color
                    ]
                ];
            }
        }

        return $events;
    }
}
