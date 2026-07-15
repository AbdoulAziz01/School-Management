<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PostgreSQL n'indexe pas automatiquement une colonne foreignId() comme le
 * fait MySQL. Ces tables tenant filtrent systématiquement par school_id
 * (SchoolScope) sans index dédié — `users` et `subjects` en ont déjà un via
 * leurs contraintes uniques composites (school_id, identifier)/(school_id,
 * code), les autres tables tenant ci-dessous n'en ont aucun.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'levels',
        'academic_years',
        'classes',
        'grades',
        'attendances',
        'schedules',
        'events',
        'assignments',
        'teacher_assignments',
        'class_groups',
        'timetables',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->index('school_id', "{$table}_school_id_index");
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $blueprint->dropIndex("{$table}_school_id_index");
            });
        }
    }
};
