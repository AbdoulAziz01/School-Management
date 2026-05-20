<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class School extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'code',
        'is_active',
        'email',
        'phone',
        'address',
        'city',
        'logo_path',
        'logo_data',
        'logo_mime',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
}
