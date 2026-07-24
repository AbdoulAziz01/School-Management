<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlatformBrandingComposer
{
    public function compose(View $view): void
    {
        $platformName = config('platform.name', 'AzelieEdu');
        $user = Auth::user();

        $view->with([
            'platformName' => $platformName,
            'platformUser' => $user,
            'platformUserName' => $user?->name ?? '',
            'platformUserEmail' => $user?->email ?? '',
            'platformUserInitials' => $this->initials($user?->name),
        ]);
    }

    private function initials(?string $name): string
    {
        if (! $name) {
            return '??';
        }

        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) >= 2) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }
}
