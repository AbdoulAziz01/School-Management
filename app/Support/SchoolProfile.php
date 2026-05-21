<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Validation\Rule;

class SchoolProfile
{
    /**
     * @return array<string, mixed>
     */
    public static function officialRules(?int $schoolId = null): array
    {
        return [
            'name'                 => ['required', 'string', 'max:255'],
            'establishment_type'   => ['nullable', Rule::in(array_keys(School::ESTABLISHMENT_TYPES))],
            'motto'                => ['nullable', 'string', 'max:255'],
            'authorization_number' => ['nullable', 'string', 'max:100'],
            'director_name'        => ['nullable', 'string', 'max:255'],
            'deputy_director_name' => ['nullable', 'string', 'max:255'],
            'region'               => ['nullable', 'string', 'max:100'],
            'department'           => ['nullable', 'string', 'max:100'],
            'timezone'             => ['required', Rule::in(array_keys(School::TIMEZONES))],
            'locale'               => ['required', Rule::in(array_keys(School::LOCALES))],
            'default_academic_year_id' => array_filter([
                'nullable',
                'integer',
                $schoolId
                    ? Rule::exists('academic_years', 'id')->where('school_id', $schoolId)
                    : null,
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function operationalRules(): array
    {
        return [
            'email'             => ['nullable', 'email', 'max:255'],
            'secretariat_email' => ['nullable', 'email', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:50'],
            'phone_secondary'   => ['nullable', 'string', 'max:50'],
            'whatsapp'          => ['nullable', 'string', 'max:50'],
            'website'           => ['nullable', 'url', 'max:255'],
            'city'              => ['nullable', 'string', 'max:100'],
            'district'          => ['nullable', 'string', 'max:100'],
            'address'           => ['nullable', 'string', 'max:500'],
            'description'       => ['nullable', 'string', 'max:5000'],
            'secretariat_hours' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fullRules(?int $schoolId = null): array
    {
        return array_merge(
            self::officialRules($schoolId),
            self::operationalRules(),
            [
                'logo'        => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
                'remove_logo' => ['sometimes', 'boolean'],
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function officialAttributes(array $validated): array
    {
        return [
            'name'                     => $validated['name'],
            'establishment_type'       => $validated['establishment_type'] ?? null,
            'motto'                    => $validated['motto'] ?? null,
            'authorization_number'     => $validated['authorization_number'] ?? null,
            'director_name'            => $validated['director_name'] ?? null,
            'deputy_director_name'     => $validated['deputy_director_name'] ?? null,
            'region'                   => $validated['region'] ?? null,
            'department'               => $validated['department'] ?? null,
            'timezone'                 => $validated['timezone'],
            'locale'                   => $validated['locale'],
            'default_academic_year_id' => $validated['default_academic_year_id'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function operationalAttributes(array $validated): array
    {
        return [
            'email'             => $validated['email'] ?? null,
            'secretariat_email' => $validated['secretariat_email'] ?? null,
            'phone'             => $validated['phone'] ?? null,
            'phone_secondary'   => $validated['phone_secondary'] ?? null,
            'whatsapp'          => $validated['whatsapp'] ?? null,
            'website'           => $validated['website'] ?? null,
            'city'              => $validated['city'] ?? null,
            'district'          => $validated['district'] ?? null,
            'address'           => $validated['address'] ?? null,
            'description'       => $validated['description'] ?? null,
            'secretariat_hours' => $validated['secretariat_hours'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function fullAttributes(array $validated): array
    {
        return array_merge(
            self::officialAttributes($validated),
            self::operationalAttributes($validated)
        );
    }
}
