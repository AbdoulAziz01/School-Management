<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToSchool;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'subject_id',
        'teacher_id',
        'class_id',
        'date',
        'status', // present, absent, late, excused
        'reason',
        'justified',
        'justified_at',
        'justification_status',
        'session_type',
        'minutes_late',
        'start_time',
        'end_time',
        'classroom',
        'school_id',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'minutes_late' => 'integer',
        'justified_at' => 'datetime',
    ];

    // Constantes pour les statuts de présence
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EXCUSED = 'excused';

    // Constantes pour les statuts de justification
    public const JUSTIFICATION_PENDING = 'pending';
    public const JUSTIFICATION_APPROVED = 'approved';
    public const JUSTIFICATION_REJECTED = 'rejected';

    /**
     * Relation avec l'utilisateur (élève).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Alias pour compatibilité - Relation avec l'étudiant.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relation avec la matière.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relation avec l'enseignant.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Relation avec la classe.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Vérifie si l'absence est justifiée.
     */
    public function isJustified()
    {
        return $this->justification_status === self::JUSTIFICATION_APPROVED;
    }

    /**
     * Vérifie si l'absence est en attente de justification.
     */
    public function isPendingJustification()
    {
        return $this->justification_status === self::JUSTIFICATION_PENDING;
    }

    /**
     * Vérifie si l'utilisateur est en retard.
     */
    public function isLate()
    {
        return $this->status === self::STATUS_LATE;
    }

    /**
     * Vérifie si l'utilisateur est absent.
     */
    public function isAbsent()
    {
        return $this->status === self::STATUS_ABSENT;
    }

    /**
     * Vérifie si l'utilisateur est présent.
     */
    public function isPresent()
    {
        return $this->status === self::STATUS_PRESENT;
    }

    /**
     * Vérifie si l'absence est excusée.
     */
    public function isExcused()
    {
        return $this->status === self::STATUS_EXCUSED;
    }

    /** Alias utilisé dans les formulaires (colonne DB : reason). */
    public function getNotesAttribute(): ?string
    {
        return $this->reason;
    }
}
