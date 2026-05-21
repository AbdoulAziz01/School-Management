<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_promotion_academic_year_id')) {
                $table->foreignId('last_promotion_academic_year_id')
                    ->nullable()
                    ->after('class_id')
                    ->constrained('academic_years')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_promotion_academic_year_id')) {
                $table->dropConstrainedForeignId('last_promotion_academic_year_id');
            }
        });
    }
};
