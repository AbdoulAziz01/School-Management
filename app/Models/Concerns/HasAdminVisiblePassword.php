<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Crypt;

trait HasAdminVisiblePassword
{
    public function setAdminVisiblePassword(?string $plainPassword): void
    {
        $this->forceFill([
            'admin_visible_password' => $plainPassword !== null
                ? Crypt::encryptString($plainPassword)
                : null,
        ])->save();
    }

    public function clearAdminVisiblePassword(): void
    {
        if ($this->admin_visible_password === null) {
            return;
        }

        $this->forceFill(['admin_visible_password' => null])->save();
    }

    public function adminVisiblePassword(): ?string
    {
        if (empty($this->admin_visible_password)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->admin_visible_password);
        } catch (\Throwable) {
            return null;
        }
    }

    public function canViewUserPasswords(): bool
    {
        return $this->isSuperAdmin() || $this->role === self::ROLE_ADMIN;
    }
}
