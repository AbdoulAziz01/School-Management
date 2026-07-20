<?php

namespace App\Services\Reports;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\SchoolBot\BulletinComputation;
use App\Support\Grading\GradeSequence;
use Carbon\Carbon;
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
            'absenceCount' => $this->countAbsences($student, $this->semesterDateRange($academicYear, $semester)),
        ];
    }

    /**
     * Nombre d'absences d'un élève sur une période donnée (bornes
     * incluses). Sans bornes, compte toutes les absences enregistrées.
     *
     * @param  array{0: ?Carbon, 1: ?Carbon}  $range
     */
    private function countAbsences(User $student, array $range): int
    {
        [$start, $end] = $range;

        return Attendance::where('user_id', $student->id)
            ->where('status', 'absent')
            ->when($start && $end, fn ($q) => $q->whereBetween('date', [$start->toDateString(), $end->toDateString()]))
            ->count();
    }

    /**
     * Découpe la période de l'année scolaire en deux semestres, au même
     * pivot que BulletinComputation::getCurrentSemester() (fin janvier /
     * début février) — Attendance n'a pas de colonne semestre.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function semesterDateRange(AcademicYear $academicYear, int $semester): array
    {
        if (! $academicYear->start_date || ! $academicYear->end_date) {
            return [null, null];
        }

        $start = Carbon::parse($academicYear->start_date);
        $end = Carbon::parse($academicYear->end_date);

        $pivot = Carbon::create($start->year, 2, 1);
        if ($pivot->lt($start)) {
            $pivot->addYear();
        }

        return $semester === 1
            ? [$start, $pivot->copy()->subDay()]
            : [$pivot, $end];
    }

    /**
     * Rapport de fin d'année : synthèse par élève (S1, S2, moyenne annuelle, décision).
     *
     * @return list<array<string, mixed>>
     */
    public function buildClassAnnualReport(SchoolClass $class, AcademicYear $academicYear): array
    {
        $class->loadMissing(['level', 'school']);
        $isPrimaire = $class->level?->isPrimaireCycle() ?? false;
        $overallMaxGrade = $this->bulletinComputation->referenceMaxGradeForLevel($class->level);
        $students = $this->approvedStudentsInClass($class);
        $rows = [];

        foreach ($students as $student) {
            if ($isPrimaire) {
                // Primaire : pas de semestres — moyenne annuelle calculée
                // directement à partir des compositions (voir
                // App\Support\Grading). Le S1/S2 des autres cycles n'a pas
                // de sens ici, on ne le réutilise donc pas.
                $moyenneAnnuelle = $this->getAnnualSummary($student, $class, $academicYear)['moyenne'];
                $s1Display = $moyenneAnnuelle;
                $s2Display = 0.0;
            } else {
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
                $s1Display = $s1['moyenne'];
                $s2Display = $s2['moyenne'];
            }

            $rows[] = [
                'identifier' => $student->identifier ?? '-',
                'name' => $student->name,
                'semestre1' => $s1Display,
                'semestre2' => $s2Display,
                'moyenne_annuelle' => $moyenneAnnuelle,
                'max_grade' => $overallMaxGrade,
                'decision' => $this->bulletinComputation->getDecisionText($moyenneAnnuelle, $overallMaxGrade),
                'mention' => $this->bulletinComputation->getMention($moyenneAnnuelle, $overallMaxGrade),
            ];
        }

        usort($rows, fn ($a, $b) => $b['moyenne_annuelle'] <=> $a['moyenne_annuelle']);

        return $rows;
    }

    /**
     * Bulletins annuels complets (toutes les matières, pas juste la
     * synthèse) pour toute une classe en un seul PDF — équivalent
     * "classe complète" du bulletin annuel élève
     * (StudentBulletinController::annual()), pour le primaire (3
     * compositions, pas de semestres) comme pour le secondaire (S1 + S2).
     *
     * @return list<array<string, mixed>>
     */
    /**
     * Bulletin annuel (compositions, pas de semestre) d'UN élève —
     * utilisé par BulletinVerifyController pour vérifier l'authenticité
     * d'un bulletin annuel primaire via QR code (le secondaire, lui,
     * réutilise buildSemesterBulletin() une fois par semestre).
     *
     * @return array<string, mixed>
     */
    public function buildAnnualBulletinForStudent(User $student, SchoolClass $class, AcademicYear $academicYear): array
    {
        $class->loadMissing(['level', 'school']);
        $level = $class->level;
        $overallMaxGrade = $this->bulletinComputation->referenceMaxGradeForLevel($level);

        $grades = Grade::where('user_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get();

        $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $level, $class->school);
        $generalAverage = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

        return [
            'student_id' => $student->id,
            'studentInfo' => [
                'name' => $student->name,
                'class' => $class->name,
                'serie' => $level?->serie,
                'identifier' => $student->identifier ?? '-',
                'academic_year' => $academicYear->name,
                'date_of_birth' => $student->date_of_birth?->format('d/m/Y') ?? '-',
                'level' => $level?->name ?? '-',
            ],
            'bulletinData' => $bulletinData,
            'generalAverage' => $generalAverage,
            'rankData' => ['rank' => null, 'total' => 0],
            'overallMaxGrade' => $overallMaxGrade,
        ];
    }

    public function buildClassAnnualBulletins(SchoolClass $class, AcademicYear $academicYear): array
    {
        $class->loadMissing(['level', 'school']);
        $level = $class->level;
        $isPrimaire = $level?->isPrimaireCycle() ?? false;
        $overallMaxGrade = $this->bulletinComputation->referenceMaxGradeForLevel($level);
        $students = $this->approvedStudentsInClass($class);
        $absenceRange = [
            $academicYear->start_date ? Carbon::parse($academicYear->start_date) : null,
            $academicYear->end_date ? Carbon::parse($academicYear->end_date) : null,
        ];

        $bulletins = [];
        foreach ($students as $student) {
            if ($isPrimaire) {
                $grades = Grade::where('user_id', $student->id)
                    ->where('academic_year_id', $academicYear->id)
                    ->with('subject')
                    ->get();

                $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $level, $class->school);
                $moyenneAnnuelle = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

                $semestre1Data = ['data' => $bulletinData, 'moyenne' => $moyenneAnnuelle];
                $semestre2Data = ['data' => [], 'moyenne' => 0];
            } else {
                $sem1 = $this->buildSemesterBulletin($student, $class, $academicYear, 1);
                $sem2 = $this->buildSemesterBulletin($student, $class, $academicYear, 2);
                $semestre1Data = ['data' => $sem1['bulletinData'], 'moyenne' => $sem1['generalAverage']];
                $semestre2Data = ['data' => $sem2['bulletinData'], 'moyenne' => $sem2['generalAverage']];

                $moyenneAnnuelle = 0.0;
                if ($semestre1Data['moyenne'] > 0 && $semestre2Data['moyenne'] > 0) {
                    $moyenneAnnuelle = round(($semestre1Data['moyenne'] + $semestre2Data['moyenne']) / 2, 2);
                } elseif ($semestre1Data['moyenne'] > 0) {
                    $moyenneAnnuelle = $semestre1Data['moyenne'];
                } elseif ($semestre2Data['moyenne'] > 0) {
                    $moyenneAnnuelle = $semestre2Data['moyenne'];
                }
            }

            $ratio = $overallMaxGrade > 0 ? $moyenneAnnuelle / $overallMaxGrade : 0;
            $decisionColor = match (true) {
                $ratio >= 10 / 20 => 'success',
                $ratio >= 8 / 20 => 'warning',
                default => 'danger',
            };

            $bulletins[] = [
                'student_id' => $student->id,
                'studentInfo' => [
                    'name' => $student->name,
                    'class' => $class->name,
                    'serie' => $level?->serie,
                    'identifier' => $student->identifier ?? '-',
                    'academic_year' => $academicYear->name,
                    'date_of_birth' => $student->date_of_birth?->format('d/m/Y') ?? '-',
                    'level' => $level?->name ?? '-',
                ],
                'semestre1Data' => $semestre1Data,
                'semestre2Data' => $semestre2Data,
                'moyenneAnnuelle' => $moyenneAnnuelle,
                'decision' => [
                    'text' => $this->bulletinComputation->getDecisionText($moyenneAnnuelle, $overallMaxGrade),
                    'color' => $decisionColor,
                    'mention' => $ratio >= 12 / 20 ? $this->bulletinComputation->getMention($moyenneAnnuelle, $overallMaxGrade) : null,
                ],
                'isPrimaire' => $isPrimaire,
                'overallMaxGrade' => $overallMaxGrade,
                'absenceCount' => $this->countAbsences($student, $absenceRange),
            ];
        }

        return $bulletins;
    }

    /**
     * Relevé d'une seule composition (primaire) pour toute une classe —
     * une note par matière (pas de moyenne sur plusieurs compositions),
     * pour les cas où l'admin veut sortir "toutes les copies de la
     * composition 2" sans attendre la fin de l'année.
     *
     * @return list<array<string, mixed>>
     */
    public function buildClassCompositionBulletins(SchoolClass $class, AcademicYear $academicYear, string $compositionType): array
    {
        $class->loadMissing(['level', 'school']);
        $level = $class->level;
        $overallMaxGrade = $this->bulletinComputation->referenceMaxGradeForLevel($level);
        $students = $this->approvedStudentsInClass($class);

        $bulletins = [];
        foreach ($students as $student) {
            $grades = Grade::where('user_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('type', $compositionType)
                ->with('subject')
                ->get();

            $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $level, $class->school);
            $moyenne = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

            $bulletins[] = [
                'student_id' => $student->id,
                'studentInfo' => [
                    'name' => $student->name,
                    'class' => $class->name,
                    'serie' => $level?->serie,
                    'identifier' => $student->identifier ?? '-',
                    'academic_year' => $academicYear->name,
                    'level' => $level?->name ?? '-',
                ],
                'bulletinData' => $bulletinData,
                'moyenne' => $moyenne,
                'overallMaxGrade' => $overallMaxGrade,
            ];
        }

        return $bulletins;
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
                'bareme' => $grade->subject_id ? GradeSequence::maxGradeFor($class, (int) $grade->subject_id) : 20.0,
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
     * Primaire : moyenne annuelle directe (toutes les notes de l'année,
     * pas de découpage semestriel — voir buildClassAnnualReport()).
     *
     * @return array{moyenne: float}
     */
    private function getAnnualSummary(User $student, SchoolClass $class, AcademicYear $academicYear): array
    {
        $grades = Grade::where('user_id', $student->id)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get();

        $bulletinData = $this->bulletinComputation->calculateBulletinData($grades, $class->level, $class->school);
        $moyenne = $this->bulletinComputation->calculateWeightedAverage($bulletinData);

        return ['moyenne' => $moyenne];
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
