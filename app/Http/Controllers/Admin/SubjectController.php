<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Level;
use App\Support\FormationLmdSettings;
use App\Support\SchoolSubjectProvisioner;
use App\Support\SenegalGradeSequence;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Affiche la liste des matières
     */
    public function index(Request $request)
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;

        if ($school?->isClassic()) {
            SchoolSubjectProvisioner::ensureForSchool($schoolId);
        }

        $query = Subject::with('teachers');
        
        // Recherche par nom ou code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('code', 'ilike', "%{$search}%");
            });
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filtre par cycle (primaire / collège / lycée)
        if ($request->filled('cycle')) {
            $cycle = $request->cycle;
            $query->whereHas('levels', fn ($q) => $q->where('levels.cycle', $cycle));
        }

        $subjects = $query->orderBy('name')->paginate(15)->withQueryString();

        $levelsCount = Level::count();
        $isFormationSchool = $school?->isFormation() ?? false;

        return view('admin.subjects.index', compact('subjects', 'isFormationSchool', 'levelsCount'));
    }

    /**
     * Affiche le formulaire de création d'une matière
     */
    public function create()
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;

        if ($school?->isClassic()) {
            SchoolSubjectProvisioner::ensureForSchool($schoolId);
        }

        $levels = Level::orderedPedagogically()->get();
        $isFormationSchool = $school?->isFormation() ?? false;
        $lmdSettings = $school?->usesLmdGrading() ? FormationLmdSettings::fromSchool($school) : null;
        $gradeTypes = FormationLmdSettings::allSelectableGradeTypes();
        $typeLabels = SenegalGradeSequence::LABELS;

        return view('admin.subjects.create', compact('levels', 'isFormationSchool', 'lmdSettings', 'gradeTypes', 'typeLabels'));
    }

    /**
     * Enregistre une nouvelle matière
     */
    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;
        $isFormationSchool = $school?->isFormation() ?? false;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('subjects', 'code')->where(fn ($q) => $q->where('school_id', $schoolId)),
            ],
            'coefficient' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'description' => ['nullable', 'string'],
            'hours_per_week' => ['nullable', 'numeric', 'min:0'],
            'is_core_subject' => ['boolean'],
            'is_active' => ['boolean'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['exists:levels,id'],
        ];

        if ($school?->usesLmdGrading()) {
            $rules = array_merge($rules, FormationLmdSettings::validationRules());
        }

        $validated = $request->validate($rules, $this->validationMessages());

        if ($school?->usesLmdGrading()) {
            $lmdError = $this->validateLmdWeightSum($validated);
            if ($lmdError) {
                return back()->withInput()->withErrors(['exam_weight_percent' => $lmdError]);
            }
        }

        try {
            $subject = Subject::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'coefficient' => $validated['coefficient'],
                'description' => $validated['description'] ?? null,
                'hours_per_week' => $validated['hours_per_week'] ?? 0,
                'is_core_subject' => $request->boolean('is_core_subject'),
                'is_active' => $request->boolean('is_active', true),
                'lmd_settings' => $school?->usesLmdGrading()
                    ? FormationLmdSettings::fromValidated($validated)->toArray()
                    : null,
                'created_by' => auth()->id(),
            ]);
            
            // Associer aux niveaux si spécifiés
            if (!empty($validated['levels'])) {
                $subject->levels()->attach($validated['levels']);
            }

            return redirect()
                ->route('admin.subjects.index')
                ->with('success', 'La matière a été créée avec succès.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de la matière.');
        }
    }

    /**
     * Affiche les détails d'une matière
     */
    public function show(Subject $subject)
    {
        $subject->load(['levels', 'teacherAssignments.teacher', 'teacherAssignments.schoolClass']);
        return view('admin.subjects.show', compact('subject'));
    }

    /**
     * Affiche le formulaire d'édition d'une matière
     */
    public function edit(Subject $subject)
    {
        $schoolId = TenantSchool::id() ?? auth()->user()?->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;

        $levels = Level::orderedPedagogically()->get();
        $subject->load('levels');
        $isFormationSchool = $school?->isFormation() ?? false;
        $lmdSettings = $school?->usesLmdGrading() ? FormationLmdSettings::fromSubject($subject) : null;
        $gradeTypes = FormationLmdSettings::allSelectableGradeTypes();
        $typeLabels = SenegalGradeSequence::LABELS;

        return view('admin.subjects.edit', compact('subject', 'levels', 'isFormationSchool', 'lmdSettings', 'gradeTypes', 'typeLabels'));
    }

    /**
     * Met à jour une matière
     */
    public function update(Request $request, Subject $subject)
    {
        $schoolId = auth()->user()->school_id;
        $school = $schoolId ? \App\Models\School::find($schoolId) : null;
        $isFormationSchool = $school?->isFormation() ?? false;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('subjects', 'code')
                    ->where(fn ($q) => $q->where('school_id', $schoolId))
                    ->ignore($subject->id),
            ],
            'coefficient' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'description' => ['nullable', 'string'],
            'hours_per_week' => ['nullable', 'numeric', 'min:0'],
            'is_core_subject' => ['boolean'],
            'is_active' => ['boolean'],
            'levels' => ['nullable', 'array'],
            'levels.*' => ['exists:levels,id'],
        ];

        if ($school?->usesLmdGrading()) {
            $rules = array_merge($rules, FormationLmdSettings::validationRules());
        }

        $validated = $request->validate($rules, $this->validationMessages());

        if ($school?->usesLmdGrading()) {
            $lmdError = $this->validateLmdWeightSum($validated);
            if ($lmdError) {
                return back()->withInput()->withErrors(['exam_weight_percent' => $lmdError]);
            }
        }

        try {
            $subject->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'coefficient' => $validated['coefficient'],
                'description' => $validated['description'] ?? null,
                'hours_per_week' => $validated['hours_per_week'] ?? 0,
                'is_core_subject' => $request->boolean('is_core_subject'),
                'is_active' => $request->boolean('is_active', true),
                'lmd_settings' => $school?->usesLmdGrading()
                    ? FormationLmdSettings::fromValidated($validated)->toArray()
                    : null,
                'updated_by' => auth()->id(),
            ]);
            
            // Synchroniser les niveaux
            $subject->levels()->sync($validated['levels'] ?? []);

            return redirect()
                ->route('admin.subjects.index')
                ->with('success', 'La matière a été mise à jour avec succès.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour de la matière.');
        }
    }

    /**
     * Supprime une matière
     */
    public function destroy(Subject $subject)
    {
        try {
            // Vérifier si la matière est utilisée
            if ($subject->teacherAssignments()->exists()) {
                return back()->with('error', 'Cette matière ne peut pas être supprimée car elle est affectée à des enseignants.');
            }

            if ($subject->grades()->exists()) {
                return back()->with('error', 'Cette matière ne peut pas être supprimée car des notes y sont déjà enregistrées.');
            }

            $subject->levels()->detach();
            $subject->delete();

            return redirect()
                ->route('admin.subjects.index')
                ->with('success', 'La matière a été supprimée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue lors de la suppression de la matière.');
        }
    }

    private function validationMessages(): array
    {
        return [
            'code.unique' => 'Ce code est déjà utilisé pour une matière de votre établissement (ex. Français avec le code FR). Modifiez la matière existante ou choisissez un autre code.',
            'code.required' => 'Le code de la matière est obligatoire.',
            'name.required' => 'Le nom de la matière est obligatoire.',
        ];
    }

    /** @param  array<string, mixed>  $validated */
    private function validateLmdWeightSum(array $validated): ?string
    {
        $cc = (int) $validated['cc_weight_percent'];
        $exam = (int) $validated['exam_weight_percent'];

        if ($cc + $exam !== 100) {
            return 'La somme CC + Examen doit être égale à 100 %.';
        }

        return null;
    }
}
