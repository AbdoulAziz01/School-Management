<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\BelongsToSchool;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\SchoolClass;
use App\Models\Grade;

class User extends Authenticatable
{
    use BelongsToSchool, HasFactory, Notifiable, HasRoles, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'first_name', 'last_name', 'email', 'role', 'status', 'class_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Élève {$eventName}");
    }

    // Langues de communication parent (pour les notifications WhatsApp inclusives)
    public const PARENT_LANG_FR_TEXT  = 'fr_text';
    public const PARENT_LANG_WO_AUDIO = 'wo_audio';
    public const PARENT_LANG_PU_AUDIO = 'pu_audio';
    public const PARENT_LANGS = [self::PARENT_LANG_FR_TEXT, self::PARENT_LANG_WO_AUDIO, self::PARENT_LANG_PU_AUDIO];

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
        'first_name',
        'last_name',
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
        'birth_certificate_path',
        'city',
        'postal_code',
        'country',
        'gender',
        'guardian_phone',
        'conduct_evaluation',
        'assiduity_comment',
        'status',
        'parent_name',
        'parent_whatsapp',
        'parent_lang',
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
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at'        => 'datetime',
        'invitation_email_sent_at' => 'datetime',
        'password'                 => 'hashed',
        'date_of_birth'            => 'date',
        'phone'            => 'encrypted',
        'address'          => 'encrypted',
        'guardian_phone'   => 'encrypted',
        'parent_whatsapp'  => 'encrypted',
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
        $this->loadMissing('schoolClass');
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
        $this->loadMissing('schoolClass');
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

    /** Autorisation d'accès au panneau de révélation ponctuelle des mots de passe générés. */
    public function canViewUserPasswords(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
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

    /**
     * Nom du rôle Spatie canonique correspondant à la valeur (éventuellement
     * un alias historique) actuellement stockée dans users.role.
     */
    public function canonicalRoleName(): ?string
    {
        return match (true) {
            $this->role === self::ROLE_SUPER_ADMIN => self::ROLE_SUPER_ADMIN,
            $this->role === self::ROLE_ADMIN => self::ROLE_ADMIN,
            $this->role === self::ROLE_SURVEILLANT => self::ROLE_SURVEILLANT,
            in_array($this->role, self::ROLE_TEACHER_ALIASES, true) => self::ROLE_TEACHER,
            in_array($this->role, self::ROLE_STUDENT_ALIASES, true) => self::ROLE_STUDENT,
            default => null,
        };
    }

    /**
     * Aligne le rôle Spatie (spatie/laravel-permission) sur users.role, qui
     * reste la source de vérité fonctionnelle. Appelé automatiquement à
     * chaque sauvegarde où le rôle change (voir booted()) et par
     * RolePermissionSeeder pour le rattrapage initial.
     */
    public function syncRoleFromColumn(): void
    {
        $canonical = $this->canonicalRoleName();

        if ($canonical === null) {
            return;
        }

        if (! \Spatie\Permission\Models\Role::where('name', $canonical)->where('guard_name', 'web')->exists()) {
            return;
        }

        $this->syncRoles([$canonical]);
    }

    protected static function booted(): void
    {
        static::saved(function (self $user) {
            if ($user->wasChanged('role') || $user->wasRecentlyCreated) {
                $user->syncRoleFromColumn();
            }
        });
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
