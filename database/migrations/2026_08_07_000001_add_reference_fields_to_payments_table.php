<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traçabilité du mode de paiement pour les chèques, virements et paiements
 * mobiles (Wave/Orange Money) : un simple libellé "Chèque" sur le reçu ne
 * suffit pas à retrouver un chèque en cas de litige ou de rejet bancaire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Numéro de chèque, référence de virement, ou ID de transaction
            // Wave/Orange Money — selon payment_method. Vide pour "Espèces".
            $table->string('payment_reference')->nullable()->after('payment_method');
            // Banque émettrice (chèque) ou banque du virement.
            $table->string('payment_bank')->nullable()->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'payment_bank']);
        });
    }
};
