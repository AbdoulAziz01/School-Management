<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('file_type', ['pdf', 'doc', 'video', 'link', 'other'])->default('pdf');
            // Chemin relatif dans storage/app/private/lms/lessons/ (PDF/doc)
            // ou URL complète (video/link)
            $table->string('file_path', 512)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['class_id', 'subject_id']);
            $table->index(['school_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
