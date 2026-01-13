<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ne pas ajouter les colonnes qui existent déjà
            if (!Schema::hasColumn('users', 'user_id')) {
                $table->string('user_id')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('eleve')->after('email');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('pending')->after('role');
            }
            if (!Schema::hasColumn('users', 'subject')) {
                $table->string('subject')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'desired_class')) {
                $table->string('desired_class')->nullable()->after('subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('users', 'user_id')) {
                $columnsToDrop[] = 'user_id';
            }
            if (Schema::hasColumn('users', 'role')) {
                $columnsToDrop[] = 'role';
            }
            if (Schema::hasColumn('users', 'status')) {
                $columnsToDrop[] = 'status';
            }
            if (Schema::hasColumn('users', 'subject')) {
                $columnsToDrop[] = 'subject';
            }
            if (Schema::hasColumn('users', 'desired_class')) {
                $columnsToDrop[] = 'desired_class';
            }
            
            if (count($columnsToDrop) > 0) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};