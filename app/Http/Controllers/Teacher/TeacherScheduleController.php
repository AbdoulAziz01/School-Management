<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Timetable;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherScheduleController extends Controller
{
    /**
     * Afficher l'emploi du temps de l'enseignant
     */
    public function index()
    {
        $teacher = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Récupérer les classes assignées via class_teacher
        $assignedClasses = $teacher->assignedClasses ?? collect();
        
        // Récupérer les matières assignées via teacher_subjects
        $assignedSubjects = DB::table('teacher_subjects')
            ->join('subjects', 'teacher_subjects.subject_id', '=', 'subjects.id')
            ->where('teacher_subjects.teacher_id', $teacher->id)
            ->select('subjects.*')
            ->get();
        
        // Créer une collection d'affectations pour la compatibilité avec la vue
        $assignments = collect();
        foreach ($assignedClasses as $class) {
            foreach ($assignedSubjects as $subject) {
                $assignments->push((object)[
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'schoolClass' => $class,
                    'subject' => $subject
                ]);
            }
        }
        
        // Créneaux horaires
        $timeSlots = [
            '08:00 - 09:00',
            '09:00 - 10:00',
            '10:00 - 11:00',
            '11:00 - 12:00',
            '14:00 - 15:00',
            '15:00 - 16:00',
            '16:00 - 17:00',
        ];
        
        // Jours de la semaine
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        
        // Organiser par jour et créneau
        $scheduleGrid = [];
        foreach ($days as $day) {
            $scheduleGrid[$day] = [];
            foreach ($timeSlots as $slot) {
                $scheduleGrid[$day][$slot] = null;
            }
        }
        
        // Tenter de récupérer depuis la table timetables
        $timetables = Timetable::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();
        
        foreach ($timetables as $timetable) {
            $day = $timetable->day ?? $timetable->day_of_week;
            $time = $timetable->start_time . ' - ' . $timetable->end_time;
            
            if (isset($scheduleGrid[$day])) {
                $scheduleGrid[$day][$time] = [
                    'class' => $timetable->schoolClass,
                    'subject' => $timetable->subject,
                    'room' => $timetable->room ?? ''
                ];
            }
        }
        
        return view('teacher.schedule', compact(
            'teacher',
            'assignments',
            'scheduleGrid',
            'timeSlots',
            'days',
            'currentYear'
        ));
    }
}
