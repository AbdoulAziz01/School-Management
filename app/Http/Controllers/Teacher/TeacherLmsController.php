<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherLmsController extends Controller
{
    // ── Index / tableau de bord ───────────────────────────────────────────────

    public function index(): \Illuminate\View\View
    {
        $teacher = auth()->user();

        $lessonsCount     = Lesson::where('teacher_id', $teacher->id)->count();
        $assignmentsCount = Assignment::where('teacher_id', $teacher->id)->count();
        $quizzesCount     = Quiz::where('teacher_id', $teacher->id)->count();
        $submissionsCount = Submission::whereHas(
            'assignment', fn ($q) => $q->where('teacher_id', $teacher->id)
        )->count();

        $lessons = Lesson::where('teacher_id', $teacher->id)
            ->with(['subject', 'schoolClass'])
            ->latest()
            ->get();

        $assignments = Assignment::where('teacher_id', $teacher->id)
            ->withCount('submissions')
            ->with(['subject', 'schoolClass'])
            ->latest()
            ->get();

        $quizzes = Quiz::where('teacher_id', $teacher->id)
            ->withCount('questions')
            ->with('subject')
            ->latest()
            ->get();

        return view('teacher.lms.index', compact(
            'lessonsCount', 'assignmentsCount', 'quizzesCount', 'submissionsCount',
            'lessons', 'assignments', 'quizzes', 'teacher'
        ));
    }

    // ── Cours (Lessons) ───────────────────────────────────────────────────────

    public function lessonCreate(): \Illuminate\View\View
    {
        $teacher  = auth()->user();
        $classes  = $this->teacherClasses($teacher->id);
        $subjects = $this->teacherSubjects($teacher->id);

        return view('teacher.lms.lessons.create', compact('classes', 'subjects'));
    }

    public function lessonStore(Request $request): RedirectResponse
    {
        $teacher = auth()->user();

        $data = $request->validate([
            'class_id'   => ['required', 'integer', Rule::exists('classes', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'title'      => ['required', 'string', 'max:255'],
            'description'=> ['nullable', 'string', 'max:1000'],
            'file_type'  => ['required', Rule::in(['pdf', 'doc', 'video', 'link'])],
            'file'       => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:20480'], // 20 Mo
            'video_url'  => ['nullable', 'url', 'max:512'],
        ]);

        $this->assertTeacherOwnsClassAndSubject($teacher->id, (int) $data['class_id'], (int) $data['subject_id']);

        $filePath = null;

        if (in_array($data['file_type'], ['pdf', 'doc'], true) && $request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/lessons', 'local');
        } elseif (in_array($data['file_type'], ['video', 'link'], true)) {
            $filePath = $request->input('video_url');
        }

        Lesson::create([
            'class_id'    => $data['class_id'],
            'subject_id'  => $data['subject_id'],
            'school_id'   => $teacher->school_id,
            'teacher_id'  => $teacher->id,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'file_type'   => $data['file_type'],
            'file_path'   => $filePath,
            'is_published'=> true,
            'published_at'=> now(),
        ]);

        return redirect()->route('teacher.lms.index')
            ->with('success', 'Cours publié avec succès.');
    }

    public function lessonDestroy(Lesson $lesson): RedirectResponse
    {
        if ((int) $lesson->teacher_id !== (int) auth()->id()) {
            abort(403);
        }

        if ($lesson->file_path && in_array($lesson->file_type, ['pdf', 'doc'], true)) {
            Storage::disk('local')->delete($lesson->file_path);
        }

        $lesson->delete();

        return back()->with('success', 'Cours supprimé.');
    }

    // ── Devoirs (Assignments) ─────────────────────────────────────────────────

    public function assignmentCreate(): \Illuminate\View\View
    {
        $teacher  = auth()->user();
        $classes  = $this->teacherClasses($teacher->id);
        $subjects = $this->teacherSubjects($teacher->id);

        return view('teacher.lms.assignments.create', compact('classes', 'subjects'));
    }

    public function assignmentStore(Request $request): RedirectResponse
    {
        $teacher = auth()->user();

        $data = $request->validate([
            'class_id'     => ['required', 'integer', Rule::exists('classes', 'id')],
            'subject_id'   => ['required', 'integer', Rule::exists('subjects', 'id')],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'due_date'     => ['required', 'date', 'after:today'],
            'points'       => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $this->assertTeacherOwnsClassAndSubject($teacher->id, (int) $data['class_id'], (int) $data['subject_id']);

        Assignment::create([
            'class_id'     => $data['class_id'],
            'subject_id'   => $data['subject_id'],
            'school_id'    => $teacher->school_id,
            'teacher_id'   => $teacher->id,
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'due_date'     => $data['due_date'],
            'points'       => $data['points'],
            'status'       => 'published',
        ]);

        return redirect()->route('teacher.lms.index')
            ->with('success', 'Devoir publié avec succès.');
    }

    public function assignmentSubmissions(Assignment $assignment): \Illuminate\View\View
    {
        if ((int) $assignment->teacher_id !== (int) auth()->id()) {
            abort(403);
        }

        $submissions = Submission::where('assignment_id', $assignment->id)
            ->with('student')
            ->get();

        return view('teacher.lms.assignments.submissions', compact('assignment', 'submissions'));
    }

    public function submissionGrade(Request $request, Submission $submission): RedirectResponse
    {
        if ((int) $submission->assignment->teacher_id !== (int) auth()->id()) {
            abort(403);
        }

        $data = $request->validate([
            'grade'    => ['required', 'numeric', 'min:0', 'max:' . $submission->assignment->points],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission->update([
            'grade'    => $data['grade'],
            'feedback' => $data['feedback'] ?? null,
            'status'   => 'graded',
        ]);

        return back()->with('success', 'Note enregistrée.');
    }

    public function assignmentDestroy(Assignment $assignment): RedirectResponse
    {
        if ((int) $assignment->teacher_id !== (int) auth()->id()) {
            abort(403);
        }

        $assignment->submissions()->delete();
        $assignment->delete();

        return back()->with('success', 'Devoir supprimé.');
    }

    // ── Quiz ──────────────────────────────────────────────────────────────────

    public function quizCreate(): \Illuminate\View\View
    {
        $teacher  = auth()->user();
        $subjects = $this->teacherSubjects($teacher->id);

        return view('teacher.lms.quizzes.create', compact('subjects'));
    }

    public function quizStore(Request $request): RedirectResponse
    {
        $teacher = auth()->user();

        $data = $request->validate([
            'subject_id'              => ['required', 'integer', Rule::exists('subjects', 'id')],
            'title'                   => ['required', 'string', 'max:255'],
            'description'             => ['nullable', 'string', 'max:1000'],
            'time_limit'              => ['nullable', 'integer', 'min:1', 'max:180'],
            'questions'               => ['required', 'array', 'min:1', 'max:30'],
            'questions.*.text'        => ['required', 'string', 'max:500'],
            'questions.*.points'      => ['required', 'numeric', 'min:0.5', 'max:10'],
            'questions.*.options'     => ['required', 'array', 'min:2', 'max:5'],
            'questions.*.options.*.text'    => ['required', 'string', 'max:255'],
            'questions.*.options.*.correct' => ['nullable', 'boolean'],
        ]);

        if (! $this->teacherSubjects($teacher->id)->pluck('id')->contains((int) $data['subject_id'])) {
            abort(403, 'Vous n\'êtes pas affecté à cette matière.');
        }

        $quiz = Quiz::create([
            'subject_id'   => $data['subject_id'],
            'school_id'    => $teacher->school_id,
            'teacher_id'   => $teacher->id,
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'time_limit'   => $data['time_limit'] ?? null,
            'is_published' => true,
        ]);

        foreach ($data['questions'] as $qOrder => $qDef) {
            $question = Question::create([
                'quiz_id'       => $quiz->id,
                'question_text' => $qDef['text'],
                'points'        => $qDef['points'],
                'order'         => $qOrder + 1,
            ]);

            foreach ($qDef['options'] as $oOrder => $opt) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['text'],
                    'is_correct'  => ! empty($opt['correct']),
                    'order'       => $oOrder + 1,
                ]);
            }
        }

        return redirect()->route('teacher.lms.index')
            ->with('success', 'Quiz publié avec succès.');
    }

    public function quizDestroy(Quiz $quiz): RedirectResponse
    {
        if ((int) $quiz->teacher_id !== (int) auth()->id()) {
            abort(403);
        }

        $quiz->questions()->each(fn ($q) => $q->options()->delete());
        $quiz->questions()->delete();
        $quiz->delete();

        return back()->with('success', 'Quiz supprimé.');
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    private function teacherClasses(int $teacherId): \Illuminate\Database\Eloquent\Collection
    {
        return SchoolClass::whereHas(
            'teacherAssignments',
            fn ($q) => $q->where('teacher_id', $teacherId)
        )->get();
    }

    private function teacherSubjects(int $teacherId): \Illuminate\Database\Eloquent\Collection
    {
        return Subject::whereHas(
            'teacherAssignments',
            fn ($q) => $q->where('teacher_id', $teacherId)
        )->get();
    }

    /**
     * Garde-fou IDOR : empêche un enseignant de créer du contenu (leçon,
     * devoir) sur une classe/matière à laquelle il n'est pas affecté, y
     * compris potentiellement d'un autre établissement (cf. audit sécurité —
     * même pattern que TeacherAttendanceController::store()).
     */
    private function assertTeacherOwnsClassAndSubject(int $teacherId, int $classId, int $subjectId): void
    {
        if (! $this->teacherClasses($teacherId)->pluck('id')->contains($classId)) {
            abort(403, 'Vous n\'êtes pas affecté à cette classe.');
        }

        if (! $this->teacherSubjects($teacherId)->pluck('id')->contains($subjectId)) {
            abort(403, 'Vous n\'êtes pas affecté à cette matière.');
        }
    }
}
