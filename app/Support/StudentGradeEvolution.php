<?php

namespace App\Support;

use App\Models\User;
use App\Support\Grading\GradeSequence;
use App\Support\Grading\PrimaryGradingSettings;
use Illuminate\Support\Collection;

class StudentGradeEvolution
{
    /**
     * Données pour le diagramme d'évolution (notes réelles par matière).
     * Colonnes S1/S2 × Devoir/Composition (secondaire) ou à plat
     * Composition 1→N (primaire, N = nombre de compositions par défaut
     * du niveau — vue multi-matières, ne peut pas refléter un nombre de
     * compositions différent par matière) selon le cycle de la classe.
     *
     * @return array{labels: array<int, string>, subjects: array<int, array{id: int, name: string, average: float|null, evaluations: array}>}
     */
    public static function chartData(User $student, ?int $academicYearId = null): array
    {
        $student->loadMissing(['grades.subject', 'class.level']);

        $isPrimaire = $student->class?->level?->isPrimaireCycle() ?? false;
        $slots = self::slotsFor($student, $isPrimaire);

        $validGrades = $student->grades
            ->when($academicYearId, fn (Collection $c) => $c->where('academic_year_id', $academicYearId))
            ->whereIn('type', GradeSequence::allTypes());

        $labels = self::evaluationLabels($slots, $isPrimaire);

        $subjects = $validGrades
            ->groupBy('subject_id')
            ->map(function (Collection $subjectGrades) use ($slots, $isPrimaire, $student) {
                $subject = $subjectGrades->first()->subject;
                $subjectId = (int) $subjectGrades->first()->subject_id;
                $maxGrade = $student->class ? GradeSequence::maxGradeFor($student->class, $subjectId) : 20.0;
                $evaluations = self::buildEvaluations($subjectGrades, $slots, $isPrimaire);

                $officialGrades = collect($evaluations)
                    ->pluck('grade')
                    ->filter(fn ($g) => $g !== null);

                return [
                    'id'          => $subjectId,
                    'name'        => $subject->name ?? 'Inconnu',
                    'average'     => $officialGrades->isNotEmpty()
                        ? round((float) $officialGrades->avg(), 2)
                        : null,
                    'max_grade'   => $maxGrade,
                    'evaluations' => $evaluations,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        return [
            'labels'      => $labels,
            'is_primaire' => $isPrimaire,
            'subjects'    => $subjects,
        ];
    }

    /** @return array<int, array{0: int, 1: string}> liste de [semestre, type] */
    private static function slotsFor(User $student, bool $isPrimaire): array
    {
        if ($isPrimaire && $student->class?->level) {
            $count = PrimaryGradingSettings::defaults($student->class->level)->compositionsCount;

            return array_map(fn ($i) => [1, 'composition'.$i], range(1, $count));
        }

        $slots = [];
        foreach ([1, 2] as $semester) {
            foreach (SenegalGradeSequence::ORDER as $type) {
                $slots[] = [$semester, $type];
            }
        }

        return $slots;
    }

    /**
     * @param  array<int, array{0: int, 1: string}>  $slots
     * @return array<int, string>
     */
    private static function evaluationLabels(array $slots, bool $isPrimaire): array
    {
        return array_map(
            fn ($slot) => $isPrimaire
                ? GradeSequence::shortLabel($slot[1])
                : 'S'.$slot[0].' · '.GradeSequence::shortLabel($slot[1]),
            $slots
        );
    }

    /**
     * @param  array<int, array{0: int, 1: string}>  $slots
     * @return array<int, array{label: string, semester: int, type: string, grade: float|null}>
     */
    private static function buildEvaluations(Collection $subjectGrades, array $slots, bool $isPrimaire): array
    {
        $labels = self::evaluationLabels($slots, $isPrimaire);
        $evaluations = [];

        foreach ($slots as $i => [$semester, $type]) {
            $grade = $subjectGrades->first(
                fn ($g) => (int) $g->semester === $semester && $g->type === $type
            );

            $evaluations[] = [
                'label'    => $labels[$i],
                'semester' => $semester,
                'type'     => $type,
                'grade'    => $grade !== null ? (float) $grade->grade : null,
            ];
        }

        return $evaluations;
    }
}
