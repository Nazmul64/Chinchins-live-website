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
        // 1. Seed Roles and Permissions
        $this->call(RoleAndPermissionSeeder::class);

        // 2. Admin User (Create / Restore)
        $this->call(AdminUserSeeder::class);

        // 3. Reseller System
        if (class_exists(\Database\Seeders\ResellerSeeder::class)) {
            $this->call(\Database\Seeders\ResellerSeeder::class);
        }

        // 4. Payment Methods
        if (class_exists(\Database\Seeders\PaymentMethodSeeder::class)) {
            $this->call(\Database\Seeders\PaymentMethodSeeder::class);
        }

        // 5. 6 Diamond Burst Motion Coin Packages
        $this->call(CoinPackageSeeder::class);

        // 6. 30 Strong-Motion Animated SVG Gifts
        $this->call(StrongMotionGiftsSeeder::class);

        // 7. VIP Privilege Cards (8 Cards)
        if (method_exists(\App\Models\VipPrivilegeCard::class, 'seedDefaultCards')) {
            \App\Models\VipPrivilegeCard::seedDefaultCards();
        }

        // 8. Spend Less Cards (4 Cards)
        if (method_exists(\App\Models\SpendLessCard::class, 'seedDefaultCards')) {
            \App\Models\SpendLessCard::seedDefaultCards();
        }

        // 9. My Bag Items (11 Items)
        if (method_exists(\App\Models\BagItem::class, 'seedDefaultItems')) {
            \App\Models\BagItem::seedDefaultItems();
        }

        // 10. Profile Bases
        if (method_exists(\App\Models\ProfileBase::class, 'seedDefaultBases')) {
            \App\Models\ProfileBase::seedDefaultBases();
        }
    }
}
