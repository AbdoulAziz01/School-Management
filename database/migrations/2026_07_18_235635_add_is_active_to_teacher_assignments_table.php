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
        Schema::table('teacher_assignments', function (Blueprint $table) {
            // Permet à l'enseignant de désactiver lui-même une affectation
            // (ex : au primaire, il enseigne tout le programme par défaut
            // mais peut décocher une matière qu'il ne couvre pas en
            // pratique), sans que l'admin ne perde cette information au
            // prochain enregistrement de sa fiche (voir TeacherTeachingService::sync()).
            $table->boolean('is_active')->default(true)->after('academic_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
