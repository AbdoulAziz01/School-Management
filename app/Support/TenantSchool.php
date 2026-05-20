<?php

namespace App\Support;

use App\Models\User;

class TenantSchool
{
    public const FILTER_KEY = 'tenant_school_filter';

    public const ID_KEY = 'tenant_school_id';

    public static function applyForUser(User $user): void
    {
        if ($user->isSuperAdmin()) {
            session([
                self::FILTER_KEY => false,
            ]);
            session()->forget(self::ID_KEY);

            return;
        }

        session([
            self::FILTER_KEY => true,
            self::ID_KEY => $user->school_id,
        ]);
    }

    public static function clear(): void
    {
        session()->forget([self::FILTER_KEY, self::ID_KEY]);
    }

    public static function shouldFilter(): bool
    {
        return session(self::FILTER_KEY, false) === true
            && session(self::ID_KEY) !== null;
    }

    public static function id(): ?int
    {
        $id = session(self::ID_KEY);

        return $id !== null ? (int) $id : null;
    }
}
