<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Subject;
use App\Support\Grading\PrimaryGradingSettings;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Grille de notation du primaire — note maximale, coefficient, nombre de
 * compositions et type d'évaluation, configurables par (niveau, matière)
 * sans toucher au code (voir App\Support\Grading\PrimaryGradingSettings).
 *
 * Même patron que Accounting\FeeTypeController::index()/updateLevelAmounts()
 * (tableau croisé Niveau × Matière, un modal par niveau, un seul submit
 * enregistre toutes les matières de ce niveau d'un coup).
 */
class PrimaryGradingConfigController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = TenantSchool::id() ?? $request->user()->school_id;

        $levels = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('cycle', 'primaire')
            ->orderBy('order')
            ->get();

        $subjects = Subject::where('school_id', $schoolId)
            ->whereHas('levels', fn ($q) => $q->where('levels.cycle', 'primaire'))
            ->orderBy('name')
            ->get();

        // Grille déjà résolue (repli sur les valeurs par défaut si la
        // matière n'a pas encore été configurée pour ce niveau) : la vue
        // ne fait que des lectures de tableau.
        $grid = [];
        foreach ($levels as $level) {
            foreach ($subjects as $subject) {
                $grid[$level->id][$subject->id] = PrimaryGradingSettings::fromLevelSubject($level, $subject);
            }
        }

        return view('admin.primary-grading.matrix', [
            'levels' => $levels,
            'subjects' => $subjects,
            'grid' => $grid,
        ]);
    }

    /**
     * Enregistre en un clic tous les réglages d'un niveau (modal du tableau
     * croisé). URL fixe, le niveau voyage dans le corps de la requête.
     */
    public function update(Request $request)
    {
        $schoolId = TenantSchool::id() ?? $request->user()->school_id;

        $level = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('cycle', 'primaire')
            ->findOrFail($request->input('level_id'));

        $rules = PrimaryGradingSettings::validationRules();
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.max_grade' => $rules['max_grade'],
            'settings.*.coefficient' => $rules['coefficient'],
            'settings.*.compositions_count' => $rules['compositions_count'],
            'settings.*.evaluation_type' => $rules['evaluation_type'],
            'settings.*.is_active' => $rules['is_active'],
        ]);

        $subjectIds = Subject::where('school_id', $schoolId)->pluck('id');

        foreach ($validated['settings'] as $subjectId => $data) {
            if (! $subjectIds->contains((int) $subjectId)) {
                continue;
            }

            $subject = Subject::find($subjectId);
            PrimaryGradingSettings::fromValidated($data, $level)->persistTo($level, $subject);
        }

        return redirect()
            ->route('admin.primary-grading.index')
            ->with('success', "Notation mise à jour pour « {$level->name} ».");
    }
}
