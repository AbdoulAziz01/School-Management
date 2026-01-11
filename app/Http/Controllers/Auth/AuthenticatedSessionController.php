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
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        
        // Récupérer l'utilisateur authentifié
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
    }
    
    /**
     * Redirect to the appropriate dashboard based on user role
     */
    protected function redirectToDashboard($user): RedirectResponse
    {
        $route = match($user->role) {
            'admin' => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'student' => 'student.dashboard',
            default => 'dashboard',
        };
        
        return redirect()->intended(route($route, absolute: false));
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
