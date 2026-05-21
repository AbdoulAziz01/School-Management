<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formation_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('formation_department_id')->nullable()->constrained('formation_departments')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('filiere', 120);
            $table->string('diploma_type', 40);
            $table->string('formation_year', 80);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id']);
        });

        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'formation_promotion_id')) {
                $table->foreignId('formation_promotion_id')
                    ->nullable()
                    ->after('school_id')
                    ->constrained('formation_promotions')
                    ->nullOnDelete();
            }
        });

        $this->migrateExistingPromotions();
    }

    private function migrateExistingPromotions(): void
    {
        if (! Schema::hasColumn('classes', 'promotion_name')) {
            return;
        }

        $classes = DB::table('classes')
            ->whereNotNull('promotion_name')
            ->where('promotion_name', '!=', '')
            ->orderBy('id')
            ->get();

        $groups = $classes->groupBy(function ($class) {
            return implode('|', [
                $class->school_id ?? '',
                $class->academic_year_id ?? '',
                $class->promotion_name ?? '',
                $class->filiere ?? '',
                $class->diploma_type ?? '',
                $class->formation_year ?? '',
                $class->formation_department_id ?? '',
            ]);
        });

        foreach ($groups as $group) {
            $first = $group->first();
            if (! $first->school_id || ! $first->academic_year_id) {
                continue;
            }

            $promotionId = DB::table('formation_promotions')->insertGetId([
                'school_id' => $first->school_id,
                'formation_department_id' => $first->formation_department_id,
                'academic_year_id' => $first->academic_year_id,
                'name' => $first->promotion_name,
                'filiere' => $first->filiere ?? '',
                'diploma_type' => $first->diploma_type ?? 'autre',
                'formation_year' => $first->formation_year ?? '',
                'description' => $first->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('classes')
                ->whereIn('id', $group->pluck('id'))
                ->update(['formation_promotion_id' => $promotionId]);
        }
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            if (Schema::hasColumn('classes', 'formation_promotion_id')) {
                $table->dropConstrainedForeignId('formation_promotion_id');
            }
        });

        Schema::dropIfExists('formation_promotions');
    }
};
