<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\User;
use App\Services\StudentInvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dates (début/fin) de l'année scolaire courante, ouvertes en lecture-écriture
 * au directeur — c'est cette période qui détermine les mensualités générées
 * par StudentInvoiceService (une facture par mois entre start_date et
 * end_date). La création/clôture/réouverture d'années scolaires reste
 * réservée à l'Admin (Admin\AcademicYearController) : sujet pédagogique
 * (passage de classe, etc.), hors périmètre du directeur. Ici, on ne touche
 * jamais qu'à l'année déjà marquée courante.
 */
class AcademicYearSettingsController extends Controller
{
    public function __construct(
        private StudentInvoiceService $invoices
    ) {}

    public function edit(Request $request): View
    {
        $academicYear = $this->currentYear($request);

        return view('accounting.directeur.academic-year.edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $academicYear = $this->currentYear($request);

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ], [
            'end_date.required' => 'La date de fin est obligatoire : elle sert à générer les mensualités de toute l\'année.',
            'end_date.after' => 'La date de fin doit être postérieure à la date de début.',
        ]);

        $academicYear->update($validated);

        // Complète les mensualités manquantes pour les mois désormais couverts
        // (idempotent — ne duplique jamais une facture déjà générée, ne
        // supprime jamais une facture existante si la période est raccourcie).
        $students = User::where('school_id', $request->user()->school_id)
            ->whereIn('role', User::ROLE_STUDENT_ALIASES)
            ->where('status', User::STATUS_APPROVED)
            ->get();

        foreach ($students as $student) {
            $this->invoices->generateForStudent($student, $academicYear);
        }

        return redirect()->route('directeur.academic-year.edit')
            ->with('success', "Dates de l'année scolaire mises à jour. Mensualités régénérées pour {$students->count()} élève(s).");
    }

    private function currentYear(Request $request): AcademicYear
    {
        $academicYear = AcademicYear::where('school_id', $request->user()->school_id)
            ->where('is_current', true)
            ->first();

        abort_unless($academicYear, 404, 'Aucune année scolaire courante — contactez l\'administrateur de l\'établissement.');

        return $academicYear;
    }
}
