<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

class StudentGradeEvolution
{
    private const SHORT_LABELS = [
        'devoir1'     => 'D1',
        'devoir2'     => 'D2',
        'composition' => 'Compo',
    ];

    /**
     * Données pour le diagramme d'évolution (notes réelles par matière).
     *
     * @return array{labels: array<int, string>, subjects: array<int, array{id: int, name: string, average: float|null, evaluations: array}>}
     */
    public static function chartData(User $student, ?int $academicYearId = null): array
    {
        $student->loadMissing(['grades.subject']);

        $validGrades = $student->grades
            ->when($academicYearId, fn (Collection $c) => $c->where('academic_year_id', $academicYearId))
            ->whereIn('type', SenegalGradeSequence::ORDER)
            ->whereIn('semester', [1, 2]);

        $labels = self::evaluationLabels();

        $subjects = $validGrades
            ->groupBy('subject_id')
            ->map(function (Collection $subjectGrades) use ($labels) {
                $subject = $subjectGrades->first()->subject;
                $evaluations = self::buildEvaluations($subjectGrades);

                $officialGrades = collect($evaluations)
                    ->pluck('grade')
                    ->filter(fn ($g) => $g !== null);

                return [
                    'id'          => (int) $subjectGrades->first()->subject_id,
                    'name'        => $subject->name ?? 'Inconnu',
                    'average'     => $officialGrades->isNotEmpty()
                        ? round((float) $officialGrades->avg(), 2)
                        : null,
                    'evaluations' => $evaluations,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        return [
            'labels'   => $labels,
            'subjects' => $subjects,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function evaluationLabels(): array
    {
        $labels = [];

        foreach ([1, 2] as $semester) {
            foreach (SenegalGradeSequence::ORDER as $type) {
                $labels[] = 'S'.$semester.' · '.self::SHORT_LABELS[$type];
            }
        }

        return $labels;
    }

    /**
     * @return array<int, array{label: string, semester: int, type: string, grade: float|null}>
     */
    private static function buildEvaluations(Collection $subjectGrades): array
    {
        $evaluations = [];

        foreach ([1, 2] as $semester) {
            foreach (SenegalGradeSequence::ORDER as $type) {
                $grade = $subjectGrades->first(
                    fn ($g) => (int) $g->semester === $semester && $g->type === $type
                );

                $evaluations[] = [
                    'label'    => 'S'.$semester.' · '.self::SHORT_LABELS[$type],
                    'semester' => $semester,
                    'type'     => $type,
                    'grade'    => $grade !== null ? (float) $grade->grade : null,
                ];
            }
        }

        return $evaluations;
    }
}
