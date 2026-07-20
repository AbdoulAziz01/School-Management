<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Schedule;
use App\Models\TeacherAssignment;
use App\Support\ScheduleHelper;
use Illuminate\Support\Facades\Auth;

class TeacherScheduleController extends Controller
{
    public function index()
    {
        $teacher     = Auth::user();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $assignments = TeacherAssignment::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->active()
            ->when($currentYear, fn ($q) => $q->where('academic_year_id', $currentYear->id))
            ->get();

        $days      = ScheduleHelper::DAYS;
        $timeSlots = ScheduleHelper::timeSlots();

        $schedules = Schedule::with(['schoolClass', 'subject', 'teacher'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $scheduleGrid = ScheduleHelper::buildGrid($schedules);

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
