<?php

namespace App\Services;

use App\Models\Subject;
use App\Models\User;
use App\Services\Import\SpreadsheetParser;
use App\Services\Import\TextNormalizer;
use App\Support\SchoolUserIdentifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Import en masse d'enseignants depuis un fichier Excel/CSV — même moteur
 * que App\Services\StudentImportService (parsing partagé, mapping,
 * validation ligne par ligne, création directe), adapté aux champs
 * enseignant : matières multiples optionnelles, résolues par nom texte
 * libre séparé par « ; » (ex. "Mathématiques; Physique-Chimie").
 *
 * Les classes ne s'importent volontairement pas : l'affectation se fait
 * manuellement par l'admin après l'import, une fois les emplois du temps
 * connus (contrairement aux élèves, où la classe est connue dès l'inscription).
 */
class TeacherImportService
{
    public function __construct(
        private TeacherTeachingService $teaching
    ) {}

    /** Champs proposés au mapping, dans l'ordre d'affichage. */
    public const CANONICAL_FIELDS = [
        'last_name'     => ['label' => 'Nom', 'required' => true],
        'first_name'    => ['label' => 'Prénom', 'required' => true],
        'email'         => ['label' => 'Email', 'required' => true],
        'phone'         => ['label' => 'Téléphone', 'required' => false],
        'address'       => ['label' => 'Adresse', 'required' => false],
        'date_of_birth' => ['label' => 'Date de naissance', 'required' => false],
        // Optionnel : au primaire un enseignant couvre toutes les matières
        // de sa classe, pas de liste à saisir — utile surtout au collège/lycée.
        'subjects'      => ['label' => 'Matières (séparées par ;, optionnel — collège/lycée)', 'required' => false],
    ];

    private const FIELD_ALIASES = [
        'last_name'     => ['nom', 'lastname', 'nom de famille'],
        'first_name'    => ['prenom', 'prénom', 'firstname'],
        'email'         => ['email', 'e-mail', 'mail', 'courriel'],
        'phone'         => ['telephone', 'téléphone', 'tel', 'contact', 'whatsapp'],
        'address'       => ['adresse', 'address'],
        'date_of_birth' => ['date de naissance', 'naissance', 'né le', 'nee le', 'dob'],
        'subjects'      => ['matiere', 'matieres', 'matière', 'matières', 'subject', 'subjects'],
    ];

    /** @return array{headers: list<string>, rows: list<list<mixed>>} */
    public function parseFile(string $absolutePath): array
    {
        return SpreadsheetParser::parse($absolutePath);
    }

    /** @return array<string, int|null> */
    public function guessMapping(array $headers): array
    {
        $normalizedHeaders = array_map(fn ($h) => TextNormalizer::normalize($h), $headers);
        $mapping = [];

        foreach (self::CANONICAL_FIELDS as $field => $meta) {
            $mapping[$field] = null;
            $aliases = array_map(fn ($a) => TextNormalizer::normalize($a), self::FIELD_ALIASES[$field]);

            foreach ($normalizedHeaders as $index => $header) {
                if (in_array($header, $aliases, true)) {
                    $mapping[$field] = $index;
                    continue 2;
                }
            }

            foreach ($normalizedHeaders as $index => $header) {
                foreach ($aliases as $alias) {
                    if ($alias !== '' && str_contains($header, $alias)) {
                        $mapping[$field] = $index;
                        continue 3;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * @param  list<list<mixed>>  $rows
     * @param  array<string, int|null>  $mapping
     * @return Collection<int, array{line:int, data:array<string,mixed>, errors:list<string>, subject_ids:list<int>, subjects_display:string}>
     */
    public function validateRows(array $rows, array $mapping, int $schoolId): Collection
    {
        $subjects = Subject::where('school_id', $schoolId)->get();

        $existingEmails = User::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($e) => Str::lower($e))
            ->flip();

        $seenEmailsInFile = [];

        return collect($rows)->map(function (array $row, int $index) use ($mapping, $subjects, $existingEmails, &$seenEmailsInFile) {
            $line = $index + 2;
            $errors = [];

            $data = [];
            foreach ($mapping as $field => $columnIndex) {
                $data[$field] = $columnIndex !== null ? trim((string) ($row[$columnIndex] ?? '')) : '';
            }

            foreach (self::CANONICAL_FIELDS as $field => $meta) {
                if ($meta['required'] && $data[$field] === '') {
                    $errors[] = "« {$meta['label']} » est requis.";
                }
            }

            if ($data['email'] !== '') {
                if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Email invalide : « {$data['email']} ».";
                } else {
                    $emailKey = Str::lower($data['email']);
                    if ($existingEmails->has($emailKey)) {
                        $errors[] = 'Email déjà utilisé par un compte existant.';
                    } elseif (isset($seenEmailsInFile[$emailKey])) {
                        $errors[] = "Email en double dans le fichier (ligne {$seenEmailsInFile[$emailKey]}).";
                    } else {
                        $seenEmailsInFile[$emailKey] = $line;
                    }
                }
            }

            [$subjectIds, $unresolvedSubjects] = $this->resolveNames($data['subjects'], $subjects);
            if ($unresolvedSubjects !== []) {
                $errors[] = 'Matière(s) introuvable(s) : '.implode(', ', $unresolvedSubjects).'.';
            }

            $dateOfBirthParsed = null;
            if ($data['date_of_birth'] !== '') {
                $dateOfBirthParsed = \App\Services\Import\FlexibleDateParser::parse($data['date_of_birth']);
                if (! $dateOfBirthParsed) {
                    $errors[] = "Date de naissance invalide : « {$data['date_of_birth']} ».";
                } elseif ($dateOfBirthParsed->isAfter(now())) {
                    $errors[] = 'Date de naissance dans le futur.';
                }
            }

            return [
                'line' => $line,
                'data' => array_merge($data, ['date_of_birth_parsed' => $dateOfBirthParsed?->format('Y-m-d')]),
                'errors' => $errors,
                'subject_ids' => $subjectIds,
                'subjects_display' => $data['subjects'],
            ];
        });
    }

    /**
     * Crée un compte enseignant pour chaque ligne valide (les lignes en
     * erreur sont ignorées), avec affectation matières/classes.
     *
     * @param  Collection<int, array>  $validatedRows
     * @return array{created: int, skipped: int, credentials: list<array>}
     */
    public function commit(Collection $validatedRows, int $schoolId): array
    {
        $created = 0;
        $credentials = [];

        DB::transaction(function () use ($validatedRows, $schoolId, &$created, &$credentials) {
            foreach ($validatedRows as $row) {
                if (! empty($row['errors'])) {
                    continue;
                }

                $data = $row['data'];
                $identifier = SchoolUserIdentifier::next($schoolId, 'P');
                $plainPassword = Str::password(10, symbols: false);
                $fullName = trim($data['first_name'].' '.$data['last_name']);

                $teacher = (new User)->forceFill([
                    'name'             => $fullName,
                    'first_name'       => $data['first_name'],
                    'last_name'        => $data['last_name'],
                    'email'            => $data['email'],
                    'identifier'       => $identifier,
                    'password'         => Hash::make($plainPassword),
                    'role'             => User::ROLE_TEACHER,
                    'status'           => User::STATUS_APPROVED,
                    'school_id'        => $schoolId,
                    'phone'            => $data['phone'] !== '' ? $data['phone'] : null,
                    'address'          => $data['address'] !== '' ? $data['address'] : null,
                    'date_of_birth'    => $data['date_of_birth_parsed'] ?? null,
                    'email_verified_at' => now(),
                ]);
                $teacher->save();

                // Classes délibérément non affectées ici — voir la note en
                // tête de classe : l'admin les assigne manuellement après import.
                $this->teaching->sync($teacher, $row['subject_ids'], []);

                $created++;
                $credentials[] = [
                    'identifier' => $identifier,
                    'name' => $fullName,
                    'email' => $data['email'],
                    'password' => $plainPassword,
                    'subjects' => $row['subjects_display'],
                ];
            }
        });

        return [
            'created' => $created,
            'skipped' => $validatedRows->count() - $created,
            'credentials' => $credentials,
        ];
    }

    /**
     * Résout une liste de noms séparés par « ; » contre une collection de
     * modèles ayant un attribut `name` — utilisé pour les matières.
     *
     * @return array{0: list<int>, 1: list<string>} [ids résolus, noms non trouvés]
     */
    private function resolveNames(string $text, Collection $models): array
    {
        if ($text === '') {
            return [[], []];
        }

        $ids = [];
        $unresolved = [];

        foreach (explode(';', $text) as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }

            $needle = TextNormalizer::normalize($name);
            $match = $models->first(fn ($model) => TextNormalizer::normalize($model->name) === $needle);

            if ($match) {
                $ids[] = $match->id;
            } else {
                $unresolved[] = $name;
            }
        }

        return [array_values(array_unique($ids)), $unresolved];
    }
}
