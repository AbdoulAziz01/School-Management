<?php

namespace App\Providers;

use App\Http\View\Composers\PortalNavbarComposer;
use App\Http\View\Composers\PlatformBrandingComposer;
use App\Http\View\Composers\SchoolBrandingComposer;
use App\Http\View\Composers\StudentSidebarComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Model::shouldBeStrict(! $this->app->isProduction());

        DB::prohibitDestructiveCommands($this->app->isProduction());

        // Alimentation automatique du grand livre (ledger_entries) — voir
        // docblock de la migration : jamais un contrôleur ne doit créer de
        // LedgerEntry directement.
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\Expense::observe(\App\Observers\ExpenseObserver::class);
        \App\Models\SalaryPayment::observe(\App\Observers\SalaryPaymentObserver::class);

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->mixedCase()->numbers()->symbols()->uncompromised()
            : Password::min(8));

        // super_admin passe outre toute vérification de permission Spatie
        // (Gate::allows()/$user->can()) — cohérent avec TenantSchool::applyForUser()
        // qui désactive déjà le scope multi-établissement pour ce rôle.
        Gate::before(fn ($user, string $ability) => $user->isSuperAdmin() ? true : null);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->string('email')->lower().'|'.$request->ip())
                ->response(fn () => response()->json(
                    ['message' => 'Trop de tentatives. Réessayez dans quelques minutes.'],
                    429
                ));
        });

        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(100)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $identifier = $notifiable->identifier ?? '—';
            $appName = config('app.name', 'EduManager');

            return (new MailMessage)
                ->subject("{$appName} — Choisissez votre mot de passe")
                ->greeting('Bonjour '.($notifiable->name ?? '').',')
                ->line('Un compte enseignant a été créé pour vous sur '.$appName.'.')
                ->line('**Identifiant de connexion :** '.$identifier)
                ->line('Vous pourrez vous connecter avec cet identifiant ou avec votre adresse email, une fois le mot de passe défini.')
                ->action('Choisir mon mot de passe', $url)
                ->line('Ce lien expire dans '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60).' minutes.')
                ->line('Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet email.');
        });

        Paginator::useBootstrapFive();

        View::composer([
            'layouts.student',
            'student.*',
        ], StudentSidebarComposer::class);

        View::composer('partials.portal-top-navbar', PortalNavbarComposer::class);

        View::composer([
            'admin.layouts.app',
            'admin.components.sidebar',
            'layouts.student',
            'teacher.components.sidebar',
            'student.*',
            'accounting.directeur.sidebar',
            'accounting.comptable.sidebar',
            'accounting.caisse.sidebar',
        ], SchoolBrandingComposer::class);

        View::share('platformName', config('platform.name', 'EduManager'));

        View::composer('platform.*', PlatformBrandingComposer::class);
    }
}
