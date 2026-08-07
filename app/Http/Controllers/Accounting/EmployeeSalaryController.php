<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Services\EmployeeSalaryProfileService;
use Carbon\Carbon;
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
        User::ROLE_COMPTABLE,
        User::ROLE_CAISSIER,
        User::ROLE_DIRECTEUR,
    ];

    /** @var array<string, list<string>> filtre du menu "Gestion des salaires" par catégorie de personnel */
    private const ROLE_GROUPS = [
        'teachers' => ['teacher', 'professeur'],
        'surveillants' => ['surveillant'],
        'admin' => ['admin'],
        'accounting' => [User::ROLE_COMPTABLE, User::ROLE_CAISSIER],
        'direction' => [User::ROLE_DIRECTEUR],
    ];

    public function __construct(
        private EmployeeSalaryProfileService $salaries
    ) {}

    public function index(Request $request)
    {
        $roleGroup = $request->query('role_group');
        $roles = ($roleGroup && isset(self::ROLE_GROUPS[$roleGroup])) ? self::ROLE_GROUPS[$roleGroup] : self::EMPLOYEE_ROLES;

        $query = User::whereIn('role', $roles);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('identifier', 'ilike', "%{$search}%");
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
            'roleGroup' => $roleGroup,
        ]);
    }

    /**
     * Suivi (lecture seule) des paiements de salaire du mois pour tout le
     * personnel — profs, surveillants, comptables, caissiers, admin. Le
     * paiement effectif reste réservé au comptable/caissier
     * (SalaryPaymentController) : séparation des tâches volontaire, le
     * directeur pilote mais n'exécute pas d'opération quotidienne.
     */
    public function checklist(Request $request)
    {
        $period = $this->resolveChecklistPeriod($request);
        $roleGroup = $request->query('role_group');

        $roleGroupFilters = [
            'teachers' => User::ROLE_TEACHER_ALIASES,
            'surveillants' => [User::ROLE_SURVEILLANT],
            'admin' => [User::ROLE_ADMIN],
            'accounting' => [User::ROLE_COMPTABLE, User::ROLE_CAISSIER],
        ];

        $query = SalaryPayment::where('period', $period)->with(['user', 'paidBy']);

        if ($roleGroup && isset($roleGroupFilters[$roleGroup])) {
            $query->whereHas('user', fn ($q) => $q->whereIn('role', $roleGroupFilters[$roleGroup]));
        }

        $payments = $query->get()->sortBy(fn (SalaryPayment $p) => $p->user->name);

        return view('accounting.directeur.salaries.checklist', [
            'payments' => $payments,
            'period' => $period,
            'roleGroup' => $roleGroup,
        ]);
    }

    private function resolveChecklistPeriod(Request $request): Carbon
    {
        if ($request->filled('period')) {
            try {
                return Carbon::createFromFormat('Y-m', $request->input('period'))->startOfMonth();
            } catch (\Exception) {
                // format invalide, on retombe sur le mois courant
            }
        }

        return now()->startOfMonth();
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
        abort_unless($request->user()->can('parametrage.salaires'), 403);

        $validated = $request->validate([
            'monthly_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $this->salaries->setSalary($employee, (float) $validated['monthly_amount'], $request->user());

        return redirect()
            ->route('directeur.salaries.edit', $employee)
            ->with('success', "Salaire mis à jour pour {$employee->name}.");
    }
}
