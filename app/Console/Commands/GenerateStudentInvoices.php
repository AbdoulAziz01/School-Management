<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\User;
use App\Services\StudentInvoiceService;
use App\Support\SchoolModules;
use Illuminate\Console\Command;

/**
 * (Ré)génère les factures élèves pour l'année scolaire courante de chaque
 * établissement privé ayant le module Comptabilité activé. Idempotent
 * (StudentInvoiceService::generateForStudent() ne duplique jamais) — sert
 * à la fois à rattraper les élèves déjà inscrits avant l'activation du
 * module, et pourra être planifiée (ex. mensuellement) sans risque.
 */
class GenerateStudentInvoices extends Command
{
    protected $signature = 'accounting:generate-student-invoices {--school= : Limiter à un établissement (ID)}';

    protected $description = 'Génère les factures élèves (frais/mensualités) pour l\'année scolaire courante';

    public function handle(StudentInvoiceService $invoices): int
    {
        $schools = School::withoutGlobalScopes()
            ->when($this->option('school'), fn ($q, $id) => $q->where('id', $id))
            ->get()
            ->filter(fn (School $school) => SchoolModules::isEnabled($school, SchoolModules::ACCOUNTING));

        if ($schools->isEmpty()) {
            $this->info('Aucun établissement avec le module Comptabilité activé.');

            return self::SUCCESS;
        }

        foreach ($schools as $school) {
            $academicYear = AcademicYear::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('is_current', true)
                ->first();

            if (! $academicYear) {
                $this->warn("« {$school->name} » : aucune année scolaire courante, ignoré.");

                continue;
            }

            $students = User::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->whereIn('role', User::ROLE_STUDENT_ALIASES)
                ->where('status', User::STATUS_APPROVED)
                ->get();

            $count = 0;
            foreach ($students as $student) {
                $count += $invoices->generateForStudent($student, $academicYear)->count();
            }

            $this->info("« {$school->name} » : {$students->count()} élève(s) traité(s), {$count} facture(s) générée(s)/déjà existante(s).");
        }

        return self::SUCCESS;
    }
}
