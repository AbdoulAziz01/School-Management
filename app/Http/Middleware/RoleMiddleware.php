<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur a le rôle requis
        if ($user->role !== $role) {
            // Rediriger vers le tableau de bord approprié selon le rôle
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'eleve':
                    return redirect()->route('student.dashboard');
                case 'professeur':
                    return redirect()->route('teacher.dashboard');
                default:
                    Auth::logout();
                    return redirect()->route('login')->with('error', 'Rôle non reconnu.');
            }
        }

        return $next($request);
    }
}
