<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['identifier']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['school_id', 'identifier'], 'users_school_identifier_unique');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE UNIQUE INDEX users_identifier_platform_unique ON users (identifier) WHERE school_id IS NULL'
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS users_identifier_platform_unique');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_school_identifier_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('identifier');
        });
    }
};
