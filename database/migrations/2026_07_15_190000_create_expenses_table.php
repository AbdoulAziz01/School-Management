<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dépenses diverses (fournitures, matériel, internet, électricité, eau,
 * entretien, prime, autre) — les salaires ont leur propre table
 * (salary_payments) à cause de la logique de génération mensuelle, mais
 * sont réunis avec les dépenses au niveau du reporting (Phase 6.5), pas de
 * la base. Jamais supprimée : une erreur passe par status=cancelled avec
 * traçabilité, jamais un DELETE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->string('category');
            $table->string('beneficiary');
            $table->decimal('amount', 12, 2);
            $table->string('motif');
            $table->date('expense_date');
            $table->string('payment_method');
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('justificatif_path')->nullable();
            $table->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index(['school_id', 'expense_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
