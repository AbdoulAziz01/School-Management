<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\VirtualClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentVirtualClassController extends Controller
{
    // ── Liste des classes virtuelles de la classe de l'élève ────────────────

    public function index(): View
    {
        $student = auth()->user();

        $classes = VirtualClass::where('class_id', $student->class_id)
            ->with(['teacher', 'subject'])
            ->orderByDesc('scheduled_at')
            ->get();

        $active   = $classes->where('is_active', true);
        $upcoming = $classes->filter(fn ($c) => $c->isUpcoming());
        $past     = $classes->filter(fn ($c) => $c->isPast());

        return view('student.virtual-class.index', compact(
            'active', 'upcoming', 'past', 'student'
        ));
    }

    // ── Rejoindre une séance ─────────────────────────────────────────────────

    public function join(VirtualClass $virtualClass): View|RedirectResponse
    {
        $student = auth()->user();

        abort_if((int) $virtualClass->class_id !== (int) $student->class_id, 403);

        if (! $virtualClass->is_active) {
            return redirect()->route('student.virtual-class.index')
                ->with('error', 'Cette séance n\'est pas encore ouverte. Veuillez patienter.');
        }

        $virtualClass->load(['teacher', 'subject', 'classRoom']);

        return view('student.virtual-class.join', [
            'vc'      => $virtualClass,
            'student' => $student,
        ]);
    }
}
