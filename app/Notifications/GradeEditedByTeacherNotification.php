<?php

namespace App\Notifications;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * Notification en base uniquement (badge dans la barre de navigation).
 * Volontairement NON mise en file : le badge doit apparaître immédiatement
 * après la correction, sans dépendre d'un worker de file d'attente actif.
 * L'email correspondant est envoyé séparément, en file (voir
 * TeacherGradesController::notifySchoolStaffOfGradeEdit()), pour ne pas
 * bloquer la requête de l'enseignant sur l'envoi SMTP.
 */
class GradeEditedByTeacherNotification extends Notification
{
    public function __construct(
        public Grade $grade,
        public User $teacher,
        public string $oldGrade,
        public string $newGrade,
        public float $maxGrade = 20.0,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
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
            'max_grade' => $this->maxGrade,
            'semester' => $this->grade->semester,
            'message' => "{$this->teacher->name} a corrigé la note de {$this->grade->user?->name} en {$this->grade->subject?->name} ({$this->oldGrade}/{$this->formattedMaxGrade()} → {$this->newGrade}/{$this->formattedMaxGrade()}).",
        ];
    }

    private function formattedMaxGrade(): string
    {
        return rtrim(rtrim(number_format($this->maxGrade, 2), '0'), '.');
    }
}
