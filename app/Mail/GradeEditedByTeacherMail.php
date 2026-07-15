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
 * Email envoyé en file d'attente à l'administration lorsqu'un enseignant
 * utilise sa correction unique sur une note. Séparé de
 * GradeEditedByTeacherNotification (badge en base, synchrone) pour que le
 * badge ne dépende pas d'un worker de file actif.
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
