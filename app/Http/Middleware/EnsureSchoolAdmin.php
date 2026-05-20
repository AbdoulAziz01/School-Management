<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (! $user->isAdmin() || ! $user->school_id) {
            if ($user->isSuperAdmin()) {
                return redirect()->route('platform.dashboard');
            }

            return redirect()->route('login')->with('error', 'Accès réservé à l\'administrateur de l\'établissement.');
        }

        return $next($request);
    }
}
