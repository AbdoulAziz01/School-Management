<?php

namespace App\Listeners;

use App\Events\StudentEnrolled;
use App\Models\AcademicYear;
use App\Services\StudentInvoiceService;

/**
 * Premier vrai listener du module Comptabilité (voir docblock de
 * StudentEnrolled) : génère automatiquement les factures de l'élève
 * (inscription, mensualités...) à l'inscription, sans que StudentController
 * ait besoin de connaître quoi que ce soit sur la facturation.
 *
 * Volontairement synchrone (pas de ShouldQueue) : l'environnement ne fait
 * pas tourner de worker de file d'attente en continu (voir le bug corrigé
 * sur GradeEditedByTeacherNotification) et la génération de factures est
 * bon marché (quelques insertions), pas de raison de dépendre d'une queue.
 */
class GenerateStudentInvoicesOnEnrollment
{
    public function __construct(
        private StudentInvoiceService $invoices
    ) {}

    public function handle(StudentEnrolled $event): void
    {
        $academicYear = AcademicYear::where('school_id', $event->student->school_id)
            ->where('is_current', true)
            ->first();

        if (! $academicYear) {
            return;
        }

        $this->invoices->generateForStudent($event->student, $academicYear);
    }
}
