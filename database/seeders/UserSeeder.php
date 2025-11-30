<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with predefined users and assign roles.
     */
    public function run(): void
    {
        // User 1: Admin user with super-admin role
        $admin = User::firstOrCreate(
            ['email' => 'admin@themesbrand.com'],
            [
                'name' => 'admin',
                'first_name' => null,
                'last_name' => null,
                'middle_name' => null,
                'name_extension' => null,
                'password' => Hash::make('12345678'),
                'avatar' => null,
                'photo_path' => null,
                'cover_photo_path' => null,
                'active_status' => true,
                'email_verified_at' => null,
                'created_at' => '2025-11-27 18:37:31',
                'updated_at' => '2025-11-27 18:37:31',
            ]
        );

        // Assign super-admin role to admin user
        $superAdminRole = Role::where('name', 'super-admin')->where('guard_name', 'web')->first();
        if ($superAdminRole && !$admin->hasRole('super-admin')) {
            $admin->assignRole($superAdminRole);
        }

        // User 2: Mark Vincent Quiao with admin role
        $markVincent = User::firstOrCreate(
            ['email' => 'markvincentquiao@gmail.com'],
            [
                'name' => 'Mark Vincent Quiao',
                'first_name' => 'Mark Vincent',
                'last_name' => 'Quiao',
                'middle_name' => null,
                'name_extension' => null,
                'password' => Hash::make('12345678'),
                'avatar' => null,
                'photo_path' => null,
                'cover_photo_path' => null,
                'active_status' => true,
                'email_verified_at' => null,
                'created_at' => '2025-11-27 18:44:46',
                'updated_at' => '2025-11-27 18:47:15',
            ]
        );

        // Assign admin role to Mark Vincent Quiao
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRole && !$markVincent->hasRole('admin')) {
            $markVincent->assignRole($adminRole);
        }


        // User 3: John Doe with user role
        $johnDoe = User::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'name' => 'John Doe',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'middle_name' => null,
                'name_extension' => null,
                'password' => Hash::make('12345678'),
                'avatar' => null,
                'photo_path' => null,
                'cover_photo_path' => null,
                'active_status' => true,
                'email_verified_at' => null,
                'created_at' => '2025-11-27 18:37:31',
                'updated_at' => '2025-11-27 18:37:31',
            ]
        );

        // Assign user role to John Doe
        $userRole = Role::where('name', 'user')->where('guard_name', 'web')->first();
        if ($userRole && !$johnDoe->hasRole('user')) {
            $johnDoe->assignRole($userRole);
        }
    }
}

