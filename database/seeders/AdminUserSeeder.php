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
            'first_name' => 'Michael Amus',
            'middle_name' => 'Hillario',
            'last_name' => 'Refugio',
            'nickname' => 'mhr',
            'email' => 'michael.refugio@mhrhealthcare.com',
            'roles' => UserRole::ADMIN,
            'password' => Hash::make('Mhr@2025'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'avatar' => null,
            'first_name' => 'Angelu',
            'middle_name' => 'Sarco',
            'last_name' => 'De Jesus',
            'nickname' => 'jelay',
            'email' => 'angeludejesus43@gmail.com',
            'roles' => UserRole::EMPLOYEE,
            'password' => Hash::make('@Angelu07'),
            'email_verified_at' => now(),
        ]);
    }
}
