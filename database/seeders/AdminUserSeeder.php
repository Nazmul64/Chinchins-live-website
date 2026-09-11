<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds to create or restore the primary Super Admin account.
     */
    public function run(): void
    {
        // 1. Ensure Super Admin role exists
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name'        => 'Super Admin',
                'description' => 'Full administrative access to all modules, roles, staff, and core system settings.',
                'status'      => 'active',
                'is_default'  => true,
            ]
        );

        // Sync all permissions to Super Admin role
        if (class_exists(Permission::class)) {
            $allPermIds = Permission::pluck('id')->toArray();
            if (!empty($allPermIds)) {
                $superAdminRole->permissions()->sync($allPermIds);
            }
        }

        // 2. Check for existing or soft-deleted admin account
        $admin = User::withTrashed()
            ->where(function ($q) {
                $q->where('email', 'admin@gmail.com')
                  ->orWhere('email', 'like', '%admin@gmail.com')
                  ->orWhere('account_id', '1000000001');
            })
            ->first();

        if ($admin) {
            if ($admin->trashed()) {
                $admin->restore();
            }
            $admin->email                 = 'admin@gmail.com';
            $admin->name                  = 'Super Admin';
            $admin->nickname              = 'Admin';
            $admin->first_name            = 'Admin';
            $admin->last_name             = 'User';
            $admin->phone                 = '01700000000';
            $admin->password              = Hash::make('admin@gmail.com');
            $admin->account_id            = '1000000001';
            $admin->role_id               = $superAdminRole->id;
            $admin->status                = 'active';
            $admin->is_active             = true;
            $admin->is_verified           = true;
            $admin->is_locked             = false;
            $admin->locked_reason         = null;
            $admin->locked_until          = null;
            $admin->failed_login_attempts = 0;
            $admin->deleted_reason        = null;
            $admin->deleted_by            = null;
            $admin->level                 = 'Lv10';
            $admin->email_verified_at     = now();
            $admin->save();
        } else {
            User::create([
                'email'                 => 'admin@gmail.com',
                'name'                  => 'Super Admin',
                'nickname'              => 'Admin',
                'first_name'            => 'Admin',
                'last_name'             => 'User',
                'phone'                 => '01700000000',
                'password'              => Hash::make('admin@gmail.com'),
                'account_id'            => '1000000001',
                'role_id'               => $superAdminRole->id,
                'status'                => 'active',
                'is_active'             => true,
                'is_verified'           => true,
                'is_locked'             => false,
                'locked_reason'         => null,
                'locked_until'          => null,
                'failed_login_attempts' => 0,
                'level'                 => 'Lv10',
                'email_verified_at'     => now(),
            ]);
        }
    }
}
