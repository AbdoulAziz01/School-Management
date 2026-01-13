<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        // Passer les matières à la vue pour le formulaire
        $subjects = Subject::orderBy('name')->get();
        return view('auth.register', compact('subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:eleve,teacher'],
            'desired_class' => ['required_if:role,eleve', 'string', 'nullable'],
            'subjects' => ['required_if:role,teacher', 'array'],
            'subjects.*' => ['exists:subjects,id'],
        ], [
            'desired_class.required_if' => 'La classe est obligatoire pour les élèves.',
            'subjects.required_if' => 'Veuillez sélectionner au moins une matière enseignée.',
        ]);

        // Génération de l'identifiant selon le rôle
        $role = $request->role;
        $prefix = match($role) {
            'teacher' => 'P',
            'eleve' => 'E',
            default => 'U',
        };
        
        $year = date('Y');
        
        // Trouver le dernier utilisateur avec ce préfixe et cette année
        $lastUser = User::where('identifier', 'like', $prefix . $year . '%')
                        ->orderBy('identifier', 'desc')
                        ->first();
        
        if ($lastUser) {
            $lastNumber = intval(substr($lastUser->identifier, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        $identifier = $prefix . $year . $newNumber;

        // Créer l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'identifier' => $identifier,
            'role' => $request->role,
            'status' => 'pending',
            'desired_class' => $request->desired_class,
        ]);

        // Marquer l'email comme vérifié immédiatement
        $user->email_verified_at = now();
        $user->save();

        // Enregistrer les matières pour les professeurs
        if ($request->role === 'teacher' && $request->has('subjects')) {
            $user->subjects()->attach($request->subjects);
        }

        event(new Registered($user));

        // NE PAS connecter automatiquement, rediriger vers login
        // Auth::login($user);

        return redirect()->route('login')->with('status', 'Votre compte a été créé. Il sera activé après validation par un administrateur.');
    }
}
