<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();

            // 1, 2 ou 3 — jamais plus
            $table->unsignedTinyInteger('attempt_number');

            $table->decimal('score', 5, 2)->default(0);
            $table->decimal('max_score', 5, 2)->default(0);

            // Clé JSON : {question_id: option_id|null}
            $table->json('answers')->nullable();

            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            // Verrou unique : impossible d'avoir deux attempt_number identiques par (quiz, élève)
            $table->unique(['quiz_id', 'user_id', 'attempt_number'], 'uq_quiz_user_attempt');

            $table->index(['quiz_id', 'user_id']);
        });

        /*
         * Contrainte CHECK PostgreSQL — interdit toute valeur hors de {1,2,3} au niveau moteur.
         * Sur MySQL 8.0.16+ la contrainte est créée mais non forcée ; l'application s'en charge.
         */
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE quiz_attempts
                ADD CONSTRAINT chk_attempt_number_max3
                CHECK (attempt_number >= 1 AND attempt_number <= 3)
            ');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
