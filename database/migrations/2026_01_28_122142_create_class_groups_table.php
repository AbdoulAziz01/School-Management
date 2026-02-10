<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('school_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_students')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Contrainte d'unicité sur le nom du groupe par classe et année académique
            $table->unique(['name', 'school_class_id', 'academic_year_id'], 'class_group_unique_name_per_class_year');
        });
        
        // Création de la table de liaison entre les groupes et les étudiants
        Schema::create('class_group_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Un étudiant ne peut être qu'une seule fois dans un groupe à une période donnée
            $table->unique(['class_group_id', 'student_id', 'start_date'], 'unique_student_in_group_per_period');
        });
        
        // Ajout de la colonne class_group_id à la table schedules si elle n'existe pas déjà
        if (!Schema::hasColumn('schedules', 'class_group_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->foreignId('class_group_id')->nullable()->after('class_id')
                    ->constrained('class_groups')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer la contrainte de clé étrangère de la table schedules si elle existe
        if (Schema::hasColumn('schedules', 'class_group_id')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropForeign(['class_group_id']);
                $table->dropColumn('class_group_id');
            });
        }
        
        // Supprimer la table de liaison entre les groupes et les étudiants
        Schema::dropIfExists('class_group_student');
        
        // Enfin, supprimer la table des groupes de classe
        Schema::dropIfExists('class_groups');
    }
};
