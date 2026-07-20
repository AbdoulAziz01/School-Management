<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentsImportCredentialsExport;
use App\Exports\StudentsImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Services\StudentImportService;
use App\Support\TenantSchool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Import en masse d'élèves depuis un fichier Excel/CSV, en 3 étapes :
 * upload → mapping des colonnes → prévisualisation avec erreurs → import.
 * L'état entre chaque étape (fichier temporaire, mapping choisi) voyage
 * dans la session, scopé à l'établissement courant pour éviter qu'un admin
 * ne réutilise le token d'import d'un autre établissement.
 */
class StudentImportController extends Controller
{
    public function __construct(
        private StudentImportService $importer
    ) {}

    public function create(): View
    {
        return view('admin.students.import.upload');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new StudentsImportTemplateExport, 'modele-import-eleves.xlsx');
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

        session()->put("import.students.{$token}", [
            'school_id' => $schoolId,
            'path' => $storedPath,
            'headers' => $parsed['headers'],
        ]);

        return redirect()
            ->route('admin.students.import.mapping', ['token' => $token]);
    }

    public function mapping(string $token): View|RedirectResponse
    {
        $state = $this->requireState($token);
        if ($state instanceof RedirectResponse) {
            return $state;
        }

        return view('admin.students.import.mapping', [
            'token' => $token,
            'headers' => $state['headers'],
            'fields' => StudentImportService::CANONICAL_FIELDS,
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

        $missingRequired = collect(StudentImportService::CANONICAL_FIELDS)
            ->filter(fn ($meta, $field) => $meta['required'] && $mapping[$field] === null)
            ->map(fn ($meta) => $meta['label']);

        if ($missingRequired->isNotEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Colonnes obligatoires non associées : '.$missingRequired->implode(', ').'.');
        }

        session()->put("import.students.{$token}.mapping", $mapping);

        $parsed = $this->importer->parseFile(Storage::disk('local')->path($state['path']));
        $validated = $this->importer->validateRows($parsed['rows'], $mapping, $state['school_id']);

        return view('admin.students.import.preview', [
            'token' => $token,
            'rows' => $validated,
            'validCount' => $validated->filter(fn ($r) => empty($r['errors']))->count(),
            'errorCount' => $validated->filter(fn ($r) => ! empty($r['errors']))->count(),
        ]);
    }

    public function store(string $token): RedirectResponse
    {
        // Chaque élève créé fait hasher un mot de passe (bcrypt) + synchroniser
        // son rôle + journaliser l'activité : quelques centaines de lignes
        // dépassent facilement la limite par défaut de 60s.
        set_time_limit(600);

        $state = $this->requireState($token);
        if ($state instanceof RedirectResponse) {
            return $state;
        }

        $mapping = session("import.students.{$token}.mapping");
        if (! $mapping) {
            return redirect()->route('admin.students.import.mapping', ['token' => $token])
                ->with('error', 'Veuillez d\'abord associer les colonnes.');
        }

        $parsed = $this->importer->parseFile(Storage::disk('local')->path($state['path']));
        $validated = $this->importer->validateRows($parsed['rows'], $mapping, $state['school_id']);

        $result = $this->importer->commit($validated, $state['school_id']);

        Storage::disk('local')->delete($state['path']);
        session()->forget("import.students.{$token}");

        $credentialsToken = null;
        if (! empty($result['credentials'])) {
            $credentialsToken = (string) Str::uuid();
            $credentialsPath = "imports/credentials-{$credentialsToken}.xlsx";
            Excel::store(new StudentsImportCredentialsExport($result['credentials']), $credentialsPath, 'local');
            session()->put("import.students.credentials.{$credentialsToken}", [
                'school_id' => $state['school_id'],
                'path' => $credentialsPath,
            ]);
        }

        return redirect()
            ->route('admin.students.import.result', ['token' => $credentialsToken ?? 'none'])
            ->with('success', "{$result['created']} élève(s) importé(s), {$result['skipped']} ligne(s) ignorée(s) (erreurs).")
            ->with('import_created', $result['created'])
            ->with('import_skipped', $result['skipped']);
    }

    public function result(string $token): View
    {
        return view('admin.students.import.result', [
            'credentialsToken' => $token === 'none' ? null : $token,
        ]);
    }

    public function downloadCredentials(string $token): BinaryFileResponse|RedirectResponse
    {
        $state = session("import.students.credentials.{$token}");

        if (! $state || $state['school_id'] !== $this->schoolId() || ! Storage::disk('local')->exists($state['path'])) {
            return redirect()->route('admin.students.import')->with('error', 'Fichier introuvable ou déjà téléchargé.');
        }

        return response()->download(Storage::disk('local')->path($state['path']), 'identifiants-eleves-importes.xlsx');
    }

    /** @return array<string, int|null> */
    private function mappingFromRequest(Request $request): array
    {
        $mapping = [];
        foreach (array_keys(StudentImportService::CANONICAL_FIELDS) as $field) {
            $value = $request->input("mapping.{$field}");
            $mapping[$field] = ($value === null || $value === '') ? null : (int) $value;
        }

        return $mapping;
    }

    /** @return array{school_id:int, path:string, headers:list<string>}|RedirectResponse */
    private function requireState(string $token): array|RedirectResponse
    {
        $state = session("import.students.{$token}");

        if (! $state || $state['school_id'] !== $this->schoolId() || ! Storage::disk('local')->exists($state['path'])) {
            return redirect()->route('admin.students.import')
                ->with('error', 'Session d\'import expirée ou invalide. Recommencez.');
        }

        return $state;
    }

    private function schoolId(): int
    {
        return TenantSchool::id() ?? (int) auth()->user()?->school_id;
    }
}
