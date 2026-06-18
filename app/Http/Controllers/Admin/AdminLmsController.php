<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Submission;

class AdminLmsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $stats = [
            'lessons'     => Lesson::count(),
            'assignments' => Assignment::count(),
            'submissions' => Submission::count(),
            'quizzes'     => Quiz::count(),
            'graded'      => Submission::where('status', 'graded')->count(),
            'pending'     => Submission::where('status', 'pending')->count(),
        ];

        $lessons = Lesson::with(['subject', 'schoolClass', 'teacher'])
            ->latest()
            ->paginate(15, ['*'], 'lessons_page');

        $assignments = Assignment::with(['subject', 'schoolClass', 'teacher'])
            ->withCount('submissions')
            ->latest()
            ->paginate(15, ['*'], 'assignments_page');

        $quizzes = Quiz::with(['subject', 'teacher'])
            ->withCount('questions')
            ->latest()
            ->paginate(15, ['*'], 'quizzes_page');

        return view('admin.lms.index', compact('stats', 'lessons', 'assignments', 'quizzes'));
    }

    public function destroyLesson(Lesson $lesson): \Illuminate\Http\RedirectResponse
    {
        $lesson->delete();
        return back()->with('success', 'Cours supprimé.');
    }

    public function destroyAssignment(Assignment $assignment): \Illuminate\Http\RedirectResponse
    {
        $assignment->submissions()->delete();
        $assignment->delete();
        return back()->with('success', 'Devoir supprimé.');
    }

    public function destroyQuiz(Quiz $quiz): \Illuminate\Http\RedirectResponse
    {
        $quiz->questions()->each(fn ($q) => $q->options()->delete());
        $quiz->questions()->delete();
        $quiz->delete();
        return back()->with('success', 'Quiz supprimé.');
    }
}
