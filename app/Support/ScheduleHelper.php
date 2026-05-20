<?php

namespace App\Support;

use App\Models\Schedule;
use Illuminate\Support\Collection;

class ScheduleHelper
{
    public const DAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
    ];

    /** @return list<array{start: string, end: string, label: string}> */
    public static function timeSlots(): array
    {
        return [
            ['start' => '08:00', 'end' => '09:00', 'label' => '08:00 - 09:00'],
            ['start' => '09:00', 'end' => '10:00', 'label' => '09:00 - 10:00'],
            ['start' => '10:00', 'end' => '11:00', 'label' => '10:00 - 11:00'],
            ['start' => '11:00', 'end' => '12:00', 'label' => '11:00 - 12:00'],
            ['start' => '14:00', 'end' => '15:00', 'label' => '14:00 - 15:00'],
            ['start' => '15:00', 'end' => '16:00', 'label' => '15:00 - 16:00'],
            ['start' => '16:00', 'end' => '17:00', 'label' => '16:00 - 17:00'],
        ];
    }

    public static function formatTime(mixed $time): string
    {
        if ($time instanceof \DateTimeInterface) {
            return $time->format('H:i');
        }

        $str = (string) $time;

        return strlen($str) >= 5 ? substr($str, 0, 5) : $str;
    }

    public static function subjectColor(?int $subjectId): string
    {
        if (! $subjectId) {
            return '#f59e0b';
        }

        $palette = ['#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#84cc16'];

        return $palette[$subjectId % count($palette)];
    }

    /**
     * Grille [jour][label créneau] pour les vues tableau (admin / prof).
     *
     * @param  Collection<int, Schedule>  $schedules
     * @return array<string, array<string, array{class: mixed, subject: mixed, teacher: mixed, room: string}|null>>
     */
    public static function buildGrid(Collection $schedules): array
    {
        $grid = [];
        foreach (self::DAYS as $dayName) {
            $grid[$dayName] = [];
            foreach (self::timeSlots() as $slot) {
                $grid[$dayName][$slot['label']] = null;
            }
        }

        foreach ($schedules as $schedule) {
            $dayName = self::DAYS[(int) $schedule->day_of_week] ?? null;
            if (! $dayName) {
                continue;
            }

            $start = self::formatTime($schedule->start_time);
            $end   = self::formatTime($schedule->end_time);
            $label = $start.' - '.$end;

            if (! array_key_exists($label, $grid[$dayName])) {
                $grid[$dayName][$label] = null;
            }

            $grid[$dayName][$label] = [
                'id'      => $schedule->id,
                'class'   => $schedule->schoolClass,
                'subject' => $schedule->subject,
                'teacher' => $schedule->teacher,
                'room'    => $schedule->room ?? '',
            ];
        }

        return $grid;
    }

    /**
     * Collection indexée par jour (1–6) pour la vue élève.
     *
     * @param  Collection<int, Schedule>  $schedules
     * @return Collection<int, Collection<int, object>>
     */
    public static function buildByDay(Collection $schedules): Collection
    {
        $byDay = collect();
        foreach (array_keys(self::DAYS) as $day) {
            $byDay[$day] = collect();
        }

        foreach ($schedules as $schedule) {
            $day = (int) $schedule->day_of_week;
            if (! isset(self::DAYS[$day])) {
                continue;
            }

            $byDay[$day]->push((object) [
                'start_time' => self::formatTime($schedule->start_time),
                'end_time'   => self::formatTime($schedule->end_time),
                'subject'    => (object) [
                    'name'  => $schedule->subject?->name ?? 'Matière',
                    'color' => self::subjectColor($schedule->subject_id),
                ],
                'teacher'   => (object) ['name' => $schedule->teacher?->name ?? '—'],
                'classroom' => (object) ['name' => $schedule->room ?: '—'],
            ]);
        }

        foreach ($byDay as $day => $slots) {
            $byDay[$day] = $slots->sortBy('start_time')->values();
        }

        return $byDay;
    }

    /** @return list<string> */
    public static function slotLabels(): array
    {
        return array_column(self::timeSlots(), 'label');
    }
}
