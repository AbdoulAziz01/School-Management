<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('assignments', 'instructions')) {
                $table->text('instructions')->nullable()->after('description');
            }
            if (! Schema::hasColumn('assignments', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('status')
                    ->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'instructions')) {
                $table->dropColumn('instructions');
            }
            if (Schema::hasColumn('assignments', 'school_id')) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            }
        });
    }
};
