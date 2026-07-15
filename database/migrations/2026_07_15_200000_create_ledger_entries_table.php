<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Écriture comptable immuable : un mouvement d'argent = une ligne, jamais
 * modifiée ni supprimée. Alimentée automatiquement par des Observers sur
 * Payment/Expense/SalaryPayment (voir app/Observers) — jamais écrite à la
 * main par un contrôleur, ce qui garantit "aucune entrée d'argent sans
 * passer par le module" et "aucune double saisie".
 *
 * amount est signé : une annulation (Payment/Expense/SalaryPayment) génère
 * une seconde ligne du même entry_type avec un montant négatif plutôt que
 * de modifier la ligne d'origine — SUM(amount) reste donc toujours exact
 * sans jamais réécrire l'historique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->string('entry_type');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->decimal('amount', 12, 2);
            $table->string('description');
            $table->timestamp('recorded_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('school_id');
            $table->index(['school_id', 'recorded_at']);
            $table->index(['source_type', 'source_id']);
            $table->index('entry_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
