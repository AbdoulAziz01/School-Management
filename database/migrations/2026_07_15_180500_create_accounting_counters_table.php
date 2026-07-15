<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Compteurs séquentiels par école (ex. numéro de reçu), incrémentés sous
 * lockForUpdate() (voir ReceiptNumberGenerator) pour éviter toute collision
 * entre deux caissiers encaissant au même instant — même classe de bug que
 * celle déjà corrigée sur StudentController::autoAssignToClass (Phase 5.5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->unsignedBigInteger('next_value')->default(1);
            $table->timestamps();

            $table->unique(['school_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_counters');
    }
};
