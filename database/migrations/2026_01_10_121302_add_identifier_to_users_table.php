<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la colonne identifier SANS contrainte unique d'abord
        Schema::table('users', function (Blueprint $table) {
            $table->string('identifier')->nullable()->after('id');
        });

        // Donner des identifiers uniques aux users existants
        $users = User::all();
        $studentCounter = 1;
        $teacherCounter = 1;
        $adminCounter = 1;

        foreach ($users as $user) {
            if ($user->role === 'student') {
                $user->identifier = 'E' . date('Y') . str_pad($studentCounter, 3, '0', STR_PAD_LEFT);
                $studentCounter++;
            } elseif ($user->role === 'teacher') {
                $user->identifier = 'P' . date('Y') . str_pad($teacherCounter, 3, '0', STR_PAD_LEFT);
                $teacherCounter++;
            } elseif ($user->role === 'admin') {
                $user->identifier = 'ADMIN' . str_pad($adminCounter, 2, '0', STR_PAD_LEFT);
                $adminCounter++;
            } else {
                // Si pas de rôle défini, mettre student par défaut
                $user->identifier = 'E' . date('Y') . str_pad($studentCounter, 3, '0', STR_PAD_LEFT);
                $studentCounter++;
            }
            $user->save();
        }

        // Maintenant ajouter la contrainte unique
        Schema::table('users', function (Blueprint $table) {
            $table->string('identifier')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('identifier');
        });
    }
};
