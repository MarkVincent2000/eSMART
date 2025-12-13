<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the permissions table and assign them to roles.
     */
    public function run(): void
    {
        $permissionMatrix = [
            'view-user-management' => ['super-admin','admin'],
            'view-role-management' => ['super-admin'],
            'view-permission-management' => ['super-admin'],
            'view-student-management' => ['super-admin','admin'],
            'view-enrollment-management' => ['super-admin','admin'],
            'view-semester-management' => ['super-admin'],
            'view-my-info' => ['user'],
            'manage-engagement' => ['super-admin','admin'],
        ];

        foreach ($permissionMatrix as $permissionName => $roleNames) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $roles = Role::whereIn('name', $roleNames)->get();

            foreach ($roles as $role) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }
}

