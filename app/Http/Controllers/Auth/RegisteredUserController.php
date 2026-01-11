<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:student,teacher'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Générer l'identifiant selon le rôle
        if ($request->role === 'student') {
            $prefix = 'E';
        } elseif ($request->role === 'teacher') {
            $prefix = 'P';
        } else {
            $prefix = 'A';
        }

        // Générer le numéro séquentiel
        $year = date('Y');
        $lastUser = User::where('identifier', 'like', $prefix . $year . '%')
                        ->orderBy('id', 'desc')
                        ->first();

        if ($lastUser) {
            $lastNumber = (int) substr($lastUser->identifier, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $identifier = $prefix . $year . $newNumber;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'identifier' => $identifier,
            'role' => $request->role,
            'status' => 'pending', // Mettre en attente de validation
            'email_verified_at' => now(), 
            'password' => Hash::make($request->password),
        ]);

        // NE PAS connecter l'utilisateur automatiquement
        // Rediriger vers la page de connexion avec un message
        return redirect()->route('login')->with('status', 'Votre compte a été créé. Il sera activé après validation par un administrateur.');
    }
}
