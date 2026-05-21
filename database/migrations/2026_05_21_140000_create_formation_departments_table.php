<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formation_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'formation_department_id')) {
                $table->foreignId('formation_department_id')
                    ->nullable()
                    ->after('school_id')
                    ->constrained('formation_departments')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'formation_department_id')) {
                $table->dropConstrainedForeignId('formation_department_id');
            }
        });

        Schema::dropIfExists('formation_departments');
    }
};
