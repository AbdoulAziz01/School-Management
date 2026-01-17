<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    protected $table = 'classes'; // Ajout de cette ligne pour spécifier le nom de la table
    
    protected $fillable = [
        'name',
        'academic_year_id',
        'level_id',
        'capacity',
        'description'
    ];

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
     * Convertit les chiffres en lettres pour les noms de classe
     * Exemple : "6eme 1" devient "6ème A"
     */
    public function getNameAttribute($value)
    {
        // Si la valeur est vide, on la retourne telle quelle
        if (empty($value)) {
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