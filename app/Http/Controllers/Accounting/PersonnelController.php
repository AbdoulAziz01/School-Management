<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SchoolUserIdentifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Vue d'ensemble du personnel et des élèves pour le directeur — comptage +
 * listes par rôle, pour la transparence sur qui travaille dans
 * l'établissement. Le directeur peut en plus créer ses subordonnés directs
 * (comptable/caissier) ; la gestion des autres rôles (admin, surveillant,
 * enseignant, élève) reste dans le panneau Admin (Admin\TeacherController,
 * Admin\StudentController, Admin\AccountingStaffController).
 */
class PersonnelController extends Controller
{
    /** Rôles que le directeur est autorisé à créer lui-même. */
    private const CREATABLE_ROLES = [User::ROLE_COMPTABLE, User::ROLE_CAISSIER];

    /** @var array<string, array{label: string, roles: list<string>, icon: string}> */
    private const GROUPS = [
        'teachers' => ['label' => 'Enseignants', 'roles' => ['teacher', 'professeur'], 'icon' => 'fa-chalkboard-teacher'],
        'surveillants' => ['label' => 'Surveillants', 'roles' => ['surveillant'], 'icon' => 'fa-user-shield'],
        'admins' => ['label' => 'Administrateurs', 'roles' => ['admin'], 'icon' => 'fa-user-tie'],
        'comptables' => ['label' => 'Comptables', 'roles' => ['comptable'], 'icon' => 'fa-calculator'],
        'caissiers' => ['label' => 'Caissiers', 'roles' => ['caissier'], 'icon' => 'fa-cash-register'],
        'students' => ['label' => 'Élèves', 'roles' => ['eleve', 'student'], 'icon' => 'fa-user-graduate'],
    ];

    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        $counts = [];
        foreach (self::GROUPS as $key => $group) {
            $counts[$key] = [
                ...$group,
                'count' => User::where('school_id', $schoolId)
                    ->whereIn('role', $group['roles'])
                    ->count(),
            ];
        }

        return view('accounting.directeur.personnel.index', ['counts' => $counts]);
    }

    public function show(Request $request, string $group)
    {
        abort_unless(isset(self::GROUPS[$group]), 404);

        $meta = self::GROUPS[$group];

        $query = User::where('school_id', $request->user()->school_id)
            ->whereIn('role', $meta['roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('identifier', 'ilike', "%{$search}%");
            });
        }

        $people = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('accounting.directeur.personnel.show', [
            'group' => $group,
            'meta' => $meta,
            'people' => $people,
        ]);
    }

    public function create(): View
    {
        return view('accounting.directeur.personnel.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(self::CREATABLE_ROLES)],
        ]);

        $schoolId = $request->user()->school_id;
        $prefix = $validated['role'] === User::ROLE_CAISSIER ? 'CAI' : 'CPT';
        $identifier = SchoolUserIdentifier::next($schoolId, $prefix);
        $password = Str::password(10, symbols: false);

        $staff = (new User)->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'identifier' => $identifier,
            'password' => Hash::make($password),
            'role' => $validated['role'],
            'status' => User::STATUS_APPROVED,
            'school_id' => $schoolId,
            'phone' => $validated['phone'] ?? null,
            'email_verified_at' => now(),
        ]);
        $staff->save();

        $group = $validated['role'] === User::ROLE_CAISSIER ? 'caissiers' : 'comptables';
        $label = $validated['role'] === User::ROLE_CAISSIER ? 'Caissier' : 'Comptable';

        return redirect()->route('directeur.personnel.show', $group)
            ->with('success', "{$label} créé avec succès. Identifiants affichés ci-dessous — communiquez-les manuellement.")
            ->with('new_staff_credentials', [
                'name' => $staff->name,
                'identifier' => $staff->identifier,
                'email' => $staff->email,
                'password' => $password,
            ]);
    }

    /**
     * Régénère le mot de passe d'un comptable/caissier — réservé aux
     * subordonnés directs du directeur (même restriction que store()) et à
     * son propre établissement, pas de contrôle par rôle admin ici.
     */
    public function regenerateCredentials(Request $request, User $staff): RedirectResponse
    {
        abort_unless(
            in_array($staff->role, self::CREATABLE_ROLES, true) && $staff->school_id === $request->user()->school_id,
            404,
            'Compte introuvable.'
        );

        $password = Str::password(10, symbols: false);
        $staff->forceFill(['password' => Hash::make($password)])->save();

        $group = $staff->role === User::ROLE_CAISSIER ? 'caissiers' : 'comptables';

        return redirect()->route('directeur.personnel.show', $group)
            ->with('success', "Nouveau mot de passe généré pour {$staff->name}. Affiché ci-dessous — communiquez-le manuellement.")
            ->with('new_staff_credentials', [
                'name' => $staff->name,
                'identifier' => $staff->identifier,
                'email' => $staff->email,
                'password' => $password,
            ]);
    }
}
