<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rend school_id obligatoire sur les tables tenant (hors "users", où NULL
 * reste légitime pour le super_admin — accès plateforme entière).
 *
 * Avant cette migration, school_id était nullable sans aucun garde-fou DB :
 * plusieurs seeders créaient des lignes sans jamais le renseigner (corrigé
 * séparément), laissant des enregistrements non rattachés à un établissement
 * et donc non protégés par SchoolScope. Cette migration comble les lignes
 * orphelines avant de poser la contrainte NOT NULL.
 *
 * Le rattachement automatique n'est appliqué que s'il existe exactement UNE
 * école : au-delà, deviner le bon établissement pour une ligne orpheline
 * risquerait une fuite de données entre établissements plutôt que de la
 * corriger. Dans ce cas, la contrainte n'est pas posée sur les tables encore
 * concernées et un avertissement est affiché — à traiter manuellement.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tenantTables = [
        'levels',
        'subjects',
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
        $schoolCount = DB::table('schools')->count();
        $singleSchoolId = $schoolCount === 1 ? DB::table('schools')->value('id') : null;

        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            $orphanCount = DB::table($table)->whereNull('school_id')->count();

            if ($orphanCount > 0) {
                if ($singleSchoolId !== null) {
                    DB::table($table)->whereNull('school_id')->update(['school_id' => $singleSchoolId]);
                } else {
                    echo "  [enforce_school_id_not_null] {$table} : {$orphanCount} ligne(s) sans school_id et {$schoolCount} établissement(s) en base — contrainte NOT NULL NON appliquée, à traiter manuellement.".PHP_EOL;

                    continue;
                }
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('school_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unsignedBigInteger('school_id')->nullable()->change();
            });
        }
    }
};
