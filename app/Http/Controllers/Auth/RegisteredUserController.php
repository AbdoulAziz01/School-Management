<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Support\SchoolUserIdentifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'school_code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                Rule::requiredIf(fn () => $request->role === 'teacher'),
                'nullable',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:eleve,teacher'],
            'desired_class' => ['required_if:role,eleve', 'string', 'nullable'],
            'subjects' => ['required_if:role,teacher', 'array'],
            'subjects.*' => ['exists:subjects,id'],
        ], [
            'school_code.required' => 'Le code établissement est obligatoire.',
            'desired_class.required_if' => 'La classe est obligatoire pour les élèves.',
            'subjects.required_if' => 'Veuillez sélectionner au moins une matière enseignée.',
        ]);

        $school = School::where('code', strtoupper(trim($request->school_code)))
            ->where('is_active', true)
            ->first();

        if (! $school) {
            return back()
                ->withInput()
                ->withErrors(['school_code' => 'Code établissement invalide ou école inactive.']);
        }

        if ($request->role === 'teacher' && $request->has('subjects')) {
            $validCount = Subject::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->whereIn('id', $request->subjects)
                ->count();

            if ($validCount !== count($request->subjects)) {
                return back()
                    ->withInput()
                    ->withErrors(['subjects' => 'Une ou plusieurs matières n\'appartiennent pas à cet établissement.']);
            }
        }

        $role = $request->role;
        $prefix = match ($role) {
            'teacher' => 'P',
            'eleve' => 'E',
            default => 'U',
        };

        $identifier = SchoolUserIdentifier::next($school->id, $prefix);

        $user = User::withoutGlobalScopes()->create([
            'name' => $request->name,
            'email' => $request->filled('email') ? $request->email : null,
            'password' => Hash::make($request->password),
            'identifier' => $identifier,
            'user_id' => $identifier,
            'role' => $request->role,
            'status' => 'pending',
            'desired_class' => $request->desired_class,
            'school_id' => $school->id,
            'email_verified_at' => now(),
        ]);

        if ($request->role === 'teacher' && $request->has('subjects')) {
            $user->subjects()->attach($request->subjects);
        }

        event(new Registered($user));

        return redirect()->route('login')->with(
            'status',
            "Compte créé pour « {$school->name} ». Il sera activé après validation par l'administration de l'établissement."
        );
    }
}
