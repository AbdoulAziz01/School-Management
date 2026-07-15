<?php

namespace App\Support;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * Traduit une entrée du journal d'activité (Spatie Activitylog) en une
 * phrase simple, compréhensible par un non-développeur — remplace
 * l'affichage brut "champ: ancienne_valeur → nouvelle_valeur" qui n'a de
 * sens que pour un développeur.
 */
class ActivityLogPresenter
{
    private const RESOURCE_LABELS = [
        'App\\Models\\User' => 'Élève',
        'App\\Models\\Grade' => 'Note',
        'App\\Models\\Attendance' => 'Présence',
        'App\\Models\\SchoolClass' => 'Classe',
        'App\\Models\\School' => 'Établissement',
        'App\\Models\\AcademicYear' => 'Année scolaire',
        'App\\Models\\Subject' => 'Matière',
        'App\\Models\\FeeType' => 'Type de frais',
        'App\\Models\\FeeAmount' => 'Montant de frais',
        'App\\Models\\EmployeeSalaryProfile' => 'Salaire employé',
        'App\\Models\\StudentInvoice' => 'Facture élève',
        'App\\Models\\Payment' => 'Paiement',
        'App\\Models\\CashSession' => 'Session de caisse',
    ];

    private const FIELD_LABELS = [
        'name' => 'Nom',
        'first_name' => 'Prénom',
        'last_name' => 'Nom de famille',
        'email' => 'Email',
        'role' => 'Rôle',
        'status' => 'Statut',
        'class_id' => 'Classe',
        'grade' => 'Note',
        'type' => 'Type d\'évaluation',
        'semester' => 'Semestre',
        'coefficient' => 'Coefficient',
        'is_current' => 'Année courante',
        'is_closed' => 'Clôturée',
        'is_active' => 'Active',
        'capacity' => 'Capacité',
        'level_id' => 'Niveau',
        'academic_year_id' => 'Année académique',
        'code' => 'Code',
        'slug' => 'Identifiant',
        'establishment_type' => 'Type d\'établissement',
        'date' => 'Date',
        'justification_status' => 'Justification',
        'subject_id' => 'Matière',
        'user_id' => 'Élève',
        'teacher_id' => 'Enseignant',
        'school_id' => 'Établissement',
        'amount' => 'Montant',
        'monthly_amount' => 'Salaire mensuel',
        'is_recurring' => 'Récurrent',
        'category' => 'Catégorie',
        'effective_from' => 'Applicable à partir du',
        'effective_to' => 'Applicable jusqu\'au',
        'fee_type_id' => 'Type de frais',
    ];

    /** Champs volontairement masqués : techniques ou déjà exprimés ailleurs dans la phrase. */
    private const HIDDEN_FIELDS = ['teacher_edited_at'];

    private const VALUE_LABELS = [
        'status' => ['pending' => 'En attente', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'],
        'justification_status' => ['pending' => 'En attente', 'approved' => 'Approuvée', 'rejected' => 'Rejetée'],
        'role' => [
            'admin' => 'Administrateur',
            'teacher' => 'Enseignant',
            'professeur' => 'Enseignant',
            'eleve' => 'Élève',
            'student' => 'Élève',
            'surveillant' => 'Surveillant',
            'super_admin' => 'Super administrateur',
        ],
        'type' => ['devoir1' => 'Devoir 1', 'devoir2' => 'Devoir 2', 'composition' => 'Composition'],
    ];

    public static function resourceLabel(?string $subjectType): string
    {
        return self::RESOURCE_LABELS[$subjectType] ?? class_basename($subjectType ?? '') ?: 'Élément';
    }

    public static function describe(Activity $log): string
    {
        if ($log->subject_type === Grade::class) {
            return self::describeGrade($log);
        }

        return self::describeGeneric($log);
    }

    private static function describeGrade(Activity $log): string
    {
        /** @var Grade|null $grade */
        $grade = $log->subject;
        $new = $log->properties['attributes'] ?? [];
        $old = $log->properties['old'] ?? [];

        // Lecture directe (sans passer par les relations Eloquent, dont le
        // lazy loading est désactivé) : de simples colonnes/lookups par id.
        $studentName = $grade?->user_id ? User::withoutGlobalScopes()->find($grade->user_id)?->name : null;
        $subjectName = $grade?->subject_id ? Subject::withoutGlobalScopes()->find($grade->subject_id)?->name : null;
        $studentName ??= 'un élève';
        $subjectName ??= 'une matière';

        if ($log->event === 'deleted') {
            return "Note supprimée : {$studentName} — {$subjectName}.";
        }

        $isCorrection = array_key_exists('teacher_edited_at', $new) && $new['teacher_edited_at'] !== null;

        if (array_key_exists('grade', $new)) {
            $newGrade = self::formatGrade($new['grade']);

            if (array_key_exists('grade', $old)) {
                $oldGrade = self::formatGrade($old['grade']);
                $verb = $isCorrection ? 'Note corrigée' : 'Note modifiée';

                return "{$verb} : {$studentName} — {$subjectName} : {$oldGrade}/20 → {$newGrade}/20.";
            }

            $typeLabel = self::VALUE_LABELS['type'][$new['type'] ?? ''] ?? ($new['type'] ?? '');

            return "Nouvelle note : {$studentName} — {$subjectName} : {$newGrade}/20 ({$typeLabel}).";
        }

        return "Note modifiée : {$studentName} — {$subjectName}.";
    }

    private static function describeGeneric(Activity $log): string
    {
        $resource = self::resourceLabel($log->subject_type);
        $new = $log->properties['attributes'] ?? [];
        $old = $log->properties['old'] ?? [];

        if ($log->event === 'deleted') {
            return "{$resource} supprimé(e).";
        }

        $changes = [];
        foreach ($new as $field => $value) {
            if (in_array($field, self::HIDDEN_FIELDS, true)) {
                continue;
            }

            $label = self::FIELD_LABELS[$field] ?? $field;
            $newLabel = self::formatValue($field, $value);

            if (array_key_exists($field, $old)) {
                $oldLabel = self::formatValue($field, $old[$field]);
                $changes[] = "{$label} : {$oldLabel} → {$newLabel}";
            } else {
                $changes[] = "{$label} : {$newLabel}";
            }
        }

        if (empty($changes)) {
            return $log->event === 'created' ? "{$resource} créé(e)." : "{$resource} modifié(e).";
        }

        $verb = $log->event === 'created' ? 'Création' : 'Modification';

        return "{$verb} — " . implode(' · ', $changes);
    }

    private static function formatValue(string $field, mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_bool($value) || in_array($field, ['is_current', 'is_closed', 'is_active'], true)) {
            return $value ? 'Oui' : 'Non';
        }

        if (isset(self::VALUE_LABELS[$field][$value])) {
            return self::VALUE_LABELS[$field][$value];
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    private static function formatGrade(mixed $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
