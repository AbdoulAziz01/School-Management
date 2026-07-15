<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class School extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'code', 'is_active', 'establishment_type', 'establishment_category'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Établissement {$eventName}");
    }

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

    /**
     * Axe juridique/statutaire, indépendant de establishment_type (qui est
     * purement pédagogique). Conditionne les modules activables par défaut
     * (ex. comptabilité complète pour le privé) — voir App\Support\SchoolModules.
     */
    public const CATEGORY_PUBLIC = 'public';

    public const CATEGORY_PRIVATE = 'private';

    public const ESTABLISHMENT_CATEGORIES = [
        self::CATEGORY_PUBLIC => 'Public',
        self::CATEGORY_PRIVATE => 'Privé',
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
        'establishment_category',
        'enabled_modules',
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
        'formation_lmd_settings',
        'formation_use_lmd',
        'login_credentials_snapshot',
        'card_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_academic_year_id' => 'integer',
        'formation_lmd_settings' => 'array',
        'formation_use_lmd' => 'boolean',
        'login_credentials_snapshot' => 'array',
        'card_settings' => 'array',
        'enabled_modules' => 'array',
    ];

    protected $attributes = [
        'formation_use_lmd' => true,
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

    public function hasEstablishmentType(): bool
    {
        return $this->establishment_type !== null && $this->establishment_type !== '';
    }

    /** Classes Bootstrap pour badge d’affichage plateforme. */
    public function establishmentTypeBadgeClass(): string
    {
        return match ($this->establishment_type) {
            self::TYPE_PRIMAIRE  => 'bg-info text-dark',
            self::TYPE_COLLEGE   => 'bg-primary',
            self::TYPE_LYCEE     => 'bg-success',
            self::TYPE_MIXTE     => 'bg-warning text-dark',
            self::TYPE_FORMATION => 'bg-secondary',
            default              => 'bg-light text-dark border',
        };
    }

    /** Résumé des niveaux / parcours gérés selon le type. */
    public function establishmentTypeDescription(): ?string
    {
        if (! $this->hasEstablishmentType()) {
            return null;
        }

        if ($this->isFormation()) {
            $base = 'École de formation professionnelle — promotions et modules personnalisés.';

            return $this->usesLmdGrading()
                ? $base.' Système LMD (CC + examen).'
                : $base.' Notes type école classique (sans LMD).';
        }

        return match ($this->establishment_type) {
            self::TYPE_PRIMAIRE => 'Primaire : classes CI, CP, CE1, CE2, CM1, CM2.',
            self::TYPE_COLLEGE => 'Collège : 6ème, 5ème, 4ème, 3ème.',
            self::TYPE_LYCEE => 'Lycée : Seconde, Première, Terminale.',
            self::TYPE_MIXTE => 'Mixte : primaire (CI → CM2) + collège + lycée. Passage CM2 → 6ème possible.',
            default => null,
        };
    }

    public function isFormation(): bool
    {
        return $this->establishment_type === self::TYPE_FORMATION;
    }

    public function isClassic(): bool
    {
        return ! $this->isFormation();
    }

    public function isMixte(): bool
    {
        return $this->establishment_type === self::TYPE_MIXTE;
    }

    public function isPrimaireEstablishment(): bool
    {
        return $this->establishment_type === self::TYPE_PRIMAIRE;
    }

    public function establishmentCategoryLabel(): ?string
    {
        return self::ESTABLISHMENT_CATEGORIES[$this->establishment_category] ?? null;
    }

    public function isPublicEstablishment(): bool
    {
        return $this->establishment_category === self::CATEGORY_PUBLIC;
    }

    public function isPrivateEstablishment(): bool
    {
        return $this->establishment_category === self::CATEGORY_PRIVATE;
    }

    /** Formation pro avec calcul des moyennes LMD (CC + examen par module). */
    public function usesLmdGrading(): bool
    {
        return $this->isFormation() && ($this->formation_use_lmd ?? true);
    }

    /** Établissement scolaire classique ou formation sans LMD (notes type devoirs + composition). */
    public function usesClassicGrading(): bool
    {
        return ! $this->usesLmdGrading();
    }

    /** Passage automatique de classe (primaire, collège, lycée). */
    public function supportsAutomaticClassPromotion(): bool
    {
        return $this->usesClassicGrading();
    }
}
