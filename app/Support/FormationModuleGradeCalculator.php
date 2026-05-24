<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Moyenne module LMD : 30 % CC + 70 % examen si les deux existent, sinon 100 % sur la note disponible.
 */
class FormationModuleGradeCalculator
{
    /**
     * @param  Collection<int, \App\Models\Grade>|iterable  $grades
     * @return array{
     *     average: float|null,
     *     cc_average: float|null,
     *     exam_average: float|null,
     *     mode: 'cc_and_exam'|'cc_only'|'exam_only'|'single'|null,
     *     validated: bool
     * }
     */
    public static function summarize(iterable $grades, FormationLmdSettings $settings): array
    {
        $collection = $grades instanceof Collection ? $grades : collect($grades);

        $ccGrades = $collection->filter(
            fn ($g) => in_array((string) $g->type, $settings->ccGradeTypes, true)
        );
        $examGrades = $collection->filter(
            fn ($g) => in_array((string) $g->type, $settings->examGradeTypes, true)
        );

        $ccAverage = $ccGrades->isNotEmpty()
            ? round((float) $ccGrades->avg('grade'), 2)
            : null;

        $examAverage = $examGrades->isNotEmpty()
            ? round((float) $examGrades->avg('grade'), 2)
            : null;

        $mode = null;
        $average = null;

        if ($ccAverage !== null && $examAverage !== null) {
            $mode = 'cc_and_exam';
            $average = round(
                ($ccAverage * $settings->ccWeightRatio()) + ($examAverage * $settings->examWeightRatio()),
                2
            );
        } elseif ($ccAverage !== null) {
            $mode = 'cc_only';
            $average = $ccAverage;
        } elseif ($examAverage !== null) {
            $mode = 'exam_only';
            $average = $examAverage;
        } elseif ($collection->count() === 1) {
            $mode = 'single';
            $average = round((float) $collection->first()->grade, 2);
        } elseif ($collection->isNotEmpty() && $ccAverage === null && $examAverage === null) {
            $mode = 'single';
            $average = round((float) $collection->avg('grade'), 2);
        }

        return [
            'average' => $average,
            'cc_average' => $ccAverage,
            'exam_average' => $examAverage,
            'mode' => $mode,
            'validated' => $average !== null && $average >= $settings->passingGradeMin,
        ];
    }

    public static function moduleAverage(iterable $grades, FormationLmdSettings $settings): ?float
    {
        return self::summarize($grades, $settings)['average'];
    }

    public static function modeLabel(?string $mode, ?FormationLmdSettings $settings = null): ?string
    {
        return match ($mode) {
            'cc_and_exam' => $settings
                ? "CC {$settings->ccWeightPercent} % + Examen {$settings->examWeightPercent} %"
                : 'CC + Examen',
            'cc_only' => 'CC seul (100 %)',
            'exam_only' => 'Examen seul (100 %)',
            'single' => 'Note unique (100 %)',
            default => null,
        };
    }
}
