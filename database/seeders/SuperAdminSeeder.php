<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('SUPER_ADMIN_EMAIL et SUPER_ADMIN_PASSWORD requis dans .env');

            return;
        }

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SUPER_ADMIN_NAME', 'Super Administrateur'),
                'identifier' => 'SA'.date('Y').'001',
                'user_id' => 'SA'.date('Y').'001',
                'password' => Hash::make($password),
                'role' => User::ROLE_SUPER_ADMIN,
                'status' => User::STATUS_APPROVED,
                'school_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
