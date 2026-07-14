<?php

namespace App\Services\SchoolBot;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchoolBotStatsService
{
    public function __construct(
        private BulletinComputation $bulletinComputation
    ) {}

    public function stats(int $schoolId): array
    {
        $studentQuery = User::query()->where('school_id', $schoolId)->whereIn('role', User::ROLE_STUDENT_ALIASES);
        $teacherQuery = User::query()->where('school_id', $schoolId)->whereIn('role', User::ROLE_TEACHER_ALIASES);

        $currentYear = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();

        $repeatersCount = $this->countRepeatersEstimate($schoolId);

        return [
            'current_academic_year' => $currentYear ? [
                'id' => $currentYear->id,
                'name' => $currentYear->name,
            ] : null,
            'students' => [
                'total' => (clone $studentQuery)->count(),
                'approved' => (clone $studentQuery)->where('status', User::STATUS_APPROVED)->count(),
                'pending' => (clone $studentQuery)->where('status', User::STATUS_PENDING)->count(),
                'rejected' => (clone $studentQuery)->where('status', User::STATUS_REJECTED)->count(),
                'unassigned_class' => (clone $studentQuery)
                    ->where('status', User::STATUS_APPROVED)
                    ->whereNull('class_id')
                    ->count(),
            ],
            'teachers' => [
                'total' => (clone $teacherQuery)->count(),
                'approved' => (clone $teacherQuery)->where('status', User::STATUS_APPROVED)->count(),
                'pending' => (clone $teacherQuery)->where('status', User::STATUS_PENDING)->count(),
            ],
            'admins' => User::query()->where('school_id', $schoolId)->where('role', User::ROLE_ADMIN)->count(),
            'classes' => SchoolClass::query()->where('school_id', $schoolId)->count(),
            'repeaters' => [
                'count' => $repeatersCount,
                'definition' => 'Élèves approuvés ayant des notes sur au moins deux années académiques distinctes (heuristique, pas de champ « redoublant » en base).',
            ],
        ];
    }

    /**
     * Heuristique redoublants : plusieurs academic_year_id distincts dans grades.
     */
    public function countRepeatersEstimate(int $schoolId): int
    {
        $studentIds = User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return 0;
        }

