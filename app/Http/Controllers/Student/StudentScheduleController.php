<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Support\ScheduleHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $week  = (int) $request->query('week', now()->weekOfYear);
        $year  = (int) $request->query('year', now()->year);
        $days  = ScheduleHelper::DAYS;
        $timeSlots = ScheduleHelper::timeSlots();

        $hasRealData = false;
        $schedule    = ScheduleHelper::buildByDay(collect());
        $subjectCount = 0;

        if ($user->class_id) {
            $rows = Schedule::with(['subject', 'teacher', 'schoolClass'])
                ->where('class_id', $user->class_id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            if ($rows->isNotEmpty()) {
                $hasRealData  = true;
                $schedule     = ScheduleHelper::buildByDay($rows);
                $subjectCount = $rows->pluck('subject_id')->unique()->count();
            }
        }

        $currentWeek = now()->weekOfYear;
        $weeks       = [];
        for ($i = 1; $i <= 52; $i++) {
            $weekStart = Carbon::now()->setISODate($year, $i)->startOfWeek();
            $weeks[$i] = 'Semaine '.$i.' ('.$weekStart->format('d/m').' - '.
                          $weekStart->copy()->endOfWeek()->format('d/m/Y').')';
        }

        return view('student.schedule', compact(
            'schedule',
            'days',
            'timeSlots',
            'weeks',
            'currentWeek',
            'year',
            'week',
            'hasRealData',
            'subjectCount'
        ));
    }
}
