<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Rediriger l'utilisateur déjà connecté vers son tableau de bord
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }
        
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
            $request->session()->regenerate();
            
            $user = $request->user();
            
            // Vérifier le statut de l'utilisateur
            if ($user->status === 'pending') {
                Auth::logout();
                return back()->with('error', 'Votre compte est en attente de validation par un administrateur.');
            }
            
            if ($user->status === 'rejected') {
                Auth::logout();
                return back()->with('error', 'Votre compte a été rejeté. Veuillez contacter l\'administrateur.');
            }
            
            // Rediriger vers le tableau de bord approprié
            return $this->redirectToDashboard($user);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la connexion : ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = config('app.env') === 'local'
                ? 'Erreur lors de la connexion : ' . $e->getMessage()
                : 'Une erreur est survenue lors de la connexion. Veuillez réessayer.';

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Redirect to the appropriate dashboard based on user role.
     * Tolère les alias historiques (professeur/teacher, eleve/student).
     */
    protected function redirectToDashboard($user): RedirectResponse
    {
        $role = $user->role;

        if ($role === User::ROLE_ADMIN) {
            $route = 'admin.dashboard';
        } elseif (in_array($role, User::ROLE_TEACHER_ALIASES, true)) {
            $route = 'teacher.dashboard';
        } elseif (in_array($role, User::ROLE_STUDENT_ALIASES, true)) {
            $route = 'student.dashboard';
        } else {
            $route = 'home';
        }

        return redirect()->intended(route($route, [], false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
