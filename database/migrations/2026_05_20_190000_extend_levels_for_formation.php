<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE levels ALTER COLUMN cycle TYPE varchar(20) USING cycle::text');
            DB::statement("ALTER TABLE levels ALTER COLUMN cycle SET DEFAULT 'college'");
        } else {
            Schema::table('levels', function (Blueprint $table) {
                $table->string('cycle', 20)->default('college')->change();
            });
        }

        Schema::table('levels', function (Blueprint $table) {
            if (! Schema::hasColumn('levels', 'description')) {
                $table->text('description')->nullable()->after('serie');
            }
        });
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            if (Schema::hasColumn('levels', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
