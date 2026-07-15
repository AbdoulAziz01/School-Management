<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SchoolClass extends Model
{
    use BelongsToSchool, LogsActivity;

    protected $table = 'classes';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'academic_year_id', 'level_id', 'capacity', 'school_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Classe {$eventName}");
    }

    protected $fillable = [
        'name',
        'promotion_name',
        'filiere',
        'diploma_type',
        'formation_year',
        'formation_department_id',
        'formation_promotion_id',
        'academic_year_id',
        'level_id',
        'capacity',
        'description',
        'room_number',
        'school_id',
    ];

    public function formationDepartment(): BelongsTo
    {
        return $this->belongsTo(FormationDepartment::class, 'formation_department_id');
    }

    public function formationPromotion(): BelongsTo
    {
        return $this->belongsTo(FormationPromotion::class, 'formation_promotion_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public static function column(string $name): string
    {
        return (new static)->getTable().'.'.$name;
    }

    /**
     * CI → CP → … → CM2 → 6ème → … → Terminale, puis nom de classe (A, B…).
     * Tri par sous-requête (évite l’ambiguïté school_id avec un JOIN sur levels).
     */
    public function scopeOrderedByLevel(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();
        $cycleSql = Level::cycleOrderSql('l.cycle');

        return $query
            ->orderByRaw(
                "(SELECT {$cycleSql} FROM levels AS l WHERE l.id = {$table}.level_id LIMIT 1) ASC"
            )
            ->orderByRaw(
                "(SELECT l.order FROM levels AS l WHERE l.id = {$table}.level_id LIMIT 1) ASC"
            )
            ->orderBy("{$table}.name");
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id')
            ->whereIn('role', ['student', 'eleve'])
            ->where('status', 'approved');
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }

    /**
     * Les matières associées à cette classe
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withTimestamps();
    }

    /**
     * Les enseignants affectés à cette classe
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_teacher', 'class_id', 'teacher_id')
            ->withTimestamps()
            ->where('role', 'teacher');
    }

    /**
     * Les groupes de cette classe
     */
    public function groups(): HasMany
    {
        return $this->hasMany(ClassGroup::class, 'school_class_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    /**
     * Groupe d'une école de formation (promotion / filière).
     */
    public function isFormationGroup(): bool
    {
        return ! empty($this->attributes['diploma_type'] ?? null)
            || ! empty($this->attributes['filiere'] ?? null)
            || ! empty($this->attributes['promotion_name'] ?? null);
    }

    /**
     * Libellé affiché : niveau + section (ex. « 3ème C » au lieu de « C » seul).
     */
    public function getDisplayNameAttribute(): string
    {
        $formatted = (string) $this->name;
        $rawName = trim((string) $this->getRawOriginal('name'));
        $levelName = $this->relationLoaded('level')
            ? trim((string) ($this->level?->name ?? ''))
            : '';

        if ($levelName === '') {
            return $formatted;
        }

        $isSectionOnly = preg_match('/^\d+$/', $rawName) === 1
            || preg_match('/^[A-Z]$/i', $rawName) === 1;

        if ($isSectionOnly) {
            return $levelName.' '.$formatted;
        }

        $normalize = static fn (string $value): string => strtolower(preg_replace('/\s+/', '', $value) ?? $value);
        $levelKey = $normalize($levelName);
        $nameKey = $normalize($rawName);

        if ($nameKey !== '' && (str_starts_with($nameKey, $levelKey) || str_starts_with($levelKey, $nameKey))) {
            return $formatted;
        }

        return $formatted;
    }

    /**
     * Affichage du nom : conserve CI, CE1, CM2… ; convertit seulement les sections « 6ème 1 » → « 6ème A ».
     */
    public function getNameAttribute($value)
    {
        if (empty($value) || $this->isFormationGroup()) {
            return $value;
        }

        $raw = trim((string) $value);

        if (preg_match('/^(CI|CP|CE1|CE2|CM1|CM2)$/iu', $raw)) {
            return $this->applyClassNameSpelling($raw);
        }

        if (preg_match('/^(CI|CP|CE1|CE2|CM1|CM2)\s+[A-Z]$/iu', $raw)) {
            return $this->applyClassNameSpelling($raw);
        }

        $withSections = preg_replace_callback(
            '/\s+(\d+)$/',
            static function (array $matches): string {
                $number = (int) $matches[1];
                if ($number >= 1 && $number <= 26) {
                    return ' '.chr(64 + $number);
                }

                return $matches[0];
            },
            $raw
        );

        return $this->applyClassNameSpelling($withSections ?? $raw);
    }

    private function applyClassNameSpelling(string $value): string
    {
        $replacements = [
            '/\b6eme\b/i' => '6ème',
            '/\b5eme\b/i' => '5ème',
            '/\b4eme\b/i' => '4ème',
            '/\b3eme\b/i' => '3ème',
            '/\b2nde\b/i' => 'Seconde',
            '/\b1ere\b/i' => 'Première',
            '/\bTle\b/i'  => 'Terminale',
            '/\bTerm\b/i' => 'Terminale',
        ];

        return preg_replace(array_keys($replacements), array_values($replacements), $value);
    }
}