<?php

namespace App\Support\LoadTest;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supprime toutes les données métier et établissements ; conserve le super administrateur.
 */
class LoadTestDataPurge
{
    /**
     * Supprime un établissement et toutes ses données (cascade + pivots).
     */
    public static function forSchool(int $schoolId): void
    {
        $school = School::query()->find($schoolId);
        if (! $school) {
            return;
        }

        // La transaction n'est pas qu'une question d'atomicité : sur
        // PostgreSQL, Schema::disableForeignKeyConstraints() émet
        // `SET CONSTRAINTS ALL DEFERRED`, qui ne défère réellement les
        // contraintes que jusqu'au COMMIT d'une transaction en cours — sans
        // DB::transaction() explicite, chaque DELETE est son propre commit
        // implicite et l'ordre des suppressions redevient contraint par les FK.
        DB::transaction(function () use ($school, $schoolId) {
            Schema::disableForeignKeyConstraints();

            try {
                $classIds = SchoolClass::withoutGlobalScopes()
                    ->where('school_id', $schoolId)
                    ->pluck('id');

                if ($classIds->isNotEmpty()) {
                    foreach (['schedules', 'teacher_assignments'] as $table) {
                        if (Schema::hasTable($table) && Schema::hasColumn($table, 'class_id')) {
                            DB::table($table)->whereIn('class_id', $classIds)->delete();
                        }
                    }
                    foreach (['class_subject', 'class_teacher', 'class_group_student'] as $table) {
                        if (Schema::hasTable($table) && Schema::hasColumn($table, 'class_id')) {
                            DB::table($table)->whereIn('class_id', $classIds)->delete();
                        }
                    }
                }

                User::withoutGlobalScopes()->where('school_id', $schoolId)->delete();
                $school->delete();
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });
    }

    public static function run(): void
    {
        // Voir le commentaire de forSchool() : la transaction est nécessaire
        // pour que le report des contraintes FK (PostgreSQL) s'applique
        // réellement sur l'ensemble des suppressions ci-dessous.
        DB::transaction(function () {
            Schema::disableForeignKeyConstraints();

            try {
                foreach (['grades', 'attendances', 'schedules', 'events', 'assignments', 'timetables'] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->delete();
                    }
                }

                foreach (['teacher_assignments', 'class_subject', 'class_teacher', 'teacher_subjects', 'level_subject', 'class_group_student', 'class_groups'] as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->delete();
                    }
                }

                if (Schema::hasTable('formation_promotions')) {
                    DB::table('formation_promotions')->delete();
                }
                if (Schema::hasTable('formation_departments')) {
                    DB::table('formation_departments')->delete();
                }

                $permTables = config('permission.table_names', []);
                if (! empty($permTables['model_has_roles']) && Schema::hasTable($permTables['model_has_roles'])) {
                    DB::table($permTables['model_has_roles'])
                        ->where('model_type', User::class)
                        ->delete();
                }
                if (! empty($permTables['model_has_permissions']) && Schema::hasTable($permTables['model_has_permissions'])) {
                    DB::table($permTables['model_has_permissions'])
                        ->where('model_type', User::class)
                        ->delete();
                }

                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->delete();
                }

                User::withoutGlobalScopes()
                    ->where('role', '!=', User::ROLE_SUPER_ADMIN)
                    ->delete();

                if (Schema::hasTable('classes')) {
                    SchoolClass::withoutGlobalScopes()->delete();
                }
                if (Schema::hasTable('subjects')) {
                    Subject::withoutGlobalScopes()->delete();
                }
                if (Schema::hasTable('levels')) {
                    DB::table('levels')->delete();
                }
                if (Schema::hasTable('academic_years')) {
                    AcademicYear::withoutGlobalScopes()->delete();
                }
                if (Schema::hasTable('schools')) {
                    School::query()->delete();
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });
    }
}
