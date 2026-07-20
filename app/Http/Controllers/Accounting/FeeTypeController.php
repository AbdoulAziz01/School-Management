<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\UpdateLevelFeeAmountsRequest;
use App\Models\AcademicYear;
use App\Models\FeeAmount;
use App\Models\FeeType;
use App\Models\Level;
use App\Services\FeeConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;

/**
 * Paramétrage des frais scolaires par le directeur.
 *
 * Vue principale (index) : tableau croisé Niveau × Type de frais — le
 * directeur pense "combien paie un élève de cette classe ?", pas "quels
 * sont tous les frais enregistrés ?". L'édition se fait niveau par niveau,
 * dans un modal, sans changer de page.
 *
 * Les types de frais eux-mêmes (colonnes du tableau) restent gérés via une
 * page dédiée (types/create/edit/destroy) — ajouter un nouveau type de
 * frais ajoute automatiquement une colonne au tableau, sans code en dur.
 */
class FeeTypeController extends Controller
{
    public function __construct(
        private FeeConfigurationService $fees
    ) {}

    public function index()
    {
        $academicYear = AcademicYear::where('is_current', true)->first();
        $feeTypes = FeeType::orderBy('category')->orderBy('name')->get();
        $levels = Level::orderedPedagogically()->get();

        $grid = $academicYear ? $this->fees->amountsGrid($feeTypes, $academicYear) : [];

        // Grille déjà résolue (repli "Tous niveaux" appliqué) : la vue ne
        // fait que des lectures de tableau, aucune logique côté client.
        $displayGrid = [];
        foreach ($levels as $level) {
            foreach ($feeTypes as $feeType) {
                $displayGrid[$level->id][$feeType->id] = $this->fees->amountFromGrid($grid, $level->id, $feeType->id);
            }
        }

        // Valeurs actuelles pour pré-remplir le formulaire du modal de
        // chaque niveau (montant spécifique au niveau uniquement, pas le
        // repli "Tous niveaux" — pour ne pas créer une ligne dupliquée en
        // resoumettant une valeur qui n'était qu'héritée).
        $modalValues = [];
        foreach ($levels as $level) {
            foreach ($feeTypes as $feeType) {
                $modalValues[$level->id][$feeType->id] = $grid[$level->id][$feeType->id] ?? null;
            }
        }

        return view('accounting.directeur.fee-types.matrix', [
            'academicYear' => $academicYear,
            'feeTypes' => $feeTypes,
            'levels' => $levels,
            'displayGrid' => $displayGrid,
            'modalValues' => $modalValues,
        ]);
    }

    public function types()
    {
        $feeTypes = FeeType::orderBy('category')->orderBy('name')->get();

        $academicYear = AcademicYear::where('is_current', true)->first();
        $defaultAmounts = [];

        if ($academicYear) {
            foreach ($feeTypes as $feeType) {
                $defaultAmounts[$feeType->id] = $this->fees->amountsFor($feeType, $academicYear)->get(0)?->amount;
            }
        }

        return view('accounting.directeur.fee-types.types', [
            'feeTypes' => $feeTypes,
            'categories' => FeeType::CATEGORIES,
            'defaultAmounts' => $defaultAmounts,
        ]);
    }

    public function create()
    {
        return view('accounting.directeur.fee-types.create', [
            'categories' => FeeType::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('parametrage.frais'), 403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('fee_types', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(FeeType::CATEGORIES))],
            'is_recurring' => ['sometimes', 'boolean'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $feeType = $this->fees->createFeeType($validated);

        if (! empty($validated['amount'])) {
            $academicYear = AcademicYear::where('is_current', true)->first();

            if ($academicYear) {
                $this->fees->setAmount($feeType, $academicYear, null, (float) $validated['amount']);
            }
        }

        return redirect()
            ->route('directeur.fee-types.index')
            ->with('success', 'Type de frais créé'.(! empty($validated['amount']) ? ' avec son montant.' : '.'));
    }

    public function edit(FeeType $feeType)
    {
        return view('accounting.directeur.fee-types.edit', [
            'feeType' => $feeType,
            'categories' => FeeType::CATEGORIES,
        ]);
    }

    public function update(Request $request, FeeType $feeType)
    {
        abort_unless($request->user()->can('parametrage.frais'), 403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('fee_types', 'code')->ignore($feeType->id)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(array_keys(FeeType::CATEGORIES))],
            'is_recurring' => ['sometimes', 'boolean'],
        ]);

        $this->fees->updateFeeType($feeType, $validated);

        return redirect()
            ->route('directeur.fee-types.types')
            ->with('success', 'Type de frais mis à jour.');
    }

