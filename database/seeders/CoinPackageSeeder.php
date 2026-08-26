<?php

namespace Database\Seeders;

use App\Models\CoinPackage;
use Illuminate\Database\Seeder;

class CoinPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'coins' => 6000,
                'bonus_coins' => 1000,
                'price' => 120.00,
                'badge' => null,
                'badge_color' => 'secondary',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'coins' => 32000,
                'bonus_coins' => 8000,
                'price' => 550.00,
                'badge' => '🔥 50% OFF',
                'badge_color' => 'pink',
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'coins' => 70000,
                'bonus_coins' => 20000,
                'price' => 1150.00,
                'badge' => 'Best Value',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'coins' => 150000,
                'bonus_coins' => 50000,
                'price' => 2400.00,
                'badge' => '+30% Free',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'coins' => 350000,
                'bonus_coins' => 120000,
                'price' => 5500.00,
                'badge' => 'VIP Bonus',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($packages as $pkg) {
            CoinPackage::updateOrCreate(
                ['coins' => $pkg['coins'], 'price' => $pkg['price']],
                $pkg
            );
        }
    }
}
