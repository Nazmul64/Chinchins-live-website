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

        // List of gifts matching Chinchins Live screenshots & app design
        $sampleGifts = [
            [
                'name'        => 'Romantic Couple',
                'coins'       => 17700, // 17.70K
                'category'    => 'romantic',
                'badge'       => 'HOT',
                'sort_order'  => 1,
                'image'       => 'uploads/gifts/romantic_couple.png',
                'description' => 'A passionate romantic hug under moonlit stars.'
            ],
            [
                'name'        => 'Golden Sunset Couple',
                'coins'       => 17700, // 17.70K
                'category'    => 'romantic',
                'badge'       => 'HOT',
                'sort_order'  => 2,
                'image'       => 'uploads/gifts/sunset_couple.png',
                'description' => 'Sunset romantic portrait gift.'
            ],
            [
                'name'        => 'Vintage Romance',
                'coins'       => 17000, // 17K
                'category'    => 'romantic',
                'badge'       => 'VIP',
                'sort_order'  => 3,
                'image'       => 'uploads/gifts/vintage_romance.png',
                'description' => 'Classic cinematic romantic moment.'
            ],
            [
                'name'        => 'Candlelight Dinner',
                'coins'       => 17000, // 17K
                'category'    => 'luxury',
                'badge'       => 'VIP',
                'sort_order'  => 4,
                'image'       => 'uploads/gifts/candlelight_dinner.png',
                'description' => 'Gourmet romantic candlelight dining experience.'
            ],
            [
                'name'        => 'Crystal Castle',
                'coins'       => 10000, // 10K
                'category'    => 'luxury',
                'badge'       => 'LUXURY',
                'sort_order'  => 5,
                'image'       => 'uploads/gifts/crystal_castle.png',
                'description' => 'A magical glowing crystal fairytale palace.'
            ],
            [
                'name'        => 'Supercar & Billionaire',
                'coins'       => 9990, // 9.99K
                'category'    => 'luxury',
                'badge'       => '3D',
                'sort_order'  => 6,
                'image'       => 'uploads/gifts/supercar_luxury.png',
                'description' => 'Hypercar with billionaire boss aura.'
            ],
            [
                'name'        => 'Fairy Palace Crown',
                'coins'       => 7200, // 7.20K
                'category'    => 'luxury',
                'badge'       => 'NEW',
                'sort_order'  => 7,
                'image'       => 'uploads/gifts/fairy_crown.png',
                'description' => 'Sparkling diamond halo & crystal tiara.'
            ],
            [
                'name'        => 'Space Battleship',
                'coins'       => 6660, // 6.66K
                'category'    => 'effects',
                'badge'       => '3D',
                'sort_order'  => 8,
                'image'       => 'uploads/gifts/space_battleship.png',
                'description' => 'Futuristic supersonic intergalactic fighter jet.'
            ],
            [
                'name'        => 'Fire Dragon',
                'coins'       => 5550, // 5.55K
                'category'    => 'effects',
                'badge'       => 'HOT',
                'sort_order'  => 9,
                'image'       => 'uploads/gifts/fire_dragon.png',
                'description' => 'Fierce mythical winged blazing fire dragon.'
            ],
            [
                'name'        => 'Treasure Chest',
                'coins'       => 5000, // 5K
                'category'    => 'popular',
                'badge'       => 'GOLD',
                'sort_order'  => 10,
                'image'       => 'uploads/gifts/treasure_chest.png',
                'description' => 'Ancient pirate chest overflowing with diamonds.'
            ],
            [
                'name'        => 'Love Letter Mailbox',
                'coins'       => 4690, // 4.69K
                'category'    => 'romantic',
                'badge'       => 'SWEET',
                'sort_order'  => 11,
                'image'       => 'uploads/gifts/love_mailbox.png',
                'description' => 'Pink floral mailbox filled with sweet love letters.'
            ],
            [
                'name'        => 'Magic Genie Lamp',
                'coins'       => 4440, // 4.44K
                'category'    => 'effects',
                'badge'       => 'MAGIC',
                'sort_order'  => 12,
                'image'       => 'uploads/gifts/genie_lamp.png',
                'description' => 'Golden enchanted lamp that grants 3 wishes.'
            ],
            [
                'name'        => 'Birthday Cake',
                'coins'       => 4210, // 4.21K
                'category'    => 'popular',
                'badge'       => 'CELEBRATE',
                'sort_order'  => 13,
                'image'       => 'uploads/gifts/birthday_cake.png',
                'description' => 'Delicious layered cake with sparkling birthday candles.'
            ],
            [
                'name'        => 'Midnight Lovers',
                'coins'       => 3700, // 3.70K
                'category'    => 'romantic',
                'badge'       => 'NIGHT',
                'sort_order'  => 14,
                'image'       => 'uploads/gifts/midnight_lovers.png',
                'description' => 'Romantic moonlight beach stroll.'
            ],
            [
                'name'        => 'Galaxy Portal',
                'coins'       => 3700, // 3.70K
                'category'    => 'effects',
                'badge'       => 'COSMIC',
                'sort_order'  => 15,
                'image'       => 'uploads/gifts/galaxy_portal.png',
                'description' => 'Whirling cosmic galaxy vortex portal.'
            ],
            [
                'name'        => 'Rose Bouquet',
                'coins'       => 500, // 500
                'category'    => 'popular',
                'badge'       => 'POPULAR',
                'sort_order'  => 16,
                'image'       => 'uploads/gifts/rose_bouquet.png',
                'description' => 'Fresh red roses tied with a silk ribbon.'
            ],
        ];

        $createdGifts = [];
        foreach ($sampleGifts as $giftData) {
            $createdGifts[] = Gift::updateOrCreate(
                ['name' => $giftData['name']],
                $giftData
            );
        }

        // Assign sample received gifts to existing users (especially female hosts e.g. Nusrat or user #1/#2)
        $users = User::take(5)->get();
        if ($users->isNotEmpty() && !empty($createdGifts)) {
            $sender = $users->last(); // e.g. Sajid or top fan

            // Match exact counts from screenshot 1 & 2 for the first host
            $demoCounts = [
                0 => 2,   // Romantic Couple x2
                1 => 1,   // Sunset Couple x1
                2 => 4,   // Vintage Romance x4
                3 => 1,   // Candlelight x1
                4 => 2,   // Crystal Castle x2
                5 => 32,  // Supercar x32
                6 => 1,   // Fairy Crown x1
                7 => 4,   // Battleship x4
                8 => 12,  // Dragon x12
                9 => 18,  // Treasure chest x18
                10 => 1,  // Love mailbox x1
                11 => 43, // Genie lamp x43
                12 => 1,  // Birthday cake x1
                13 => 1,  // Midnight lovers x1
                14 => 13, // Galaxy portal x13
                15 => 80, // Rose bouquet x80
            ];

            foreach ($users as $hostIndex => $hostUser) {
                // Seed if no gifts present
                if ($hostUser->receivedGifts()->count() === 0) {
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
}
