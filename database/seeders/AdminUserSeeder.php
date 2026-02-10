<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'user_id' => 'ADM01',
            'identifier' => 'ADM01',
            'name' => 'Abdoul Aziz Gueye',
            'email' => 'gueyeabdoulaziz111@gmail.com',
            'password' => Hash::make('passer01'),
            'role' => 'admin',
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);
    }
}
