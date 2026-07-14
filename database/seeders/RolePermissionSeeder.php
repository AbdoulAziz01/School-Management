<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Définit la matrice rôles/permissions réelle (spatie/laravel-permission).
 *
 * Chaque rôle Spatie porte exactement le nom de la valeur stockée dans
 * users.role, pour que User::syncRoleFromColumn() (app/Models/User.php)
 * puisse garder les deux systèmes synchronisés automatiquement à chaque
 * sauvegarde, sans double saisie ni dérive.
 *
 * NB : ces permissions sont désormais réelles et assignables (Gate::allows(),
 * $user->can(), middleware permission:) mais ne remplacent pas encore, à ce
 * stade, les vérifications `$user->role === '...'` déjà en place dans les
 * contrôleurs — ce seeder pose les fondations RBAC (nécessaires pour des
 * rôles fins comme comptable/caissier), la migration contrôleur par
 * contrôleur vers des Policies est un chantier séparé et volontairement
 * hors scope ici pour ne pas modifier le comportement d'autorisation
 * existant sans campagne de tests dédiée.
 */
class RolePermissionSeeder extends Seeder
{
    /** @return array<string, list<string>> */
    private function permissionMatrix(): array
    {
        $studentsCrud = ['students.view', 'students.create', 'students.update', 'students.delete'];
        $classesCrud  = ['classes.view', 'classes.create', 'classes.update', 'classes.delete'];
        $gradesCrud   = ['grades.view', 'grades.create', 'grades.update', 'grades.delete'];
        $teachersCrud = ['teachers.view', 'teachers.create', 'teachers.update', 'teachers.delete'];
        $attendance   = ['attendance.view', 'attendance.record'];

        return [
            User::ROLE_ADMIN => [
                ...$studentsCrud, ...$classesCrud, ...$gradesCrud, ...$teachersCrud, ...$attendance,
                'users.manage', 'settings.manage',
            ],
            User::ROLE_SURVEILLANT => [
                'students.view', 'classes.view', 'grades.view', ...$attendance,
            ],
            User::ROLE_TEACHER => [
                'students.view', 'classes.view',
                'grades.view', 'grades.create', 'grades.update',
                ...$attendance,
            ],
            User::ROLE_STUDENT => [],
        ];
    }

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $allPermissions = collect($this->permissionMatrix())->flatten()->unique();
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // super_admin n'a pas de permissions listées explicitement : il passe
        // par le bypass Gate::before() (voir AppServiceProvider::boot()).
        $roleNames = [User::ROLE_SUPER_ADMIN, ...array_keys($this->permissionMatrix())];
        foreach ($roleNames as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        foreach ($this->permissionMatrix() as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            $role?->syncPermissions($permissions);
        }

        // Aligne tous les utilisateurs existants sur leur rôle Spatie
        // correspondant, à partir de la colonne users.role qui reste la
        // source de vérité fonctionnelle actuelle.
        User::withoutGlobalScopes()->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $user->syncRoleFromColumn();
            }
        });
    }
}
