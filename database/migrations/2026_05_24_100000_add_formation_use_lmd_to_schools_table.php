<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'formation_use_lmd')) {
                $table->boolean('formation_use_lmd')->default(true)->after('formation_lmd_settings');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'formation_use_lmd')) {
                $table->dropColumn('formation_use_lmd');
            }
        });
    }
};
