<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Import\FlexibleDateParser;
use App\Services\Import\SpreadsheetParser;
use App\Services\Import\TextNormalizer;
use App\Support\SchoolUserIdentifier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Import en masse d'élèves depuis un fichier Excel/CSV — analyse des
 * en-têtes, mapping colonnes → champs, validation ligne par ligne
 * (obligatoires, doublons, classe résolue par nom texte libre), puis
 * création directe en base pour les lignes valides.
 *
 * Miroir de la logique de App\Http\Controllers\Admin\StudentController::store()
 * (mêmes règles, mêmes valeurs par défaut) pour que les élèves importés
 * soient indiscernables de ceux créés un par un.
 */
class StudentImportService
{
    /** Champs proposés au mapping, dans l'ordre d'affichage. */
    public const CANONICAL_FIELDS = [
        'last_name'       => ['label' => 'Nom', 'required' => true],
        'first_name'      => ['label' => 'Prénom', 'required' => true],
        'email'           => ['label' => 'Email', 'required' => false],
        'date_of_birth'   => ['label' => 'Date de naissance', 'required' => true],
        'class_name'      => ['label' => 'Classe', 'required' => true],
        'parent_name'     => ['label' => 'Nom du parent', 'required' => false],
        'parent_whatsapp' => ['label' => 'WhatsApp du parent', 'required' => false],
        'parent_lang'     => ['label' => 'Langue du parent', 'required' => false],
    ];

    /** Alias reconnus pour deviner automatiquement le mapping depuis les en-têtes du fichier. */
    private const FIELD_ALIASES = [
        'last_name'       => ['nom', 'lastname', 'nom de famille', 'name'],
        'first_name'      => ['prenom', 'prénom', 'firstname'],
        'email'           => ['email', 'e-mail', 'mail', 'courriel'],
        'date_of_birth'   => ['date de naissance', 'naissance', 'date naissance', 'né le', 'nee le', 'dob'],
        'class_name'      => ['classe', 'niveau', 'class'],
        'parent_name'     => ['parent', 'nom du parent', 'tuteur', 'nom du tuteur', 'nom parent tuteur'],
        'parent_whatsapp' => ['whatsapp', 'telephone', 'téléphone', 'contact', 'tel', 'numero', 'numéro'],
        'parent_lang'     => ['langue', 'langue du parent', 'lang'],
    ];

    /** @return array{headers: list<string>, rows: list<list<mixed>>} */
    public function parseFile(string $absolutePath): array
    {
        return SpreadsheetParser::parse($absolutePath);
    }

