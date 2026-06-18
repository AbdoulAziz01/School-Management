<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            // Chemin relatif dans storage/app/private/lms/submissions/
            $table->string('file_path', 512)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['pending', 'graded', 'returned'])->default('pending');
            $table->timestamps();

            // Un seul rendu par élève par devoir
            $table->unique(['assignment_id', 'user_id']);
            $table->index(['school_id', 'user_id']);
            $table->index(['assignment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
