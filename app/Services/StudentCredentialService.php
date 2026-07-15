<?php

namespace App\Services;

use App\Mail\StudentCredentialsMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Génération, affichage ponctuel et envoi des identifiants de connexion
 * élève — extrait de StudentController, qui mélangeait cette logique avec
 * le reste du CRUD élève.
 */
class StudentCredentialService
{
    public function assertCanManage(): void
    {
        abort_unless(auth()->user()?->canViewUserPasswords(), 403, 'Accès réservé à l\'administrateur.');
    }

    /**
     * Affiche les identifiants en clair une seule fois : uniquement juste après leur
     * génération (création, régénération, réinitialisation, envoi), via un flash de
     * session consommé par la requête suivante. Le mot de passe n'est jamais stocké
     * en clair ni de façon réversible.
     */
    public function flash(User $student, string $plainPassword): void
    {
        session()->flash("credentials_reveal.student.{$student->id}", [
            'name' => $student->name,
            'identifier' => $student->identifier,
            'email' => $student->email,
            'password' => $plainPassword,
        ]);
    }

    /** @return array{name: string, identifier: string|null, email: string|null, password: string}|null */
    public function resolvePending(User $student): ?array
    {
        if (! auth()->user()?->canViewUserPasswords()) {
            return null;
        }

        return session("credentials_reveal.student.{$student->id}");
    }

    public function assignPassword(User $student): string
    {
        $plainPassword = Str::password(10, symbols: false);

        $student->forceFill([
            'password' => Hash::make($plainPassword),
        ])->save();

        $this->flash($student, $plainPassword);

        return $plainPassword;
    }

    /** @return bool|string true si envoyé, string = message d'erreur */
    public function send(User $student, ?string $plainPassword = null): bool|string
    {
        if (empty($student->email)) {
            return 'Cet élève n\'a pas d\'adresse email.';
        }

        if (empty($student->identifier)) {
            return 'Cet élève n\'a pas d\'identifiant de connexion.';
        }

        if (config('mail.default') === 'log') {
            return 'MAIL_MAILER=log : aucun email réel n\'est envoyé. Mettez MAIL_MAILER=smtp dans .env puis php artisan config:clear.';
        }

        if (empty(config('mail.mailers.smtp.password')) && config('mail.default') === 'smtp') {
            return 'Configuration incomplète : renseignez MAIL_PASSWORD dans .env.';
        }

        $plainPassword ??= $this->assignPassword($student);

        try {
            $student->loadMissing('school');
            Mail::to($student->email)->send(new StudentCredentialsMail($student, $plainPassword));

            $student->forceFill([
                'password' => Hash::make($plainPassword),
                'invitation_email_sent_at' => now(),
            ])->save();

            $this->flash($student, $plainPassword);

            return true;
        } catch (TransportExceptionInterface $e) {
            Log::error('Erreur SMTP envoi identifiants élève', [
                'student_id' => $student->id,
                'email' => $student->email,
                'message' => $e->getMessage(),
            ]);

            return 'Erreur d\'envoi email : '.$e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Erreur envoi identifiants élève', [
                'student_id' => $student->id,
                'message' => $e->getMessage(),
            ]);

            return 'Erreur d\'envoi email : '.$e->getMessage();
        }
    }
}
