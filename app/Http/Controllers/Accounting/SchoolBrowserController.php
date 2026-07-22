<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\SchoolBot\BulletinComputation;
use App\Services\SchoolOverviewService;
use App\Support\Grading\GradeSequence;
use App\Support\TeacherClassResolver;
use Illuminate\Http\Request;

/**
 * Navigation en lecture seule de l'établissement pour le directeur — Centre
 * de Commande, section "Établissement" : Classes → Élève/Enseignant/Matière.
 * Aucune action d'édition ici (création/modification/suppression pédagogique
 * reste réservée au portail Admin) : uniquement de la consultation, pour que
 * le directeur ait une vision complète sans jamais avoir besoin d'ouvrir
 * l'espace Admin.
 */
class SchoolBrowserController extends Controller
{
    public function __construct(
        private SchoolOverviewService $overview,
        private BulletinComputation $bulletinComputation
    ) {}

    private function currentYear(School $school): ?AcademicYear
    {
        return AcademicYear::where('school_id', $school->id)->where('is_current', true)->first();
    }

    // ── Classes ──────────────────────────────────────────────────────────

    public function classesIndex(Request $request)
    {
        $school = School::find($request->user()->school_id);
        $academicYear = $this->currentYear($school);

        $classes = SchoolClass::where('school_id', $school->id)
            ->with('level')
            ->withCount(['students' => fn ($q) => $q->whereIn('role', User::ROLE_STUDENT_ALIASES)])
            ->orderedByLevel()
            ->get()
            ->map(function (SchoolClass $class) use ($academicYear) {
                $class->academic_summary = $this->overview->classAcademicSummary($class, $academicYear);

                return $class;
            });

        return view('accounting.directeur.school.classes.index', compact('classes'));
    }

    public function classesShow(Request $request, SchoolClass $class)
    {
        $school = School::find($request->user()->school_id);
        abort_unless($class->school_id === $school->id, 404);

        $class->load(['level', 'academicYear', 'teachers.subjects', 'teacherAssignments.teacher', 'teacherAssignments.subject']);
        $academicYear = $this->currentYear($school);

        $students = User::where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->orderBy('name')
            ->get();

        $boys = $students->where('gender', 'M')->count();
        $girls = $students->where('gender', 'F')->count();

        // Titulaire primaire (class_teacher) + professeurs par matière
        // (TeacherAssignment) : mêmes deux voies d'affectation que côté
        // admin (voir Admin\ClassController@show).
        $classTeachers = $class->teachers
            ->concat($class->teacherAssignments->pluck('teacher')->filter())
            ->unique('id')
            ->map(function (User $teacher) use ($class) {
                $subjectsForClass = $class->teacherAssignments->where('teacher_id', $teacher->id)->pluck('subject')->filter();
                $teacher->class_subjects = $subjectsForClass->isNotEmpty() ? $subjectsForClass : $teacher->subjects;

                return $teacher;
            });

        $classSubjects = $class->level
            ? $class->level->subjects()->wherePivot('is_active', true)->orderBy('subjects.name')->get()
            : collect();

        $presentToday = \App\Models\Attendance::where('school_id', $school->id)
            ->where('class_id', $class->id)
            ->whereDate('date', today())
            ->get()
            ->unique('user_id');

        return view('accounting.directeur.school.classes.show', [
            'class' => $class,
            'students' => $students,
            'boys' => $boys,
            'girls' => $girls,
            'classTeachers' => $classTeachers,
            'classSubjects' => $classSubjects,
            'academicSummary' => $this->overview->classAcademicSummary($class, $academicYear),
            'presentToday' => $presentToday->whereIn('status', ['present', 'late'])->count(),
            'absentToday' => $presentToday->where('status', 'absent')->count(),
        ]);
    }

    // ── Matière (notes d'une classe) ─────────────────────────────────────

    public function subjectGrades(Request $request, SchoolClass $class, Subject $subject)
    {
        $school = School::find($request->user()->school_id);
        abort_unless($class->school_id === $school->id, 404);

        $academicYear = $this->currentYear($school);
        $maxGrade = GradeSequence::maxGradeFor($class, $subject->id);

        $students = User::where('class_id', $class->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->orderBy('name')
            ->get();

        $grades = Grade::where('subject_id', $subject->id)
            ->whereIn('user_id', $students->pluck('id'))
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->orderByDesc('date')
            ->get()
            ->groupBy('user_id');

        $rows = $students->map(function (User $student) use ($grades) {
            $studentGrades = $grades->get($student->id, collect());

            return [
                'student' => $student,
                'grades' => $studentGrades,
                'average' => $studentGrades->isNotEmpty() ? round($studentGrades->avg('grade'), 2) : null,
            ];
        });

        return view('accounting.directeur.school.subjects.grades', [
            'class' => $class,
            'subject' => $subject,
            'maxGrade' => $maxGrade,
            'rows' => $rows,
        ]);
    }

    // ── Enseignants ──────────────────────────────────────────────────────

    public function teachersIndex(Request $request)
    {
        $school = School::find($request->user()->school_id);

        $teachers = User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_TEACHER_ALIASES)
            ->orderBy('name')
            ->get()
            ->map(function (User $teacher) {
                $teacher->resolved_classes = TeacherClassResolver::forTeacher($teacher);

                return $teacher;
            });

        return view('accounting.directeur.school.teachers.index', compact('teachers'));
    }

