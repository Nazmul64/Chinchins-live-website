<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VipPrivilegeCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cards = [
            [
                'card_type'                 => 'super_monthly',
                'name'                      => 'Super Monthly VIP Card',
                'category_name'             => 'Super Monthly VIP Card',
                'badge_text'                => 'BEST VALUE',
                'price'                     => 300.00,
                'price_bdt'                 => 300.00,
                'original_price_bdt'        => 600.00,
                'price_coins'               => 300,
                'diamonds_reward'           => 32940,
                'cost_diamonds'             => 300,
                'daily_checkin_diamonds'    => 26330,
                'perks'                     => '3 Perks',
                'outfits'                   => 'VIP Outfits',
                'validity_days'             => 30,
                'duration_days'             => 30,
                'instant_reward_coins'      => 32940,
                'instant_reward_text'       => 'Gems in total 32,940',
                'daily_checkin_total_coins' => 26330,
                'daily_checkin_text'        => 'Gems in total 26,330',
                'total_return_coins'        => 59270,
                'daily_schedule'            => json_encode(array_map(fn($day) => [
                    'day_number'   => $day,
                    'reward_coins' => 877,
                    'text'         => "Day {$day}: 877 Gems",
                ], range(1, 30))),
                'extra_rewards'             => json_encode([
                    ['name' => 'Exclusive VIP Badge', 'type' => 'badge'],
                    ['name' => 'VIP Golden Avatar Frame', 'type' => 'frame'],
                    ['name' => 'VIP Chat Bubble', 'type' => 'bubble'],
                ]),
                'description'               => 'Unlock massive diamonds and exclusive VIP privileges for 30 days.',
                'card_color'                => '#FF4081',
                'banner_tag'                => 'Spend Less, Get More Gems!',
                'is_active'                 => true,
                'sort_order'                => 1,
            ],
            [
                'card_type'                 => 'new_user',
                'name'                      => 'New User Weekly Card',
                'category_name'             => 'New User Weekly Card',
                'badge_text'                => 'HOT',
                'price'                     => 150.00,
                'price_bdt'                 => 150.00,
                'original_price_bdt'        => 300.00,
                'price_coins'               => 150,
                'diamonds_reward'           => 8100,
                'cost_diamonds'             => 150,
                'daily_checkin_diamonds'    => 2020,
                'perks'                     => '2 Perks',
                'outfits'                   => 'Weekly Outfits',
                'validity_days'             => 7,
                'duration_days'             => 7,
                'instant_reward_coins'      => 8100,
                'instant_reward_text'       => 'Gems in total 8,100',
                'daily_checkin_total_coins' => 2020,
                'daily_checkin_text'        => 'Gems in total 2,020',
                'total_return_coins'        => 10120,
                'daily_schedule'            => json_encode(array_map(fn($day) => [
                    'day_number'   => $day,
                    'reward_coins' => 288,
                    'text'         => "Day {$day}: 288 Gems",
                ], range(1, 7))),
                'extra_rewards'             => json_encode([
                    ['name' => 'Newbie VIP Badge', 'type' => 'badge'],
                    ['name' => 'Silver Avatar Frame', 'type' => 'frame'],
                ]),
                'description'               => 'Special starter pack for new users for 7 days.',
                'card_color'                => '#7C4DFF',
                'banner_tag'                => 'Newbie Special Discount',
                'is_active'                 => true,
                'sort_order'                => 2,
            ],
            [
                'card_type'                 => 'luxury_monthly',
                'name'                      => 'Luxury Monthly VIP Card',
                'category_name'             => 'Luxury Monthly VIP Card',
                'badge_text'                => '50% OFF',
                'price'                     => 600.00,
                'price_bdt'                 => 600.00,
                'original_price_bdt'        => 1200.00,
                'price_coins'               => 600,
                'diamonds_reward'           => 65880,
                'cost_diamonds'             => 600,
                'daily_checkin_diamonds'    => 52660,
                'perks'                     => '5 Perks',
                'outfits'                   => 'Luxury VIP Outfits',
                'validity_days'             => 30,
                'duration_days'             => 30,
                'instant_reward_coins'      => 65880,
                'instant_reward_text'       => 'Gems in total 65,880',
                'daily_checkin_total_coins' => 52660,
                'daily_checkin_text'        => 'Gems in total 52,660',
                'total_return_coins'        => 118540,
                'daily_schedule'            => json_encode(array_map(fn($day) => [
                    'day_number'   => $day,
                    'reward_coins' => 1755,
                    'text'         => "Day {$day}: 1755 Gems",
                ], range(1, 30))),
                'extra_rewards'             => json_encode([
                    ['name' => 'Luxury Diamond Badge', 'type' => 'badge'],
                    ['name' => 'Diamond Avatar Frame', 'type' => 'frame'],
                    ['name' => 'Luxury Entrance Animation', 'type' => 'entrance'],
                    ['name' => 'Special Chat Bubble', 'type' => 'bubble'],
                ]),
                'description'               => 'Ultimate luxury VIP experience with maximum daily diamonds.',
                'card_color'                => '#FFD700',
                'banner_tag'                => 'Ultimate VIP Privilege',
                'is_active'                 => true,
                'sort_order'                => 3,
            ]
        ];

        foreach ($cards as $cardData) {
            $name = $cardData['name'];
            $cardData['created_at'] = now();
            $cardData['updated_at'] = now();

            DB::table('vip_privilege_cards')->updateOrInsert(
                ['name' => $name],
                $cardData
            );
        }
    }
}
