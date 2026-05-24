<?php

namespace App\Support;

use App\Models\Level;
use App\Models\School;
use App\Models\Subject;

/**
 * Catalogue matières type programme sénégalais (collège + lycée).
 * Les coefficients par niveau sont dans level_subject ; subjects garde des valeurs par défaut.
 */
class SchoolSubjectProvisioner
{
    /** @return array<string, array{0: string, 1: string, 2: float}> nom => [code, département, heures/sem] */
    public static function catalog(): array
    {
        return [
            'Français' => ['FR', 'Lettres', 5],
            'Anglais' => ['ANG', 'Langues vivantes', 3],
            'Mathématiques' => ['MATH', 'Sciences', 5],
            'Histoire-Géographie' => ['HG', 'SHS', 4],
            'PC' => ['PC', 'Sciences', 4],
            'SVT' => ['SVT', 'Sciences', 3],
            'ICE' => ['ICE', 'Technologie', 2],
            'Philosophie' => ['PHI', 'Lettres', 2],
            'EPS' => ['EPS', 'EPS', 2],
            'Arabe' => ['AR', 'Langues vivantes', 2],
            'Espagnol' => ['ES', 'Langues vivantes', 2],
        ];
    }

    /** @return array<string, int> */
    public static function coefCollege(): array
    {
        return [
            'Français' => 5,
            'Anglais' => 2,
            'Mathématiques' => 5,
            'Histoire-Géographie' => 4,
            'PC' => 4,
            'SVT' => 3,
            'ICE' => 2,
            'Philosophie' => 1,
            'EPS' => 1,
            'Arabe' => 2,
            'Espagnol' => 2,
        ];
    }

    /** @return array<string, int> */
    public static function coefLycee(): array
    {
        return [
            'Français' => 4,
            'Anglais' => 2,
            'Mathématiques' => 5,
            'Histoire-Géographie' => 3,
            'PC' => 4,
            'SVT' => 4,
            'ICE' => 2,
            'Philosophie' => 3,
            'EPS' => 1,
            'Arabe' => 2,
            'Espagnol' => 2,
        ];
    }

    /**
     * Crée le catalogue matières et les lie aux niveaux de l'établissement si besoin.
     */
    public static function ensureForSchool(?int $schoolId): void
    {
        if (! $schoolId) {
            return;
        }

        $school = School::find($schoolId);
        if ($school?->isFormation()) {
            return;
        }

        SchoolLevelProvisioner::ensureForSchool($schoolId);

        if ($school?->isPrimaireEstablishment()) {
            return;
        }

        self::ensureSubjectsExist($schoolId);
        self::syncLevelSubjectLinks($schoolId);
    }

    private static function ensureSubjectsExist(int $schoolId): void
    {
        foreach (self::catalog() as $name => [$code, $department, $hours]) {
            Subject::withoutGlobalScopes()->firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'code' => $code,
                ],
                [
                    'name' => $name,
                    'coefficient' => 1,
                    'description' => $name.' — programme standard',
                    'department' => $department,
                    'is_active' => true,
                    'hours_per_week' => $hours,
                    'is_core_subject' => true,
                ]
            );
        }
    }

    private static function syncLevelSubjectLinks(int $schoolId): void
    {
        $levels = Level::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->orderBy('order')
            ->get();

        if ($levels->isEmpty()) {
            return;
        }

        $subjectsByCode = Subject::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->get()
            ->keyBy('code');

        $nameByCode = collect(self::catalog())
            ->mapWithKeys(fn ($meta, $name) => [$meta[0] => $name]);

        foreach ($levels as $level) {
            $coefs = $level->cycle === 'college' ? self::coefCollege() : self::coefLycee();

            foreach ($subjectsByCode as $code => $subject) {
                $subjectName = $nameByCode[$code] ?? null;
                if (! $subjectName) {
                    continue;
                }

                $alreadyLinked = $level->subjects()
                    ->where('subjects.id', $subject->id)
                    ->exists();

                if ($alreadyLinked) {
                    continue;
                }

                $level->subjects()->attach($subject->id, [
                    'coefficient' => $coefs[$subjectName] ?? 1,
                    'is_compulsory' => true,
                ]);
            }
        }
    }
}
