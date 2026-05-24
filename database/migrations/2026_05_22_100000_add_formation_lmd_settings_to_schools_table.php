<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'formation_lmd_settings')) {
                $table->json('formation_lmd_settings')->nullable()->after('establishment_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'formation_lmd_settings')) {
                $table->dropColumn('formation_lmd_settings');
            }
        });
    }
};
