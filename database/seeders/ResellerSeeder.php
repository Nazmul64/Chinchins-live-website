<?php

namespace Database\Seeders;

use App\Models\Reseller;
use App\Models\ResellerSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Initialize Reseller Settings
        ResellerSetting::set('min_deposit', '500');
        ResellerSetting::set('max_deposit', '100000');
        ResellerSetting::set('min_withdraw', '1000');
        ResellerSetting::set('max_withdraw', '100000');
        ResellerSetting::set('withdraw_commission_rate', '2.50');
        ResellerSetting::set('reseller_coin_rate_per_bdt', '60.00');
        ResellerSetting::set('reseller_offer_badge', 'Up To 29%↑');
        ResellerSetting::set('reseller_instructions', "1. Transfer coins to valid User Account ID only.\n2. Confirm bKash/Nagad TrxID before sending coins.\n3. Contact admin live support for coin balance refills.");

        // 2. Create Reference Reseller matching user's screenshots
        if (Reseller::count() === 0) {
            Reseller::create([
                'name' => 'MURAD COINS RESELLER',
                'email' => 'murad@chinchins.live',
                'password' => Hash::make('password123'),
                'phone' => '01848340232',
                'level' => 'Lv5',
                'location' => 'Dhaka',
                'age' => 27,
                'gender' => 'male',
                'bio' => "কয়েন রিচার্জ, হোস্টিং এবং বিভিন্ন ধরণের গিফট ক্রয় করা হয়\nযোগাযোগ ০১848340232\nহোস্টিং সেলারি তুলনামূলক বেশি দেওয়া হয়\nঅনেক কথা বলা\nমানুষটা যদি হঠাৎ চুপ হয়ে যায়,\nবুঝে নিও আঘাতটা অনেক গভীরে লেগেছে।",
                'discount_tag' => 'Up To 29%↑',
                'badge_title' => 'Diamond Reseller',
                'coins_balance' => 500000,
                'total_sold_coins' => 125000,
                'is_active' => true,
                'is_online' => true,
            ]);

            Reseller::create([
                'name' => 'CHINCHINS OFFICIAL VIP RESELLER',
                'email' => 'vip.reseller@chinchins.live',
                'password' => Hash::make('password123'),
                'phone' => '01700000000',
                'level' => 'Lv10',
                'location' => 'Dhaka, Bangladesh',
                'age' => 29,
                'gender' => 'male',
                'bio' => "Official ChinChins Agent for 24/7 instant gem recharges, diamond packages, and VIP agency gifts. Fast, secure, and guaranteed.",
                'discount_tag' => 'Up To 29%↑',
                'badge_title' => 'Top Official Reseller',
                'coins_balance' => 1000000,
                'total_sold_coins' => 350000,
                'is_active' => true,
                'is_online' => true,
            ]);
        }
    }
}
