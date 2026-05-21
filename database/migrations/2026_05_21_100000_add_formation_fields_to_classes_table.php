<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'promotion_name')) {
                $table->string('promotion_name', 120)->nullable()->after('name');
            }
            if (! Schema::hasColumn('classes', 'filiere')) {
                $table->string('filiere', 120)->nullable()->after('promotion_name');
            }
            if (! Schema::hasColumn('classes', 'formation_year')) {
                $table->string('formation_year', 80)->nullable()->after('filiere');
            }
            if (! Schema::hasColumn('classes', 'description')) {
                $table->text('description')->nullable()->after('capacity');
            }
            if (! Schema::hasColumn('classes', 'room_number')) {
                $table->string('room_number', 20)->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            foreach (['promotion_name', 'filiere', 'formation_year', 'description', 'room_number'] as $column) {
                if (Schema::hasColumn('classes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
