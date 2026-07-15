<?php

namespace App\Mail;

use App\Models\Grade;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avertit l'administration qu'un enseignant vient d'utiliser sa correction
 * unique sur une note déjà enregistrée.
 */
class GradeEditedByTeacherMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Grade $grade,
        public User $teacher,
        public string $oldGrade,
        public string $newGrade,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' — Correction de note par un enseignant',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.grade-edited-by-teacher',
        );
    }
}
