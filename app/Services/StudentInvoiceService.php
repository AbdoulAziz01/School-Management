<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\StudentInvoice;
use App\Models\User;
use App\Support\SchoolModules;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Génère automatiquement les factures d'un élève (jamais de saisie
 * manuelle du montant dû, voir cahier des charges "aucune double saisie") :
 * un frais non récurrent (inscription, réinscription...) donne une facture
 * unique ; un frais récurrent (mensualité) donne une facture par mois de
 * l'année scolaire. Idempotent (firstOrCreate) : peut être rappelé sans
 * jamais dupliquer une facture déjà générée.
 */
class StudentInvoiceService
{
    public function __construct(
        private FeeConfigurationService $fees
    ) {}

    /** @return Collection<int, StudentInvoice> */
    public function generateForStudent(User $student, AcademicYear $academicYear): Collection
    {
        $school = \App\Models\School::find($student->school_id);

        if (! SchoolModules::isEnabled($school, SchoolModules::ACCOUNTING)) {
            return collect();
        }

        $levelId = $student->class_id
            ? SchoolClass::withoutGlobalScopes()->find($student->class_id)?->level_id
            : null;

        $feeTypes = FeeType::where('school_id', $student->school_id)->get();
        $created = collect();

        foreach ($feeTypes as $feeType) {
            if ($feeType->is_recurring) {
                $created = $created->merge($this->generateRecurringInvoices($student, $feeType, $academicYear, $levelId));
            } else {
                $invoice = $this->generateSingleInvoice($student, $feeType, $academicYear, $levelId);
                if ($invoice) {
                    $created->push($invoice);
                }
            }
        }

        return $created;
    }

    private function generateSingleInvoice(User $student, FeeType $feeType, AcademicYear $academicYear, ?int $levelId): ?StudentInvoice
    {
        $amount = $this->fees->amountForLevel($feeType, $academicYear, $levelId);

        if ($amount === null) {
            return null;
        }

        return StudentInvoice::firstOrCreate(
            [
                'student_id' => $student->id,
                'fee_type_id' => $feeType->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'school_id' => $student->school_id,
                'label' => $feeType->name,
                'amount_due' => $amount,
                'due_date' => $academicYear->start_date ?? now(),
                'status' => StudentInvoice::STATUS_PENDING,
            ]
        );
    }

    /** @return Collection<int, StudentInvoice> */
    private function generateRecurringInvoices(User $student, FeeType $feeType, AcademicYear $academicYear, ?int $levelId): Collection
    {
        $amount = $this->fees->amountForLevel($feeType, $academicYear, $levelId);

        if ($amount === null || ! $academicYear->start_date || ! $academicYear->end_date) {
            return collect();
        }

        $months = CarbonPeriod::create(
            $academicYear->start_date->copy()->startOfMonth(),
            '1 month',
            $academicYear->end_date->copy()->startOfMonth()
        );

        $invoices = collect();

        foreach ($months as $monthStart) {
            $invoices->push(StudentInvoice::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'fee_type_id' => $feeType->id,
                    'academic_year_id' => $academicYear->id,
                    'due_date' => $monthStart->copy(),
                ],
                [
                    'school_id' => $student->school_id,
                    'label' => $feeType->name.' — '.$monthStart->locale('fr')->translatedFormat('F Y'),
                    'amount_due' => $amount,
                    'status' => StudentInvoice::STATUS_PENDING,
                ]
            ));
        }

        return $invoices;
    }
}
