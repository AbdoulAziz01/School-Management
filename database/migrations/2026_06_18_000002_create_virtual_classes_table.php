<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();

            $table->string('title', 200);
            $table->text('description')->nullable();

            // Identifiant de salle Jitsi — unique dans tout le système
            $table->string('room_name', 80)->unique();
            $table->string('meeting_password', 64)->nullable();

            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(60);

            // Cycle de vie de la séance
            $table->boolean('is_active')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(['class_id', 'scheduled_at']);
            $table->index(['school_id', 'is_active']);
            $table->index(['teacher_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_classes');
    }
};
