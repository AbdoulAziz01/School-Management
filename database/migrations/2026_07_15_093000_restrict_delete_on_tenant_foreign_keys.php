<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remplace cascadeOnDelete() par restrictOnDelete() sur school_id (12 tables
 * tenant) et sur grades.user_id / grades.subject_id.
 *
 * Avec le cascade actuel, supprimer un établissement ou une matière efface
 * silencieusement tout l'historique associé (notes, présences…) sans aucune
 * confirmation ni possibilité de revenir en arrière. Les deux voies de
 * suppression légitimes existantes ne dépendent pas du cascade :
 * - SchoolController::destroy() refuse déjà de supprimer un établissement
 *   non vide (vérifie users()->count() avant d'agir) ;
 * - LoadTestDataPurge (purge de masse) désactive explicitement les
 *   contraintes FK et gère l'ordre de suppression lui-même.
 * Le restrict ne fait donc que bloquer les suppressions accidentelles
 * hors de ces deux chemins contrôlés.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $schoolIdTables = [
        'levels', 'subjects', 'academic_years', 'classes', 'grades',
        'attendances', 'schedules', 'events', 'assignments',
        'teacher_assignments', 'class_groups', 'timetables',
    ];

    public function up(): void
    {
        foreach ($this->schoolIdTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['school_id']);
                $blueprint->foreign('school_id')->references('id')->on('schools')->restrictOnDelete();
            });
        }

        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $blueprint) {
                $blueprint->dropForeign(['user_id']);
                $blueprint->foreign('user_id')->references('id')->on('users')->restrictOnDelete();

                $blueprint->dropForeign(['subject_id']);
                $blueprint->foreign('subject_id')->references('id')->on('subjects')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $blueprint) {
                $blueprint->dropForeign(['user_id']);
                $blueprint->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

                $blueprint->dropForeign(['subject_id']);
                $blueprint->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            });
        }

        foreach ($this->schoolIdTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['school_id']);
                $blueprint->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            });
        }
    }
};
