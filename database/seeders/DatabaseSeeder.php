<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the Admin and System Admin seeders
        $this->call([
            AdminUserSeeder::class,
            SystemAdminUserSeeder::class,
        ]);
    }
}
