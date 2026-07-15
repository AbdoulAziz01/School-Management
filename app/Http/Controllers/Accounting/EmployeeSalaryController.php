<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmployeeSalaryProfileService;
use Illuminate\Http\Request;

/**
 * Paramétrage des salaires du personnel (enseignants, surveillants,
 * administratifs) par le directeur.
 */
class EmployeeSalaryController extends Controller
{
    /** @var list<string> */
    private const EMPLOYEE_ROLES = [
        ...User::ROLE_TEACHER_ALIASES,
        User::ROLE_SURVEILLANT,
        User::ROLE_ADMIN,
    ];

    public function __construct(
        private EmployeeSalaryProfileService $salaries
    ) {}

    public function index(Request $request)
    {
        $query = User::whereIn('role', self::EMPLOYEE_ROLES);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('identifier', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();

        $currentSalaries = collect();
        foreach ($employees as $employee) {
            $currentSalaries[$employee->id] = $this->salaries->currentProfile($employee);
        }

        return view('accounting.directeur.salaries.index', [
            'employees' => $employees,
            'currentSalaries' => $currentSalaries,
        ]);
    }

    public function edit(User $employee)
    {
        abort_unless(in_array($employee->role, self::EMPLOYEE_ROLES, true), 404, 'Employé introuvable.');

        return view('accounting.directeur.salaries.edit', [
            'employee' => $employee,
            'currentProfile' => $this->salaries->currentProfile($employee),
            'history' => $this->salaries->history($employee),
        ]);
    }

    public function update(Request $request, User $employee)
    {
        abort_unless(in_array($employee->role, self::EMPLOYEE_ROLES, true), 404, 'Employé introuvable.');

        $validated = $request->validate([
            'monthly_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $this->salaries->setSalary($employee, (float) $validated['monthly_amount'], $request->user());

        return redirect()
            ->route('directeur.salaries.edit', $employee)
            ->with('success', "Salaire mis à jour pour {$employee->name}.");
    }
}
