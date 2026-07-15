<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\ClassHistoricalContext;
use Carbon\Carbon;

/**
 * Calcule les statistiques d'une classe (moyennes, distribution des notes,
 * évolution mensuelle, classement) — extrait de
 * ClassController::show()/buildMonthlyPeriodAverages()/buildClassStudentInsights()
 * qui mélangeaient ce calcul avec la logique HTTP du contrôleur.
 */
class ClassStatisticsService
{
    /**
     * @param  array<int, int>  $studentIds
     * @return array<string, mixed>
     */
    public function calculate(SchoolClass $class, array $studentIds, ?AcademicYear $academicYear): array
    {
        $classAverage = 0;
        $totalGrades = 0;
        $passCount = 0; // >= 10/20
        $failCount = 0; // < 10/20
        $studentAverages = [];
        $gradeDistribution = [
            'excellent' => 0, // >= 16
            'good' => 0,      // >= 14
            'average' => 0,   // >= 12
            'passing' => 0,   // >= 10
            'failing' => 0,   // < 10
        ];
        $monthlyAverages = [];

        if (count($studentIds) > 0) {
            $allGradesQuery = Grade::whereIn('user_id', $studentIds)
                ->whereHas('subject')
                ->with('subject');

            if ($academicYear) {
                $allGradesQuery = ClassHistoricalContext::applyGradeYearFilter($allGradesQuery, $academicYear, $class->school_id);
            }

            $allGrades = $allGradesQuery->get();

            foreach ($studentIds as $studentId) {
                $studentGrades = $allGrades->where('user_id', $studentId);
                $studentAvg = $this->calculateWeightedAverage($studentGrades);

                if ($studentAvg !== null) {
                    $studentAverages[$studentId] = $studentAvg;

                    if ($studentAvg >= 16) {
                        $gradeDistribution['excellent']++;
                    } elseif ($studentAvg >= 14) {
                        $gradeDistribution['good']++;
                    } elseif ($studentAvg >= 12) {
                        $gradeDistribution['average']++;
                    } elseif ($studentAvg >= 10) {
                        $gradeDistribution['passing']++;
                    } else {
                        $gradeDistribution['failing']++;
                    }

                    if ($studentAvg >= 10) {
                        $passCount++;
                    } else {
                        $failCount++;
                    }
                }
            }

            if (count($studentAverages) > 0) {
                $classAverage = array_sum($studentAverages) / count($studentAverages);
            }

            $monthlyAverages = $this->buildMonthlyPeriodAverages($studentIds, $allGrades, $academicYear);

            $hasChartData = collect($monthlyAverages)->contains(fn ($row) => $row['average'] !== null);

            if (! $hasChartData && $allGrades->isNotEmpty()) {
                $gradeStart = Carbon::parse($allGrades->min('date'))->startOfMonth();
                $gradeEnd = Carbon::parse($allGrades->max('date'))->endOfMonth();
                if ($gradeEnd->gt(now())) {
                    $gradeEnd = now()->copy()->endOfMonth();
                }

                $monthlyAverages = $this->buildMonthlyPeriodAverages($studentIds, $allGrades, $academicYear, $gradeStart, $gradeEnd);
            }

            $totalGrades = $allGrades->count();
        }

        $evolutionPeriod = null;
        if (count($monthlyAverages) > 0) {
            $firstLabel = $monthlyAverages[0]['month'];
            $lastLabel = $monthlyAverages[count($monthlyAverages) - 1]['month'];
            $evolutionPeriod = $firstLabel.' — '.$lastLabel;
        }

        $studentsWithGrades = count($studentAverages);
        $bestAverage = $studentsWithGrades > 0 ? max($studentAverages) : 0;
        $lowestAverage = $studentsWithGrades > 0 ? min($studentAverages) : 0;

        $studentInsights = $this->buildClassStudentInsights($studentAverages);

        return [
            'average' => round($classAverage, 2),
            'best_average' => round($bestAverage, 2),
            'lowest_average' => round($lowestAverage, 2),
            'total_students' => count($studentIds),
            'students_with_grades' => $studentsWithGrades,
            'total_grades' => $totalGrades,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_rate' => $studentsWithGrades > 0 ? round(($passCount / $studentsWithGrades) * 100, 1) : 0,
            'grade_distribution' => $gradeDistribution,
            'monthly_averages' => $monthlyAverages,
            'evolution_period' => $evolutionPeriod,
            'best_student' => $studentInsights['best_student'],
            'lowest_student' => $studentInsights['lowest_student'],
            'ranking' => $studentInsights['ranking'],
            'passing_students' => $studentInsights['passing_students'],
            'failing_students' => $studentInsights['failing_students'],
            'students_by_bucket' => $studentInsights['students_by_bucket'],
        ];
    }

