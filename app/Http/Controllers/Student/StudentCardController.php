<?php

// app/Http/Controllers/Student/StudentCardController.php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\SchoolModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

final class StudentCardController extends Controller
{
    // ── Vue carte scolaire (recto/verso imprimable) ───────────────────────────

    public function show(): View|RedirectResponse
    {
        $student = auth()->user();
        $student->load(['schoolClass.level', 'school']);

        // Fonctionnalité invisible côté élève tant que l'admin ne l'a pas
        // explicitement activée (voir admin/cards — "Activer pour les
        // élèves"), même en accès direct par URL.
        if (! SchoolModules::isEnabled($student->school, SchoolModules::STUDENT_CARDS)) {
            return redirect()->route('student.dashboard')
                ->with('error', "La carte scolaire n'est pas encore disponible. Contactez l'administration.");
        }

        // URL signée valide 1 an — encodée dans le QR verso
        // À chaque scan : signature vérifiée cryptographiquement par Laravel
        $signedUrl = URL::signedRoute(
            'student.card.verify',
            ['student' => $student->id],
            now()->addYear(),
        );

        $school       = $student->school;
        $cardSettings = array_merge([
            'primary_color'    => '#003087',
            'accent_color'     => '#c9a84c',
            'badge_text'       => 'Élève',
            'header_subtitle'  => 'Institut Supérieur · Carte Officielle',
            'show_nationality' => true,
            'show_dob'         => true,
            'footer_brand'     => 'AzelieEdu',
        ], (array) ($school?->card_settings ?? []));

        return view('student.carte_scolaire', [
            'student'      => $student,
            'school'       => $school,
            'signedUrl'    => $signedUrl,
            'issuedDate'   => now()->format('d/m/Y'),
            'acadYear'     => date('Y') . '/' . (date('Y') + 1),
            'cardSettings' => $cardSettings,
        ]);
    }

    // ── Vérification QR (route publique + middleware signed) ──────────────────

    /**
     * Accessible sans authentification.
     * Si l'URL est valide → affiche le profil de vérification.
     * Si non connecté    → redirige vers login avec URL de retour.
     */
    public function verify(Request $request, User $student): View|RedirectResponse
    {
        // Le middleware 'signed' a déjà vérifié la signature ; on arrive ici uniquement si valide.
        $student->load(['schoolClass.level', 'school']);

        if (! auth()->check()) {
            session(['url.intended' => $request->fullUrl()]);

            return redirect()->route('login')
                ->with('info', 'Connectez-vous pour afficher le profil de cet étudiant.');
        }

        return view('student.card_verify', compact('student'));
    }
}
