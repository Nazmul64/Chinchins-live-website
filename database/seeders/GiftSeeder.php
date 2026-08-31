<?php

namespace Database\Seeders;

use App\Models\Gift;
use App\Models\User;
use App\Models\UserGift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class GiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uploadDir = public_path('uploads/gifts');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0777, true, true);
        }

        // 10 Core Gifts matching Chinchins Live app screenshots & design
        $sampleGifts = [
            [
                'name'        => 'Romantic Couple',
                'coins'       => 17700, // 17.70K
                'category'    => 'romantic',
                'badge'       => 'HOT',
                'sort_order'  => 1,
                'image'       => 'uploads/gifts/romantic_couple.png',
            ],
            [
                'name'        => 'Golden Sunset Couple',
                'coins'       => 17700, // 17.70K
                'category'    => 'romantic',
                'badge'       => 'HOT',
                'sort_order'  => 2,
                'image'       => 'uploads/gifts/sunset_couple.png',
            ],
            [
                'name'        => 'Vintage Romance',
                'coins'       => 17000, // 17K
                'category'    => 'romantic',
                'badge'       => 'VIP',
                'sort_order'  => 3,
                'image'       => 'uploads/gifts/vintage_romance.png',
            ],
            [
                'name'        => 'Crystal Castle',
                'coins'       => 10000, // 10K
                'category'    => 'luxury',
                'badge'       => 'LUXURY',
                'sort_order'  => 4,
                'image'       => 'uploads/gifts/crystal_castle.png',
            ],
            [
                'name'        => 'Supercar & Billionaire',
                'coins'       => 9990, // 9.99K
                'category'    => 'luxury',
                'badge'       => '3D',
                'sort_order'  => 5,
                'image'       => 'uploads/gifts/supercar_luxury.png',
            ],
            [
                'name'        => 'Fairy Palace Crown',
                'coins'       => 7200, // 7.20K
                'category'    => 'luxury',
                'badge'       => 'NEW',
                'sort_order'  => 6,
                'image'       => 'uploads/gifts/fairy_crown.png',
            ],
            [
                'name'        => 'Space Battleship',
                'coins'       => 6660, // 6.66K
                'category'    => 'effects',
                'badge'       => '3D',
                'sort_order'  => 7,
                'image'       => 'uploads/gifts/space_battleship.png',
            ],
            [
                'name'        => 'Fire Dragon',
                'coins'       => 5550, // 5.55K
                'category'    => 'effects',
                'badge'       => 'HOT',
                'sort_order'  => 8,
                'image'       => 'uploads/gifts/fire_dragon.png',
            ],
            [
                'name'        => 'Treasure Chest',
                'coins'       => 5000, // 5K
                'category'    => 'popular',
                'badge'       => 'GOLD',
                'sort_order'  => 9,
                'image'       => 'uploads/gifts/treasure_chest.png',
            ],
            [
                'name'        => 'Rose Bouquet',
                'coins'       => 500, // 500
                'category'    => 'popular',
                'badge'       => 'POPULAR',
                'sort_order'  => 10,
                'image'       => 'uploads/gifts/rose_bouquet.png',
            ],
        ];

        $createdGifts = [];
        foreach ($sampleGifts as $giftData) {
            $createdGifts[] = Gift::updateOrCreate(
                ['name' => $giftData['name']],
                $giftData
            );
        }

        // Assign sample received gifts to users to match Screenshot 1 & 2
        $users = User::take(5)->get();
        if ($users->isNotEmpty() && !empty($createdGifts)) {
            $sender = $users->last(); // Fan / Top Gifter (e.g. Sajid)

            // Quantities matching mobile app screenshots
            $demoCounts = [
                0 => 2,   // Romantic Couple x2 (💎 17.70K)
                1 => 1,   // Sunset Couple x1 (💎 17.70K)
                2 => 4,   // Vintage Romance x4 (💎 17K)
                3 => 2,   // Crystal Castle x2 (💎 10K)
                4 => 32,  // Supercar x32 (💎 9.99K)
                5 => 1,   // Fairy Crown x1 (💎 7.20K)
                6 => 4,   // Battleship x4 (💎 6.66K)
                7 => 12,  // Fire Dragon x12 (💎 5.55K)
                8 => 18,  // Treasure chest x18 (💎 5K)
                9 => 80,  // Rose bouquet x80 (💎 500)
            ];

            foreach ($users as $hostUser) {
                // Remove old seed records if re-seeding to keep it neat
                UserGift::where('user_id', $hostUser->id)->delete();

                foreach ($demoCounts as $gIdx => $qty) {
                    if (isset($createdGifts[$gIdx])) {
                        $gift = $createdGifts[$gIdx];
                        UserGift::create([
                            'user_id'        => $hostUser->id,
                            'sender_id'      => $sender->id !== $hostUser->id ? $sender->id : null,
                            'gift_id'        => $gift->id,
                            'quantity'       => $qty,
                            'coins_per_unit' => $gift->coins,
                            'total_coins'    => $gift->coins * $qty,
                            'context'        => 'profile',
                        ]);
                    }
                }
            }
        }
    }
}
