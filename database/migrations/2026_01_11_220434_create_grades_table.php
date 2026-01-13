<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->decimal('grade', 5, 2);
            $table->text('comments')->nullable();
            $table->string('appreciation')->nullable();
            $table->date('date');
            $table->string('type'); // Examen, Devoir, Participation, etc.
            $table->integer('coefficient')->default(1);
            $table->timestamps();
            
            // Clé unique pour éviter les doublons
            $table->unique(['user_id', 'subject_id', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
