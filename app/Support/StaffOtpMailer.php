<?php

namespace App\Support;

use App\Mail\StaffOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class StaffOtpMailer
{
    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return bool|string true si envoyé, string = message d'erreur
     */
    public static function send(User $user, string $accountLabel = 'administrateur'): bool|string
    {
        if (empty($user->email)) {
            return 'Ce compte n\'a pas d\'adresse email.';
        }

        if (empty($user->identifier)) {
            return 'Ce compte n\'a pas d\'identifiant de connexion.';
        }

        if (config('mail.default') === 'log') {
            return 'MAIL_MAILER=log : aucun email réel n\'est envoyé. Mettez MAIL_MAILER=smtp dans .env puis php artisan config:clear.';
        }

        if (empty(config('mail.mailers.smtp.password')) && config('mail.default') === 'smtp') {
            return 'Configuration incomplète : renseignez MAIL_PASSWORD et MAIL_FROM_ADDRESS dans .env, puis php artisan config:clear.';
        }

        $otpCode = self::generateCode();

        try {
            Mail::to($user->email)->send(new StaffOtpMail($user, $otpCode, $accountLabel));

            $user->forceFill([
                'password' => Hash::make($otpCode),
                'invitation_email_sent_at' => now(),
            ])->save();

            return true;
        } catch (TransportExceptionInterface $e) {
            Log::error('Erreur SMTP envoi OTP staff', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);

            if (str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'Authentication failed')) {
                return 'Échec connexion SMTP (535) : vérifiez MAIL_PASSWORD dans .env.';
            }

            if (str_contains($e->getMessage(), '525') || str_contains($e->getMessage(), 'Unauthorized IP')) {
                return 'SMTP bloque votre IP (525) : autorisez votre IP chez votre fournisseur email.';
            }

            return 'Erreur d\'envoi email : '.$e->getMessage();
        } catch (\Throwable $e) {
            Log::error('Erreur envoi OTP staff', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);

            return 'Erreur d\'envoi email : '.$e->getMessage();
        }
    }

    public static function accountLabelFor(User $user): string
    {
        return match ($user->role) {
            User::ROLE_SURVEILLANT => 'surveillant',
            User::ROLE_ADMIN => 'administrateur',
            default => 'utilisateur',
        };
    }
}
