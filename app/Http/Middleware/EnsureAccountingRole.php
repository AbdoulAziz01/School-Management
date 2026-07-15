<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garde d'accès aux 3 portails du module Comptabilité (directeur/comptable/
 * caissier), sur le même modèle que TeacherMiddleware/StudentMiddleware —
 * paramétrée par rôle plutôt que dupliquée en 3 classes quasi identiques,
 * dans l'esprit du middleware `module:accounting` déjà en place
 * (EnsureSchoolModuleEnabled).
 *
 * Usage : ->middleware('accounting.role:directeur')
 */
class EnsureAccountingRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! in_array($role, User::ROLE_ACCOUNTING_STAFF, true)) {
            abort(500, "Rôle comptable inconnu : {$role}.");
        }

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== $role) {
            return redirect()->route('login')->with('error', 'Accès non autorisé à cet espace.');
        }

        $school = $user->school()->withoutGlobalScopes()->first();
        if ($school && ! $school->is_active) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'L\'accès à votre établissement est suspendu.');
        }

        return $next($request);
    }
}
