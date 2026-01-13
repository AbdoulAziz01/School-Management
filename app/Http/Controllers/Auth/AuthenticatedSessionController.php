<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            // Journaliser l'erreur complète pour le débogage
            \Log::error('Erreur lors de la connexion : ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            // Rediriger avec un message d'erreur plus détaillé en environnement de développement
            $errorMessage = config('app.env') === 'local' 
                ? 'Erreur lors de la connexion : ' . $e->getMessage()
                : 'Une erreur est survenue lors de la connexion. Veuillez réessayer.';
                
            return back()->with('error', $errorMessage);
        }
    }
    
    /**
     * Redirect to the appropriate dashboard based on user role
     */
    protected function redirectToDashboard($user): RedirectResponse
    {
        $route = match($user->role) {
            'admin'     => 'admin.dashboard',
            'professeur' => 'teacher.dashboard',
            'eleve'     => 'student.dashboard',
            default     => 'home',
        };
        
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
