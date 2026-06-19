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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete()->after('user_id');
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete()->after('subject_id');
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete()->after('teacher_id');
            $table->string('session_type', 50)->nullable()->after('reason');
            $table->unsignedSmallInteger('minutes_late')->nullable()->after('session_type');
            $table->time('start_time')->nullable()->after('minutes_late');
            $table->time('end_time')->nullable()->after('start_time');
            $table->string('classroom', 100)->nullable()->after('end_time');
            $table->timestamp('justified_at')->nullable()->after('justified');
            $table->string('justification_status', 20)->nullable()->after('justified_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropConstrainedForeignId('class_id');
            $table->dropColumn(['session_type', 'minutes_late', 'start_time', 'end_time', 'classroom', 'justified_at', 'justification_status']);
        });
    }
};
