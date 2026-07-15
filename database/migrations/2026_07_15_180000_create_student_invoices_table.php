<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ce qu'un élève doit réellement pour un fee_type donné — généré
 * automatiquement (StudentInvoiceService), jamais saisi à la main.
 * amount_paid est dénormalisé (mis à jour par PaymentService à chaque
 * allocation) pour éviter de resommer payment_allocations à chaque
 * affichage de la liste des impayés.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('fee_type_id')->constrained()->restrictOnDelete();
            $table->string('label');
            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->date('due_date');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index('school_id');
            $table->index(['student_id', 'academic_year_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_invoices');
    }
};
