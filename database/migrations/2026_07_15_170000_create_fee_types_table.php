<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('category');
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
};
