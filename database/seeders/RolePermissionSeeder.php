<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            User::ROLE_ADMIN,
            User::ROLE_TEACHER,
            User::ROLE_STUDENT,
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Assign admin role to the first user
        $admin = User::first();
        if ($admin) {
            $admin->assignRole(User::ROLE_ADMIN);
        }

        // Assign student role to users with role = 'eleve'
        $students = User::where('role', 'eleve')->get();
        foreach ($students as $student) {
            $student->assignRole(User::ROLE_STUDENT);
        }

        // Assign teacher role to users with role = 'professeur'
        $teachers = User::where('role', 'professeur')->get();
        foreach ($teachers as $teacher) {
            $teacher->assignRole(User::ROLE_TEACHER);
        }
    }
}
