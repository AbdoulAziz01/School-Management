<?php

namespace App\Services\Reports;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\SchoolBot\BulletinComputation;
use Illuminate\Support\Collection;

class BulletinReportService
{
    public function __construct(
        private BulletinComputation $bulletinComputation
    ) {}

    public function currentSemester(): int
    {
        return $this->bulletinComputation->getCurrentSemester();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildClassSemesterBulletins(SchoolClass $class, AcademicYear $academicYear, int $semester): array
    {
        $class->loadMissing(['level', 'school']);
        $students = $this->approvedStudentsInClass($class);
        $coefficients = $this->bulletinComputation->fetchLevelCoefficients($class->level);
        $classStats = $this->calculateClassStats($class, $semester, $academicYear);
        $averages = $this->calculateClassAverages($class, $semester, $academicYear);

        arsort($averages);
        $rankByStudent = [];
        $rank = 1;
        foreach ($averages as $studentId => $avg) {
            $rankByStudent[(int) $studentId] = $rank++;
        }

        $bulletins = [];
        foreach ($students as $student) {
            $bulletins[] = $this->buildSemesterBulletin(
                $student,
                $class,
                $academicYear,
                $semester,
                $coefficients,
                $classStats,
                $rankByStudent[$student->id] ?? null,
                count($averages)
            );
        }

        return $bulletins;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSemesterBulletin(
        User $student,
        ?SchoolClass $class,
        AcademicYear $academicYear,
        int $semester,
        ?array $coefficients = null,
        ?array $classStats = null,
        ?int $rank = null,
        ?int $rankTotal = null,
    ): array {
        $student->loadMissing('schoolClass');
        $class ??= $student->schoolClass;
        $class?->loadMissing(['level', 'school']);
        $level = $class?->level;

        $grades = Grade::where('user_id', $student->id)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get();

        $coefficients ??= $this->bulletinComputation->fetchLevelCoefficients($level);
        $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $level, $class?->school, $coefficients);
        $generalAverage = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

        if ($classStats === null && $class) {
            $classStats = $this->calculateClassStats($class, $semester, $academicYear);
        }

        if ($rank === null && $class) {
            $averages = $this->calculateClassAverages($class, $semester, $academicYear);
            arsort($averages);
            $rank = 1;
            foreach ($averages as $studentId => $avg) {
                if ((int) $studentId === (int) $student->id) {
                    break;
                }
                $rank++;
            }
            $rankTotal = count($averages);
            if (! isset($averages[$student->id])) {
                $rank = null;
            }
        }

        return [
            'student_id' => $student->id,
            'student' => [
                'name' => $student->name,
                'identifier' => $student->identifier ?? '-',
                'date_of_birth' => $student->date_of_birth?->format('d/m/Y') ?? '-',
            ],
            'studentInfo' => [
                'name' => $student->name,
                'class' => $class?->name ?? 'Non assigné',
                'serie' => $level?->serie,
                'identifier' => $student->identifier ?? '-',
                'academic_year' => $academicYear->name,
                'semester' => $semester,
                'date_of_birth' => $student->date_of_birth?->format('d/m/Y') ?? '-',
                'level' => $level?->name ?? '-',
            ],
            'bulletinData' => $bulletinData,
            'generalAverage' => $generalAverage,
            'rankData' => ['rank' => $rank, 'total' => $rankTotal ?? 0],
            'classStats' => $classStats ?? ['average' => null, 'highest' => null, 'lowest' => null],
            'usesLmd' => $class?->school?->usesLmdGrading() ?? false,
        ];
    }

    /**
     * Rapport de fin d'année : synthèse par élève (S1, S2, moyenne annuelle, décision).
     *
     * @return list<array<string, mixed>>
     */
    public function buildClassAnnualReport(SchoolClass $class, AcademicYear $academicYear): array
    {
        $class->loadMissing(['level', 'school']);
        $students = $this->approvedStudentsInClass($class);
        $rows = [];

        foreach ($students as $student) {
            $s1 = $this->getSemesterSummary($student, $class, $academicYear, 1);
            $s2 = $this->getSemesterSummary($student, $class, $academicYear, 2);

            $moyenneAnnuelle = 0.0;
            if ($s1['moyenne'] > 0 && $s2['moyenne'] > 0) {
                $moyenneAnnuelle = round(($s1['moyenne'] + $s2['moyenne']) / 2, 2);
            } elseif ($s1['moyenne'] > 0) {
                $moyenneAnnuelle = $s1['moyenne'];
            } elseif ($s2['moyenne'] > 0) {
                $moyenneAnnuelle = $s2['moyenne'];
            }

            $rows[] = [
                'identifier' => $student->identifier ?? '-',
                'name' => $student->name,
                'semestre1' => $s1['moyenne'],
                'semestre2' => $s2['moyenne'],
                'moyenne_annuelle' => $moyenneAnnuelle,
                'decision' => $this->bulletinComputation->getDecisionText($moyenneAnnuelle),
                'mention' => $this->bulletinComputation->getMention($moyenneAnnuelle),
            ];
        }

        usort($rows, fn ($a, $b) => $b['moyenne_annuelle'] <=> $a['moyenne_annuelle']);

        return $rows;
    }

    /**
     * Export CSV : toutes les notes d'une classe pour une année (optionnellement un semestre).
     *
     * @return list<array<string, string|int|float|null>>
     */
    public function buildGradesExportRows(SchoolClass $class, AcademicYear $academicYear, ?int $semester = null): array
    {
        $studentIds = $this->approvedStudentsInClass($class)->pluck('id');

        $query = Grade::query()
            ->whereIn('user_id', $studentIds)
            ->where('academic_year_id', $academicYear->id)
            ->with(['subject', 'user']);

        if ($semester !== null) {
            $query->where('semester', $semester);
        }

        $rows = [];
        foreach ($query->orderBy('user_id')->orderBy('subject_id')->orderBy('date')->get() as $grade) {
            $rows[] = [
                'identifiant' => $grade->user?->identifier ?? '',
                'eleve' => $grade->user?->name ?? '',
                'classe' => $class->name,
                'matiere' => $grade->subject?->name ?? '',
                'code_matiere' => $grade->subject?->code ?? '',
                'semestre' => $grade->semester,
                'type' => $grade->type,
                'note' => $grade->grade,
                'coefficient' => $grade->coefficient,
                'date' => $grade->date?->format('Y-m-d') ?? '',
                'appreciation' => $grade->appreciation ?? '',
            ];
        }

        return $rows;
    }

    /**
     * @return Collection<int, User>
     */
    public function approvedStudentsInClass(SchoolClass $class): Collection
    {
        return User::query()
            ->where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{moyenne: float}
     */
    private function getSemesterSummary(User $student, SchoolClass $class, AcademicYear $academicYear, int $semester): array
    {
        $bulletin = $this->buildSemesterBulletin($student, $class, $academicYear, $semester);

        return ['moyenne' => (float) ($bulletin['generalAverage'] ?? 0)];
    }

    /**
     * @return array<int, float>
     */
    private function calculateClassAverages(SchoolClass $class, int $semester, AcademicYear $academicYear): array
    {
        $studentIds = $this->approvedStudentsInClass($class)->pluck('id');

        if ($studentIds->isEmpty()) {
            return [];
        }

        $gradesByStudent = Grade::whereIn('user_id', $studentIds)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get()
            ->groupBy('user_id');

        $coefficients = $this->bulletinComputation->fetchLevelCoefficients($class->level);
        $averages = [];

        foreach ($studentIds as $studentId) {
            $studentGrades = $gradesByStudent->get($studentId, collect());
            $bulletinData = $this->bulletinComputation->calculateBulletinData($studentGrades, $class->level, $class->school, $coefficients);
            $averages[$studentId] = $this->bulletinComputation->calculateWeightedAverage($bulletinData);
        }

        return $averages;
    }

    /**
     * @return array{average: float|null, highest: float|null, lowest: float|null}
     */
    private function calculateClassStats(SchoolClass $class, int $semester, AcademicYear $academicYear): array
    {
        $averages = array_filter(
            $this->calculateClassAverages($class, $semester, $academicYear),
            fn ($a) => $a > 0
        );

        if (empty($averages)) {
            return ['average' => null, 'highest' => null, 'lowest' => null];
        }

        return [
            'average' => round(array_sum($averages) / count($averages), 2),
            'highest' => round(max($averages), 2),
            'lowest' => round(min($averages), 2),
        ];
    }

}
