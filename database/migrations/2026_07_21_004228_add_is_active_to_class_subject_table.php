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
        Schema::table('class_subject', function (Blueprint $table) {
            // Une ligne présente avec is_active = false désactive
            // explicitement une matière pour CETTE classe précise (ex. une
            // matière activée au niveau mais non enseignée dans une classe
            // donnée) — l'absence de ligne signifie "suit le réglage du
            // niveau" (level_subject.is_active), voir TeacherSubjectResolver.
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_subject', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
