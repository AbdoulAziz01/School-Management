<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountingStaffCredentialService;
use App\Support\SchoolUserIdentifier;
use App\Support\TenantSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Gestion (par l'admin de l'établissement) des comptes du module
 * Comptabilité : directeur, comptable, caissier. Même pattern que
 * TeacherController (identifiant SchoolUserIdentifier, mot de passe généré
 * et révélé une seule fois), simplifié : pas d'affectation classes/matières.
 */
class AccountingStaffController extends Controller
{
    /** @var array<string, string> */
    private const ROLE_LABELS = [
        User::ROLE_DIRECTEUR => 'Directeur',
        User::ROLE_COMPTABLE => 'Comptable',
        User::ROLE_CAISSIER => 'Caissier',
    ];

    /** @var array<string, string> */
    private const IDENTIFIER_PREFIXES = [
        User::ROLE_DIRECTEUR => 'DIR',
        User::ROLE_COMPTABLE => 'CPT',
        User::ROLE_CAISSIER => 'CAI',
    ];

    public function __construct(
        private AccountingStaffCredentialService $credentials
    ) {}

    public function index(Request $request)
    {
        $query = User::whereIn('role', User::ROLE_ACCOUNTING_STAFF);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('identifier', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('role') && array_key_exists($request->role, self::ROLE_LABELS)) {
            $query->where('role', $request->role);
        }

        $staff = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.accounting-staff.index', [
            'staff' => $staff,
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }

    public function create()
    {
        return view('admin.accounting-staff.create', ['roleLabels' => self::ROLE_LABELS]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(array_keys(self::ROLE_LABELS))],
        ]);

        try {
            DB::beginTransaction();

            $schoolId = auth()->user()->school_id ?? TenantSchool::id();

            if (! $schoolId) {
                throw new \RuntimeException('Établissement introuvable pour la génération de l\'identifiant.');
            }

            $identifier = SchoolUserIdentifier::next($schoolId, self::IDENTIFIER_PREFIXES[$validated['role']]);

            $staff = (new User)->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'identifier' => $identifier,
                'password' => Hash::make(Str::password(32)),
                'role' => $validated['role'],
                'status' => User::STATUS_APPROVED,
                'school_id' => $schoolId,
                'phone' => $validated['phone'] ?? null,
                'email_verified_at' => now(),
            ]);
            $staff->save();

            DB::commit();

            $this->credentials->assignPassword($staff);

            return redirect()
                ->route('admin.accounting-staff.show', $staff)
                ->with('success', ucfirst(self::ROLE_LABELS[$validated['role']]).' créé. Identifiants affichés ci-dessous — communiquez-les manuellement.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création compte comptabilité', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création du compte.');
        }
    }

    public function show(User $accountingStaff)
    {
        abort_unless($accountingStaff->isAccountingStaff(), 404, 'Compte introuvable.');

        $pendingCredentials = $this->credentials->resolvePending($accountingStaff);
        $canViewPassword = auth()->user()?->canViewUserPasswords() ?? false;

        return view('admin.accounting-staff.show', [
            'staffMember' => $accountingStaff,
            'roleLabels' => self::ROLE_LABELS,
            'pendingCredentials' => $pendingCredentials,
            'canViewPassword' => $canViewPassword,
        ]);
    }

    public function edit(User $accountingStaff)
    {
        abort_unless($accountingStaff->isAccountingStaff(), 404, 'Compte introuvable.');

        $pendingCredentials = $this->credentials->resolvePending($accountingStaff);
        $canViewPassword = auth()->user()?->canViewUserPasswords() ?? false;

        return view('admin.accounting-staff.edit', [
            'staffMember' => $accountingStaff,
            'roleLabels' => self::ROLE_LABELS,
            'pendingCredentials' => $pendingCredentials,
            'canViewPassword' => $canViewPassword,
        ]);
    }

    public function update(Request $request, User $accountingStaff)
    {
        abort_unless($accountingStaff->isAccountingStaff(), 404, 'Compte introuvable.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($accountingStaff->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $accountingStaff->update($validated);

        return redirect()
            ->route('admin.accounting-staff.edit', $accountingStaff)
            ->with('success', 'Compte mis à jour avec succès.');
    }

    public function regenerateCredentials(User $accountingStaff)
    {
        abort_unless($accountingStaff->isAccountingStaff(), 404, 'Compte introuvable.');
        $this->credentials->assertCanManage();

        $this->credentials->assignPassword($accountingStaff);

        return back()->with('success', 'Nouveau mot de passe généré. Il reste visible sur cette fiche.');
    }

    public function destroy(User $accountingStaff)
    {
        abort_unless($accountingStaff->isAccountingStaff(), 404, 'Compte introuvable.');

        try {
            $accountingStaff->delete();

            return redirect()
                ->route('admin.accounting-staff.index')
                ->with('success', 'Le compte a été supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur suppression compte comptabilité', [
                'staff_id' => $accountingStaff->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }
}
