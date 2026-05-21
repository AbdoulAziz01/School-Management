<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\School;
use App\Support\TenantSchool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LevelController extends Controller
{
    public function index(): View
    {
        $school = $this->school();

        $levels = Level::withCount(['classes', 'subjects'])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('admin.cycles.index', compact('levels', 'school'));
    }

    public function create(): View
    {
        $school = $this->school();

        abort_unless($school->isFormation(), 403, 'La gestion des cycles est réservée aux écoles de formation.');

        $level = new Level(['cycle' => 'formation', 'order' => Level::max('order') + 1]);

        return view('admin.cycles.form', compact('level', 'school'));
    }

    public function store(Request $request): RedirectResponse
    {
        $school = $this->school();
        abort_unless($school->isFormation(), 403);

        $validated = $this->validateLevel($request, $school);

        Level::create($validated);

        return redirect()
            ->route('admin.cycles.index')
            ->with('success', 'Cycle de formation créé avec succès.');
    }

    public function edit(Level $level): View
    {
        $school = $this->school();
        abort_unless($school->isFormation(), 403);

        return view('admin.cycles.form', compact('level', 'school'));
    }

    public function update(Request $request, Level $level): RedirectResponse
    {
        $school = $this->school();
        abort_unless($school->isFormation(), 403);

        $level->update($this->validateLevel($request, $school));

        return redirect()
            ->route('admin.cycles.index')
            ->with('success', 'Cycle mis à jour.');
    }

    public function destroy(Level $level): RedirectResponse
    {
        $school = $this->school();
        abort_unless($school->isFormation(), 403);

        if ($level->classes()->exists()) {
            return back()->with('error', 'Impossible de supprimer un cycle qui possède des promotions (classes).');
        }

        $level->delete();

        return redirect()
            ->route('admin.cycles.index')
            ->with('success', 'Cycle supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLevel(Request $request, School $school): array
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'order'       => ['required', 'integer', 'min:1', 'max:99'],
            'description' => ['nullable', 'string', 'max:2000'],
            'serie'       => ['nullable', 'string', 'max:50'],
        ]);

        return [
            'name'        => $validated['name'],
            'order'       => $validated['order'],
            'description' => $validated['description'] ?? null,
            'serie'       => $validated['serie'] ?? null,
            'cycle'       => 'formation',
            'school_id'   => $school->id,
        ];
    }

    private function school(): School
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        abort_unless($schoolId, 403);

        return School::findOrFail($schoolId);
    }
}
