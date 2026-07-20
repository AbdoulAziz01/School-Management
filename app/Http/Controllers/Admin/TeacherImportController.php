<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TeachersImportCredentialsExport;
use App\Exports\TeachersImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Services\TeacherImportService;
use App\Support\TenantSchool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Import en masse d'enseignants — même flux en 4 étapes que
 * StudentImportController (upload → mapping → aperçu → import), voir ce
 * contrôleur pour le détail des choix de conception (état en session
 * scopé à l'établissement, fichier temporaire sur le disque privé).
 */
class TeacherImportController extends Controller
{
    public function __construct(
        private TeacherImportService $importer
    ) {}

    public function create(): View
    {
        return view('admin.teachers.import.upload');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new TeachersImportTemplateExport, 'modele-import-enseignants.xlsx');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [], ['file' => 'fichier']);

        $schoolId = $this->schoolId();
        $token = (string) Str::uuid();
        $storedPath = $request->file('file')->storeAs('imports', $token.'.'.$request->file('file')->extension(), 'local');

        $parsed = $this->importer->parseFile(Storage::disk('local')->path($storedPath));

        if (empty($parsed['headers'])) {
            Storage::disk('local')->delete($storedPath);

            return back()->with('error', 'Le fichier est vide ou son format n\'a pas pu être lu.');
        }

        session()->put("import.teachers.{$token}", [
            'school_id' => $schoolId,
            'path' => $storedPath,
            'headers' => $parsed['headers'],
        ]);

        return redirect()->route('admin.teachers.import.mapping', ['token' => $token]);
    }

    public function mapping(string $token): View|RedirectResponse
    {
        $state = $this->requireState($token);
        if ($state instanceof RedirectResponse) {
            return $state;
        }

        return view('admin.teachers.import.mapping', [
            'token' => $token,
            'headers' => $state['headers'],
            'fields' => TeacherImportService::CANONICAL_FIELDS,
            'guessedMapping' => $this->importer->guessMapping($state['headers']),
        ]);
    }

    public function preview(Request $request, string $token): View|RedirectResponse
    {
        $state = $this->requireState($token);
        if ($state instanceof RedirectResponse) {
            return $state;
        }

        $mapping = $this->mappingFromRequest($request);

        $missingRequired = collect(TeacherImportService::CANONICAL_FIELDS)
            ->filter(fn ($meta, $field) => $meta['required'] && $mapping[$field] === null)
            ->map(fn ($meta) => $meta['label']);

        if ($missingRequired->isNotEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Colonnes obligatoires non associées : '.$missingRequired->implode(', ').'.');
        }

        session()->put("import.teachers.{$token}.mapping", $mapping);

        $parsed = $this->importer->parseFile(Storage::disk('local')->path($state['path']));
        $validated = $this->importer->validateRows($parsed['rows'], $mapping, $state['school_id']);

        return view('admin.teachers.import.preview', [
            'token' => $token,
            'rows' => $validated,
            'validCount' => $validated->filter(fn ($r) => empty($r['errors']))->count(),
            'errorCount' => $validated->filter(fn ($r) => ! empty($r['errors']))->count(),
        ]);
    }

    public function store(string $token): RedirectResponse
    {
        // Chaque enseignant créé fait hasher un mot de passe (bcrypt) et
        // synchroniser ses matières/classes : une centaine de lignes peut
        // dépasser la limite par défaut de 60s.
        set_time_limit(600);

        $state = $this->requireState($token);
        if ($state instanceof RedirectResponse) {
            return $state;
        }

        $mapping = session("import.teachers.{$token}.mapping");
        if (! $mapping) {
            return redirect()->route('admin.teachers.import.mapping', ['token' => $token])
                ->with('error', 'Veuillez d\'abord associer les colonnes.');
        }

        $parsed = $this->importer->parseFile(Storage::disk('local')->path($state['path']));
        $validated = $this->importer->validateRows($parsed['rows'], $mapping, $state['school_id']);

        $result = $this->importer->commit($validated, $state['school_id']);

        Storage::disk('local')->delete($state['path']);
        session()->forget("import.teachers.{$token}");

        $credentialsToken = null;
        if (! empty($result['credentials'])) {
            $credentialsToken = (string) Str::uuid();
            $credentialsPath = "imports/credentials-{$credentialsToken}.xlsx";
            Excel::store(new TeachersImportCredentialsExport($result['credentials']), $credentialsPath, 'local');
            session()->put("import.teachers.credentials.{$credentialsToken}", [
                'school_id' => $state['school_id'],
                'path' => $credentialsPath,
            ]);
        }

        return redirect()
            ->route('admin.teachers.import.result', ['token' => $credentialsToken ?? 'none'])
            ->with('success', "{$result['created']} enseignant(s) importé(s), {$result['skipped']} ligne(s) ignorée(s) (erreurs).")
            ->with('import_created', $result['created'])
            ->with('import_skipped', $result['skipped']);
    }

    public function result(string $token): View
    {
        return view('admin.teachers.import.result', [
            'credentialsToken' => $token === 'none' ? null : $token,
        ]);
    }

    public function downloadCredentials(string $token): BinaryFileResponse|RedirectResponse
    {
        $state = session("import.teachers.credentials.{$token}");

        if (! $state || $state['school_id'] !== $this->schoolId() || ! Storage::disk('local')->exists($state['path'])) {
            return redirect()->route('admin.teachers.import')->with('error', 'Fichier introuvable ou déjà téléchargé.');
        }

        return response()->download(Storage::disk('local')->path($state['path']), 'identifiants-enseignants-importes.xlsx');
    }

    /** @return array<string, int|null> */
    private function mappingFromRequest(Request $request): array
    {
        $mapping = [];
        foreach (array_keys(TeacherImportService::CANONICAL_FIELDS) as $field) {
            $value = $request->input("mapping.{$field}");
            $mapping[$field] = ($value === null || $value === '') ? null : (int) $value;
        }

        return $mapping;
    }

    /** @return array{school_id:int, path:string, headers:list<string>}|RedirectResponse */
    private function requireState(string $token): array|RedirectResponse
    {
        $state = session("import.teachers.{$token}");

        if (! $state || $state['school_id'] !== $this->schoolId() || ! Storage::disk('local')->exists($state['path'])) {
            return redirect()->route('admin.teachers.import')
                ->with('error', 'Session d\'import expirée ou invalide. Recommencez.');
        }

        return $state;
    }

    private function schoolId(): int
    {
        return TenantSchool::id() ?? (int) auth()->user()?->school_id;
    }
}