    public function teachersShow(Request $request, User $teacher)
    {
        $school = School::find($request->user()->school_id);
        abort_unless($teacher->school_id === $school->id && $teacher->isTeacher(), 404);

        $academicYear = $this->currentYear($school);
        $classes = TeacherClassResolver::forTeacher($teacher, $academicYear);

        $assignments = $teacher->teacherAssignments()
            ->with(['schoolClass.level', 'subject', 'academicYear'])
            ->orderByDesc('academic_year_id')
            ->get();

        $schedule = \App\Models\Schedule::where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'subject'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $studentsCount = User::whereIn('class_id', $classes->pluck('id'))
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->count();

        $classPerformance = $classes->map(fn (SchoolClass $class) => [
            'class' => $class,
            'summary' => $this->overview->classAcademicSummary($class, $academicYear),
        ]);

        return view('accounting.directeur.school.teachers.show', [
            'teacher' => $teacher,
            'classes' => $classes,
            'assignments' => $assignments,
            'schedule' => $schedule,
            'studentsCount' => $studentsCount,
            'classPerformance' => $classPerformance,
        ]);
    }

    // ── Élèves ───────────────────────────────────────────────────────────

    public function studentsIndex(Request $request)
    {
        $school = School::find($request->user()->school_id);

        $query = User::where('school_id', $school->id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->with('schoolClass.level');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")->orWhere('identifier', 'ilike', "%{$search}%");
            });
        }

        $students = $query->orderBy('name')->paginate(25)->withQueryString();
        $classes = SchoolClass::where('school_id', $school->id)->orderedByLevel()->get();

        return view('accounting.directeur.school.students.index', compact('students', 'classes'));
    }

    public function studentsShow(Request $request, User $student)
    {
        $school = School::find($request->user()->school_id);
        abort_unless($student->school_id === $school->id && $student->isStudent(), 404);

        $student->load('schoolClass.level');
        $academicYear = $this->currentYear($school);

        $grades = Grade::where('user_id', $student->id)
            ->when($academicYear, fn ($q) => $q->where('academic_year_id', $academicYear->id))
            ->with('subject')
            ->get();

        $class = $student->schoolClass;
        $bySubject = $grades->groupBy('subject.name')->map(function ($subjectGrades) use ($class) {
            $subjectId = (int) $subjectGrades->first()->subject_id;
            $maxGrade = $class ? GradeSequence::maxGradeFor($class, $subjectId) : 20.0;
            $average = $subjectGrades->avg('grade');

            return [
                'subject' => $subjectGrades->first()->subject,
                'average' => $average !== null ? round($average, 2) : null,
                'max_grade' => $maxGrade,
                'count' => $subjectGrades->count(),
            ];
        });

        $generalAverage = $academicYear ? $this->overview->studentGeneralAverage($student, $academicYear) : null;

        $invoices = \App\Models\StudentInvoice::where('student_id', $student->id)->orderByDesc('due_date')->get();
        $payments = \App\Models\Payment::where('student_id', $student->id)->orderByDesc('paid_at')->get();

        $attendance = \App\Models\Attendance::where('user_id', $student->id)
            ->orderByDesc('date')
            ->limit(30)
            ->get();
        $attendanceStats = [
            'present' => \App\Models\Attendance::where('user_id', $student->id)->where('status', 'present')->count(),
            'absent' => \App\Models\Attendance::where('user_id', $student->id)->where('status', 'absent')->count(),
            'late' => \App\Models\Attendance::where('user_id', $student->id)->where('status', 'late')->count(),
        ];

        return view('accounting.directeur.school.students.show', [
            'student' => $student,
            'bySubject' => $bySubject,
            'generalAverage' => $generalAverage,
            'referenceMaxGrade' => $this->bulletinComputation->referenceMaxGradeForLevel($class?->level),
            'invoices' => $invoices,
            'payments' => $payments,
            'attendance' => $attendance,
            'attendanceStats' => $attendanceStats,
        ]);
    }
}
