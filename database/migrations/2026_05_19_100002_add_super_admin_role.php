<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'teacher', 'eleve') DEFAULT 'eleve'");
        }

        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $exists = DB::table('users')->where('email', $email)->exists();
        if ($exists) {
            DB::table('users')->where('email', $email)->update([
                'role' => 'super_admin',
                'school_id' => null,
                'status' => 'approved',
                'updated_at' => now(),
            ]);

            return;
        }

        $identifier = 'SA' . date('Y') . '001';

        DB::table('users')->insert([
            'name' => env('SUPER_ADMIN_NAME', 'Super Administrateur'),
            'email' => $email,
            'identifier' => $identifier,
            'user_id' => $identifier,
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'status' => 'approved',
            'school_id' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');
        if ($email) {
            DB::table('users')->where('email', $email)->where('role', 'super_admin')->delete();
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'eleve') DEFAULT 'eleve'");
        }
    }
};
