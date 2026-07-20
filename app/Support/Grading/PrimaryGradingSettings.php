<?php

namespace App\Support\Grading;

use App\Models\Level;
use App\Models\Subject;

/**
 * Paramètres de notation du primaire, par (Level, Subject) — note
 * maximale, coefficient, nombre de compositions, type d'évaluation.
 * Calqué sur App\Support\FormationLmdSettings (même patron : value
 * object + résolution en cascade + validationRules() statique), mais
 * résolu par niveau+matière (via le pivot level_subject) plutôt que par
 * matière seule, car une matière peut avoir des réglages différents
 * entre CI et CM2.
 *
 * Aucune valeur n'est codée en dur dans le moteur de notation : tant
 * qu'une matière n'a pas été configurée par l'admin, defaults() fournit
 * des valeurs de repli (10/1/3 compositions, 2 pour la CM2) — jamais un
 * échec silencieux ni un blocage de la saisie.
 */
class PrimaryGradingSettings
{
    public function __construct(
        public float $maxGrade = 10.0,
        public float $coefficient = 1.0,
        public int $compositionsCount = 3,
        public string $evaluationType = 'composition',
        public bool $isActive = true,
    ) {}

    public static function fromLevelSubject(Level $level, Subject $subject): self
    {
        $fallback = self::defaults($level);

        $pivot = $subject->levels()
            ->where('levels.id', $level->id)
            ->first()
            ?->pivot;

        if (! $pivot) {
            return $fallback;
        }

        return new self(
            maxGrade: $pivot->max_grade !== null ? (float) $pivot->max_grade : $fallback->maxGrade,
            coefficient: $pivot->coefficient !== null ? (float) $pivot->coefficient : $fallback->coefficient,
            compositionsCount: $pivot->compositions_count !== null ? (int) $pivot->compositions_count : $fallback->compositionsCount,
            evaluationType: $pivot->evaluation_type ?? $fallback->evaluationType,
            isActive: $pivot->is_active === null ? true : (bool) $pivot->is_active,
        );
    }

    /** CM2 prépare le CFEE en 2 compositions par défaut (activable à 3 depuis l'admin) ; les autres niveaux du primaire en ont 3. */
    public static function defaults(Level $level): self
    {
        return new self(
            maxGrade: 10.0,
            coefficient: 1.0,
            compositionsCount: $level->isCm2() ? 2 : 3,
            evaluationType: 'composition',
            isActive: true,
        );
    }

    /** @param  array<string, mixed>  $validated */
    public static function fromValidated(array $validated, Level $level): self
    {
        $fallback = self::defaults($level);

        return new self(
            maxGrade: (float) ($validated['max_grade'] ?? $fallback->maxGrade),
            coefficient: (float) ($validated['coefficient'] ?? $fallback->coefficient),
            compositionsCount: (int) ($validated['compositions_count'] ?? $fallback->compositionsCount),
            evaluationType: (string) ($validated['evaluation_type'] ?? $fallback->evaluationType),
            isActive: (bool) ($validated['is_active'] ?? false),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'max_grade' => $this->maxGrade,
            'coefficient' => $this->coefficient,
            'compositions_count' => $this->compositionsCount,
            'evaluation_type' => $this->evaluationType,
            'is_active' => $this->isActive,
        ];
    }

    public function persistTo(Level $level, Subject $subject): void
    {
        $subject->levels()->syncWithoutDetaching([
            $level->id => $this->toArray(),
        ]);
    }

    /** @return array<string, mixed> */
    public static function validationRules(): array
    {
        return [
            'max_grade' => ['required', 'numeric', 'min:1', 'max:1000'],
            'coefficient' => ['required', 'numeric', 'min:0.5', 'max:10'],
            'compositions_count' => ['required', 'integer', 'min:1', 'max:6'],
            'evaluation_type' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
