<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Support\ScheduleHelper;
use App\Support\StudentClassContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StudentOfflineSyncController extends Controller
{
    public function sync(): JsonResponse
    {
        $user = Auth::user();
        $year = AcademicYear::where('is_current', true)->first();

        // ── Profil ────────────────────────────────────────────────────────────
        $profile = [
            'name'      => $user->name,
            'avatar'    => strtoupper(mb_substr($user->name, 0, 2)),
            'class'     => StudentClassContext::labelForYear($user, $year),
            'school'    => optional($user->school)->name ?? 'EduManager',
            'year'      => $year?->name ?? '—',
            'synced_at' => now()->format('d/m/Y à H:i'),
        ];

        // ── Stats générales ───────────────────────────────────────────────────
        $gradesBase = Grade::where('user_id', $user->id)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id));

        $average = (clone $gradesBase)->count() > 0
            ? round((float) (clone $gradesBase)->avg('grade'), 2)
            : null;

        $attBase = Attendance::where('user_id', $user->id)
            ->when($year?->start_date, fn ($q) => $q->whereDate('date', '>=', $year->start_date))
            ->when($year?->end_date,   fn ($q) => $q->whereDate('date', '<=', $year->end_date));

        $totalDays   = (clone $attBase)->count();
        $presentDays = (clone $attBase)->where('status', 'present')->count();
        $absentDays  = (clone $attBase)->where('status', 'absent')->count();
        $lateDays    = (clone $attBase)->where('status', 'late')->count();
        $attRate     = $totalDays > 0
            ? round(($presentDays / $totalDays) * 100, 1)
            : null;

        $stats = [
            'average'         => $average,
            'attendance_rate' => $attRate,
            'total_days'      => $totalDays,
            'present_days'    => $presentDays,
            'absent_days'     => $absentDays,
            'late_days'       => $lateDays,
        ];

        // ── Notes par matière ─────────────────────────────────────────────────
        $grades = Grade::with('subject')
            ->where('user_id', $user->id)
            ->when($year, fn ($q) => $q->where('academic_year_id', $year->id))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn ($g) => $g->subject?->name ?? 'Autre')
            ->map(fn ($gs) => [
                'subject'  => $gs->first()->subject?->name ?? 'Autre',
                'color'    => $gs->first()->subject?->color ?? '#f59e0b',
                'average'  => round((float) $gs->avg('grade'), 2),
                'count'    => $gs->count(),
                'entries'  => $gs->take(5)->map(fn ($g) => [
                    'grade' => (float) $g->grade,
                    'type'  => $g->type ?? '-',
                    'date'  => $g->date
                        ? Carbon::parse($g->date)->format('d/m/Y')
                        : $g->created_at?->format('d/m/Y'),
                ])->values(),
            ])
            ->values();

        // ── Cours (LMS) ───────────────────────────────────────────────────────
        $lessons = collect();
        if ($user->class_id) {
            $lessons = Lesson::where('class_id', $user->class_id)
                ->where('is_published', true)
                ->with(['subject', 'teacher'])
                ->orderBy('subject_id')
                ->get()
                ->map(fn ($l) => [
                    'id'          => $l->id,
                    'title'       => $l->title,
                    'subject'     => $l->subject?->name ?? 'Cours',
                    'color'       => $l->subject?->color ?? '#f59e0b',
                    'teacher'     => $l->teacher?->name ?? '',
                    'type'        => $l->file_type,
                    'description' => $l->description ?? '',
                    'download_url'=> ($l->file_type !== 'video' && $l->file_type !== 'link')
                        ? route('student.lms.download', $l->id)
                        : null,
                    'link_url'    => in_array($l->file_type, ['video', 'link'], true)
                        ? $l->file_path
                        : null,
                ]);
        }

        // ── Emploi du temps ───────────────────────────────────────────────────
        $schedule = collect();
        if ($user->class_id) {
            $rows = Schedule::with(['subject', 'teacher'])
                ->where('class_id', $user->class_id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            $schedule = collect(ScheduleHelper::DAYS)
                ->map(function ($dayName, $dayNum) use ($rows) {
                    $courses = $rows->where('day_of_week', $dayNum)
                        ->values()
                        ->map(fn ($s) => [
                            'subject' => $s->subject?->name ?? '',
                            'color'   => $s->subject?->color ?? '#f59e0b',
                            'teacher' => $s->teacher?->name ?? '',
                            'start'   => substr((string) $s->start_time, 0, 5),
                            'end'     => substr((string) $s->end_time, 0, 5),
                        ]);
                    return ['day' => $dayName, 'courses' => $courses];
                })
                ->values();
        }

        return response()->json(compact('profile', 'stats', 'grades', 'lessons', 'schedule'));
    }
}
