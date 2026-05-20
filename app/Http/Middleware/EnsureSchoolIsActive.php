<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        if (! $user->school_id) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Votre compte n\'est rattaché à aucun établissement.');
        }

        $school = $user->school()->withoutGlobalScopes()->first();

        if (! $school || ! $school->is_active) {
            Auth::logout();

            return redirect()->route('login')->with(
                'error',
                'L\'accès à cet établissement est suspendu. Contactez l\'administrateur de la plateforme.'
            );
        }

        return $next($request);
    }
}
