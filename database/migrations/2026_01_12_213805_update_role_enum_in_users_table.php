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
        // Modifier la colonne role pour accepter 'eleve' au lieu de 'student'
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'eleve') DEFAULT 'eleve'");
        
        // Mettre à jour les rôles existants de 'student' à 'eleve'
        \DB::table('users')
            ->where('role', 'student')
            ->update(['role' => 'eleve']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à la configuration précédente
        \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student') DEFAULT 'student'");
        
        // Remettre les rôles à leur valeur précédente
        \DB::table('users')
            ->where('role', 'eleve')
            ->update(['role' => 'student']);
    }
};