        // PostgreSQL exige que SELECT ne soit pas * quand GROUP BY user_id (pas MySQL).
        return DB::table('grades')
            ->where('school_id', $schoolId)
            ->whereIn('user_id', $studentIds)
            ->whereNotNull('academic_year_id')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT academic_year_id) >= 2')
            ->get()
            ->count();
    }

    /**
     * @return list<array{id:int, identifier:?string, name:string, status:string, class:?string, academic_years_with_grades:int}>
     */
    public function repeatersList(int $schoolId, int $limit = 50): array
    {
        $limit = max(1, min($limit, 200));

        $rows = DB::table('grades')
            ->where('school_id', $schoolId)
            ->whereNotNull('academic_year_id')
            ->selectRaw('user_id, COUNT(DISTINCT academic_year_id) as academic_years_with_grades')
            ->groupBy('user_id')
            ->havingRaw('COUNT(DISTINCT academic_year_id) >= 2')
            ->orderByDesc('academic_years_with_grades')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $users = User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->whereIn('id', $rows->pluck('user_id'))
            ->with(['class:id,name'])
            ->get()
            ->keyBy('id');

        $out = [];
        foreach ($rows as $row) {
            $u = $users->get((int) $row->user_id);
            if (! $u) {
                continue;
            }
            $out[] = [
                'id' => $u->id,
                'identifier' => $u->identifier,
                'name' => $u->name,
                'status' => $u->status,
                'class' => $u->class?->name,
                'academic_years_with_grades' => (int) $row->academic_years_with_grades,
            ];
        }

        return $out;
    }

    /**
     * Agrégats pass / fail selon la moyenne générale pondérée (bulletin semestriel, année courante).
     *
     * @return array<string, mixed>
     */
    public function outcomes(int $schoolId): array
    {
        $year = AcademicYear::where('school_id', $schoolId)->where('is_current', true)->first();
        if (! $year) {
            return [
                'configured' => false,
                'message' => 'Aucune année académique courante.',
            ];
        }

        $semester = $this->bulletinComputation->getCurrentSemester();
        $threshold = (float) config('services.school_bot.passing_grade_min', 10.0);

        $classes = SchoolClass::query()->where('school_id', $schoolId)->with('level')->get();

        $pass = 0;
        $fail = 0;
        $no_data = 0;

        foreach ($classes as $class) {
            $averages = $this->calculateClassAveragesWeighted($schoolId, $class, $semester, $year);
            foreach ($averages as $average) {
                if ($average <= 0.0) {
                    $no_data++;
                } elseif ($average >= $threshold) {
                    $pass++;
                } else {
                    $fail++;
                }
            }
        }

        $approvedAssigned = User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->whereNotNull('class_id')
            ->count();

        $counted = $pass + $fail + $no_data;
        $unincluded = max(0, $approvedAssigned - $counted);

        return [
            'configured' => true,
            'academic_year' => [
                'id' => $year->id,
                'name' => $year->name,
            ],
            'semester' => $semester,
            'passing_grade_min' => $threshold,
            'method' => 'Moyenne générale pondérée (coefficients level_subject), même formule que le bulletin sénégalais par semestre.',
            'counts' => [
                'pass' => $pass,
                'fail' => $fail,
                'no_data' => $no_data + $unincluded,
            ],
            'students_assigned_to_class' => $approvedAssigned,
            'notes' => $unincluded > 0
                ? '« no_data » inclut les écarts entre effectif affecté et élèves comptés par classe (classes sans élèves ou incohérences).'
                : null,
        ];
    }

    /**
     * @return array<int, float> user_id => moyenne
     */
    private function calculateClassAveragesWeighted(int $schoolId, SchoolClass $class, int $semester, AcademicYear $academicYear): array
    {
        $studentIds = User::query()
            ->where('school_id', $schoolId)
            ->where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return [];
        }

        $gradesByStudent = Grade::query()
            ->where('school_id', $schoolId)
            ->whereIn('user_id', $studentIds)
            ->where('semester', $semester)
            ->where('academic_year_id', $academicYear->id)
            ->with('subject')
            ->get()
            ->groupBy('user_id');

        $coefficients = $this->bulletinComputation->fetchLevelCoefficients($class->level);

        $averages = [];
        foreach ($studentIds as $studentId) {
            $studentGrades = $gradesByStudent->get($studentId, collect());
            $bulletinData = $this->bulletinComputation->calculateBulletinData($studentGrades, $class->level, $coefficients);
            $averages[(int) $studentId] = $this->bulletinComputation->calculateWeightedAverage($bulletinData);
        }

        return $averages;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    public function studentSearchQuery(string $q, int $schoolId)
    {
        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';

        return User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', $term)
                    ->orWhere('identifier', 'like', $term);
            })
            ->orderBy('name')
            ->with(['class:id,name']);
    }

    /**
     * Élèves et enseignants : recherche par identifiant ou nom (pour Botpress / outils avec Bearer).
     *
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    public function userSearchQuery(string $q, int $schoolId)
    {
        $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
        $roles = array_merge(User::ROLE_STUDENT_ALIASES, User::ROLE_TEACHER_ALIASES);

        return User::query()
            ->where('school_id', $schoolId)
            ->whereIn('role', $roles)
            ->where(function ($query) use ($term, $q) {
                $query->where('identifier', $q)
                    ->orWhere('identifier', 'like', $term)
                    ->orWhere('name', 'like', $term);
            })
            ->orderByRaw('CASE WHEN identifier = ? THEN 0 ELSE 1 END', [$q])
            ->orderBy('name')
            ->with(['class:id,name']);
    }
}
