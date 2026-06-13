<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InitSuperAdmin extends Command
{
    protected $signature   = 'app:init-super-admin {--force : Bypass confirmation}';
    protected $description = 'Supprime TOUS les utilisateurs et toutes les données, puis recrée uniquement le super admin.';

    public function handle(): int
    {
        $email    = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');
        $name     = env('SUPER_ADMIN_NAME', 'Super Administrateur');

        if (! $email || ! $password) {
            $this->error('SUPER_ADMIN_EMAIL et SUPER_ADMIN_PASSWORD doivent être définis dans .env');
            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $this->warn('⚠  Cette commande va SUPPRIMER ABSOLUMENT TOUS les utilisateurs, écoles et données.');
            $this->line("   Seul le super admin <{$email}> sera recréé.");
            if (! $this->confirm('Continuer ?', false)) {
                $this->info('Annulé.');
                return self::SUCCESS;
            }
        }

        $this->line('Nettoyage en cours...');

        Schema::disableForeignKeyConstraints();

        try {
            $dataTables = [
                'grades', 'attendances', 'schedules', 'events', 'assignments', 'timetables',
                'teacher_assignments', 'class_subject', 'class_teacher', 'teacher_subjects',
                'level_subject', 'class_group_student', 'class_groups',
                'formation_promotions', 'formation_departments', 'sessions',
            ];

            foreach ($dataTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            // Nettoyer les rôles Spatie liés aux utilisateurs
            $permTables = config('permission.table_names', []);
            foreach (['model_has_roles', 'model_has_permissions'] as $key) {
                if (! empty($permTables[$key]) && Schema::hasTable($permTables[$key])) {
                    DB::table($permTables[$key])->where('model_type', User::class)->delete();
                }
            }

            // Supprimer les classes, matières, niveaux, années scolaires, écoles
            foreach (['classes', 'subjects', 'levels', 'academic_years', 'schools'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            // Supprimer TOUS les utilisateurs
            User::withoutGlobalScopes()->delete();

        } finally {
            Schema::enableForeignKeyConstraints();
        }

        // Recréer le super admin
        $superAdmin = User::withoutGlobalScopes()->create([
            'name'               => $name,
            'email'              => $email,
            'identifier'         => 'SA' . date('Y') . '001',
            'password'           => Hash::make($password),
            'role'               => User::ROLE_SUPER_ADMIN,
            'status'             => User::STATUS_APPROVED,
            'school_id'          => null,
            'email_verified_at'  => now(),
        ]);

        $this->newLine();
        $this->info('Réinitialisation terminée.');
        $this->table(
            ['Champ', 'Valeur'],
            [
                ['ID',     $superAdmin->id],
                ['Nom',    $superAdmin->name],
                ['Email',  $superAdmin->email],
                ['Rôle',   $superAdmin->role],
                ['Statut', $superAdmin->status],
            ]
        );
        $this->newLine();
        $this->line('Connectez-vous sur /login avec cet email et le mot de passe défini dans SUPER_ADMIN_PASSWORD.');

        return self::SUCCESS;
    }
}
