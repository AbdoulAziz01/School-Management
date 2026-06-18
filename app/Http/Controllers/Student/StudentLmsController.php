<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentLmsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $student = auth()->user();

        if (! $student->class_id) {
            return view('student.lms.index', [
                'lessonsBySubject' => collect(),
                'assignments'      => collect(),
                'quizzes'          => collect(),
                'student'          => $student,
            ]);
        }

        $classId    = $student->class_id;
        $subjectIds = DB::table('class_subject')
            ->where('class_id', $classId)
            ->pluck('subject_id');

        $lessonsBySubject = Lesson::where('class_id', $classId)
            ->where('is_published', true)
            ->with(['subject', 'teacher'])
            ->orderBy('subject_id')
            ->get()
            ->groupBy(fn ($l) => $l->subject->name ?? 'Autre');

        $assignments = Assignment::where('class_id', $classId)
            ->whereIn('status', ['published', 'graded'])
            ->with([
                'subject',
                'submissions' => fn ($q) => $q->where('user_id', $student->id),
            ])
            ->orderByDesc('due_date')
            ->get();

        $quizzes = Quiz::whereIn('subject_id', $subjectIds)
            ->where('is_published', true)
            ->with([
                'subject',
                'questions.options',
                'attempts' => fn ($q) => $q
                    ->where('user_id', $student->id)
                    ->select('id', 'quiz_id', 'user_id', 'attempt_number', 'score', 'max_score', 'completed_at'),
            ])
            ->get();

        return view('student.lms.index', compact(
            'lessonsBySubject', 'assignments', 'quizzes', 'student'
        ));
    }

    public function downloadLesson(Lesson $lesson): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $student = auth()->user();

        if ((int) $lesson->class_id !== (int) $student->class_id || ! $lesson->is_published) {
            abort(403);
        }

        if (in_array($lesson->file_type, ['video', 'link'], true)) {
            return redirect($lesson->file_path);
        }

        if (! Storage::disk('local')->exists($lesson->file_path)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::disk('local')->download(
            $lesson->file_path,
            $lesson->title . '.pdf'
        );
    }
}
