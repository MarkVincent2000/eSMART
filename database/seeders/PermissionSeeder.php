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
        // Create the permission
        $permission = Permission::firstOrCreate(
            ['name' => 'view-admin-management', 'guard_name' => 'web'],
            [
                'created_at' => '2025-11-28 04:34:17',
                'updated_at' => '2025-11-28 04:34:17',
            ]
        );

        // Assign permission to role_id 1 (super-admin)
        $role1 = Role::find(1);
        if ($role1 && !$role1->hasPermissionTo($permission)) {
            $role1->givePermissionTo($permission);
        }

        // Assign permission to role_id 2 (admin)
        $role2 = Role::find(2);
        if ($role2 && !$role2->hasPermissionTo($permission)) {
            $role2->givePermissionTo($permission);
        }
    }
}

