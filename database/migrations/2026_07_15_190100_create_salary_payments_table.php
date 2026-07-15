<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une ligne par employé par période (mois), générée par une commande
 * idempotente (SalaryPaymentService::generateForPeriod()) à partir du
 * salaire actif de EmployeeSalaryProfile — jamais le montant dû saisi à la
 * main. unique(user_id, period) rend la génération mensuelle sûre à
 * rejouer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('employee_salary_profile_id')->constrained()->restrictOnDelete();
            $table->date('period');
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period']);
            $table->index('school_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