    private function calculateWeightedAverage($grades): ?float
    {
        if ($grades->isEmpty()) {
            return null;
        }

        $weightedSum = 0;
        $totalCoef = 0;

        foreach ($grades->groupBy('subject_id') as $subjectGrades) {
            $subjectAvg = $subjectGrades->avg('grade');
            if ($subjectAvg === null) {
                continue;
            }

            $coefficient = $subjectGrades->first()->subject->coefficient ?? 1;
            $weightedSum += $subjectAvg * $coefficient;
            $totalCoef += $coefficient;
        }

        return $totalCoef > 0 ? $weightedSum / $totalCoef : null;
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function getAcademicYearMonthRange(?AcademicYear $academicYear): array
    {
        $now = Carbon::now();

        if ($academicYear?->start_date && $academicYear?->end_date) {
            $start = Carbon::parse($academicYear->start_date)->startOfMonth();
            $end = Carbon::parse($academicYear->end_date)->endOfMonth();

            // Ex. : nous sommes en mai 2026 mais l'année en BDD commence en oct. 2026
            if ($now->lt($start)) {
                $start->subYear();
                $end->subYear();
            }
        } else {
            $start = $now->copy()->month(9)->day(1)->startOfMonth();
            if ($now->month < 9) {
                $start->subYear();
            }
            $end = $start->copy()->addMonths(9)->endOfMonth();
        }

        if ($end->gt($now)) {
            $end = $now->copy()->endOfMonth();
        }

        if ($start->gt($end)) {
            $start = $end->copy()->startOfMonth();
        }

        return [$start, $end];
    }

    /**
     * Moyenne de classe par mois (notes saisies uniquement dans le mois concerné).
     *
     * @param  array<int, int>  $studentIds
     */
    private function buildMonthlyPeriodAverages(
        array $studentIds,
        $allGrades,
        ?AcademicYear $academicYear,
        ?Carbon $periodStartOverride = null,
        ?Carbon $periodEndOverride = null
    ): array {
        if ($allGrades->isEmpty() || count($studentIds) === 0) {
            return [];
        }

        if ($periodStartOverride && $periodEndOverride) {
            $periodStart = $periodStartOverride->copy()->startOfMonth();
            $periodEnd = $periodEndOverride->copy()->endOfMonth();
        } else {
            [$periodStart, $periodEnd] = $this->getAcademicYearMonthRange($academicYear);
        }

        $monthlyAverages = [];
        $currentMonth = $periodStart->copy();

        while ($currentMonth <= $periodEnd) {
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();

            $monthGrades = $allGrades->filter(function ($grade) use ($monthStart, $monthEnd) {
                $date = $grade->date;

                return $date && $date->between($monthStart, $monthEnd);
            });

            $monthlyStudentAverages = collect($studentIds)
                ->map(fn ($studentId) => $this->calculateWeightedAverage($monthGrades->where('user_id', $studentId)))
                ->filter(fn ($average) => $average !== null);

            $monthAverage = $monthlyStudentAverages->count() > 0 ? $monthlyStudentAverages->avg() : null;

            $monthlyAverages[] = [
                'month' => $currentMonth->translatedFormat('M Y'),
                'average' => $monthAverage !== null ? round($monthAverage, 2) : null,
                'count' => $monthGrades->count(),
            ];

            $currentMonth->addMonth();
        }

        return $monthlyAverages;
    }

    /**
     * @param  array<int, float>  $studentAverages
     * @return array<string, mixed>
     */
    private function buildClassStudentInsights(array $studentAverages): array
    {
        if ($studentAverages === []) {
            return [
                'best_student' => null,
                'lowest_student' => null,
                'ranking' => [],
                'passing_students' => [],
                'failing_students' => [],
                'students_by_bucket' => [
                    'excellent' => [],
                    'good' => [],
                    'average' => [],
                    'passing' => [],
                    'failing' => [],
                ],
            ];
        }

        $users = User::query()
            ->whereIn('id', array_keys($studentAverages))
            ->get(['id', 'name', 'identifier', 'email'])
            ->keyBy('id');

        $ranking = [];
        $passing = [];
        $failing = [];
        $buckets = [
            'excellent' => [],
            'good' => [],
            'average' => [],
            'passing' => [],
            'failing' => [],
        ];

        foreach ($studentAverages as $studentId => $average) {
            $user = $users->get($studentId);
            if (! $user) {
                continue;
            }

            $rounded = round($average, 2);
            $entry = [
                'id' => $user->id,
                'name' => $user->name,
                'identifier' => $user->identifier,
                'email' => $user->email,
                'average' => $rounded,
                'url' => route('admin.students.show', $user),
            ];

            $ranking[] = $entry;

            if ($rounded >= 10) {
                $passing[] = $entry;
            } else {
                $failing[] = $entry;
            }

            if ($rounded >= 16) {
                $buckets['excellent'][] = $entry;
            } elseif ($rounded >= 14) {
                $buckets['good'][] = $entry;
            } elseif ($rounded >= 12) {
                $buckets['average'][] = $entry;
            } elseif ($rounded >= 10) {
                $buckets['passing'][] = $entry;
            } else {
                $buckets['failing'][] = $entry;
            }
        }

        usort($ranking, fn ($a, $b) => $b['average'] <=> $a['average']);
        usort($passing, fn ($a, $b) => $b['average'] <=> $a['average']);
        usort($failing, fn ($a, $b) => $b['average'] <=> $a['average']);

        foreach ($buckets as $key => $list) {
            usort($buckets[$key], fn ($a, $b) => $b['average'] <=> $a['average']);
        }

        $best = $ranking[0] ?? null;
        $lowest = $ranking[count($ranking) - 1] ?? null;

        return [
            'best_student' => $best,
            'lowest_student' => $lowest,
            'ranking' => $ranking,
            'passing_students' => $passing,
            'failing_students' => $failing,
            'students_by_bucket' => $buckets,
        ];
    }
}
