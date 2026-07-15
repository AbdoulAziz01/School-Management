<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Génération et affichage ponctuel des identifiants de connexion pour les
 * comptes du module Comptabilité (directeur/comptable/caissier) — même
 * pattern que TeacherCredentialService (mot de passe jamais stocké/affiché
 * de façon réversible, révélation unique via flash de session), mais réduit
 * à l'essentiel : ces comptes n'ont pas d'envoi d'email automatique séparé,
 * l'admin communique les identifiants affichés sur la fiche.
 */
class AccountingStaffCredentialService
{
    public function assertCanManage(): void
    {
        abort_unless(auth()->user()?->canViewUserPasswords(), 403, 'Accès réservé à l\'administrateur.');
    }

    public function flash(User $staff, string $plainPassword): void
    {
        session()->flash("credentials_reveal.accounting_staff.{$staff->id}", [
            'name' => $staff->name,
            'identifier' => $staff->identifier,
            'email' => $staff->email,
            'password' => $plainPassword,
        ]);
    }

    /** @return array{name: string, identifier: string|null, email: string, password: string}|null */
    public function resolvePending(User $staff): ?array
    {
        if (! auth()->user()?->canViewUserPasswords()) {
            return null;
        }

        return session("credentials_reveal.accounting_staff.{$staff->id}");
    }

    public function assignPassword(User $staff): string
    {
        $plainPassword = Str::password(10, symbols: false);

        $staff->forceFill([
            'password' => Hash::make($plainPassword),
        ])->save();

        $this->flash($staff, $plainPassword);

        return $plainPassword;
    }
}
