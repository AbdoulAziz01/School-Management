<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Champs supplémentaires pour démos / bulletins alignés données élèves réalistes.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $after = 'address';

            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city', 120)->nullable()->after($after);
                $after = 'city';
            }
            if (! Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after($after);
                $after = 'postal_code';
            }
            if (! Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable()->after($after);
                $after = 'country';
            }
            if (! Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 15)->nullable()->after($after);
                $after = 'gender';
            }
            if (! Schema::hasColumn('users', 'guardian_phone')) {
                $table->string('guardian_phone', 30)->nullable()->after($after);
                $after = 'guardian_phone';
            }
            if (! Schema::hasColumn('users', 'conduct_evaluation')) {
                $table->text('conduct_evaluation')->nullable()->after($after);
                $after = 'conduct_evaluation';
            }
            if (! Schema::hasColumn('users', 'assiduity_comment')) {
                $table->text('assiduity_comment')->nullable()->after($after);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['assiduity_comment', 'conduct_evaluation', 'guardian_phone', 'gender', 'country', 'postal_code', 'city'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
