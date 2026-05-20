<?php

namespace App\Providers;

use App\Http\View\Composers\PlatformBrandingComposer;
use App\Http\View\Composers\SchoolBrandingComposer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
            'admin.layouts.app',
            'admin.components.sidebar',
            'layouts.student',
            'teacher.components.sidebar',
            'student.*',
        ], SchoolBrandingComposer::class);

        View::share('platformName', config('platform.name', 'EduManager'));

        View::composer('platform.*', PlatformBrandingComposer::class);
    }
}
