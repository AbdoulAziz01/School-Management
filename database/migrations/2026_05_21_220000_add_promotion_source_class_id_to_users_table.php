<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'promotion_source_class_id')) {
                $table->foreignId('promotion_source_class_id')
                    ->nullable()
                    ->after('last_promotion_academic_year_id')
                    ->constrained('classes')
                    ->nullOnDelete();
            }
        });

        $this->backfillPromotionSourceClasses();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'promotion_source_class_id')) {
                $table->dropConstrainedForeignId('promotion_source_class_id');
            }
        });
    }

    private function backfillPromotionSourceClasses(): void
    {
        $rows = DB::table('users')
            ->whereNotNull('last_promotion_academic_year_id')
            ->whereNull('promotion_source_class_id')
            ->get(['id', 'class_id', 'last_promotion_academic_year_id']);

        foreach ($rows as $row) {
            $sourceClassId = $this->inferSourceClassId(
                (int) $row->class_id,
                (int) $row->last_promotion_academic_year_id
            );

            if ($sourceClassId) {
                DB::table('users')->where('id', $row->id)->update([
                    'promotion_source_class_id' => $sourceClassId,
                ]);
            }
        }
    }

    private function inferSourceClassId(int $currentClassId, int $promotionYearId): ?int
    {
        $currentClass = DB::table('classes')->where('id', $currentClassId)->first();
        if (! $currentClass) {
            return null;
        }

        if ((int) $currentClass->academic_year_id === $promotionYearId) {
            return $currentClassId;
        }

        $currentLevel = DB::table('levels')->where('id', $currentClass->level_id)->first();
        if (! $currentLevel) {
            return null;
        }

        $sourceLevelOrder = (int) $currentLevel->order - 1;
        if ($sourceLevelOrder < 1) {
            return null;
        }

        $sourceLevel = DB::table('levels')
            ->where('school_id', $currentClass->school_id)
            ->where('order', $sourceLevelOrder)
            ->first();

        if (! $sourceLevel) {
            return null;
        }

        return DB::table('classes')
            ->where('school_id', $currentClass->school_id)
            ->where('academic_year_id', $promotionYearId)
            ->where('level_id', $sourceLevel->id)
            ->orderBy('name')
            ->value('id');
    }
};
