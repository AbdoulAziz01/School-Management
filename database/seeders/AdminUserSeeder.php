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
                'user_id' => 'ADM01',
                'identifier' => 'ADM01',
                'name' => 'Abdoul Aziz Gueye',
                'password' => Hash::make('passer01'),
                'role' => 'admin',
                'status' => 'approved',
                'school_id' => $school?->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
