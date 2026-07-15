<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Profil (édition + mot de passe) partagé par les 3 portails du module
 * Comptabilité (directeur/comptable/caissier) — même besoin fonctionnel que
 * TeacherProfileController, mais un seul contrôleur plutôt que 3 quasi
 * identiques puisqu'aucun de ces rôles n'a de champ spécifique (contrairement
 * à enseignant/élève).
 */
class AccountingProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('accounting.profile.edit', [
            'accountingUser' => $user,
            'routePrefix' => $this->routePrefix($user),
            ...$this->layoutFor($user),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route($this->routePrefix($user).'.profile.edit')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route($this->routePrefix($user).'.profile.edit')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }

    private function routePrefix(User $user): string
    {
        return match ($user->role) {
            User::ROLE_DIRECTEUR => 'directeur',
            User::ROLE_COMPTABLE => 'comptable',
            User::ROLE_CAISSIER => 'caisse',
            default => abort(404),
        };
    }

    /** @return array{layout: string, layoutParams: array<string, mixed>} */
    private function layoutFor(User $user): array
    {
        $prefix = $this->routePrefix($user);

        return [
            'layout' => 'admin.layouts.app',
            'layoutParams' => [
                'sidebarView' => "accounting.{$prefix}.sidebar",
                'navbarView' => "accounting.{$prefix}.navbar",
            ],
        ];
    }
}
