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
        Schema::table('levels', function (Blueprint $table) {
            // Ajout de la série pour le système sénégalais (L, S, ES, ou null pour collège)
            $table->string('serie')->nullable()->after('cycle');
        });
        
        Schema::table('subjects', function (Blueprint $table) {
            // Ajouter les colonnes manquantes si elles n'existent pas
            if (!Schema::hasColumn('subjects', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
            if (!Schema::hasColumn('subjects', 'department')) {
                $table->string('department')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('subjects', 'hours_per_week')) {
                $table->float('hours_per_week')->default(2)->after('department');
            }
            if (!Schema::hasColumn('subjects', 'is_core_subject')) {
                $table->boolean('is_core_subject')->default(false)->after('hours_per_week');
            }
            if (!Schema::hasColumn('subjects', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_core_subject');
            }
            if (!Schema::hasColumn('subjects', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by');
            }
        });
        
        // Modifier la table level_subject pour ajouter is_compulsory si non existant
        Schema::table('level_subject', function (Blueprint $table) {
            if (!Schema::hasColumn('level_subject', 'is_compulsory')) {
                $table->boolean('is_compulsory')->default(true)->after('coefficient');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn('serie');
        });
    }
};
