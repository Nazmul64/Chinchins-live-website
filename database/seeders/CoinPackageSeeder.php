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
                'title' => 'Starter Pack',
                'coins' => 7560,
                'bonus_coins' => 0,
                'price' => 150.00,
                'currency' => 'BDT',
                'badge' => '50% off',
                'badge_color' => 'danger',
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Basic Pack',
                'coins' => 8100,
                'bonus_coins' => 0,
                'price' => 300.00,
                'currency' => 'BDT',
                'badge' => '17% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Popular Pack',
                'coins' => 16380,
                'bonus_coins' => 0,
                'price' => 600.00,
                'currency' => 'BDT',
                'badge' => '17% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Super Pack',
                'coins' => 32940,
                'bonus_coins' => 0,
                'price' => 1200.00,
                'currency' => 'BDT',
                'badge' => '30% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Mega Pack',
                'coins' => 66600,
                'bonus_coins' => 0,
                'price' => 2400.00,
                'currency' => 'BDT',
                'badge' => '60% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'VIP King Pack',
                'coins' => 167400,
                'bonus_coins' => 0,
                'price' => 6100.00,
                'currency' => 'BDT',
                'badge' => '80% off',
                'badge_color' => 'danger',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($packages as $pkg) {
            CoinPackage::updateOrCreate(
                ['coins' => $pkg['coins']],
                $pkg
            );
        }
    }
}
