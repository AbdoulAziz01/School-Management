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
            // Permet de désactiver une matière pour un niveau donné : toutes
            // les écoles primaire n'enseignent pas les 16 matières du
            // catalogue (ex. Éducation Religieuse). Une matière désactivée
            // disparaît des choix proposés aux enseignants pour ce niveau.
            $table->boolean('is_active')->default(true)->after('is_compulsory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('level_subject', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
