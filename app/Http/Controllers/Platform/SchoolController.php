<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(): View
    {
        $schools = School::withCount([
            'users',
            'users as students_count' => fn ($q) => $q->whereIn('role', User::ROLE_STUDENT_ALIASES),
            'users as admins_count' => fn ($q) => $q->where('role', User::ROLE_ADMIN),
        ])->latest()->paginate(15);

        return view('platform.schools.index', compact('schools'));
    }

    public function create(): View
    {
        return view('platform.schools.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $school = School::create([
            'name' => $validated['name'],
            'slug' => School::slugFromName($validated['name']),
            'code' => School::generateUniqueCode(),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->createSchoolAdmin($school, $validated);

        return redirect()
            ->route('platform.schools.show', $school)
            ->with('success', "École « {$school->name} » créée. Code d'inscription : {$school->code}");
    }

    public function show(School $school): View
    {
        $school->loadCount([
            'users',
            'users as students_count' => fn ($q) => $q->whereIn('role', User::ROLE_STUDENT_ALIASES),
            'users as teachers_count' => fn ($q) => $q->whereIn('role', User::ROLE_TEACHER_ALIASES),
        ]);

        $admins = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('role', User::ROLE_ADMIN)
            ->get();

        return view('platform.schools.show', compact('school', 'admins'));
    }

    public function edit(School $school): View
    {
        return view('platform.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $school->update([
            'name' => $validated['name'],
            'slug' => School::slugFromName($validated['name'], $school->id),
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

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
            'admin_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $this->createSchoolAdmin($school, $validated);

        return back()->with('success', 'Administrateur d\'établissement créé.');
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

    private function createSchoolAdmin(School $school, array $data): User
    {
        $identifier = $this->nextAdminIdentifier($school->id);

        return User::withoutGlobalScopes()->create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'identifier' => $identifier,
            'user_id' => $identifier,
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'school_id' => $school->id,
            'email_verified_at' => now(),
        ]);
    }

    private function nextAdminIdentifier(int $schoolId): string
    {
        $prefix = 'ADM'.$schoolId;
        $last = User::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('role', User::ROLE_ADMIN)
            ->where('identifier', 'like', $prefix.'%')
            ->orderByDesc('identifier')
            ->value('identifier');

        if ($last) {
            $num = (int) substr($last, strlen($prefix)) + 1;
        } else {
            $num = 1;
        }

        return $prefix.str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }
}
