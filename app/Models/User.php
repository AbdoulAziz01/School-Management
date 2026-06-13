<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\BelongsToSchool;
use App\Models\Concerns\HasAdminVisiblePassword;
use Spatie\Permission\Traits\HasRoles;
use App\Models\SchoolClass;
use App\Models\Grade;

class User extends Authenticatable
{
    use BelongsToSchool, HasAdminVisiblePassword, HasFactory, Notifiable, HasRoles;

    public const ROLE_SUPER_ADMIN = 'super_admin';

    // Constantes pour les rôles (alignées avec l'enum DB après migration 2026_01_12)
    public const ROLE_ADMIN   = 'admin';
    public const ROLE_SURVEILLANT = 'surveillant';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STUDENT = 'eleve';

    /** Comptes avec accès au panneau /admin de l'établissement */
    public const ROLE_SCHOOL_STAFF = [self::ROLE_ADMIN, self::ROLE_SURVEILLANT];

    // Aliases historiques tolérés dans les vérifications de rôle
    public const ROLE_TEACHER_ALIASES = ['teacher', 'professeur'];
    public const ROLE_STUDENT_ALIASES = ['eleve', 'student'];

    // Constantes pour les statuts
    public const STATUS_PENDING  = 'pending';
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
        'class_id',
        'last_promotion_academic_year_id',
        'promotion_source_class_id',
        'date_of_birth',
        'phone',
        'address',
        'invitation_email_sent_at',
        'desired_class',
        'profile_photo_path',
        'city',
        'postal_code',
        'country',
        'gender',
        'guardian_phone',
        'conduct_evaluation',
        'assiduity_comment',
    ];
    
    /**
     * Get the class that the user belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    
    /**
     * Get the teacher's class assignments.
     */
    public function teacherAssignments()
    {
        return $this->hasMany(\App\Models\TeacherAssignment::class, 'teacher_id');
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
        'admin_visible_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'invitation_email_sent_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
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

    /**
     * Obtenir les présences de l'utilisateur.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Obtenir les événements de la classe de l'utilisateur.
     */
    public function events()
    {
        if ($this->schoolClass) {
            return $this->schoolClass->events();
        }
        return collect();
    }

    /**
     * Obtenir les devoirs de l'utilisateur.
     */
    public function assignments()
    {
        if ($this->schoolClass) {
            return $this->schoolClass->assignments();
        }
        return collect();
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
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'class_id')
            ->withTimestamps();
    }

    // Méthodes utilitaires (tolérantes aux alias historiques)
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSurveillant(): bool
    {
        return $this->role === self::ROLE_SURVEILLANT;
    }

    public function isSchoolStaff(): bool
    {
        return in_array($this->role, self::ROLE_SCHOOL_STAFF, true);
    }

    public function isTeacher(): bool
    {
        return in_array($this->role, self::ROLE_TEACHER_ALIASES, true);
    }

    public function isStudent(): bool
    {
        return in_array($this->role, self::ROLE_STUDENT_ALIASES, true);
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
