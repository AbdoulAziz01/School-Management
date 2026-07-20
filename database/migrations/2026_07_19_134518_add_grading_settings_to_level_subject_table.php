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
        Schema::table('level_subject', function (Blueprint $table) {
            // Paramétrage de notation par (niveau, matière) — primaire
            // aujourd'hui, réutilisable par tout autre cycle demain. Null
            // = pas encore configuré, App\Support\Grading\PrimaryGradingSettings
            // retombe alors sur des valeurs par défaut (10/1/3 compositions).
            $table->decimal('max_grade', 5, 2)->nullable()->after('coefficient');
            $table->unsignedTinyInteger('compositions_count')->nullable()->after('max_grade');
            $table->string('evaluation_type')->nullable()->after('compositions_count');
            $table->json('settings')->nullable()->after('evaluation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('level_subject', function (Blueprint $table) {
            $table->dropColumn(['max_grade', 'compositions_count', 'evaluation_type', 'settings']);
        });
    }
};
