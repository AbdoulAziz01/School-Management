<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur a le bon rôle
        if (auth()->user()->role !== $role) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}
