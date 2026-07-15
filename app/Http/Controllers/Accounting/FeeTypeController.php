<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeType;
use App\Models\Level;
use App\Services\FeeConfigurationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Paramétrage des frais scolaires par le directeur — types de frais
 * (inscription, réinscription, mensualité...) et leurs montants par niveau
 * pour l'année scolaire en cours.
 */
class FeeTypeController extends Controller
{
    public function __construct(
        private FeeConfigurationService $fees
    ) {}

    public function index()
    {
        $feeTypes = FeeType::orderBy('category')->orderBy('name')->get();

        return view('accounting.directeur.fee-types.index', [
            'feeTypes' => $feeTypes,
            'categories' => FeeType::CATEGORIES,
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
        ]);

        $feeType = $this->fees->createFeeType($validated);

        return redirect()
            ->route('directeur.fee-types.amounts', $feeType)
            ->with('success', 'Type de frais créé. Renseignez maintenant les montants par niveau.');
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
            ->route('directeur.fee-types.index')
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
            ->route('directeur.fee-types.index')
            ->with('success', 'Type de frais supprimé.');
    }

    public function amounts(FeeType $feeType)
    {
        $academicYear = AcademicYear::where('is_current', true)->first();

        if (! $academicYear) {
            return redirect()
                ->route('directeur.fee-types.index')
                ->with('error', 'Aucune année scolaire courante — impossible de configurer les montants.');
        }

        $levels = Level::orderBy('order')->get();
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
}
