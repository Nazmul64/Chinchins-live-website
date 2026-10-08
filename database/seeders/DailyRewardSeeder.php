<?php

namespace Database\Seeders;

use App\Models\DailyReward;
use Illuminate\Database\Seeder;

class DailyRewardSeeder extends Seeder
{
    public function run(): void
    {
        $rewards = [
            ['day_number' => 1, 'reward_coins' => 50],
            ['day_number' => 2, 'reward_coins' => 50],
            ['day_number' => 3, 'reward_coins' => 50],
            ['day_number' => 4, 'reward_coins' => 50],
            ['day_number' => 5, 'reward_coins' => 50],
            ['day_number' => 6, 'reward_coins' => 50],
            ['day_number' => 7, 'reward_coins' => 100], // Day 7 bonus: 100 reward
        ];

        foreach ($rewards as $reward) {
            DailyReward::updateOrCreate(
                ['day_number' => $reward['day_number']],
                [
                    'reward_coins' => $reward['reward_coins'],
                    'is_active'    => true,
                    'icon_image'   => null,
                ]
            );
        }
    }
}
