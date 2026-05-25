<?php

namespace App\Support;

use App\Models\School;
use App\Models\User;

class SchoolLoginCredentials
{
    /**
     * Données affichables sur la fiche établissement (identifiants + mots de passe connus).
     *
     * @return array{
     *   staff: list<array{name: string, email: string, identifier: string, password: string, role: string}>,
     *   student_default_password: ?string,
     *   student_email_hint: ?string,
     *   source: string
     * }|null
     */
    public static function displayFor(School $school): ?array
    {
        $snapshot = $school->login_credentials_snapshot;
        if (is_array($snapshot) && ! empty($snapshot['staff'])) {
            return [
                'staff' => $snapshot['staff'],
                'student_default_password' => $snapshot['student_default_password'] ?? null,
                'student_email_hint' => $snapshot['student_email_hint'] ?? null,
                'source' => $snapshot['source'] ?? 'stored',
            ];
        }

        return self::inferLoadTestSnapshot($school);
    }

    public static function recordStaffPassword(School $school, User $user, string $password, ?string $roleLabel = null): void
    {
        $snapshot = $school->login_credentials_snapshot ?? ['staff' => [], 'source' => 'platform'];
        $staff = collect($snapshot['staff'] ?? [])
            ->reject(fn (array $row) => ($row['email'] ?? '') === $user->email)
            ->values()
            ->all();

        $staff[] = [
            'name' => $user->name,
            'email' => $user->email,
            'identifier' => $user->identifier,
            'password' => $password,
            'role' => $roleLabel ?? $user->role,
            'updated_at' => now()->toIso8601String(),
        ];

        $snapshot['staff'] = $staff;
        $snapshot['source'] = $snapshot['source'] ?? 'platform';

        $school->forceFill(['login_credentials_snapshot' => $snapshot])->save();
    }

    public static function recordLoadTestDefaults(School $school, User $admin, string $password = 'password'): void
    {
        $school->forceFill([
            'login_credentials_snapshot' => [
                'source' => 'load_test',
                'student_default_password' => $password,
                'student_email_hint' => '*@'.$school->slug.'.edu.sn',
                'staff' => [[
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'identifier' => $admin->identifier,
                    'password' => $password,
                    'role' => User::ROLE_ADMIN,
                    'updated_at' => now()->toIso8601String(),
                ]],
            ],
        ])->save();
    }

    /**
     * @return array{staff: list<array>, student_default_password: string, student_email_hint: string, source: string}|null
     */
    private static function inferLoadTestSnapshot(School $school): ?array
    {
        $password = (string) config('school.load_test_default_password', 'password');
        $pattern = config('school.load_test_admin_email_pattern');

        $admins = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('role', User::ROLE_ADMIN)
            ->orderBy('id')
            ->get();

        $staff = [];
        foreach ($admins as $admin) {
            if (is_string($pattern) && $pattern !== '' && ! preg_match($pattern, (string) $admin->email)) {
                continue;
            }
            $staff[] = [
                'name' => $admin->name,
                'email' => $admin->email,
                'identifier' => $admin->identifier,
                'password' => $password,
                'role' => User::ROLE_ADMIN,
            ];
        }

        if ($staff === []) {
            return null;
        }

        return [
            'staff' => $staff,
            'student_default_password' => $password,
            'student_email_hint' => '*@'.$school->slug.'.edu.sn',
            'source' => 'load_test_inferred',
        ];
    }
}
