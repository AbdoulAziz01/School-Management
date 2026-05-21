<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('establishment_type', 30)->nullable()->after('city');
            $table->string('motto')->nullable()->after('establishment_type');
            $table->string('authorization_number', 100)->nullable()->after('motto');
            $table->string('director_name')->nullable()->after('authorization_number');
            $table->string('deputy_director_name')->nullable()->after('director_name');
            $table->string('website')->nullable()->after('email');
            $table->string('whatsapp', 50)->nullable()->after('phone');
            $table->string('phone_secondary', 50)->nullable()->after('whatsapp');
            $table->string('secretariat_email')->nullable()->after('phone_secondary');
            $table->string('region', 100)->nullable()->after('city');
            $table->string('department', 100)->nullable()->after('region');
            $table->string('district', 100)->nullable()->after('department');
            $table->string('timezone', 64)->default('Africa/Dakar')->after('district');
            $table->string('locale', 10)->default('fr')->after('timezone');
            $table->text('description')->nullable()->after('locale');
            $table->text('secretariat_hours')->nullable()->after('description');
            $table->foreignId('default_academic_year_id')
                ->nullable()
                ->after('secretariat_hours')
                ->constrained('academic_years')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_academic_year_id');
            $table->dropColumn([
                'establishment_type',
                'motto',
                'authorization_number',
                'director_name',
                'deputy_director_name',
                'website',
                'whatsapp',
                'phone_secondary',
                'secretariat_email',
                'region',
                'department',
                'district',
                'timezone',
                'locale',
                'description',
                'secretariat_hours',
            ]);
        });
    }
};
