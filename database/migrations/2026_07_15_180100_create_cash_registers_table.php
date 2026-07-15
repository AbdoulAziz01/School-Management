<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->default('Caisse principale');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('user_id');
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
