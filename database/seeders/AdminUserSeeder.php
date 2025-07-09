<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'avatar' => null,
            'first_name' => 'Admin',
            'middle_name' => null,
            'last_name' => 'User',
            'nickname' => 'admin',
            'email' => 'admin@example.com',
            'roles' => UserRole::ADMIN,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'avatar' => null,
            'first_name' => 'System',
            'middle_name' => null,
            'last_name' => 'Admin',
            'nickname' => 'system-admin',
            'email' => 'system-admin@example.com',
            'roles' => UserRole::ADMIN,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'avatar' => null,
            'first_name' => 'Sample',
            'middle_name' => null,
            'last_name' => 'User',
            'nickname' => 'sample-user',
            'email' => 'sample-user@example.com',
            'roles' => UserRole::EMPLOYEE,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'avatar' => null,
            'first_name' => 'Sample',
            'middle_name' => null,
            'last_name' => 'User2',
            'nickname' => 'sample-user2',
            'email' => 'sample-user2@example.com',
            'roles' => UserRole::EMPLOYEE,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
