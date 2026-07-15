<?php

namespace App\Notifications;

use App\Models\CashSession;
use Illuminate\Notifications\Notification;

/**
 * Alerte le directeur quand une caisse est clôturée avec un écart —
 * réutilise le système de cloche déjà en place (badge sidebar + dropdown
 * navbar, voir GradeEditedByTeacherNotification pour le même pattern).
 * Synchrone (pas de ShouldQueue) pour que le badge apparaisse
 * immédiatement, sans dépendre d'un worker de file d'attente.
 */
class CashSessionDiscrepancyNotification extends Notification
{
    public function __construct(
        public CashSession $session,
        public string $cashierName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sign = $this->session->difference > 0 ? 'excédent' : 'manque';
        $amount = number_format(abs($this->session->difference), 0, ',', ' ');

        return [
            'type' => 'cash_session_discrepancy',
            'cash_session_id' => $this->session->id,
            'message' => "Écart de caisse à la clôture de {$this->cashierName} : {$amount} FCFA de {$sign}.",
        ];
    }
}
