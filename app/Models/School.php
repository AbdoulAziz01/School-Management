<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class School extends Model
{
    public const TYPE_PRIMAIRE = 'primaire';

    public const TYPE_COLLEGE = 'college';

    public const TYPE_LYCEE = 'lycee';

    public const TYPE_MIXTE = 'mixte';

    public const TYPE_FORMATION = 'formation';

    public const ESTABLISHMENT_TYPES = [
        self::TYPE_PRIMAIRE  => 'Primaire',
        self::TYPE_COLLEGE   => 'Collège',
        self::TYPE_LYCEE     => 'Lycée',
        self::TYPE_MIXTE     => 'Mixte (primaire + collège + lycée)',
        self::TYPE_FORMATION => 'École de formation professionnelle',
    ];

    public const TIMEZONES = [
        'Africa/Dakar' => 'Dakar (UTC+0)',
    ];

    public const LOCALES = [
        'fr' => 'Français',
        'wo' => 'Wolof',
    ];

    protected $fillable = [
        'name',
        'slug',
        'code',
        'is_active',
        'email',
        'phone',
        'phone_secondary',
        'whatsapp',
        'secretariat_email',
        'website',
        'address',
        'city',
        'region',
        'department',
        'district',
        'establishment_type',
        'motto',
        'authorization_number',
        'director_name',
        'deputy_director_name',
        'timezone',
        'locale',
        'description',
        'secretariat_hours',
        'default_academic_year_id',
        'logo_path',
        'logo_data',
        'logo_mime',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_academic_year_id' => 'integer',
    ];

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(4).'-'.Str::random(4));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public static function slugFromName(string $name, ?int $exceptSchoolId = null): string
    {
        $base = Str::slug($name) ?: 'etablissement';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)
            ->when($exceptSchoolId, fn ($q) => $q->where('id', '!=', $exceptSchoolId))
            ->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_ADMIN);
    }

    /** Administrateurs et surveillants de l'établissement */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('role', User::ROLE_SCHOOL_STAFF);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function defaultAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'default_academic_year_id');
    }

    public function establishmentTypeLabel(): ?string
    {
        return self::ESTABLISHMENT_TYPES[$this->establishment_type] ?? null;
    }

    public function isFormation(): bool
    {
        return $this->establishment_type === self::TYPE_FORMATION;
    }

    public function isClassic(): bool
    {
        return ! $this->isFormation();
    }
}
