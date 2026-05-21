<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\PlatformMetrics;
use App\Support\SchoolLogoStorage;
use App\Support\SchoolProfile;
use App\Support\SchoolSubjectProvisioner;
use App\Support\StaffOtpMailer;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $query = PlatformMetrics::schoolWithCountsQuery()->latest();

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'no_admin') {
                $query->whereDoesntHave('users', fn ($q) => $q->where('role', User::ROLE_ADMIN));
            } elseif ($request->status === 'pending') {
                $query->whereHas('users', fn ($q) => $q->where('status', User::STATUS_PENDING));
            }
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $schools = $query->paginate(15)->withQueryString();
        $currentYears = PlatformMetrics::currentAcademicYearsBySchool();

        return view('platform.schools.index', compact('schools', 'currentYears'));
    }

    public function create(): View
    {
        $school = new School([
            'timezone'  => 'Africa/Dakar',
            'locale'    => 'fr',
            'is_active' => true,
        ]);

        return view('platform.schools.create', compact('school'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            array_merge(
                SchoolProfile::fullRules(),
                [
                    'is_active'     => ['sometimes', 'boolean'],
                    'admin_name'    => ['required', 'string', 'max:255'],
                    'admin_email'   => ['required', 'email', 'max:255', 'unique:users,email'],
                ]
            ),
            [
                'admin_email.unique' => 'Cette adresse email est déjà utilisée par un autre compte (admin, enseignant, élève…). Choisissez une adresse différente pour l\'administrateur de l\'établissement.',
            ],
            [
                'admin_email' => 'email admin',
            ]
        );

        unset($validated['default_academic_year_id']);

        $school = School::create(array_merge(
            SchoolProfile::fullAttributes($validated),
            [
                'slug'      => School::slugFromName($validated['name']),
                'code'      => School::generateUniqueCode(),
                'is_active' => $request->boolean('is_active', true),
                'timezone'  => $validated['timezone'] ?? 'Africa/Dakar',
                'locale'    => $validated['locale'] ?? 'fr',
            ]
        ));

        if ($request->hasFile('logo')) {
            SchoolLogoStorage::store($school, $request->file('logo'));
        }

        $admin = $this->createSchoolStaff($school, $validated, User::ROLE_ADMIN);

        SchoolSubjectProvisioner::ensureForSchool($school->id);

        $otpResult = StaffOtpMailer::send($admin, StaffOtpMailer::accountLabelFor($admin));

        $redirect = redirect()
            ->route('platform.schools.show', $school)
            ->with('success', "École « {$school->name} » créée. Code d'inscription : {$school->code}")
            ->with('new_admin_login', [
                'email' => $admin->email,
                'identifier' => $admin->identifier,
                'otp_sent' => $otpResult === true,
            ]);

        if (is_string($otpResult)) {
            $redirect->with('error', "Compte admin créé, mais l'email OTP n'a pas pu être envoyé : {$otpResult}");
        }

        return $redirect;
    }

    public function show(School $school): View
    {
        PlatformMetrics::loadSchoolDetailCounts($school);

        $subjectsCount = PlatformMetrics::subjectsCountForSchool($school->id);
        $currentAcademicYear = PlatformMetrics::currentAcademicYearForSchool($school->id);

        $healthAlerts = [];
        if ($school->admins_count < 1) {
            $healthAlerts[] = ['type' => 'danger', 'message' => 'Aucun administrateur assigné à cet établissement.'];
        }
        if (! $currentAcademicYear) {
            $healthAlerts[] = ['type' => 'warning', 'message' => 'Aucune année scolaire courante configurée.'];
        }
        if ($school->pending_count > 0) {
            $healthAlerts[] = ['type' => 'info', 'message' => "{$school->pending_count} inscription(s) en attente de validation."];
        }
        if ($school->unassigned_students_count > 0) {
            $healthAlerts[] = ['type' => 'warning', 'message' => "{$school->unassigned_students_count} élève(s) sans classe assignée."];
        }
        if (! $school->is_active) {
            $healthAlerts[] = ['type' => 'secondary', 'message' => 'Établissement désactivé — les utilisateurs ne peuvent plus s\'y connecter.'];
        }

        $staffMembers = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->whereIn('role', User::ROLE_SCHOOL_STAFF)
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('platform.schools.show', compact(
            'school',
            'staffMembers',
            'subjectsCount',
            'currentAcademicYear',
            'healthAlerts',
        ));
    }

    public function edit(School $school): View
    {
        $academicYears = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->orderByDesc('start_date')
            ->get(['id', 'name', 'is_current']);

        return view('platform.schools.edit', compact('school', 'academicYears'));
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate(array_merge(
            SchoolProfile::fullRules($school->id),
            ['is_active' => ['sometimes', 'boolean']]
        ));

        $school->update(array_merge(
            SchoolProfile::fullAttributes($validated),
            [
                'slug'      => School::slugFromName($validated['name'], $school->id),
                'is_active' => $request->boolean('is_active'),
            ]
        ));

        if ($request->boolean('remove_logo')) {
            SchoolLogoStorage::clear($school);
        } elseif ($request->hasFile('logo')) {
            SchoolLogoStorage::store($school, $request->file('logo'));
        }

        return redirect()
            ->route('platform.schools.show', $school)
            ->with('success', 'Établissement mis à jour.');
    }

    public function toggleActive(School $school): RedirectResponse
    {
        $school->update(['is_active' => ! $school->is_active]);

        $message = $school->is_active
            ? "L'établissement « {$school->name} » est activé."
            : "L'établissement « {$school->name} » est désactivé.";

        return back()->with('success', $message);
    }

    public function regenerateCode(School $school): RedirectResponse
    {
        $school->update(['code' => School::generateUniqueCode()]);

        return back()->with('success', "Nouveau code d'inscription : {$school->code}");
    }

    public function storeAdmin(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'staff_role' => ['required', 'in:'.User::ROLE_ADMIN.','.User::ROLE_SURVEILLANT],
        ], [
            'admin_email.unique' => 'Cette adresse email est déjà utilisée par un autre compte. Choisissez une adresse différente.',
        ], [
            'admin_email' => 'email',
        ]);

        $staff = $this->createSchoolStaff($school, $validated, $validated['staff_role']);

        $label = $staff->role === User::ROLE_SURVEILLANT ? 'Surveillant' : 'Administrateur';
        $otpResult = StaffOtpMailer::send($staff, StaffOtpMailer::accountLabelFor($staff));

        $redirect = back()->with('success', "{$label} créé — identifiant : {$staff->identifier}. Un code OTP a été envoyé à {$staff->email}.");

        if (is_string($otpResult)) {
            $redirect->with('error', "Compte créé, mais l'email OTP n'a pas pu être envoyé : {$otpResult}");
        }

        return $redirect;
    }

    public function resetAdminPassword(Request $request, School $school, User $user): RedirectResponse
    {
        if ($user->school_id !== $school->id || ! $user->isSchoolStaff()) {
            abort(404);
        }

        $otpResult = StaffOtpMailer::send($user, StaffOtpMailer::accountLabelFor($user));

        if ($otpResult === true) {
            return back()->with('success', "Un nouveau code OTP a été envoyé à {$user->email} ({$user->identifier}).");
        }

        return back()->with('error', $otpResult);
    }

    public function destroy(School $school): RedirectResponse
    {
        if ($school->users()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un établissement qui contient encore des utilisateurs.');
        }

        $school->delete();

        return redirect()
            ->route('platform.schools.index')
            ->with('success', 'Établissement supprimé.');
    }

    private function createSchoolStaff(School $school, array $data, string $role): User
    {
        $identifier = $this->nextStaffIdentifier($school->id, $role);

        return User::withoutGlobalScopes()->create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make(Str::random(32)),
            'identifier' => $identifier,
            'user_id' => $identifier,
            'role' => $role,
            'status' => User::STATUS_APPROVED,
            'school_id' => $school->id,
            'email_verified_at' => now(),
        ]);
    }

    private function nextStaffIdentifier(int $schoolId, string $role): string
    {
        $prefix = ($role === User::ROLE_SURVEILLANT ? 'SUR' : 'ADM').$schoolId;

        $last = User::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('role', $role)
            ->where('identifier', 'like', $prefix.'%')
            ->orderByDesc('identifier')
            ->value('identifier');

        $num = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        return $prefix.str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }
}
