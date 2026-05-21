<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE levels DROP CONSTRAINT IF EXISTS levels_cycle_check');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE levels ADD CONSTRAINT levels_cycle_check CHECK (cycle::text = ANY (ARRAY['college'::text, 'lycee'::text]))");
    }
};
