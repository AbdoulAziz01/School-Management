<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\VirtualClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherVirtualClassController extends Controller
{
    // ── Tableau de bord ─────────────────────────────────────────────────────

    public function index(): View
    {
        $teacher = auth()->user();

        $classes = VirtualClass::where('teacher_id', $teacher->id)
            ->with(['classRoom.level', 'subject'])
            ->orderByDesc('scheduled_at')
            ->get();

        $stats = [
            'total'    => $classes->count(),
            'active'   => $classes->where('is_active', true)->count(),
            'upcoming' => $classes->filter(fn ($c) => $c->isUpcoming())->count(),
            'past'     => $classes->filter(fn ($c) => $c->isPast())->count(),
        ];

        return view('teacher.virtual-class.index', compact('classes', 'stats'));
    }

    // ── Formulaire de création ───────────────────────────────────────────────

    public function create(): View
    {
        $teacher = auth()->user();

        $myClasses = SchoolClass::whereHas(
            'teacherAssignments',
            fn ($q) => $q->where('teacher_id', $teacher->id)
        )->with('level')->orderedByLevel()->get();

        $subjects = Subject::whereHas(
            'teacherAssignments',
            fn ($q) => $q->where('teacher_id', $teacher->id)
        )->orderBy('name')->get();

        return view('teacher.virtual-class.create', compact('myClasses', 'subjects'));
    }

    // ── Enregistrement ──────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $teacher = auth()->user();

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:200'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'class_id'         => ['required', 'integer', 'exists:classes,id'],
            'subject_id'       => ['nullable', 'integer', 'exists:subjects,id'],
            'scheduled_at'     => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'meeting_password' => ['nullable', 'string', 'max:64'],
        ]);

        VirtualClass::create([
            ...$validated,
            'teacher_id' => $teacher->id,
            'school_id'  => $teacher->school_id,
            'room_name'  => VirtualClass::generateRoomName((int) $teacher->school_id),
            'is_active'  => false,
        ]);

        return redirect()->route('teacher.virtual-class.index')
            ->with('success', 'Classe virtuelle planifiée avec succès !');
    }

    // ── Ouvrir / Fermer la séance ────────────────────────────────────────────

    public function toggle(VirtualClass $virtualClass): RedirectResponse
    {
        abort_if($virtualClass->teacher_id !== auth()->id(), 403);

        if ($virtualClass->is_active) {
            $virtualClass->update([
                'is_active' => false,
                'closed_at' => now(),
            ]);

            return redirect()->route('teacher.virtual-class.index')
                ->with('success', 'Séance terminée et salle fermée.');
        }

        $virtualClass->update([
            'is_active'  => true,
            'opened_at'  => $virtualClass->opened_at ?? now(),
            'closed_at'  => null,
        ]);

        return redirect()->route('teacher.virtual-class.join', $virtualClass)
            ->with('success', 'Salle ouverte ! Les élèves peuvent maintenant rejoindre.');
    }

    // ── Rejoindre (animateur) ─────────────────────────────────────────────────

    public function join(VirtualClass $virtualClass): View|RedirectResponse
    {
        abort_if($virtualClass->teacher_id !== auth()->id(), 403);

        if (! $virtualClass->is_active) {
            return redirect()->route('teacher.virtual-class.index')
                ->with('error', 'Ouvrez d\'abord la salle avant de la rejoindre.');
        }

        $virtualClass->load(['classRoom', 'subject']);

        return view('teacher.virtual-class.room', [
            'vc'      => $virtualClass,
            'teacher' => auth()->user(),
        ]);
    }

    // ── Suppression ─────────────────────────────────────────────────────────

    public function destroy(VirtualClass $virtualClass): RedirectResponse
    {
        abort_if($virtualClass->teacher_id !== auth()->id(), 403);

        $virtualClass->delete();

        return redirect()->route('teacher.virtual-class.index')
            ->with('success', 'Classe virtuelle supprimée.');
    }
}
