<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (! Schema::hasColumn('schools', 'logo_data')) {
                $table->longText('logo_data')->nullable()->after('logo_path');
            }
            if (! Schema::hasColumn('schools', 'logo_mime')) {
                $table->string('logo_mime', 100)->nullable()->after('logo_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'logo_mime')) {
                $table->dropColumn('logo_mime');
            }
            if (Schema::hasColumn('schools', 'logo_data')) {
                $table->dropColumn('logo_data');
            }
        });
    }
};
