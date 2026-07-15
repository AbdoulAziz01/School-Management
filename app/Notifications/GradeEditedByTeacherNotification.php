<?php

namespace App\Notifications;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avertit admins/surveillants qu'un enseignant a utilisé sa correction
 * unique sur une note — par email et par notification en base (badge dans
 * la barre de navigation).
 */
class GradeEditedByTeacherNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Grade $grade,
        public User $teacher,
        public string $oldGrade,
        public string $newGrade,
    ) {}

    /**
     * database est toujours inclus (badge dans la barre de navigation) ;
     * mail seulement si le destinataire a une adresse, pour ne pas faire
     * échouer tout le job en file (mail + database) sur un compte sans email.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(config('app.name').' — Correction de note par un enseignant')
            ->markdown('emails.grade-edited-by-teacher', [
                'grade' => $this->grade,
                'teacher' => $this->teacher,
                'oldGrade' => $this->oldGrade,
                'newGrade' => $this->newGrade,
            ]);
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'grade_edited_by_teacher',
            'grade_id' => $this->grade->id,
            'student_name' => $this->grade->user?->name,
            'subject_name' => $this->grade->subject?->name,
            'teacher_name' => $this->teacher->name,
            'old_grade' => $this->oldGrade,
            'new_grade' => $this->newGrade,
            'semester' => $this->grade->semester,
            'message' => "{$this->teacher->name} a corrigé la note de {$this->grade->user?->name} en {$this->grade->subject?->name} ({$this->oldGrade}/20 → {$this->newGrade}/20).",
        ];
    }
}
