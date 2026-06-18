<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();

        User::withoutGlobalScopes()->updateOrCreate(
            ['email' => 'gueyeabdoulaziz111@gmail.com'],
            [
                'user_id'           => 'SA2026001',
                'identifier'        => 'SA2026001',
                'name'              => 'Abdoul Aziz Gueye',
                'first_name'        => 'Abdoul Aziz',
                'last_name'         => 'Gueye',
                'password'          => Hash::make('passer01@'),
                'role'              => 'super_admin',
                'status'            => 'approved',
                'school_id'         => null,   // super_admin = accès plateforme entière
                'email_verified_at' => now(),
            ]
        );
    }
}
