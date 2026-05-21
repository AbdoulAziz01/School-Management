<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    use BelongsToSchool;

    protected $table = 'classes';

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
        $this->loadMissing('level');

        $formatted = (string) $this->name;
        $rawName = trim((string) $this->getRawOriginal('name'));
        $levelName = trim((string) ($this->level?->name ?? ''));

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
     * Convertit les chiffres en lettres pour les noms de classe
     * Exemple : "6eme 1" devient "6ème A"
     */
    public function getNameAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        if ($this->isFormationGroup()) {
            return $value;
        }

        // Remplacer les chiffres par des lettres (1 -> A, 2 -> B, etc.)
        $value = preg_replace_callback('/(\d+)(\s|-|$)/', function($matches) {
            $number = (int)$matches[1];
            if ($number >= 1 && $number <= 26) {
                $letter = chr(64 + $number); // 65 = 'A' en ASCII
                return $letter . (isset($matches[2]) ? $matches[2] : '');
            }
            return $matches[0]; // Ne pas modifier si le nombre n'est pas entre 1 et 26
        }, $value);

        // Remplacer les abréviations par leur équivalent en toutes lettres
        $replacements = [
            '/\b6eme\b/i' => '6ème',
            '/\b5eme\b/i' => '5ème',
            '/\b4eme\b/i' => '4ème',
            '/\b3eme\b/i' => '3ème',
            '/\b2nde\b/i' => 'Seconde',
            '/\b1ere\b/i' => 'Première',
            '/\bTle\b/i'  => 'Terminale',
            '/\bTerm\b/i' => 'Terminale'
        ];

        return preg_replace(array_keys($replacements), array_values($replacements), $value);
    }
}