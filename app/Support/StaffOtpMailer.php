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
     * Génère un code OTP, l'enregistre comme mot de passe du compte, puis tente l'envoi par email.
     *
     * @return array{ok: bool, code?: string, mailed?: bool, message?: string}
     */
    public static function send(User $user, string $accountLabel = 'administrateur'): array
    {
        if (empty($user->email)) {
            return ['ok' => false, 'message' => 'Ce compte n\'a pas d\'adresse email.'];
        }

        if (empty($user->identifier)) {
            return ['ok' => false, 'message' => 'Ce compte n\'a pas d\'identifiant de connexion.'];
        }

        if (config('mail.default') === 'log') {
            $result = self::assignCodeWithoutMail($user);

            return $result['ok']
                ? array_merge($result, [
                    'mailed' => false,
                    'message' => 'Mode log : aucun email envoyé — communiquez le mot de passe manuellement.',
                ])
                : $result;
        }

        if (empty(config('mail.mailers.smtp.password')) && config('mail.default') === 'smtp') {
            $result = self::assignCodeWithoutMail($user);

            return $result['ok']
                ? array_merge($result, [
                    'mailed' => false,
                    'message' => 'SMTP non configuré — communiquez le mot de passe manuellement.',
                ])
                : ['ok' => false, 'message' => 'Configuration incomplète : renseignez MAIL_PASSWORD et MAIL_FROM_ADDRESS dans .env, puis php artisan config:clear.'];
        }

        $otpCode = self::generateCode();

        $user->forceFill([
            'password' => Hash::make($otpCode),
            'invitation_email_sent_at' => now(),
        ])->save();

        try {
            Mail::to($user->email)->send(new StaffOtpMail($user, $otpCode, $accountLabel));

            return ['ok' => true, 'code' => $otpCode, 'mailed' => true];
        } catch (TransportExceptionInterface $e) {
            Log::error('Erreur SMTP envoi OTP staff', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);

            $message = self::transportErrorMessage($e);

            return [
                'ok' => true,
                'code' => $otpCode,
                'mailed' => false,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            Log::error('Erreur envoi OTP staff', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $e->getMessage(),
            ]);

            return [
                'ok' => true,
                'code' => $otpCode,
                'mailed' => false,
                'message' => 'Erreur d\'envoi email : '.$e->getMessage(),
            ];
        }
    }

    /**
     * Enregistre un code OTP comme mot de passe sans envoyer d'email (affichage manuel par le super admin).
     *
     * @return array{ok: bool, code?: string, message?: string}
     */
    public static function assignCodeWithoutMail(User $user, ?string $code = null): array
    {
        if (empty($user->identifier)) {
            return ['ok' => false, 'message' => 'Ce compte n\'a pas d\'identifiant de connexion.'];
        }

        $otpCode = $code ?? self::generateCode();

        $user->forceFill([
            'password' => Hash::make($otpCode),
            'invitation_email_sent_at' => now(),
        ])->save();

        return ['ok' => true, 'code' => $otpCode, 'mailed' => false];
    }

    private static function transportErrorMessage(TransportExceptionInterface $e): string
    {
        if (str_contains($e->getMessage(), '535') || str_contains($e->getMessage(), 'Authentication failed')) {
            return 'Échec connexion SMTP (535) : vérifiez MAIL_PASSWORD dans .env.';
        }

        if (str_contains($e->getMessage(), '525') || str_contains($e->getMessage(), 'Unauthorized IP')) {
            return 'SMTP bloque votre IP (525) : autorisez votre IP chez votre fournisseur email.';
        }

        return 'Erreur d\'envoi email : '.$e->getMessage();
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
