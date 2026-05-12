<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supprime le CHECK obsolète sur role (souvent hérité d'un ENUM MySQL / migration)
 * pour accepter les valeurs alignées avec l'app (eleve, teacher, admin, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        }
    }

    public function down(): void
    {
        // Ne pas recréer le CHECK hérité : les rôles sont gérés côté application.
    }
};
