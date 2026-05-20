<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /** @var list<string> */
    private array $tenantTables = [
        'users',
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
        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('school_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('schools')
                    ->cascadeOnDelete();
            });
        }

        $defaultSchoolId = $this->ensureDefaultSchool();

        foreach ($this->tenantTables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            DB::table($table)->whereNull('school_id')->update(['school_id' => $defaultSchoolId]);
        }

        if (Schema::hasTable('subjects') && $this->hasIndex('subjects', 'subjects_code_unique')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropUnique(['code']);
            });
            Schema::table('subjects', function (Blueprint $table) {
                $table->unique(['school_id', 'code']);
            });
        }

    }

    public function down(): void
    {
        if (Schema::hasTable('subjects')) {
            try {
                Schema::table('subjects', function (Blueprint $table) {
                    $table->dropUnique(['school_id', 'code']);
                });
                Schema::table('subjects', function (Blueprint $table) {
                    $table->unique('code');
                });
            } catch (\Throwable) {
                //
            }
        }

        foreach (array_reverse($this->tenantTables) as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('school_id');
            });
        }

        DB::table('schools')->where('slug', 'etablissement-principal')->delete();
    }

    private function ensureDefaultSchool(): int
    {
        $existing = DB::table('schools')->where('slug', 'etablissement-principal')->value('id');
        if ($existing) {
            return (int) $existing;
        }

        $name = config('school.default_name', 'Établissement principal');

        return (int) DB::table('schools')->insertGetId([
            'name' => $name,
            'slug' => 'etablissement-principal',
            'code' => 'ECOLE-DEFAULT',
            'is_active' => true,
            'email' => null,
            'phone' => null,
            'address' => null,
            'city' => null,
            'logo_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($idx) => ($idx->name ?? '') === $indexName);
        }

        if ($driver === 'mysql') {
            $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

            return count($rows) > 0;
        }

        return true;
    }
};
