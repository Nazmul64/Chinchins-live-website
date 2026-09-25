<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles and Permissions for RBAC System
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Primary Super Admin Login Account (admin@gmail.com)
        $this->call(AdminUserSeeder::class);
    }
}
