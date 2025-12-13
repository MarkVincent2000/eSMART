<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the roles table with the standard roles.
     */
    public function run(): void
    {
        foreach (['super-admin', 'admin', 'secretary', 'user'] as $role) {
            Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'web']
            );
        }
    }
}


