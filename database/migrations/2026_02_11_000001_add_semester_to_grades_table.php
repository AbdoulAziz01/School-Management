<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter les nouvelles colonnes
        Schema::table('grades', function (Blueprint $table) {
            // Ajout du semestre pour le système sénégalais
            if (!Schema::hasColumn('grades', 'semester')) {
                $table->integer('semester')->default(1)->after('type'); // 1 ou 2
            }
            
            // Ajout de l'année académique
            if (!Schema::hasColumn('grades', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable()->after('semester');
            }
        });
        
        // Ajouter la contrainte de clé étrangère séparément
        Schema::table('grades', function (Blueprint $table) {
            if (Schema::hasColumn('grades', 'academic_year_id')) {
                $constraintName = 'grades_academic_year_id_foreign';
                $driver = Schema::getConnection()->getDriverName();

                if ($driver === 'pgsql') {
                    $foreignKeys = DB::select("
                        SELECT constraint_name
                        FROM information_schema.table_constraints
                        WHERE table_schema = current_schema()
                        AND table_name = 'grades'
                        AND constraint_type = 'FOREIGN KEY'
                        AND constraint_name = ?
                    ", [$constraintName]);
                } else {
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.TABLE_CONSTRAINTS
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'grades'
                        AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                        AND CONSTRAINT_NAME = ?
                    ", [$constraintName]);
                }

                if (empty($foreignKeys)) {
                    $table->foreign('academic_year_id')
                          ->references('id')
                          ->on('academic_years')
                          ->nullOnDelete();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            if (Schema::hasColumn('grades', 'academic_year_id')) {
                $table->dropForeign(['academic_year_id']);
                $table->dropColumn('academic_year_id');
            }
            if (Schema::hasColumn('grades', 'semester')) {
                $table->dropColumn('semester');
            }
        });
    }
};
