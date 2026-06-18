<?php

// app/Services/NotificationService.php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service de notification WhatsApp multilingue et inclusive.
 *
 * Stratégie :
 *   parent_lang = fr_text  → SMS/texte WhatsApp en français avec émojis
 *   parent_lang = wo_audio → Message vocal en wolof  (fichier .mp3 hébergé)
 *   parent_lang = pu_audio → Message vocal en pulaar (fichier .mp3 hébergé)
 *
 * Convention des fichiers audio :
 *   storage/app/public/audio/{lang}/{event}.mp3
 *   ex: storage/app/public/audio/wo_audio/absence.mp3
 *
 * Config requise dans config/services.php :
 *   'ultramsg' => [
 *       'instance_id' => env('ULTRAMSG_INSTANCE_ID'),
 *       'token'       => env('ULTRAMSG_TOKEN'),
 *   ]
 */
final class NotificationService
{
    public const EVENT_ABSENCE = 'absence';
    public const EVENT_RETARD  = 'retard';
    public const EVENT_NOTE    = 'note';
    public const EVENT_REUNION = 'reunion';

    private string $instanceId;
    private string $token;
    private string $baseUrl;

    public function __construct(string $instanceId = '', string $token = '')
    {
        $this->instanceId = $instanceId ?: (string) config('services.ultramsg.instance_id', '');
        $this->token      = $token      ?: (string) config('services.ultramsg.token', '');
        $this->baseUrl    = "https://api.ultramsg.com/{$this->instanceId}";
    }

    // ─── Points d'entrée sémantiques ─────────────────────────────────────────

    public function notifyAbsence(User $student, string $date, string $subject = ''): bool
    {
        return $this->notify($student, self::EVENT_ABSENCE, [
            'date'    => $date,
            'subject' => $subject,
        ]);
    }

    public function notifyLate(User $student, string $date, int $minutes = 0): bool
    {
        return $this->notify($student, self::EVENT_RETARD, [
            'date'    => $date,
            'minutes' => $minutes,
        ]);
    }

    public function notifyNote(User $student, string $subject, float|int $grade): bool
    {
        return $this->notify($student, self::EVENT_NOTE, [
            'subject' => $subject,
            'grade'   => $grade,
        ]);
    }

    // ─── Logique principale ───────────────────────────────────────────────────

    public function notify(User $student, string $event, array $context = []): bool
    {
        if (blank($student->parent_whatsapp)) {
            return false;
        }

        $lang  = $student->parent_lang ?? User::PARENT_LANG_FR_TEXT;
        $phone = $student->parent_whatsapp;

        // ── Branchement texte / audio ─────────────────────────────────────────
        if ($lang === User::PARENT_LANG_FR_TEXT) {
            return $this->sendText($phone, $this->buildTextMessage($student, $event, $context));
        }

        // Langue audio (wo_audio ou pu_audio)
        $audioUrl = $this->resolveAudioUrl($lang, $event);

        if ($audioUrl === null) {
            // Fichier audio absent → fallback texte avec mention de la langue
            Log::warning("[NotificationService] Fichier audio manquant : audio/{$lang}/{$event}.mp3 — fallback texte.");
            return $this->sendText($phone, $this->buildTextMessage($student, $event, $context));
        }

        return $this->sendAudio($phone, $audioUrl);
    }

    // ─── Envoi texte ──────────────────────────────────────────────────────────

