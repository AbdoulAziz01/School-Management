<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! in_array($user->role, ['eleve', 'student'], true)) {
            return redirect()->route('login')->with('error', 'Accès non autorisé.');
        }

        $school = $user->school()->withoutGlobalScopes()->first();
        if ($school && ! $school->is_active) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'L\'accès à votre établissement est suspendu.');
        }

        return $next($request);
    }
}
