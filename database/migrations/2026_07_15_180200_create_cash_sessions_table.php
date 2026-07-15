<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Une session = une période ouverture→clôture d'une caisse. Les
 * paiements/dépenses enregistrés pendant la session lui sont rattachés
 * (cash_session_id) pour calculer le solde attendu à la clôture.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('opened_at');
            $table->decimal('opening_balance', 12, 2);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('expected_closing_balance', 12, 2)->nullable();
            $table->decimal('actual_closing_balance', 12, 2)->nullable();
            $table->decimal('difference', 12, 2)->nullable();
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index(['cash_register_id', 'status']);
        });

        // Une seule session ouverte par caisse à la fois (PostgreSQL).
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE UNIQUE INDEX cash_sessions_one_open_per_register '.
                "ON cash_sessions (cash_register_id) WHERE status = 'open'"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
