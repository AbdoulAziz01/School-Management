<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentQuizController extends Controller
{
    private const MAX_ATTEMPTS = 3;

    // ── Vue de présentation du quiz ─────────────────────────────────────────

    public function show(Quiz $quiz): View|RedirectResponse
    {
        $student = auth()->user();

        $this->authorizeAccess($quiz, $student);

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $student->id)
            ->orderBy('attempt_number')
            ->get();

        // Tentative incomplète → reprendre directement
        $incomplete = $attempts->first(fn ($a) => ! $a->isCompleted());
        if ($incomplete) {
            return redirect()->route('student.quiz.take', $incomplete);
        }

        $quiz->load(['questions.options', 'subject']);

        return view('student.quiz.show', [
            'quiz'              => $quiz,
            'attempts'          => $attempts,
            'remainingAttempts' => max(0, self::MAX_ATTEMPTS - $attempts->count()),
            'canAttempt'        => $attempts->count() < self::MAX_ATTEMPTS,
        ]);
    }

    // ── Démarrage d'une nouvelle tentative ──────────────────────────────────

    public function start(Quiz $quiz): RedirectResponse
    {
        $student = auth()->user();

        $this->authorizeAccess($quiz, $student);

        $attempt = null;
        $error   = null;

        DB::transaction(function () use ($quiz, $student, &$attempt, &$error) {
            // PostgreSQL interdit COUNT(*) FOR UPDATE — on sélectionne les ids puis on compte en PHP
            $count = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $student->id)
                ->select('id')
                ->lockForUpdate()
                ->get()
                ->count();

            if ($count >= self::MAX_ATTEMPTS) {
                $error = 'MAX_ATTEMPTS';
                return;
            }

            // Réutiliser une tentative incomplète existante
            $existing = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $student->id)
                ->whereNull('completed_at')
                ->first();

            if ($existing) {
                $attempt = $existing;
                return;
            }

            $attempt = QuizAttempt::create([
                'quiz_id'        => $quiz->id,
                'user_id'        => $student->id,
                'school_id'      => $student->school_id,
                'attempt_number' => $count + 1,
                'score'          => 0,
                'max_score'      => $quiz->totalPoints(),
                'answers'        => [],
                'started_at'     => now(),
                'completed_at'   => null,
            ]);
        });

        if ($error === 'MAX_ATTEMPTS') {
            return redirect()->route('student.quiz.show', $quiz)
                ->with('error', 'Vous avez atteint le nombre maximum de 3 tentatives pour ce quiz.');
        }

        return redirect()->route('student.quiz.take', $attempt);
    }

    // ── Page de passation du quiz ────────────────────────────────────────────

    public function take(QuizAttempt $attempt): View|RedirectResponse
    {
        $student = auth()->user();

        abort_if($attempt->user_id !== $student->id, 403);

        if ($attempt->isCompleted()) {
            return redirect()->route('student.quiz.result', $attempt);
        }

        $attempt->load(['quiz.questions.options', 'quiz.subject']);

        return view('student.quiz.take', [
            'attempt' => $attempt,
            'quiz'    => $attempt->quiz,
        ]);
    }

    // ── Soumission des réponses ──────────────────────────────────────────────

    public function submit(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        $student = auth()->user();

        abort_if($attempt->user_id !== $student->id, 403);

        if ($attempt->isCompleted()) {
            return redirect()->route('student.quiz.result', $attempt);
        }

        $validated = $request->validate([
            'answers'   => ['present', 'array'],
            'answers.*' => ['nullable', 'integer', 'min:1'],
        ]);

        $quiz  = $attempt->quiz()->with('questions.options')->first();
        $score = 0.0;

        foreach ($quiz->questions as $question) {
            $selectedId = isset($validated['answers'][$question->id])
                ? (int) $validated['answers'][$question->id]
                : null;

            if ($selectedId !== null) {
                $picked = $question->options->firstWhere('id', $selectedId);
                if ($picked && $picked->is_correct) {
                    $score += (float) $question->points;
                }
            }

            $recorded[(string) $question->id] = $selectedId;
        }

        $attempt->update([
            'score'        => $score,
            'max_score'    => $quiz->totalPoints(),
            'answers'      => $recorded ?? [],
            'completed_at' => now(),
        ]);

        return redirect()->route('student.quiz.result', $attempt)
            ->with('success', 'Quiz soumis avec succès !');
    }

    // ── Page de résultats ────────────────────────────────────────────────────

    public function result(QuizAttempt $attempt): View|RedirectResponse
    {
        $student = auth()->user();

        abort_if($attempt->user_id !== $student->id, 403);

        if (! $attempt->isCompleted()) {
            return redirect()->route('student.quiz.take', $attempt);
        }

        $attempt->load(['quiz.questions.options', 'quiz.subject']);

        $totalAttempts = QuizAttempt::where('quiz_id', $attempt->quiz_id)
            ->where('user_id', $student->id)
            ->count();

        return view('student.quiz.result', [
            'attempt'           => $attempt,
            'quiz'              => $attempt->quiz,
            'remainingAttempts' => max(0, self::MAX_ATTEMPTS - $totalAttempts),
            'canRetry'          => $totalAttempts < self::MAX_ATTEMPTS,
        ]);
    }

    // ── Vérification d'accès au quiz ─────────────────────────────────────────

    private function authorizeAccess(Quiz $quiz, $student): void
    {
        abort_unless($quiz->is_published, 403, 'Ce quiz n\'est pas disponible.');

        $hasSubject = DB::table('class_subject')
            ->where('class_id', $student->class_id)
            ->where('subject_id', $quiz->subject_id)
            ->exists();

        abort_unless($hasSubject, 403, 'Ce quiz ne fait pas partie de votre programme.');
    }
}
