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
    }
}