    private function sendText(string $phone, string $body): bool
    {
        try {
            $response = Http::timeout(8)
                ->post("{$this->baseUrl}/messages/chat", [
                    'token' => $this->token,
                    'to'    => $phone,
                    'body'  => $body,
                ]);

            if (! $response->successful()) {
                Log::error('[NotificationService] sendText échoué', [
                    'status' => $response->status(),
                    'to'     => $this->maskPhone($phone),
                ]);
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('[NotificationService] sendText exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ─── Envoi audio ──────────────────────────────────────────────────────────

    private function sendAudio(string $phone, string $audioUrl): bool
    {
        try {
            $response = Http::timeout(12)
                ->post("{$this->baseUrl}/messages/audio", [
                    'token' => $this->token,
                    'to'    => $phone,
                    'audio' => $audioUrl,
                    'caption' => '', // UltraMsg accepte un caption optionnel
                ]);

            if (! $response->successful()) {
                Log::error('[NotificationService] sendAudio échoué', [
                    'status'    => $response->status(),
                    'to'        => $this->maskPhone($phone),
                    'audioUrl'  => $audioUrl,
                ]);
                return false;
            }

            return true;

        } catch (\Throwable $e) {
            Log::error('[NotificationService] sendAudio exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ─── Construction du message texte ────────────────────────────────────────

    private function buildTextMessage(User $student, string $event, array $ctx): string
    {
        $childName   = trim("{$student->first_name} {$student->last_name}") ?: $student->name;
        $parentName  = $student->parent_name ?? 'Parent/Tuteur';
        $schoolName  = $student->school?->name ?? 'EduManager';
        $date        = $ctx['date'] ?? now()->translatedFormat('d/m/Y');

        return match ($event) {

            self::EVENT_ABSENCE => implode("\n", [
                "🏫 *{$schoolName}*",
                "",
                "Bonjour *{$parentName}*,",
                "",
                "⚠️ Votre enfant *{$childName}* était *absent(e)* le {$date}"
                    . ($ctx['subject'] ? " en *{$ctx['subject']}*" : '') . '.',
                "",
                "📞 Veuillez contacter l'établissement pour toute justification.",
                "_Message automatique — ne pas répondre._",
            ]),

            self::EVENT_RETARD => implode("\n", [
                "🏫 *{$schoolName}*",
                "",
                "Bonjour *{$parentName}*,",
                "",
                "⏰ Votre enfant *{$childName}* est arrivé(e) *en retard* le {$date}"
                    . (isset($ctx['minutes']) && $ctx['minutes'] > 0 ? " ({$ctx['minutes']} min)" : '') . '.',
                "",
                "_Message automatique — ne pas répondre._",
            ]),

            self::EVENT_NOTE => implode("\n", [
                "🏫 *{$schoolName}*",
                "",
                "Bonjour *{$parentName}*,",
                "",
                "📊 Une nouvelle note a été publiée pour *{$childName}*.",
                "Matière : *{$ctx['subject']}*  |  Note : *{$ctx['grade']}/20*",
                "",
                "_Message automatique — ne pas répondre._",
            ]),

            self::EVENT_REUNION => (static function () use ($ctx, $schoolName, $parentName): string {
                $time    = $ctx['time']    ?? '—';
                $subject = $ctx['subject'] ?? 'Réunion parents-professeurs';
                $rDate   = $ctx['date']    ?? '—';
                return implode("\n", [
                    "🏫 *{$schoolName}*",
                    "",
                    "Bonjour *{$parentName}*,",
                    "",
                    "📅 Une réunion est prévue le *{$rDate}* à *{$time}*.",
                    "Objet : {$subject}",
                    "",
                    "Merci de confirmer votre présence auprès de l'établissement.",
                    "_Message automatique — ne pas répondre._",
                ]);
            })(),

            default => "🏫 *{$schoolName}* — Notification concernant {$childName}.",
        };
    }

    // ─── Résolution URL audio ──────────────────────────────────────────────────

    /**
     * Retourne l'URL publique du fichier .mp3 ou null s'il n'existe pas.
     * Les fichiers doivent être uploadés dans : storage/app/public/audio/{lang}/{event}.mp3
     * puis rendus publics via : php artisan storage:link
     */
    private function resolveAudioUrl(string $lang, string $event): ?string
    {
        $relativePath = "audio/{$lang}/{$event}.mp3";

        if (! Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        return Storage::disk('public')->url($relativePath);
    }

    private function maskPhone(string $phone): string
    {
        return substr($phone, 0, 5) . str_repeat('*', max(0, strlen($phone) - 8)) . substr($phone, -3);
    }
}
