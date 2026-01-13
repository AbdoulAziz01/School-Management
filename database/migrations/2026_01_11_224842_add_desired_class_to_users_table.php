<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Vérifier si la colonne n'existe pas déjà
        if (!Schema::hasColumn('users', 'desired_class')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('desired_class')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Vérifier si la colonne existe avant de la supprimer
        if (Schema::hasColumn('users', 'desired_class')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('desired_class');
            });
        }
    }
};