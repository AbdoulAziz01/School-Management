<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\SchoolClass;
use App\Models\Grade;

class User extends Authenticatable
{
    use HasFactory, Notifiable; // ← HasApiTokens RETIRÉ

    // Constantes pour les rôles
    public const ROLE_ADMIN = 'admin';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STUDENT = 'student';

    // Constantes pour les statuts
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'identifier',
        'role',
        'status',
        'class_id',
        'level_id',
        'date_of_birth',
        'phone',
        'address',
        'email_verified_at',
        'desired_class' // ← AJOUTÉ pour la classe souhaitée
    ];
    
    /**
     * Get the class that the user belongs to.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    
    /**
     * Obtenir les notes de l'utilisateur.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relations
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id');
    }


    /**
     * Les classes auxquelles l'enseignant est affecté
     */
    public function assignedClasses()
    {
        if ($this->role === self::ROLE_TEACHER) {
            return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'class_id')
                ->withTimestamps();
        }
        
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'class_id')
            ->withTimestamps()
            ->wherePivot('teacher_id', $this->id);
    }

    // Méthodes utilitaires
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
