<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chaque encaissement élève. Jamais supprimé — une erreur passe par
 * status=cancelled avec traçabilité (voir PaymentService::cancel()),
 * jamais un DELETE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->string('receipt_number');
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->timestamp('paid_at');
            $table->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->string('status')->default('completed');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'receipt_number']);
            $table->index('school_id');
            $table->index('student_id');
            $table->index('cash_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
