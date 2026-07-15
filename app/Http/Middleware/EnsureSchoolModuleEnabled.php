<?php

namespace App\Http\Middleware;

use App\Support\SchoolModules;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garde de route pour les futurs modules activables par établissement
 * (ex. Comptabilité). Usage : ->middleware('module:accounting').
 *
 * Non encore branché sur aucune route — posé en fondation pour le futur
 * module Comptabilité (voir App\Support\SchoolModules).
 */
class EnsureSchoolModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = Auth::user();

        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        $school = $user?->school()->withoutGlobalScopes()->first();

        if (! SchoolModules::isEnabled($school, $module)) {
            abort(404);
        }

        return $next($request);
    }
}
