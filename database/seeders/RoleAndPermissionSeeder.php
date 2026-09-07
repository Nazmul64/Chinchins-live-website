<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // 1. Dashboard
            ['module' => 'dashboard', 'name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'Access main admin analytics dashboard'],

            // 2. Users & Balance
            ['module' => 'users', 'name' => 'View Users', 'slug' => 'users.view', 'description' => 'View registered users list and details'],
            ['module' => 'users', 'name' => 'Create User', 'slug' => 'users.create', 'description' => 'Create new user account from admin'],
            ['module' => 'users', 'name' => 'Edit User', 'slug' => 'users.edit', 'description' => 'Update user profile and details'],
            ['module' => 'users', 'name' => 'Delete User', 'slug' => 'users.delete', 'description' => 'Delete or ban user profile'],
            ['module' => 'users', 'name' => 'Toggle User Status', 'slug' => 'users.status', 'description' => 'Activate or deactivate user account'],
            ['module' => 'users', 'name' => 'Lock/Unlock Account', 'slug' => 'users.lock', 'description' => 'Lock or unlock user account'],
            ['module' => 'users', 'name' => 'Toggle Free Host Caller', 'slug' => 'users.free_caller', 'description' => 'Designate or revoke Free Host status'],
            ['module' => 'balance', 'name' => 'View Coin Balance', 'slug' => 'balance.view', 'description' => 'View user wallet and coin balance'],
            ['module' => 'balance', 'name' => 'Manual Coin Adjustment', 'slug' => 'balance.adjust', 'description' => 'Add, deduct, or set user coins'],

            // 3. Payment Methods
            ['module' => 'payment_methods', 'name' => 'View Payment Methods', 'slug' => 'payment_methods.view', 'description' => 'View deposit payment gateways'],
            ['module' => 'payment_methods', 'name' => 'Create Payment Method', 'slug' => 'payment_methods.create', 'description' => 'Add new gateway (bKash, Nagad, etc.)'],
            ['module' => 'payment_methods', 'name' => 'Edit Payment Method', 'slug' => 'payment_methods.edit', 'description' => 'Update gateway details and exchange rates'],
            ['module' => 'payment_methods', 'name' => 'Delete Payment Method', 'slug' => 'payment_methods.delete', 'description' => 'Delete payment method'],
            ['module' => 'payment_methods', 'name' => 'Toggle Payment Method Status', 'slug' => 'payment_methods.status', 'description' => 'Enable or disable gateway'],

            // 4. Coin Packages
            ['module' => 'coin_packages', 'name' => 'View Coin Packages', 'slug' => 'coin_packages.view', 'description' => 'View store coin packages'],
            ['module' => 'coin_packages', 'name' => 'Create Coin Package', 'slug' => 'coin_packages.create', 'description' => 'Add new recharge tier pack'],
            ['module' => 'coin_packages', 'name' => 'Edit Coin Package', 'slug' => 'coin_packages.edit', 'description' => 'Update coin amount and pricing'],
            ['module' => 'coin_packages', 'name' => 'Delete Coin Package', 'slug' => 'coin_packages.delete', 'description' => 'Delete recharge tier pack'],
            ['module' => 'coin_packages', 'name' => 'Toggle Coin Package Status', 'slug' => 'coin_packages.status', 'description' => 'Enable or disable coin pack in store'],

            // 5. Gifts & Rewards
            ['module' => 'gifts', 'name' => 'View Gifts', 'slug' => 'gifts.view', 'description' => 'View gifts catalog and rewards'],
            ['module' => 'gifts', 'name' => 'Create Gift', 'slug' => 'gifts.create', 'description' => 'Upload and add new live stream gift'],
            ['module' => 'gifts', 'name' => 'Edit Gift', 'slug' => 'gifts.edit', 'description' => 'Update gift animation, icon, and coin cost'],
            ['module' => 'gifts', 'name' => 'Delete Gift', 'slug' => 'gifts.delete', 'description' => 'Remove gift from catalog'],
            ['module' => 'gifts', 'name' => 'Toggle Gift Status', 'slug' => 'gifts.status', 'description' => 'Enable or disable gift in live stream'],
            ['module' => 'gifts', 'name' => 'Give Gift to User', 'slug' => 'gifts.give', 'description' => 'Directly reward gift to user profile'],

            // 6. My Bag Items
            ['module' => 'bag_items', 'name' => 'View Bag Items', 'slug' => 'bag_items.view', 'description' => 'View inventory items across 6 categories'],
            ['module' => 'bag_items', 'name' => 'Create Bag Item', 'slug' => 'bag_items.create', 'description' => 'Create new avatar frame, ride, or coupon'],
            ['module' => 'bag_items', 'name' => 'Edit Bag Item', 'slug' => 'bag_items.edit', 'description' => 'Update item duration, graphics, and price'],
            ['module' => 'bag_items', 'name' => 'Delete Bag Item', 'slug' => 'bag_items.delete', 'description' => 'Delete inventory item'],
            ['module' => 'bag_items', 'name' => 'Toggle Bag Item Status', 'slug' => 'bag_items.status', 'description' => 'Enable or disable store item'],

            // 7. Premium VIP
            ['module' => 'vip', 'name' => 'View VIP Cards', 'slug' => 'vip.view', 'description' => 'View VIP privilege tiers and subscriptions'],
            ['module' => 'vip', 'name' => 'Create VIP Card', 'slug' => 'vip.create', 'description' => 'Create monthly/weekly privilege card'],
            ['module' => 'vip', 'name' => 'Edit VIP Card', 'slug' => 'vip.edit', 'description' => 'Update card perks and pricing'],
            ['module' => 'vip', 'name' => 'Delete VIP Card', 'slug' => 'vip.delete', 'description' => 'Delete privilege card'],
            ['module' => 'vip', 'name' => 'Update Floating Banner', 'slug' => 'vip.floating_banner', 'description' => 'Update floating home VIP banner'],

            // 8. Spend Less, Get More
            ['module' => 'spend_less', 'name' => 'View Spend Less Cards', 'slug' => 'spend_less.view', 'description' => 'View daily check-in reward cards'],
            ['module' => 'spend_less', 'name' => 'Create Spend Less Card', 'slug' => 'spend_less.create', 'description' => 'Create new gems check-in package'],
            ['module' => 'spend_less', 'name' => 'Edit Spend Less Card', 'slug' => 'spend_less.edit', 'description' => 'Update gems reward amounts'],
            ['module' => 'spend_less', 'name' => 'Delete Spend Less Card', 'slug' => 'spend_less.delete', 'description' => 'Delete check-in card'],

            // 9. Level Badges & Frames
            ['module' => 'badges', 'name' => 'View Badges & Frames', 'slug' => 'badges.view', 'description' => 'View user level progression tiers'],
            ['module' => 'badges', 'name' => 'Create Level Tier', 'slug' => 'badges.create', 'description' => 'Create new level badge & base frame'],
            ['module' => 'badges', 'name' => 'Edit Level Tier', 'slug' => 'badges.edit', 'description' => 'Update coin threshold and frame graphic'],
            ['module' => 'badges', 'name' => 'Delete Level Tier', 'slug' => 'badges.delete', 'description' => 'Delete level progression tier'],

            // 10. Deposit Requests
            ['module' => 'deposits', 'name' => 'View Deposit Requests', 'slug' => 'deposits.view', 'description' => 'View manual user deposit requests'],
            ['module' => 'deposits', 'name' => 'Approve Deposit', 'slug' => 'deposits.approve', 'description' => 'Verify transaction and credit user coins'],
            ['module' => 'deposits', 'name' => 'Reject Deposit', 'slug' => 'deposits.reject', 'description' => 'Reject invalid deposit request'],
            ['module' => 'deposits', 'name' => 'Export Deposits', 'slug' => 'deposits.export', 'description' => 'Export deposit history records'],

            // 11. Withdrawals
            ['module' => 'withdrawals', 'name' => 'View Withdrawals', 'slug' => 'withdrawals.view', 'description' => 'View user coin cash out requests'],
            ['module' => 'withdrawals', 'name' => 'Approve Withdrawal', 'slug' => 'withdrawals.approve', 'description' => 'Approve payout and deduct coins'],
            ['module' => 'withdrawals', 'name' => 'Reject Withdrawal', 'slug' => 'withdrawals.reject', 'description' => 'Reject withdrawal request'],
            ['module' => 'withdrawals', 'name' => 'Withdrawal Settings', 'slug' => 'withdrawals.settings', 'description' => 'Configure commission rate and exchange limits'],
            ['module' => 'withdrawals', 'name' => 'Export Withdrawals', 'slug' => 'withdrawals.export', 'description' => 'Export cash out records'],

            // 12. KYC Verification
            ['module' => 'kyc', 'name' => 'View KYC Submissions', 'slug' => 'kyc.view', 'description' => 'View identity verification documents'],
            ['module' => 'kyc', 'name' => 'Approve KYC', 'slug' => 'kyc.approve', 'description' => 'Approve identity and mark user verified'],
            ['module' => 'kyc', 'name' => 'Reject KYC', 'slug' => 'kyc.reject', 'description' => 'Reject invalid KYC submission'],
            ['module' => 'kyc', 'name' => 'Revoke KYC', 'slug' => 'kyc.revoke', 'description' => 'Revoke previously approved verification'],

            // 13. User Reports
            ['module' => 'user_reports', 'name' => 'View User Reports', 'slug' => 'user_reports.view', 'description' => 'View in-app abuse and chat reports'],
            ['module' => 'user_reports', 'name' => 'Resolve Report', 'slug' => 'user_reports.resolve', 'description' => 'Mark report as reviewed and take action'],
            ['module' => 'user_reports', 'name' => 'Reject Report', 'slug' => 'user_reports.reject', 'description' => 'Dismiss invalid user report'],
            ['module' => 'user_reports', 'name' => 'Delete Report', 'slug' => 'user_reports.delete', 'description' => 'Delete report record'],

            // 14. Call & Revenue
            ['module' => 'call_revenue', 'name' => 'View Call Sessions & Revenue', 'slug' => 'call_revenue.view', 'description' => 'View live call history and split ledger'],
            ['module' => 'call_revenue', 'name' => 'Call & Ringtone Settings', 'slug' => 'call_revenue.settings', 'description' => 'Configure ringtones, rates, and promos'],
            ['module' => 'call_revenue', 'name' => 'Export Call Logs', 'slug' => 'call_revenue.export', 'description' => 'Export call history reports'],

            // 15. Coin Ledger
            ['module' => 'coin_ledger', 'name' => 'View Coin Ledger', 'slug' => 'coin_ledger.view', 'description' => 'View full system coin transaction ledger'],
            ['module' => 'coin_ledger', 'name' => 'Export Coin Ledger', 'slug' => 'coin_ledger.export', 'description' => 'Export coin transactions'],

            // 16. App Branding & Configuration
            ['module' => 'branding', 'name' => 'View App Branding & Config', 'slug' => 'branding.view', 'description' => 'View remote configuration and branding'],
            ['module' => 'branding', 'name' => 'Edit App Branding & Config', 'slug' => 'branding.edit', 'description' => 'Update app logo, FCM, streaming engine, legal terms'],

            // 17. Roles Management
            ['module' => 'roles', 'name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View staff roles and permissions list'],
            ['module' => 'roles', 'name' => 'Create Role', 'slug' => 'roles.create', 'description' => 'Create new custom staff role'],
            ['module' => 'roles', 'name' => 'Edit Role', 'slug' => 'roles.edit', 'description' => 'Update role metadata and description'],
            ['module' => 'roles', 'name' => 'Delete Role', 'slug' => 'roles.delete', 'description' => 'Delete custom staff role'],
            ['module' => 'roles', 'name' => 'Manage Role Permissions', 'slug' => 'roles.permissions', 'description' => 'Assign and revoke module permissions to roles'],

            // 18. Staff Management
            ['module' => 'staff', 'name' => 'View Staff Directory', 'slug' => 'staff.view', 'description' => 'View admin and staff accounts'],
            ['module' => 'staff', 'name' => 'Create Staff', 'slug' => 'staff.create', 'description' => 'Create new staff member and assign role'],
            ['module' => 'staff', 'name' => 'Edit Staff', 'slug' => 'staff.edit', 'description' => 'Update staff profile, role, and status'],
            ['module' => 'staff', 'name' => 'Delete Staff', 'slug' => 'staff.delete', 'description' => 'Delete or suspend staff account'],
            ['module' => 'staff', 'name' => 'Manage User Permissions', 'slug' => 'staff.permissions', 'description' => 'Grant or deny individual custom permissions'],
            ['module' => 'staff', 'name' => 'Reset Staff Password', 'slug' => 'staff.password_reset', 'description' => 'Reset password for staff member'],

            // 19. Activity Logs & Login History
            ['module' => 'activity_logs', 'name' => 'View Activity Logs', 'slug' => 'activity_logs.view', 'description' => 'View system audit trail of sensitive actions'],
            ['module' => 'login_history', 'name' => 'View Login History', 'slug' => 'login_history.view', 'description' => 'View staff login sessions, IP, and device logs'],
        ];

        // 1. Seed or Update Permissions
        $createdPermissions = [];
        foreach ($permissions as $perm) {
            $createdPermissions[$perm['slug']] = Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                [
                    'module'      => $perm['module'],
                    'name'        => $perm['name'],
                    'description' => $perm['description'],
                ]
            );
        }

        // 2. Create Default Roles
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name'        => 'Super Admin',
                'description' => 'Full administrative access to all modules, roles, staff, and core system settings.',
                'status'      => 'active',
                'is_default'  => true,
            ]
        );

        $subAdminRole = Role::updateOrCreate(
            ['slug' => 'sub-admin'],
            [
                'name'        => 'Sub Admin',
                'description' => 'Broad administrative access excluding role/staff deletion and core security configuration.',
                'status'      => 'active',
                'is_default'  => true,
            ]
        );

        $managerRole = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name'        => 'Manager',
                'description' => 'Operations manager responsible for deposits, withdrawals, KYC reviews, and user reports.',
                'status'      => 'active',
                'is_default'  => true,
            ]
        );

        $employeeRole = Role::updateOrCreate(
            ['slug' => 'employee'],
            [
                'name'        => 'Employee',
                'description' => 'Limited operational access for task processing, KYC inspection, and user query assistance.',
                'status'      => 'active',
                'is_default'  => true,
            ]
        );

        // 3. Assign Permissions to Default Roles
        $allPermissionIds = Permission::pluck('id')->toArray();
        $superAdminRole->permissions()->sync($allPermissionIds);

        // Sub Admin Permissions (All except sensitive deletions and branding/core config)
        $subAdminPermSlugs = array_filter(array_keys($createdPermissions), function ($slug) {
            return !in_array($slug, [
                'roles.delete',
                'roles.create',
                'staff.delete',
                'branding.edit',
                'configuration.edit',
            ]);
        });
        $subAdminPermIds = Permission::whereIn('slug', $subAdminPermSlugs)->pluck('id')->toArray();
        $subAdminRole->permissions()->sync($subAdminPermIds);

        // Manager Permissions
        $managerPermSlugs = [
            'dashboard.view',
            'users.view',
            'users.status',
            'balance.view',
            'deposits.view',
            'deposits.approve',
            'deposits.reject',
            'deposits.export',
            'withdrawals.view',
            'withdrawals.approve',
            'withdrawals.reject',
            'withdrawals.export',
            'kyc.view',
            'kyc.approve',
            'kyc.reject',
            'user_reports.view',
            'user_reports.resolve',
            'user_reports.reject',
            'call_revenue.view',
            'coin_ledger.view',
        ];
        $managerPermIds = Permission::whereIn('slug', $managerPermSlugs)->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPermIds);

        // Employee Permissions
        $employeePermSlugs = [
            'dashboard.view',
            'users.view',
            'deposits.view',
            'withdrawals.view',
            'kyc.view',
            'user_reports.view',
        ];
        $employeePermIds = Permission::whereIn('slug', $employeePermSlugs)->pluck('id')->toArray();
        $employeeRole->permissions()->sync($employeePermIds);

        // 4. Assign Super Admin Role to first user / main admin in database
        $firstAdmin = User::first();
        if ($firstAdmin && empty($firstAdmin->role_id)) {
            $firstAdmin->role_id = $superAdminRole->id;
            $firstAdmin->status = 'active';
            $firstAdmin->save();
        }
    }
}
