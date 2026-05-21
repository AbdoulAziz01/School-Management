<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolLevelController extends Controller
{
    public function index(School $school): View
    {
        abort_unless($school->isFormation(), 404);

        $levels = Level::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->withCount(['classes', 'subjects'])
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('platform.schools.cycles.index', compact('school', 'levels'));
    }

    public function create(School $school): View
    {
        abort_unless($school->isFormation(), 404);

        $nextOrder = (int) Level::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->max('order') + 1;

        $level = new Level(['cycle' => 'formation', 'order' => max(1, $nextOrder)]);

        return view('platform.schools.cycles.form', compact('school', 'level'));
    }

    public function store(Request $request, School $school): RedirectResponse
    {
        abort_unless($school->isFormation(), 404);

        Level::withoutGlobalScopes()->create($this->validateLevel($request, $school));

        return redirect()
            ->route('platform.schools.cycles.index', $school)
            ->with('success', 'Cycle de formation ajouté.');
    }

    public function edit(School $school, Level $level): View
    {
        $this->assertLevelBelongsToSchool($school, $level);

        return view('platform.schools.cycles.form', compact('school', 'level'));
    }

    public function update(Request $request, School $school, Level $level): RedirectResponse
    {
        $this->assertLevelBelongsToSchool($school, $level);

        $level->update($this->validateLevel($request, $school));

        return redirect()
            ->route('platform.schools.cycles.index', $school)
            ->with('success', 'Cycle mis à jour.');
    }

    public function destroy(School $school, Level $level): RedirectResponse
    {
        $this->assertLevelBelongsToSchool($school, $level);

        if (Level::withoutGlobalScopes()->find($level->id)?->classes()->exists()) {
            return back()->with('error', 'Ce cycle possède des promotions — supprimez-les d\'abord.');
        }

        $level->delete();

        return redirect()
            ->route('platform.schools.cycles.index', $school)
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

    private function assertLevelBelongsToSchool(School $school, Level $level): void
    {
        abort_unless($school->isFormation(), 404);
        abort_unless((int) $level->school_id === (int) $school->id, 404);
    }
}
