<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fondations pour le futur module Comptabilité (voir audit "Préparation
 * Comptabilité") :
 * - establishment_category distingue public/privé, un axe juridique/
 *   statutaire indépendant de establishment_type (qui est pédagogique :
 *   primaire/collège/lycée/formation).
 * - enabled_modules (JSON) suit le même pattern que formation_lmd_settings
 *   et card_settings déjà utilisés sur cette table : un établissement
 *   privé pourra activer la comptabilité complète, un établissement
 *   public pourra la laisser désactivée, sans ajouter de nouvelle table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('establishment_category')->nullable()->after('establishment_type');
            $table->json('enabled_modules')->nullable()->after('card_settings');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['establishment_category', 'enabled_modules']);
        });
    }
};
