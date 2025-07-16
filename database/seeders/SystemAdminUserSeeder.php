<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'avatar' => null,
            'first_name' => 'System',
            'middle_name' => null,
            'last_name' => 'Administrator',
            'nickname' => 'sysadmin',
            'email' => 'sysadmin@example.com',
            'roles' => UserRole::SYSTEM_ADMIN,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }
}
