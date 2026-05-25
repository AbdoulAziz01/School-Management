<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'login_credentials_snapshot')) {
                $table->json('login_credentials_snapshot')->nullable()->after('formation_use_lmd');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'login_credentials_snapshot')) {
                $table->dropColumn('login_credentials_snapshot');
            }
        });
    }
};
