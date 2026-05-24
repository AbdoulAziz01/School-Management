<?php

namespace App\Support;

use App\Models\School;
use App\Models\Subject;

/**
 * Paramètres de calcul des moyennes modules (écoles formation / LMD).
 * Chaque module (Subject) possède sa propre pondération CC / Examen.
 */
class FormationLmdSettings
{
    /** @param  list<string>  $ccGradeTypes */
    public function __construct(
        public int $ccWeightPercent = 30,
        public int $examWeightPercent = 70,
        public float $passingGradeMin = 10.0,
        public array $ccGradeTypes = ['devoir1', 'devoir2'],
        public array $examGradeTypes = ['composition'],
    ) {}

    public static function fromSchool(?School $school): self
    {
        if (! $school?->isFormation()) {
            return self::defaults();
        }

        $raw = $school->formation_lmd_settings;
        if (! is_array($raw)) {
            return self::defaults();
        }

        return self::fromArray($raw, self::defaults());
    }

    /**
     * Paramètres du module ; si non configurés, modèle par défaut de l'établissement.
     */
    public static function fromSubject(Subject $subject): self
    {
        $school = $subject->school ?? ($subject->school_id ? School::find($subject->school_id) : null);
        $fallback = self::fromSchool($school);

        $raw = $subject->lmd_settings;
        if (! is_array($raw) || ! array_key_exists('cc_weight_percent', $raw)) {
            return $fallback;
        }

        return self::fromArray($raw, $fallback);
    }

    public static function defaults(): self
    {
        return new self(
            ccWeightPercent: 30,
            examWeightPercent: 70,
            passingGradeMin: (float) config('school.passing_grade_min', 10),
            ccGradeTypes: ['devoir1', 'devoir2'],
            examGradeTypes: ['composition'],
        );
    }

    /** @param  array<string, mixed>  $raw */
    public static function fromArray(array $raw, ?self $fallback = null): self
    {
        $fallback ??= self::defaults();

        $ccTypes = $raw['cc_grade_types'] ?? $fallback->ccGradeTypes;
        $examTypes = $raw['exam_grade_types'] ?? $fallback->examGradeTypes;

        return new self(
            ccWeightPercent: (int) ($raw['cc_weight_percent'] ?? $fallback->ccWeightPercent),
            examWeightPercent: (int) ($raw['exam_weight_percent'] ?? $fallback->examWeightPercent),
            passingGradeMin: (float) ($raw['passing_grade_min'] ?? $fallback->passingGradeMin),
            ccGradeTypes: self::normalizeTypes(is_array($ccTypes) ? $ccTypes : $fallback->ccGradeTypes),
            examGradeTypes: self::normalizeTypes(is_array($examTypes) ? $examTypes : $fallback->examGradeTypes),
        );
    }

    /** @param  array<string, mixed>  $validated */
    public static function fromValidated(array $validated): self
    {
        return new self(
            ccWeightPercent: (int) $validated['cc_weight_percent'],
            examWeightPercent: (int) $validated['exam_weight_percent'],
            passingGradeMin: (float) $validated['passing_grade_min'],
            ccGradeTypes: array_values($validated['cc_grade_types'] ?? ['devoir1', 'devoir2']),
            examGradeTypes: array_values($validated['exam_grade_types'] ?? ['composition']),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'cc_weight_percent' => $this->ccWeightPercent,
            'exam_weight_percent' => $this->examWeightPercent,
            'passing_grade_min' => $this->passingGradeMin,
            'cc_grade_types' => $this->ccGradeTypes,
            'exam_grade_types' => $this->examGradeTypes,
        ];
    }

    public function persistToSchool(School $school): void
    {
        $school->update(['formation_lmd_settings' => $this->toArray()]);
    }

    public function persistToSubject(Subject $subject): void
    {
        $subject->update(['lmd_settings' => $this->toArray()]);
    }

    public function ccWeightRatio(): float
    {
        return max(0, min(1, $this->ccWeightPercent / 100));
    }

    public function examWeightRatio(): float
    {
        return max(0, min(1, $this->examWeightPercent / 100));
    }

    public function formulaLabel(): string
    {
        return "CC {$this->ccWeightPercent} % + Examen {$this->examWeightPercent} % (sinon note unique à 100 %)";
    }

    public function shortLabel(): string
    {
        return "CC {$this->ccWeightPercent} % / Examen {$this->examWeightPercent} %";
    }

    /** @return list<string> */
    public static function allSelectableGradeTypes(): array
    {
        return SenegalGradeSequence::ORDER;
    }

    /** @return array<string, string> */
    public static function validationRules(): array
    {
        return [
            'cc_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'exam_weight_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'passing_grade_min' => ['required', 'numeric', 'min:0', 'max:20'],
            'cc_grade_types' => ['nullable', 'array'],
            'cc_grade_types.*' => ['string', \Illuminate\Validation\Rule::in(SenegalGradeSequence::ORDER)],
            'exam_grade_types' => ['nullable', 'array'],
            'exam_grade_types.*' => ['string', \Illuminate\Validation\Rule::in(SenegalGradeSequence::ORDER)],
        ];
    }

    /** @param  list<string>  $types */
    private static function normalizeTypes(array $types): array
    {
        return array_values(array_unique(array_filter(
            array_map('strval', $types),
            fn (string $t) => $t !== ''
        )));
    }
}
