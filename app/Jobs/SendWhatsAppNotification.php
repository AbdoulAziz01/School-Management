<?php

// app/Jobs/SendWhatsAppNotification.php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job asynchrone pour ne pas bloquer la réponse HTTP lors d'une notification.
 *
 * Usage :
 *   SendWhatsAppNotification::dispatch($student, 'absence', ['date' => '18/06/2026']);
 *   SendWhatsAppNotification::dispatchAfterResponse($student, 'retard', ['date' => '...']);
 */
final class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 20;

    public function __construct(
        private readonly User   $student,
        private readonly string $event,
        private readonly array  $context = [],
    ) {}

    public function handle(NotificationService $service): void
    {
        $service->notify($this->student, $this->event, $this->context);
    }

    public function backoff(): array
    {
        return [10, 30, 60]; // secondes entre les tentatives
    }
}
