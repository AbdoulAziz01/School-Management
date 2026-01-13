<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained('levels')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->integer('coefficient')->default(1); // Coefficient spécifique au niveau
            $table->timestamps();
            
            $table->unique(['level_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_subject');
    }
};
