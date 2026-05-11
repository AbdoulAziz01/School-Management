<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'eleve') DEFAULT 'eleve'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL : pas de MODIFY/ENUM MySQL — on passe en VARCHAR puis données + défaut
            DB::statement('ALTER TABLE users ALTER COLUMN role DROP DEFAULT');
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING (role::text)');
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'eleve'");
        }

        DB::table('users')
            ->where('role', 'student')
            ->update(['role' => 'eleve']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('role', 'eleve')
            ->update(['role' => 'student']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student') DEFAULT 'student'");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN role DROP DEFAULT');
            DB::statement('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING (role::text)');
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'student'");
        }
    }
};