    public function destroy(Request $request, FeeType $feeType)
    {
        abort_unless($request->user()->can('parametrage.frais'), 403);

        if ($feeType->amounts()->exists()) {
            return back()->with('error', 'Impossible de supprimer un type de frais pour lequel des montants ont déjà été configurés.');
        }

        $feeType->delete();

        return redirect()
            ->route('directeur.fee-types.types')
            ->with('success', 'Type de frais supprimé.');
    }

    public function amounts(FeeType $feeType)
    {
        $academicYear = AcademicYear::where('is_current', true)->first();

        if (! $academicYear) {
            return redirect()
                ->route('directeur.fee-types.types')
                ->with('error', 'Aucune année scolaire courante — impossible de configurer les montants.');
        }

        $levels = Level::orderedPedagogically()->get();
        $existingAmounts = $this->fees->amountsFor($feeType, $academicYear);

        return view('accounting.directeur.fee-types.amounts', [
            'feeType' => $feeType,
            'academicYear' => $academicYear,
            'levels' => $levels,
            'existingAmounts' => $existingAmounts,
        ]);
    }

    public function updateAmounts(Request $request, FeeType $feeType)
    {
        abort_unless($request->user()->can('parametrage.frais'), 403);

        $academicYear = AcademicYear::where('is_current', true)->first();

        if (! $academicYear) {
            return back()->with('error', 'Aucune année scolaire courante.');
        }

        $validated = $request->validate([
            'amounts' => ['required', 'array'],
            'amounts.all' => ['nullable', 'numeric', 'min:0'],
            'amounts.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->fees->updateAmounts($feeType, $academicYear, $validated['amounts']);

        return redirect()
            ->route('directeur.fee-types.amounts', $feeType)
            ->with('success', 'Montants mis à jour pour '.$academicYear->name.'.');
    }

    /**
     * Enregistre en un clic tous les montants d'un niveau (modal du tableau
     * croisé). L'action du formulaire est une URL fixe (pas de segment
     * dynamique) — le niveau voyage dans le corps de la requête
     * (level_id), pour ne jamais dépendre d'une liaison JS calculée au bon
     * moment sur l'attribut action du formulaire.
     */
    public function updateLevelAmounts(UpdateLevelFeeAmountsRequest $request)
    {
        $level = Level::where('school_id', $request->user()->school_id)
            ->findOrFail($request->validated('level_id'));

        $academicYear = AcademicYear::where('is_current', true)->first();

        if (! $academicYear) {
            return back()->with('error', 'Aucune année scolaire courante.');
        }

        $this->fees->updateLevelAmounts($level, $academicYear, $request->validated()['amounts']);

        return redirect()
            ->route('directeur.fee-types.index')
            ->with('success', "Frais mis à jour pour « {$level->name} ».");
    }

    /** Historique des modifications de montants pour un niveau donné (tous types de frais confondus). */
    public function history(Level $level)
    {
        $activities = Activity::with('causer')
            ->where('subject_type', FeeAmount::class)
            ->where(function ($query) use ($level) {
                $query->where('properties->attributes->level_id', $level->id)
                    ->orWhere('properties->old->level_id', $level->id);
            })
            ->latest()
            ->limit(100)
            ->get();

        $feeTypeNames = FeeType::pluck('name', 'id');

        return view('accounting.directeur.fee-types.history', [
            'level' => $level,
            'activities' => $activities,
            'feeTypeNames' => $feeTypeNames,
        ]);
    }
}