    /**
     * Devine le mapping en-tête → champ canonique par correspondance
     * approximative (accents/casse ignorés) sur la liste d'alias.
     *
     * @param  list<string>  $headers
     * @return array<string, int|null> champ canonique => index de colonne (ou null si pas trouvé)
     */
    public function guessMapping(array $headers): array
    {
        $normalizedHeaders = array_map([self::class, 'normalize'], $headers);
        $mapping = [];

        foreach (self::CANONICAL_FIELDS as $field => $meta) {
            $mapping[$field] = null;
            $aliases = array_map([self::class, 'normalize'], self::FIELD_ALIASES[$field]);

            // Passe 1 : correspondance exacte (ex. "email" === "email").
            foreach ($normalizedHeaders as $index => $header) {
                if (in_array($header, $aliases, true)) {
                    $mapping[$field] = $index;
                    continue 2;
                }
            }

            // Passe 2 : correspondance partielle (ex. "whatsapp du parent" contient "whatsapp").
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
     * Valide chaque ligne du fichier selon le mapping choisi : champs
     * obligatoires, format de date, classe résolue par nom, doublons
     * (dans le fichier et déjà en base).
     *
     * @param  list<list<mixed>>  $rows
     * @param  array<string, int|null>  $mapping
     * @return Collection<int, array{line:int, data:array<string,mixed>, errors:list<string>, class_id:?int}>
     */
    public function validateRows(array $rows, array $mapping, int $schoolId): Collection
    {
        $classes = SchoolClass::where('school_id', $schoolId)->with('level')->get();
        $existingEmails = User::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->whereNotNull('email')
            ->pluck('email')
            ->map(fn ($e) => Str::lower($e))
            ->flip();

        $seenEmailsInFile = [];
        $seenIdentityInFile = [];

        return collect($rows)->map(function (array $row, int $index) use ($mapping, $classes, $existingEmails, &$seenEmailsInFile, &$seenIdentityInFile) {
            $line = $index + 2; // +1 pour l'en-tête, +1 pour passer en numérotation 1-based
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

            $dateOfBirth = null;
            if ($data['date_of_birth'] !== '') {
                $dateOfBirth = self::parseDate($data['date_of_birth']);
                if (! $dateOfBirth) {
                    $errors[] = "Date de naissance invalide : « {$data['date_of_birth']} ».";
                } elseif ($dateOfBirth->isAfter(now())) {
                    $errors[] = 'Date de naissance dans le futur.';
                }
            }

            $classId = null;
            if ($data['class_name'] !== '') {
                $match = self::matchClass($data['class_name'], $classes);
                if ($match) {
                    $classId = $match->id;
                } else {
                    $errors[] = "Classe introuvable : « {$data['class_name']} ». Créez-la d'abord, ou corrigez le nom.";
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

            if ($data['first_name'] !== '' && $data['last_name'] !== '' && $dateOfBirth) {
                $identityKey = Str::lower($data['first_name'].'|'.$data['last_name'].'|'.$dateOfBirth->format('Y-m-d'));
                if (isset($seenIdentityInFile[$identityKey])) {
                    $errors[] = "Doublon probable dans le fichier : même nom/prénom/date de naissance qu'à la ligne {$seenIdentityInFile[$identityKey]}.";
                } else {
                    $seenIdentityInFile[$identityKey] = $line;
                }
            }

            if ($data['parent_lang'] !== '' && ! in_array($data['parent_lang'], ['fr_text', 'wo_audio', 'pu_audio'], true)) {
                $errors[] = "Langue du parent invalide : « {$data['parent_lang']} » (attendu fr_text, wo_audio ou pu_audio).";
            }

            return [
                'line' => $line,
                'data' => array_merge($data, ['date_of_birth_parsed' => $dateOfBirth?->format('Y-m-d')]),
                'errors' => $errors,
                'class_id' => $classId,
                'class_display' => $classId ? $classes->firstWhere('id', $classId)->name : $data['class_name'],
            ];
        });
    }

    /**
     * Crée un utilisateur élève pour chaque ligne valide de $validatedRows
     * (les lignes en erreur sont ignorées). Retourne les identifiants
     * générés pour l'export de communication.
     *
     * @param  Collection<int, array>  $validatedRows
     * @return array{created: int, skipped: int, credentials: list<array>}
     */
    public function commit(Collection $validatedRows, int $schoolId): array
    {
        $created = 0;
        $credentials = [];
        $enrolledStudents = [];

        DB::transaction(function () use ($validatedRows, $schoolId, &$created, &$credentials, &$enrolledStudents) {
            foreach ($validatedRows as $row) {
                if (! empty($row['errors'])) {
                    continue;
                }

                $data = $row['data'];
                $identifier = SchoolUserIdentifier::next($schoolId, 'E');
                $plainPassword = Str::password(10, symbols: false);
                $fullName = trim($data['first_name'].' '.$data['last_name']);
                $rawWhatsapp = preg_replace('/\D/', '', $data['parent_whatsapp'] ?? '') ?: null;

                $student = (new User)->forceFill([
                    'name'            => $fullName,
                    'first_name'      => $data['first_name'],
                    'last_name'       => $data['last_name'],
                    'email'           => $data['email'] !== '' ? $data['email'] : null,
                    'identifier'      => $identifier,
                    'password'        => Hash::make($plainPassword),
                    'role'            => User::ROLE_STUDENT,
                    'status'          => 'approved',
                    'date_of_birth'   => $data['date_of_birth_parsed'],
                    'class_id'        => $row['class_id'],
                    'school_id'       => $schoolId,
                    'parent_name'     => $data['parent_name'] !== '' ? $data['parent_name'] : null,
                    'parent_whatsapp' => $rawWhatsapp,
                    'parent_lang'     => $data['parent_lang'] !== '' ? $data['parent_lang'] : 'fr_text',
                ]);
                $student->save();

                $enrolledStudents[] = $student;
                $created++;
                $credentials[] = [
                    'identifier' => $identifier,
                    'name' => $fullName,
                    'email' => $student->email,
                    'password' => $plainPassword,
                    'class' => $row['class_display'] ?? '—',
                ];
            }
        });

        foreach ($enrolledStudents as $student) {
            \App\Events\StudentEnrolled::dispatch($student);
        }

        return [
            'created' => $created,
            'skipped' => $validatedRows->count() - $created,
            'credentials' => $credentials,
        ];
    }

    private static function matchClass(string $text, Collection $classes): ?SchoolClass
    {
        $needle = self::normalize($text);

        foreach ($classes as $class) {
            if (self::normalize($class->name) === $needle) {
                return $class;
            }
            if (self::normalize($class->getRawOriginal('name')) === $needle) {
                return $class;
            }
        }

        return null;
    }

    private static function parseDate(string $value): ?Carbon
    {
        return FlexibleDateParser::parse($value);
    }

    private static function normalize(string $value): string
    {
        return TextNormalizer::normalize($value);
    }
}
