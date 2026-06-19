<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nom du parent / tuteur légal
            $table->string('parent_name', 150)->nullable()->after('guardian_phone');

            // Numéro WhatsApp en format international : +221XXXXXXXXX
            // Chiffré au repos via Laravel encryption (cast 'encrypted') → TEXT obligatoire
            $table->text('parent_whatsapp')->nullable()->after('parent_name');

            // Langue de communication préférée du parent
            // fr_text  → message texte en français (alphabétisé)
            // wo_audio → message audio en wolof (alphabétisation limitée)
            // pu_audio → message audio en pulaar (alphabétisation limitée)
            $table->string('parent_lang', 20)->default('fr_text')->after('parent_whatsapp');
        });

        // Contrainte CHECK émulée (enum natif non utilisé pour rester compatible MySQL/PgSQL)
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE users
                ADD CONSTRAINT chk_parent_lang
                CHECK (parent_lang IN ('fr_text','wo_audio','pu_audio'))");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS chk_parent_lang');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['parent_name', 'parent_whatsapp', 'parent_lang']);
        });
    }
};
