<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire le stockage réversible des mots de passe (audit sécurité : les mots
 * de passe ne doivent jamais être consultables en clair par un administrateur
 * au-delà de leur affichage ponctuel au moment de la génération).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'admin_visible_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('admin_visible_password');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'admin_visible_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('admin_visible_password')->nullable()->after('password');
            });
        }
    }
};
