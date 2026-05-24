<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PlatformMetrics
{
    public static function usersQuery(): Builder
    {
        return User::withoutGlobalScopes()->whereNotNull('school_id');
    }

    public static function globalStats(): array
    {
        $users = self::usersQuery();

        $schoolIdsWithCurrentYear = AcademicYear::withoutGlobalScopes()
            ->where('is_current', true)
            ->pluck('school_id');

        return [
            'schools_total' => School::count(),
            'schools_active' => School::where('is_active', true)->count(),
            'schools_inactive' => School::where('is_active', false)->count(),
            'users_total' => (clone $users)->count(),
            'students_total' => (clone $users)->whereIn('role', User::ROLE_STUDENT_ALIASES)->count(),
            'teachers_total' => (clone $users)->whereIn('role', User::ROLE_TEACHER_ALIASES)->count(),
            'staff_total' => (clone $users)->whereIn('role', User::ROLE_SCHOOL_STAFF)->count(),
            'pending_registrations' => (clone $users)->where('status', User::STATUS_PENDING)->count(),
            'unassigned_students' => (clone $users)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->whereNull('class_id')
                ->count(),
            'schools_without_admin' => School::whereDoesntHave('users', fn ($q) => $q->where('role', User::ROLE_ADMIN))->count(),
            'schools_without_current_year' => School::where('is_active', true)
                ->whereNotIn('id', $schoolIdsWithCurrentYear)
                ->count(),
        ];
    }

    public static function schoolWithCountsQuery(): Builder
    {
        return School::withCount([
            'users',
            'users as students_count' => fn ($q) => $q->whereIn('role', User::ROLE_STUDENT_ALIASES),
            'users as teachers_count' => fn ($q) => $q->whereIn('role', User::ROLE_TEACHER_ALIASES),
            'users as staff_count' => fn ($q) => $q->whereIn('role', User::ROLE_SCHOOL_STAFF),
            'users as admins_count' => fn ($q) => $q->where('role', User::ROLE_ADMIN),
            'users as pending_count' => fn ($q) => $q->where('status', User::STATUS_PENDING),
            'users as unassigned_students_count' => fn ($q) => $q
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->whereNull('class_id'),
            'classes',
        ]);
    }

    public static function loadSchoolDetailCounts(School $school): School
    {
        return $school->loadCount([
            'users',
            'users as students_count' => fn ($q) => $q->whereIn('role', User::ROLE_STUDENT_ALIASES),
            'users as teachers_count' => fn ($q) => $q->whereIn('role', User::ROLE_TEACHER_ALIASES),
            'users as staff_count' => fn ($q) => $q->whereIn('role', User::ROLE_SCHOOL_STAFF),
            'users as admins_count' => fn ($q) => $q->where('role', User::ROLE_ADMIN),
            'users as surveillants_count' => fn ($q) => $q->where('role', User::ROLE_SURVEILLANT),
            'users as pending_count' => fn ($q) => $q->where('status', User::STATUS_PENDING),
            'users as unassigned_students_count' => fn ($q) => $q
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->whereNull('class_id'),
            'classes',
        ]);
    }

    public static function subjectsCountForSchool(int $schoolId): int
    {
        return Subject::withoutGlobalScopes()->where('school_id', $schoolId)->count();
    }

    public static function currentAcademicYearForSchool(int $schoolId): ?AcademicYear
    {
        return AcademicYear::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();
    }

    /** @return array<int, string> school_id => year name */
    public static function currentAcademicYearsBySchool(): array
    {
        return AcademicYear::withoutGlobalScopes()
            ->where('is_current', true)
            ->pluck('name', 'school_id')
            ->all();
    }

    public static function unassignedStudentsQuery(): Builder
    {
        return User::withoutGlobalScopes()
            ->with('school')
            ->whereNotNull('school_id')
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->whereNull('class_id');
    }

    public static function academicYearsForSchool(int $schoolId)
    {
        return AcademicYear::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get();
    }

    /** Classes de l'année courante ouverte uniquement (affectation élèves). */
    public static function assignableClassesForSchool(int $schoolId)
    {
        $currentYear = self::currentAcademicYearForSchool($schoolId);

        if (! $currentYear?->allowsStudentAssignment()) {
            return collect();
        }

        return SchoolClass::withoutGlobalScopes()
            ->with(['level', 'academicYear'])
            ->where(SchoolClass::column('school_id'), $schoolId)
            ->where(SchoolClass::column('academic_year_id'), $currentYear->id)
            ->orderedByLevel()
            ->get();
    }

    public static function watchlistSchools(int $limit = 8): array
    {
        $schoolIdsWithCurrentYear = AcademicYear::withoutGlobalScopes()
            ->where('is_current', true)
            ->pluck('school_id');

        $schools = self::schoolWithCountsQuery()
            ->where(function ($q) use ($schoolIdsWithCurrentYear) {
                $q->whereDoesntHave('users', fn ($u) => $u->where('role', User::ROLE_ADMIN))
                    ->orWhereHas('users', fn ($u) => $u->where('status', User::STATUS_PENDING))
                    ->orWhere('is_active', false)
                    ->orWhere(function ($q2) use ($schoolIdsWithCurrentYear) {
                        $q2->where('is_active', true)
                            ->whereNotIn('id', $schoolIdsWithCurrentYear);
                    });
            })
            ->latest()
            ->take($limit)
            ->get();

        $years = self::currentAcademicYearsBySchool();

        return [
            'schools' => $schools,
            'years' => $years,
        ];
    }
}
