<?php

namespace App\Services;

use App\Mail\TeacherCredentialsMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Génération, affichage ponctuel et envoi des identifiants de connexion
 * enseignant — extrait de TeacherController, qui mélangeait cette logique
 * avec le reste du CRUD enseignant.
 */
class TeacherCredentialService
{
    public function emailEnabled(): bool
    {
        return (bool) config('mail.teacher_credentials_email_enabled', false);
    }

    public function assertCanManage(): void
    {
        abort_unless(auth()->user()?->canViewUserPasswords(), 403, 'Accès réservé à l\'administrateur.');
    }

    /**
     * Affiche les identifiants en clair une seule fois : uniquement juste après leur
     * génération (création, régénération, envoi), via un flash de session consommé
     * par la requête suivante. Le mot de passe n'est jamais stocké en clair ni de
     * façon réversible.
     */
    public function flash(User $teacher, string $plainPassword): void
    {
        session()->flash("credentials_reveal.teacher.{$teacher->id}", [
            'name' => $teacher->name,
            'identifier' => $teacher->identifier,
            'email' => $teacher->email,
            'password' => $plainPassword,
        ]);
    }

    /** @return array{name: string, identifier: string|null, email: string, password: string}|null */
    public function resolvePending(User $teacher): ?array
    {
        if (! auth()->user()?->canViewUserPasswords()) {
            return null;
        }

        return session("credentials_reveal.teacher.{$teacher->id}");
    }

    public function assignPassword(User $teacher): string
    {
        $plainPassword = Str::password(10, symbols: false);

        $teacher->forceFill([
            'password' => Hash::make($plainPassword),
        ])->save();

        $this->flash($teacher, $plainPassword);

        return $plainPassword;
    }

    /**
     * Envoie identifiant + mot de passe par email (si activé dans la config).
     *
     * @return bool|string true si envoyé, string = message d'erreur
     */
    public function send(User $teacher, ?string $plainPassword = null, bool $manual = false): bool|string
    {
        if (! $manual && ! $this->emailEnabled()) {
            return 'Envoi email désactivé (TEACHER_SEND_CREDENTIALS_EMAIL=false).';
        }

        if (empty($teacher->email)) {
            return 'Cet enseignant n\'a pas d\'adresse email.';
        }

        if (empty($teacher->identifier)) {
            return 'Cet enseignant n\'a pas d\'identifiant de connexion.';
        }

        if (config('mail.default') === 'log') {
            return 'MAIL_MAILER=log : aucun email réel n\'est envoyé. Mettez MAIL_MAILER=smtp dans .env puis php artisan config:clear.';
        }

        if (empty(config('mail.mailers.smtp.password')) && config('mail.default') === 'smtp') {
            return 'Configuration incomplète : renseignez MAIL_PASSWORD et MAIL_FROM_ADDRESS dans .env, puis php artisan config:clear.';
        }

        $plainPassword ??= Str::password(10, symbols: false);

        try {
            Mail::to($teacher->email)->send(new TeacherCredentialsMail($teacher, $plainPassword));

            $teacher->forceFill([
                'password' => Hash::make($plainPassword),
                'invitation_email_sent_at' => now(),
            ])->save();

            $this->flash($teacher, $plainPassword);

            return true;
        } catch (TransportExceptionInterface $e) {
            Log::error('Erreur SMTP envoi identifiants enseignant', [
                'teacher_id' => $teacher->id,
                'email' => $teacher->email,
                'message' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'Authentication failed')) {
                return 'Échec connexion Brevo (535) : vérifiez MAIL_PASSWORD (clé xsmtpsib-...) dans .env.';
            }

            if (str_contains($e->getMessage(), '525') || str_contains($e->getMessage(), 'Unauthorized IP')) {
                return 'Brevo bloque votre IP (525) : autorisez votre IP dans Brevo → Sécurité → IPs autorisées.';
            }

            return 'Erreur d\'envoi email : '.$e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Erreur envoi identifiants enseignant', [
                'teacher_id' => $teacher->id,
                'email' => $teacher->email,
                'message' => $e->getMessage(),
            ]);

            return 'Erreur d\'envoi email : '.$e->getMessage();
        }
    }
}
