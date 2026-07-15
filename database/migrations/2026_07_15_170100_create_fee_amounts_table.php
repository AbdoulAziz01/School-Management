<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Montant d'un fee_type pour une année scolaire, éventuellement différent par
 * niveau (level_id NULL = s'applique à tous les niveaux). L'unicité
 * (fee_type_id, academic_year_id, level_id) est garantie par l'application
 * (FeeAmount::upsertFor(), via updateOrCreate) plutôt qu'une contrainte SQL :
 * NULL n'est pas comparable à lui-même dans une contrainte UNIQUE standard,
 * ce qui laisserait passer plusieurs lignes "tous niveaux" en doublon.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_amounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('fee_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index('school_id');
            $table->index(['fee_type_id', 'academic_year_id']);
        });

        // Empêche les doublons "niveau précis" au niveau SQL (index partiel,
        // PostgreSQL uniquement) ; le cas level_id NULL reste couvert par
        // l'application (voir docblock ci-dessus).
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE UNIQUE INDEX fee_amounts_type_year_level_unique '.
                'ON fee_amounts (fee_type_id, academic_year_id, level_id) '.
                'WHERE level_id IS NOT NULL'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_amounts');
    }
};
