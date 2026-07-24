<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use App\Support\SchoolModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

final class AdminCardController extends Controller
{
    private const DEFAULTS = [
        'primary_color'   => '#003087',
        'accent_color'    => '#c9a84c',
        'badge_text'      => 'Élève',
        'header_subtitle' => 'Institut Supérieur · Carte Officielle',
        'show_nationality' => true,
        'show_dob'        => true,
        'footer_brand'    => 'AzelieEdu',
    ];

    // ── Liste de tous les élèves ──────────────────────────────────────────────

    public function index(Request $request): View
    {
        $school = Auth::user()->school;

        $query = User::with(['schoolClass.level'])
            ->where('role', 'student')
            ->where('status', 'approved');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name',  'ilike', "%{$search}%")
                  ->orWhere('identifier', 'ilike', "%{$search}%");
            });
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(24)->withQueryString();
        $classes  = SchoolClass::orderBy('name')->get();
        $cardsEnabled = SchoolModules::isEnabled($school, SchoolModules::STUDENT_CARDS);

        return view('admin.cards.index', compact('students', 'classes', 'school', 'cardsEnabled'));
    }

    /**
     * Active/désactive l'accès des élèves à leur carte scolaire (menu
     * "Ma Carte Scolaire" + page). Tant que ce n'est pas activé par
     * l'admin, la fonctionnalité reste invisible côté élève — voir
     * StudentCardController::show() et le menu élève.
     */
    public function toggleEnabled(): RedirectResponse
    {
        $school = Auth::user()->school;

        if (SchoolModules::isEnabled($school, SchoolModules::STUDENT_CARDS)) {
            SchoolModules::disable($school, SchoolModules::STUDENT_CARDS);
            $message = 'Les cartes scolaires ont été désactivées : les élèves ne peuvent plus y accéder.';
        } else {
            SchoolModules::enable($school, SchoolModules::STUDENT_CARDS);
            $message = 'Les cartes scolaires sont activées : les élèves peuvent désormais voir leur carte.';
        }

        return redirect()->route('admin.cards.index')->with('success', $message);
    }

    // ── Aperçu de la carte d'un élève ────────────────────────────────────────

    public function show(User $student): View
    {
        $student->load(['schoolClass.level', 'school']);
        $school = $student->school ?? Auth::user()->school;

        $signedUrl  = URL::signedRoute('student.card.verify', ['student' => $student->id], now()->addYear());
        $cardSettings = $this->resolveSettings($school);

        return view('admin.cards.show', [
            'student'      => $student,
            'school'       => $school,
            'signedUrl'    => $signedUrl,
            'issuedDate'   => now()->format('d/m/Y'),
            'acadYear'     => date('Y').'/'.(date('Y') + 1),
            'cardSettings' => $cardSettings,
        ]);
    }

    // ── Formulaire de personnalisation ───────────────────────────────────────

    public function settings(): View
    {
        $school       = Auth::user()->school;
        $cardSettings = $this->resolveSettings($school);

        return view('admin.cards.settings', compact('school', 'cardSettings'));
    }

    // ── Sauvegarde de la personnalisation ────────────────────────────────────

    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'primary_color'    => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'     => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'badge_text'       => ['required', 'string', 'max:30'],
            'header_subtitle'  => ['nullable', 'string', 'max:80'],
            'show_nationality' => ['nullable', 'boolean'],
            'show_dob'         => ['nullable', 'boolean'],
            'footer_brand'     => ['nullable', 'string', 'max:30'],
        ]);

        $validated['show_nationality'] = (bool) ($validated['show_nationality'] ?? false);
        $validated['show_dob']         = (bool) ($validated['show_dob'] ?? false);

        Auth::user()->school->update(['card_settings' => $validated]);

        return redirect()->route('admin.cards.settings')
            ->with('success', 'Personnalisation de la carte sauvegardée.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveSettings(?object $school): array
    {
        return array_merge(self::DEFAULTS, (array) ($school?->card_settings ?? []));
    }
}
