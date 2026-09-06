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

        $allCategoryGifts = [
            // =========================================================================
            // 🔥 1. HOT GIFTS (Trending, CP & High Frequency Items)
            // =========================================================================
            ['name' => 'Trophy Cup', 'coins' => 500, 'category' => 'hot', 'badge' => 'HOT', 'image' => 'uploads/gifts/trophy_cup.svg'],
            ['name' => 'Mystery Box', 'coins' => 888, 'category' => 'hot', 'badge' => 'MUST WIN', 'image' => 'uploads/gifts/mystery_box.svg'],
            ['name' => 'CP Love Letter', 'coins' => 520, 'category' => 'hot', 'badge' => '520 LOVE', 'image' => 'uploads/gifts/cp_love_letter.svg'],
            ['name' => 'In My Hands', 'coins' => 1314, 'category' => 'hot', 'badge' => 'SWEET', 'image' => 'uploads/gifts/in_my_hands.svg'],
            ['name' => 'CP Romantic Kiss', 'coins' => 999, 'category' => 'hot', 'badge' => 'HOT KISS', 'image' => 'uploads/gifts/cp_romantic_kiss.svg'],
            ['name' => 'Good Couple', 'coins' => 2000, 'category' => 'hot', 'badge' => 'COUPLE', 'image' => 'uploads/gifts/romantic_couple.png'],
            ['name' => 'Pew Pew Heart Gun', 'coins' => 333, 'category' => 'hot', 'badge' => 'PEW PEW', 'image' => 'uploads/gifts/cupid_arrow.svg'],
            ['name' => 'Jackpot Slot Machine', 'coins' => 777, 'category' => 'hot', 'badge' => 'SLOT 777', 'image' => 'uploads/gifts/slot_machine.svg'],
            ['name' => 'Celebrity Couple', 'coins' => 5200, 'category' => 'hot', 'badge' => 'STAR', 'image' => 'uploads/gifts/sunset_couple.svg'],
            ['name' => 'Best CP Star', 'coins' => 3333, 'category' => 'hot', 'badge' => 'CP STAR', 'image' => 'uploads/gifts/shooting_star_wish.svg'],
            ['name' => 'Run To Love', 'coins' => 5550, 'category' => 'hot', 'badge' => '5.55K', 'image' => 'uploads/gifts/romantic_couple.svg'],
            ['name' => 'Superstar Stage', 'coins' => 6666, 'category' => 'hot', 'badge' => 'SUPERSTAR', 'image' => 'uploads/gifts/emperor_golden_throne.svg'],
            ['name' => 'Aladdin Wish Lamp', 'coins' => 8888, 'category' => 'hot', 'badge' => 'WISH', 'image' => 'uploads/gifts/genie_lamp.svg'],
            ['name' => 'Fire Dragon 3D', 'coins' => 20000, 'category' => 'hot', 'badge' => 'DRAGON', 'image' => 'uploads/gifts/dragon_gift.svg'],
            ['name' => 'CP Sunset Yacht', 'coins' => 9999, 'category' => 'hot', 'badge' => 'CP CRUISE', 'image' => 'uploads/gifts/luxury_yacht_gift.svg'],
            ['name' => 'Cosmic Starship', 'coins' => 15000, 'category' => 'hot', 'badge' => 'WARP', 'image' => 'uploads/gifts/space_battleship.svg'],
            ['name' => 'Golden Wedding Bell', 'coins' => 299, 'category' => 'hot', 'badge' => 'BELL', 'image' => 'uploads/gifts/dhak_drum_festival.svg'],
            ['name' => 'Neon Glow Light', 'coins' => 99, 'category' => 'hot', 'badge' => 'GLOW', 'image' => 'uploads/gifts/starlight_serenade.svg'],
            ['name' => 'Music Fest DJ', 'coins' => 1999, 'category' => 'hot', 'badge' => 'PARTY', 'image' => 'uploads/gifts/champagne_gift.svg'],
            ['name' => 'Ice Cold Cola', 'coins' => 50, 'category' => 'hot', 'badge' => 'CHILL', 'image' => 'uploads/gifts/sweet_shop_dokan.svg'],
            ['name' => 'One Piece Treasure', 'coins' => 555, 'category' => 'hot', 'badge' => 'PIRATE', 'image' => 'uploads/gifts/treasure_chest.svg'],
            ['name' => 'World Cup Champion 2026', 'coins' => 2026, 'category' => 'hot', 'badge' => '2026 CUP', 'image' => 'uploads/gifts/world_cup_trophy.svg'],
            ['name' => 'Love Story Book', 'coins' => 520, 'category' => 'hot', 'badge' => 'FAIRY', 'image' => 'uploads/gifts/love_mailbox.svg'],
            ['name' => 'Paper Love Plane', 'coins' => 99, 'category' => 'hot', 'badge' => 'FLY', 'image' => 'uploads/gifts/white_dove_flying.svg'],
            ['name' => 'Golden Motorcycle', 'coins' => 3333, 'category' => 'hot', 'badge' => 'SPEED', 'image' => 'uploads/gifts/golden_motorcycle.svg'],
            ['name' => 'Timeless Love Hourglass', 'coins' => 1314, 'category' => 'hot', 'badge' => 'FOREVER', 'image' => 'uploads/gifts/love_padlock_bridge.svg'],
            ['name' => 'Romantic Memory Album', 'coins' => 999, 'category' => 'hot', 'badge' => 'MEMORIES', 'image' => 'uploads/gifts/teddy_bear_gift.svg'],
            ['name' => 'IP Robot Love', 'coins' => 666, 'category' => 'hot', 'badge' => 'CYBER', 'image' => 'uploads/gifts/cyber_matrix_cube.svg'],
            ['name' => 'Metropolis City 2026', 'coins' => 2026, 'category' => 'hot', 'badge' => 'CITY 2026', 'image' => 'uploads/gifts/neon_city_street.svg'],
            ['name' => 'iPay Diamond Card', 'coins' => 888, 'category' => 'hot', 'badge' => 'VIP CARD', 'image' => 'uploads/gifts/svip_black_card.svg'],
            ['name' => 'Ballroom Romance Dance', 'coins' => 2222, 'category' => 'hot', 'badge' => 'DANCE', 'image' => 'uploads/gifts/rainy_street_romance.svg'],
            ['name' => 'Pearl Swan Book', 'coins' => 1888, 'category' => 'hot', 'badge' => 'PEARL', 'image' => 'uploads/gifts/majestic_swan_lake.svg'],
            ['name' => 'Romantic Dreamscape', 'coins' => 5200, 'category' => 'hot', 'badge' => 'DREAM', 'image' => 'uploads/gifts/crystal_glass_slipper.svg'],
            ['name' => 'Rose Boy Smile', 'coins' => 777, 'category' => 'hot', 'badge' => 'CUTE', 'image' => 'uploads/gifts/sunflower_smile.svg'],
            ['name' => '24K Diamond Ring', 'coins' => 9999, 'category' => 'hot', 'badge' => 'DIAMOND', 'image' => 'uploads/gifts/diamond_ring_gift.svg'],
            ['name' => 'Black Gold Cyber City', 'coins' => 17700, 'category' => 'hot', 'badge' => 'HOT 17.7K', 'image' => 'uploads/gifts/billionaire_mansion.svg'],
            ['name' => 'Happy Birthday Cake', 'coins' => 1314, 'category' => 'hot', 'badge' => 'BDAY', 'image' => 'uploads/gifts/birthday_cake.svg'],
            ['name' => 'Gold Lambo Supercar', 'coins' => 25000, 'category' => 'hot', 'badge' => 'LAMBO', 'image' => 'uploads/gifts/supercar_luxury.svg'],
            ['name' => 'Explosive Formula Car', 'coins' => 30000, 'category' => 'hot', 'badge' => 'NITRO', 'image' => 'uploads/gifts/formula_racecar.svg'],
            ['name' => 'Royal Wedding Rings', 'coins' => 13140, 'category' => 'hot', 'badge' => '1314', 'image' => 'uploads/gifts/vintage_romance.svg'],
            ['name' => 'Pink Romance Blossom', 'coins' => 999, 'category' => 'hot', 'badge' => 'BLOSSOM', 'image' => 'uploads/gifts/cherry_blossom_rain.svg'],
            ['name' => 'Lucky Capsule Toys', 'coins' => 666, 'category' => 'hot', 'badge' => 'TOYS', 'image' => 'uploads/gifts/playful_monkey_king.svg'],
            ['name' => 'Superman Hero Flight', 'coins' => 7777, 'category' => 'hot', 'badge' => 'HERO', 'image' => 'uploads/gifts/lightning_zeus_bolt.svg'],
            ['name' => 'Imperial King Dragon', 'coins' => 50000, 'category' => 'hot', 'badge' => '50K MYTHIC', 'image' => 'uploads/gifts/fire_dragon.svg'],
            ['name' => 'Sovereign Scepter', 'coins' => 12000, 'category' => 'hot', 'badge' => 'ROYAL', 'image' => 'uploads/gifts/scepter_of_power.svg'],
            ['name' => 'Aegis Guardian Shield', 'coins' => 8888, 'category' => 'hot', 'badge' => 'SHIELD', 'image' => 'uploads/gifts/god_of_war_shield.svg'],
            ['name' => 'Excalibur Victory Sword', 'coins' => 9999, 'category' => 'hot', 'badge' => 'SWORD', 'image' => 'uploads/gifts/excalibur_holy_sword.svg'],
            ['name' => 'Imperial Signet Ring', 'coins' => 6666, 'category' => 'hot', 'badge' => 'KING RING', 'image' => 'uploads/gifts/sovereign_emperor_crown.svg'],
            ['name' => 'PK Battle Laser Gun', 'coins' => 1500, 'category' => 'hot', 'badge' => 'PK WIN', 'image' => 'uploads/gifts/space_rocket_gift.svg'],
            ['name' => 'Taj Mahal Wonder Palace', 'coins' => 35000, 'category' => 'hot', 'badge' => '35K PALACE', 'image' => 'uploads/gifts/taj_mahal_palace.svg'],
            ['name' => 'Countdown 2026 Clock', 'coins' => 2026, 'category' => 'hot', 'badge' => '3-2-1', 'image' => 'uploads/gifts/heart_fireworks_gift.svg'],
            ['name' => 'I Am Rich Cash Shower', 'coins' => 9999, 'category' => 'hot', 'badge' => 'I AM RICH', 'image' => 'uploads/gifts/i_am_rich_cash.svg'],
            ['name' => 'Red Heart Balloons', 'coins' => 199, 'category' => 'hot', 'badge' => 'LOVE', 'image' => 'uploads/gifts/heart_fireworks_gift.svg'],
            ['name' => 'Royal Diamond Necklace', 'coins' => 8888, 'category' => 'hot', 'badge' => 'LUXURY', 'image' => 'uploads/gifts/diamond_perfume.svg'],
            ['name' => 'MUA Kiss Lips', 'coins' => 299, 'category' => 'hot', 'badge' => 'MUA 💋', 'image' => 'uploads/gifts/romantic_kiss_gift.svg'],
            ['name' => 'Crystal Glass Slipper', 'coins' => 1999, 'category' => 'hot', 'badge' => 'CINDERELLA', 'image' => 'uploads/gifts/crystal_glass_slipper.svg'],
            ['name' => 'Rainbow Swirl Lollipop', 'coins' => 66, 'category' => 'hot', 'badge' => 'SWEET', 'image' => 'uploads/gifts/lollipop_sweet.svg'],
            ['name' => 'Heart Rose Velvet Box', 'coins' => 520, 'category' => 'hot', 'badge' => '520', 'image' => 'uploads/gifts/chocolate_box.svg'],
            ['name' => 'Festival Dhol Drum', 'coins' => 299, 'category' => 'hot', 'badge' => 'DRUM', 'image' => 'uploads/gifts/dhak_drum_festival.svg'],
            ['name' => 'Love Surprise Mobile Box', 'coins' => 1888, 'category' => 'hot', 'badge' => 'GIFT', 'image' => 'uploads/gifts/love_mailbox.svg'],
            ['name' => 'Meteor Starlight Shower', 'coins' => 5200, 'category' => 'hot', 'badge' => 'STARS', 'image' => 'uploads/gifts/meteor_shower.svg'],
            ['name' => 'UFO Alien Mothership', 'coins' => 15000, 'category' => 'hot', 'badge' => 'UFO', 'image' => 'uploads/gifts/ufo_alien_beam.svg'],
            ['name' => 'Royal Ruby Love Crown', 'coins' => 6666, 'category' => 'hot', 'badge' => 'CROWN', 'image' => 'uploads/gifts/fairy_crown.svg'],
            ['name' => 'Romantic Heart Steam Train', 'coins' => 7777, 'category' => 'hot', 'badge' => 'TRAIN', 'image' => 'uploads/gifts/traditional_rickshaw.svg'],
            ['name' => 'Carnival Ferris Wheel', 'coins' => 9999, 'category' => 'hot', 'badge' => 'WHEEL', 'image' => 'uploads/gifts/golden_vip_rickshaw.svg'],
            ['name' => 'Golden Lucky Egg Crack', 'coins' => 888, 'category' => 'hot', 'badge' => 'LUCKY 888', 'image' => 'uploads/gifts/golden_egg_crack.svg'],
            ['name' => 'Flaming Red Chili Pepper', 'coins' => 99, 'category' => 'hot', 'badge' => 'HOT 🔥', 'image' => 'uploads/gifts/hot_chili_pepper.svg'],
            ['name' => 'Single Classic Red Rose', 'coins' => 10, 'category' => 'hot', 'badge' => 'ROSE', 'image' => 'uploads/gifts/rose_bouquet.png'],
            ['name' => 'Funny Throw Tomato', 'coins' => 20, 'category' => 'hot', 'badge' => 'FUNNY', 'image' => 'uploads/gifts/sunflower_smile.svg'],
            ['name' => 'Whack Toy Hammer', 'coins' => 50, 'category' => 'hot', 'badge' => 'HAMMER', 'image' => 'uploads/gifts/thor_hammer_strike.svg'],
            ['name' => 'Melody Music Flute', 'coins' => 150, 'category' => 'hot', 'badge' => 'MUSIC', 'image' => 'uploads/gifts/krishna_flute_banshi.svg'],

            // =========================================================================
            // 🍀 2. LUCKY GIFTS (Jackpot Multipliers & Interactive)
            // =========================================================================
            ['name' => 'Lucky Mystery Chest', 'coins' => 888, 'category' => 'lucky', 'badge' => 'WIN x500', 'image' => 'uploads/gifts/mystery_box.svg'],
            ['name' => 'Lucky Kiss Lips', 'coins' => 299, 'category' => 'lucky', 'badge' => 'LUCKY', 'image' => 'uploads/gifts/romantic_kiss_gift.svg'],
            ['name' => 'Flaming Hot Chili Lucky', 'coins' => 99, 'category' => 'lucky', 'badge' => 'HOT PEPPER', 'image' => 'uploads/gifts/hot_chili_pepper.svg'],
            ['name' => 'TNT Bombastic Blast', 'coins' => 333, 'category' => 'lucky', 'badge' => 'BOOM', 'image' => 'uploads/gifts/volcano_eruption.svg'],
            ['name' => 'Lucky Puppy Dog', 'coins' => 520, 'category' => 'lucky', 'badge' => 'PUPPY', 'image' => 'uploads/gifts/playful_monkey_king.svg'],
            ['name' => 'Funny Cartoon Slipper', 'coins' => 100, 'category' => 'lucky', 'badge' => 'SLIPPER', 'image' => 'uploads/gifts/crystal_glass_slipper.svg'],
            ['name' => 'Single Lucky Red Rose', 'coins' => 10, 'category' => 'lucky', 'badge' => '10 COINS', 'image' => 'uploads/gifts/rose_bouquet.png'],
            ['name' => 'Whack Toy Hammer Lucky', 'coins' => 50, 'category' => 'lucky', 'badge' => 'WHACK', 'image' => 'uploads/gifts/thor_hammer_strike.svg'],
            ['name' => 'Lucky Glow Torch', 'coins' => 99, 'category' => 'lucky', 'badge' => 'TORCH', 'image' => 'uploads/gifts/starlight_serenade.svg'],
            ['name' => 'World Cup Trophy Lucky', 'coins' => 2026, 'category' => 'lucky', 'badge' => '2026 WIN', 'image' => 'uploads/gifts/world_cup_trophy.svg'],
            ['name' => 'Golden Lucky Egg Crack Lucky', 'coins' => 888, 'category' => 'lucky', 'badge' => '888 COINS', 'image' => 'uploads/gifts/golden_egg_crack.svg'],
            ['name' => 'Lucky Slot Machine 777', 'coins' => 777, 'category' => 'lucky', 'badge' => 'JACKPOT', 'image' => 'uploads/gifts/slot_machine.svg'],
            ['name' => 'Funny Splat Tomato', 'coins' => 20, 'category' => 'lucky', 'badge' => 'SPLAT', 'image' => 'uploads/gifts/sunflower_smile.svg'],
            ['name' => 'Aladdin Fortune Lamp', 'coins' => 8888, 'category' => 'lucky', 'badge' => 'FORTUNE', 'image' => 'uploads/gifts/genie_lamp.svg'],
            ['name' => 'Ice Cola Drink Lucky', 'coins' => 50, 'category' => 'lucky', 'badge' => 'COLA', 'image' => 'uploads/gifts/sweet_shop_dokan.svg'],
            ['name' => 'Most Win Gold Dragon', 'coins' => 20000, 'category' => 'lucky', 'badge' => 'MOST WIN', 'image' => 'uploads/gifts/fire_dragon.svg'],
            ['name' => 'Galactic Starship Lucky', 'coins' => 15000, 'category' => 'lucky', 'badge' => 'STARSHIP', 'image' => 'uploads/gifts/space_battleship.svg'],
            ['name' => 'One Piece Pirate Map', 'coins' => 555, 'category' => 'lucky', 'badge' => 'PIRATE', 'image' => 'uploads/gifts/treasure_chest.svg'],
            ['name' => 'Lucky Temple Bell', 'coins' => 299, 'category' => 'lucky', 'badge' => 'BELL', 'image' => 'uploads/gifts/dhak_drum_festival.svg'],
            ['name' => 'Super Like Love Heart', 'coins' => 100, 'category' => 'lucky', 'badge' => 'LIKE 100', 'image' => 'uploads/gifts/heart_fireworks_gift.svg'],

            // =========================================================================
            // 👑 3. SVIP GIFTS (Ultra High Tier Luxury Assets)
            // =========================================================================
            ['name' => 'Bollywood Super Diva', 'coins' => 18888, 'category' => 'svip', 'badge' => 'DIVA SVIP', 'image' => 'uploads/gifts/starlight_serenade.svg'],
            ['name' => 'Royal Bengal Tiger', 'coins' => 25000, 'category' => 'svip', 'badge' => 'SVIP TIGER', 'image' => 'uploads/gifts/bengal_tiger_roar.svg'],
            ['name' => 'Luxury Roadster Convertible', 'coins' => 33333, 'category' => 'svip', 'badge' => 'ROADSTER', 'image' => 'uploads/gifts/royal_limousine.svg'],
            ['name' => 'Taj Mahal Imperial Palace', 'coins' => 35000, 'category' => 'svip', 'badge' => '35K PALACE', 'image' => 'uploads/gifts/taj_mahal_palace.svg'],
            ['name' => 'Imperial Royal Elephant', 'coins' => 40000, 'category' => 'svip', 'badge' => 'ROYAL 40K', 'image' => 'uploads/gifts/royal_elephant_howdah.svg'],
            ['name' => 'Mayurpankhi Peacock Chariot', 'coins' => 45000, 'category' => 'svip', 'badge' => 'PEACOCK', 'image' => 'uploads/gifts/peacock_dance.svg'],
            ['name' => 'Imperial Sovereign Palace', 'coins' => 50000, 'category' => 'svip', 'badge' => '50K PALACE', 'image' => 'uploads/gifts/crystal_castle.svg'],
            ['name' => 'Sky Floating Aurora Castle', 'coins' => 75000, 'category' => 'svip', 'badge' => 'SKY CASTLE', 'image' => 'uploads/gifts/diamond_castle_gift.svg'],
            ['name' => 'Grand Festival of Lights', 'coins' => 60000, 'category' => 'svip', 'badge' => '60K FEST', 'image' => 'uploads/gifts/heart_fireworks_gift.svg'],
            ['name' => 'Golden Samosa Royal Feast', 'coins' => 5000, 'category' => 'svip', 'badge' => 'FEAST', 'image' => 'uploads/gifts/candlelight_dinner.svg'],
            ['name' => 'Enchanted Forever Rose', 'coins' => 13140, 'category' => 'svip', 'badge' => 'FOREVER 1314', 'image' => 'uploads/gifts/rose_bouquet_gift.svg'],
            ['name' => 'Jasmine Blossom Carriage', 'coins' => 20000, 'category' => 'svip', 'badge' => 'CARRIAGE', 'image' => 'uploads/gifts/golden_vip_rickshaw.svg'],

            // =========================================================================
            // 💖 4. INTIMACY GIFTS (Love, Romance & Couples)
            // =========================================================================
            ['name' => 'Vintage CP Love Letter', 'coins' => 520, 'category' => 'intimacy', 'badge' => 'LOVE 520', 'image' => 'uploads/gifts/cp_love_letter.svg'],
            ['name' => 'Holding Hands in My World', 'coins' => 1314, 'category' => 'intimacy', 'badge' => '1314', 'image' => 'uploads/gifts/in_my_hands.svg'],
            ['name' => 'Romance Memory Album Intimacy', 'coins' => 999, 'category' => 'intimacy', 'badge' => 'MEMORIES', 'image' => 'uploads/gifts/teddy_bear_gift.svg'],
            ['name' => 'Candlelight 5-Star Dinner', 'coins' => 2000, 'category' => 'intimacy', 'badge' => 'SWEET', 'image' => 'uploads/gifts/candlelight_dinner.svg'],
            ['name' => 'Golden Sunset Couple Beach', 'coins' => 5200, 'category' => 'intimacy', 'badge' => 'ETERNAL', 'image' => 'uploads/gifts/sunset_couple.svg'],
            ['name' => 'Midnight Lovers Crescent Moon', 'coins' => 9999, 'category' => 'intimacy', 'badge' => 'MOONLIGHT', 'image' => 'uploads/gifts/midnight_lovers.svg'],
            ['name' => 'Royal Romance White Carriage', 'coins' => 13140, 'category' => 'intimacy', 'badge' => 'ROYAL 13.14K', 'image' => 'uploads/gifts/vintage_romance.svg'],
            ['name' => 'Ballroom Romance Dance Intimacy', 'coins' => 2222, 'category' => 'intimacy', 'badge' => 'DANCE', 'image' => 'uploads/gifts/rainy_street_romance.svg'],
            ['name' => 'Velvet Rose Heart Box', 'coins' => 520, 'category' => 'intimacy', 'badge' => '520 BOX', 'image' => 'uploads/gifts/chocolate_box.svg'],
            ['name' => '520 Love Heart Fireworks', 'coins' => 520, 'category' => 'intimacy', 'badge' => '520 LOVE', 'image' => 'uploads/gifts/heart_fireworks_gift.svg'],

            // =========================================================================
            // 💰 5. WEALTH GIFTS (Gold, Billionaire Mansions & Jewels)
            // =========================================================================
            ['name' => 'Wealth Solid Gold Ingot', 'coins' => 8888, 'category' => 'wealth', 'badge' => 'GOLD INGOT', 'image' => 'uploads/gifts/trophy_cup.svg'],
            ['name' => 'Scepter of Sovereign Wealth', 'coins' => 12000, 'category' => 'wealth', 'badge' => 'SCEPTER', 'image' => 'uploads/gifts/scepter_of_power.svg'],
            ['name' => '24K Solitaire Diamond Ring', 'coins' => 9999, 'category' => 'wealth', 'badge' => 'DIAMOND', 'image' => 'uploads/gifts/diamond_ring_gift.svg'],
            ['name' => 'Golden Space Rocket Wealth', 'coins' => 25000, 'category' => 'wealth', 'badge' => 'ROCKET 25K', 'image' => 'uploads/gifts/space_rocket_gift.svg'],
            ['name' => 'Heart of the Ocean Blue Diamond', 'coins' => 30000, 'category' => 'wealth', 'badge' => 'OCEAN GEM', 'image' => 'uploads/gifts/diamond_perfume.svg'],
            ['name' => 'Billionaire Grand Estate Estate', 'coins' => 50000, 'category' => 'wealth', 'badge' => '50K ESTATE', 'image' => 'uploads/gifts/billionaire_mansion.svg'],
            ['name' => 'Golden Champion Victory Cup Wealth', 'coins' => 66666, 'category' => 'wealth', 'badge' => '66.6K CUP', 'image' => 'uploads/gifts/holy_grail_cup.svg'],
            ['name' => 'Diamond Crystal Palace Wealth', 'coins' => 100000, 'category' => 'wealth', 'badge' => '100K PALACE', 'image' => 'uploads/gifts/diamond_castle_gift.svg'],

            // =========================================================================
            // 🎉 6. FESTIVAL GIFTS (Carnival, Eid, New Year & Holidays)
            // =========================================================================
            ['name' => 'Sky Blessing Lanterns Festival', 'coins' => 520, 'category' => 'festival', 'badge' => 'LANTERN', 'image' => 'uploads/gifts/genie_lamp.svg'],
            ['name' => 'Sacred Blessing Festival Diya', 'coins' => 299, 'category' => 'festival', 'badge' => 'DIYA', 'image' => 'uploads/gifts/starlight_serenade.svg'],
            ['name' => 'Eid Mubarak Golden Crescent Moon', 'coins' => 2026, 'category' => 'festival', 'badge' => 'EID MUBARAK', 'image' => 'uploads/gifts/eid_crescent_moon.svg'],
            ['name' => 'Carnival Rainbow Float Festival', 'coins' => 3333, 'category' => 'festival', 'badge' => 'CARNIVAL', 'image' => 'uploads/gifts/golden_vip_rickshaw.svg'],
            ['name' => 'Christmas Sleigh & Reindeer', 'coins' => 5000, 'category' => 'festival', 'badge' => 'XMAS', 'image' => 'uploads/gifts/white_dove_flying.svg'],
            ['name' => 'Winter Wonderland Snow Globe', 'coins' => 9999, 'category' => 'festival', 'badge' => 'WINTER', 'image' => 'uploads/gifts/majestic_swan_lake.svg'],
            ['name' => 'Happy New Year 2026 Fireworks', 'coins' => 2026, 'category' => 'festival', 'badge' => 'HAPPY 2026', 'image' => 'uploads/gifts/heart_fireworks_gift.svg'],
            ['name' => 'Grand Festival Fireworks & Lights', 'coins' => 60000, 'category' => 'festival', 'badge' => '60K FEST', 'image' => 'uploads/gifts/heart_fireworks_gift.svg'],
            ['name' => 'Festive Dhol Carnival Drums', 'coins' => 299, 'category' => 'festival', 'badge' => 'DHOL', 'image' => 'uploads/gifts/dhak_drum_festival.svg'],
            ['name' => 'Bengali Traditional Rickshaw Festival', 'coins' => 1999, 'category' => 'festival', 'badge' => 'RICKSHAW', 'image' => 'uploads/gifts/traditional_rickshaw.svg'],

            // =========================================================================
            // 🎒 7. BAG GIFTS (Backpack & Collectible Items)
            // =========================================================================
            ['name' => 'Lucky Golden Tortoise Bag', 'coins' => 500, 'category' => 'bag', 'badge' => 'BAG ITEM', 'image' => 'uploads/gifts/lucky_gold_mouse.svg'],
            ['name' => 'Free Gift Voucher Ticket Bag', 'coins' => 100, 'category' => 'bag', 'badge' => 'TICKET', 'image' => 'uploads/gifts/love_mailbox.svg'],
            ['name' => 'VIP Avatar Frame Box Bag', 'coins' => 999, 'category' => 'bag', 'badge' => 'FRAME', 'image' => 'uploads/gifts/royal_crown_gift.svg'],
            ['name' => 'Chat Bubble Theme Voucher', 'coins' => 500, 'category' => 'bag', 'badge' => 'BUBBLE', 'image' => 'uploads/gifts/cyber_matrix_cube.svg'],
            ['name' => 'Luxury Supercar Entrance Pass', 'coins' => 5550, 'category' => 'bag', 'badge' => 'RIDE PASS', 'image' => 'uploads/gifts/sports_car_gift.svg'],

            // =========================================================================
            // ⭐ 8. POPULAR GIFTS (High Frequency / Entry)
            // =========================================================================
            ['name' => 'Rose Bouquet Popular', 'coins' => 99, 'category' => 'popular', 'badge' => 'POPULAR', 'image' => 'uploads/gifts/rose_bouquet_gift.svg'],
            ['name' => 'Teddy Bear Love Popular', 'coins' => 999, 'category' => 'popular', 'badge' => 'CUTE', 'image' => 'uploads/gifts/teddy_bear_gift.svg'],
            ['name' => 'Birthday Cake Popular', 'coins' => 1314, 'category' => 'popular', 'badge' => 'CELEBRATION', 'image' => 'uploads/gifts/birthday_cake.svg'],
            ['name' => 'Champagne Pop Popular', 'coins' => 1999, 'category' => 'popular', 'badge' => 'CHEERS', 'image' => 'uploads/gifts/champagne_gift.svg'],
            ['name' => 'Playful Monkey King Popular', 'coins' => 2200, 'category' => 'popular', 'badge' => 'FUNNY', 'image' => 'uploads/gifts/playful_monkey_king.svg'],

            // =========================================================================
            // 💎 9. LUXURY GIFTS (Supercars, Jets & Yachts)
            // =========================================================================
            ['name' => 'Sports Bike Ninja Luxury', 'coins' => 3333, 'category' => 'luxury', 'badge' => 'SPEED', 'image' => 'uploads/gifts/sports_bike_gift.svg'],
            ['name' => 'Luxury Supercar Luxury', 'coins' => 5550, 'category' => 'luxury', 'badge' => 'SUPERCAR', 'image' => 'uploads/gifts/sports_car_gift.svg'],
            ['name' => 'VIP Helicopter Luxury', 'coins' => 15000, 'category' => 'luxury', 'badge' => 'VIP ARRIVAL', 'image' => 'uploads/gifts/helicopter_gift.svg'],
            ['name' => 'Supersonic Private Jet Luxury', 'coins' => 25000, 'category' => 'luxury', 'badge' => 'PRIVATE JET', 'image' => 'uploads/gifts/private_jet_gift.svg'],
            ['name' => 'Mega Luxury Yacht Luxury', 'coins' => 50000, 'category' => 'luxury', 'badge' => '50K YACHT', 'image' => 'uploads/gifts/luxury_yacht_gift.svg'],

            // =========================================================================
            // 💕 10. ROMANTIC GIFTS
            // =========================================================================
            ['name' => 'Love Mailbox Romantic', 'coins' => 520, 'category' => 'romantic', 'badge' => 'ROMANTIC', 'image' => 'uploads/gifts/love_mailbox.svg'],
            ['name' => 'Candlelight Dinner Romantic', 'coins' => 2000, 'category' => 'romantic', 'badge' => 'SWEET', 'image' => 'uploads/gifts/candlelight_dinner.svg'],
            ['name' => 'Sunset Couple Walk Romantic', 'coins' => 5200, 'category' => 'romantic', 'badge' => 'ETERNAL', 'image' => 'uploads/gifts/sunset_couple.svg'],
            ['name' => 'Midnight Lovers Moon Romantic', 'coins' => 9999, 'category' => 'romantic', 'badge' => 'FULL MOON', 'image' => 'uploads/gifts/midnight_lovers.svg'],
            ['name' => 'Vintage Romance Carriage Romantic', 'coins' => 13140, 'category' => 'romantic', 'badge' => 'FOREVER 1314', 'image' => 'uploads/gifts/vintage_romance.svg'],

            // =========================================================================
            // ⚡ 11. EFFECTS / 3D GIFTS
            // =========================================================================
            ['name' => 'Flaming Fire Dragon Effects', 'coins' => 20000, 'category' => 'effects', 'badge' => 'DRAGON 3D', 'image' => 'uploads/gifts/dragon_gift.svg'],
            ['name' => 'Flying Phoenix Rebirth Effects', 'coins' => 30000, 'category' => 'effects', 'badge' => 'PHOENIX 3D', 'image' => 'uploads/gifts/phoenix_gift.svg'],
            ['name' => 'Interstellar Battleship Effects', 'coins' => 75000, 'category' => 'effects', 'badge' => 'GALACTIC 3D', 'image' => 'uploads/gifts/space_battleship.svg'],
            ['name' => 'Magic Genie Lamp Effects', 'coins' => 15000, 'category' => 'effects', 'badge' => 'WISH 3D', 'image' => 'uploads/gifts/genie_lamp.svg'],
            ['name' => 'Space Rocket Launch Effects', 'coins' => 7777, 'category' => 'effects', 'badge' => 'ROCKET 3D', 'image' => 'uploads/gifts/space_rocket_gift.svg'],

            // =========================================================================
            // 🌟 12. VIP EXCLUSIVE GIFTS
            // =========================================================================
            ['name' => 'Fairy Princess Tiara VIP', 'coins' => 1999, 'category' => 'vip', 'badge' => 'PRINCESS', 'image' => 'uploads/gifts/fairy_crown.svg'],
            ['name' => 'Royal Sovereign Crown VIP', 'coins' => 17700, 'category' => 'vip', 'badge' => 'ROYAL VIP', 'image' => 'uploads/gifts/royal_crown_gift.svg'],
            ['name' => 'Mythic Treasure Chest VIP', 'coins' => 35000, 'category' => 'vip', 'badge' => 'JACKPOT', 'image' => 'uploads/gifts/treasure_chest.svg'],
            ['name' => 'Golden Cobra Serpent VIP', 'coins' => 7500, 'category' => 'vip', 'badge' => 'MYTHIC', 'image' => 'uploads/gifts/golden_cobra_snake.svg'],
        ];

        $sortOrder = 1;
        foreach ($allCategoryGifts as $g) {
            $img = $g['image'];
            Gift::updateOrCreate(
                ['name' => $g['name']],
                [
                    'coins'          => $g['coins'],
                    'coin_price'     => $g['coins'],
                    'category'       => $g['category'],
                    'badge'          => $g['badge'],
                    'image'          => $img,
                    'icon_url'       => $img,
                    'animation_url'  => $img,
                    'file_url'       => $img,
                    'animation_type' => 'svg',
                    'format'         => 'svg',
                    'display_type'   => $g['coins'] >= 5000 ? 'fullscreen' : 'overlay',
                    'sort_order'     => $sortOrder++,
                    'is_active'      => true,
                    'is_broadcast'   => $g['coins'] >= 5000,
                ]
            );
        }
    }
}
