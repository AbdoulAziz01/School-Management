<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autorise un enseignant à corriger une note UNE seule fois après sa saisie
 * initiale. teacher_edited_at reste NULL tant que la correction n'a pas été
 * utilisée ; une fois posé, toute nouvelle tentative de modification par un
 * enseignant est refusée (seule l'administration peut alors intervenir).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->timestamp('teacher_edited_at')->nullable()->after('coefficient');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('teacher_edited_at');
        });
    }
};
