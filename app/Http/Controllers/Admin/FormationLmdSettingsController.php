<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Support\FormationLmdSettings;
use App\Support\SenegalGradeSequence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FormationLmdSettingsController extends Controller
{
    public function edit(): View
    {
        $school = $this->currentFormationSchool();
        $settings = FormationLmdSettings::fromSchool($school);
        $gradeTypes = FormationLmdSettings::allSelectableGradeTypes();
        $typeLabels = SenegalGradeSequence::LABELS;

        return view('admin.formation.lmd-settings', compact('school', 'settings', 'gradeTypes', 'typeLabels'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = $this->currentFormationSchool();

        $validated = $request->validate([
            'cc_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'exam_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'passing_grade_min' => ['required', 'numeric', 'min:0', 'max:20'],
            'cc_grade_types' => ['nullable', 'array'],
            'cc_grade_types.*' => ['string', Rule::in(SenegalGradeSequence::ORDER)],
            'exam_grade_types' => ['nullable', 'array'],
            'exam_grade_types.*' => ['string', Rule::in(SenegalGradeSequence::ORDER)],
        ]);

        $cc = (int) $validated['cc_weight_percent'];
        $exam = (int) $validated['exam_weight_percent'];

        if ($cc + $exam !== 100) {
            return back()
                ->withInput()
                ->withErrors([
                    'exam_weight_percent' => 'La somme CC + Examen doit être égale à 100 %.',
                ]);
        }

        $settings = FormationLmdSettings::fromValidated($validated);
        $settings->persistToSchool($school);

        return redirect()
            ->route('admin.formation.lmd-settings.edit')
            ->with('success', 'Paramètres LMD enregistrés.');
    }

    private function currentFormationSchool(): School
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($schoolId, 403, 'Aucun établissement associé à votre compte.');

        $school = School::findOrFail($schoolId);
        abort_unless($school->usesLmdGrading(), 403, 'Réservé aux écoles de formation avec système LMD.');

        return $school;
    }
}
