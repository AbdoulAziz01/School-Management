<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Support\PlatformMetrics;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolInspectionController extends Controller
{
    public const SECTIONS = [
        'students' => ['label' => 'Élèves', 'icon' => 'fa-user-graduate'],
        'teachers' => ['label' => 'Profs', 'icon' => 'fa-chalkboard-teacher'],
        'staff' => ['label' => 'Staff', 'icon' => 'fa-user-shield'],
        'classes' => ['label' => 'Classes', 'icon' => 'fa-door-open'],
        'subjects' => ['label' => 'Matières', 'icon' => 'fa-book'],
        'pending' => ['label' => 'En attente', 'icon' => 'fa-clock'],
    ];

    public function index(Request $request, School $school): View
    {
        $section = $request->string('section')->toString();
        if (! array_key_exists($section, self::SECTIONS)) {
            $section = 'students';
        }

        PlatformMetrics::loadSchoolDetailCounts($school);
        $currentAcademicYear = PlatformMetrics::currentAcademicYearForSchool($school->id);
        $subjectsCount = PlatformMetrics::subjectsCountForSchool($school->id);
        $academicYears = PlatformMetrics::academicYearsForSchool($school->id);

        $selectedYearId = $request->integer('academic_year_id');
        if ($selectedYearId === 0 && $currentAcademicYear) {
            $selectedYearId = $currentAcademicYear->id;
        }

        $items = $this->paginateSection($request, $school, $section, $selectedYearId);
        $statCounts = $this->sectionStatCounts($school, $selectedYearId, $subjectsCount);

        return view('platform.schools.inspection', compact(
            'school',
            'section',
            'items',
            'currentAcademicYear',
            'subjectsCount',
            'academicYears',
            'selectedYearId',
            'statCounts',
        ));
    }

    /**
     * @return array<string, int>
     */
    private function sectionStatCounts(School $school, int $selectedYearId, int $subjectsCount): array
    {
        $counts = [
            'students' => (int) $school->students_count,
            'teachers' => (int) $school->teachers_count,
            'staff' => (int) $school->staff_count,
            'classes' => (int) $school->classes_count,
            'subjects' => $subjectsCount,
            'pending' => (int) $school->pending_count,
        ];

        if ($selectedYearId <= 0) {
            return $counts;
        }

        $counts['students'] = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->whereHas('class', fn ($c) => $c->where('academic_year_id', $selectedYearId))
            ->count();

        $counts['classes'] = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('academic_year_id', $selectedYearId)
            ->count();

        return $counts;
    }

    private function paginateSection(Request $request, School $school, string $section, int $selectedYearId): LengthAwarePaginator
    {
        $search = $request->string('q')->trim()->toString();

        return match ($section) {
            'students' => User::withoutGlobalScopes()
                ->with(['class.level', 'class.academicYear'])
                ->where('school_id', $school->id)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->when($selectedYearId > 0, fn ($q) => $q->whereHas(
                    'class',
                    fn ($c) => $c->where('academic_year_id', $selectedYearId)
                ))
                ->when($search !== '', fn ($q) => $this->applyUserSearch($q, $search))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),

            'teachers' => User::withoutGlobalScopes()
                ->withCount('teacherAssignments')
                ->where('school_id', $school->id)
                ->whereIn('role', User::ROLE_TEACHER_ALIASES)
                ->when($search !== '', fn ($q) => $this->applyUserSearch($q, $search))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),

            'staff' => User::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->whereIn('role', User::ROLE_SCHOOL_STAFF)
                ->when($search !== '', fn ($q) => $this->applyUserSearch($q, $search))
                ->orderBy('role')
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),

            'classes' => SchoolClass::withoutGlobalScopes()
                ->with(['level', 'academicYear'])
                ->withCount('students')
                ->where('school_id', $school->id)
                ->when($selectedYearId > 0, fn ($q) => $q->where('academic_year_id', $selectedYearId))
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($inner) use ($search) {
                        $inner->where('name', 'ilike', "%{$search}%")
                            ->orWhere('room_number', 'ilike', "%{$search}%")
                            ->orWhereHas('level', fn ($l) => $l->where('name', 'ilike', "%{$search}%"));
                    });
                })
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),

            'subjects' => Subject::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'ilike', "%{$search}%")
                        ->orWhere('code', 'ilike', "%{$search}%");
                }))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),

            'pending' => User::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('status', User::STATUS_PENDING)
                ->when($search !== '', fn ($q) => $this->applyUserSearch($q, $search))
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString(),
        };
    }

    private function applyUserSearch($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
                ->orWhere('identifier', 'ilike', "%{$search}%")
                ->orWhere('user_id', 'ilike', "%{$search}%");
        });
    }
}
