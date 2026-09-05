<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'coins',
        'coin_price',
        'category',
        'image',
        'icon_url',
        'animation_url',
        'file_url',
        'animation_type',
        'format',
        'display_type',
        'sound_url',
        'sort_order',
        'is_active',
        'is_broadcast',
        'badge',
        'description',
    ];

    protected $casts = [
        'coins'        => 'integer',
        'coin_price'   => 'integer',
        'sort_order'   => 'integer',
        'is_active'    => 'boolean',
        'is_broadcast' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'icon_url',
        'file_url',
        'formatted_coins',
        'display_coins',
        'animation_full_url',
    ];

    /**
     * Get the full URL for the gift icon / image.
     */
    public function getImageUrlAttribute(): string
    {
        $src = $this->attributes['icon_url'] ?? $this->attributes['image'] ?? null;
        if (empty($src)) {
            return asset('uploads/gifts/diamond_ring_gift.svg');
        }

        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }

        $clean = ltrim($src, '/');
        return asset($clean);
    }

    /**
     * Alias for icon_url accessor.
     */
    public function getIconUrlAttribute(): string
    {
        return $this->getImageUrlAttribute();
    }

    /**
     * Get full URL for animation / file_url (SVGA, JSON Lottie, WebP, SVG).
     */
    public function getFileUrlAttribute(): ?string
    {
        $src = $this->attributes['file_url'] ?? $this->attributes['animation_url'] ?? $this->attributes['icon_url'] ?? $this->attributes['image'] ?? null;
        if (empty($src)) {
            return null;
        }

        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }

        return asset(ltrim($src, '/'));
    }

    /**
     * Alias for animation_full_url accessor.
     */
    public function getAnimationFullUrlAttribute(): ?string
    {
        return $this->getFileUrlAttribute();
    }

    /**
     * Getter for coin_price (fallback to coins).
     */
    public function getCoinPriceAttribute(): int
    {
        return (int) ($this->attributes['coin_price'] ?? $this->attributes['coins'] ?? 100);
    }

    /**
     * Format coins for mobile app badges (e.g. 17700 -> 17.70K, 5550 -> 5.55K, 500 -> 500).
     */
    public function getFormattedCoinsAttribute(): string
    {
        return self::formatCoins($this->coins ?: $this->coin_price);
    }

    public function getDisplayCoinsAttribute(): string
    {
        return self::formatCoins($this->coins ?: $this->coin_price);
    }

    /**
     * Static coin formatter matching Chinchins Live UI.
     */
    public static function formatCoins($coins): string
    {
        $num = (float) $coins;
        if ($num >= 1000000) {
            $val = $num / 1000000;
            return (floor($val) == $val ? number_format($val, 0) : rtrim(rtrim(number_format($val, 2), '0'), '.')) . 'M';
        }
        if ($num >= 1000) {
            $val = $num / 1000;
            $formatted = number_format($val, 2);
            if (substr($formatted, -3) === '.00') {
                return number_format($val, 0) . 'K';
            }
            return $formatted . 'K';
        }
        return (string) (int) $num;
    }

    /**
     * Seed comprehensive 27+ 2D/3D animated live streaming gifts across all categories.
     */
    public static function seedDefaultGifts(): void
    {
        $defaultGifts = [
            // ==========================================
            // 🌟 1. POPULAR GIFTS (Entry & High Frequency)
            // ==========================================
            [
                'name'           => 'Rose Bouquet',
                'coins'          => 99,
                'coin_price'     => 99,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/rose_bouquet_gift.svg',
                'icon_url'       => 'uploads/gifts/rose_bouquet_gift.svg',
                'animation_url'  => 'uploads/gifts/rose_bouquet_gift.svg',
                'file_url'       => 'uploads/gifts/rose_bouquet_gift.svg',
                'animation_type' => 'flying_petals',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Popular',
                'description'    => 'Single red rose bouquet with floating sparkle petals.',
                'sort_order'     => 1,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Romantic Kiss',
                'coins'          => 299,
                'coin_price'     => 299,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/romantic_kiss_gift.svg',
                'icon_url'       => 'uploads/gifts/romantic_kiss_gift.svg',
                'animation_url'  => 'uploads/gifts/romantic_kiss_gift.svg',
                'file_url'       => 'uploads/gifts/romantic_kiss_gift.svg',
                'animation_type' => 'particle_hearts',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Hot',
                'description'    => 'Sweet romantic kiss with floating beating hearts.',
                'sort_order'     => 2,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Heart Fireworks',
                'coins'          => 520,
                'coin_price'     => 520,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/heart_fireworks_gift.svg',
                'icon_url'       => 'uploads/gifts/heart_fireworks_gift.svg',
                'animation_url'  => 'uploads/gifts/heart_fireworks_gift.svg',
                'file_url'       => 'uploads/gifts/heart_fireworks_gift.svg',
                'animation_type' => 'fullscreen_fireworks',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Love 520',
                'description'    => '520 I Love You fireworks burst across full screen.',
                'sort_order'     => 3,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Teddy Bear Love',
                'coins'          => 999,
                'coin_price'     => 999,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/teddy_bear_gift.svg',
                'icon_url'       => 'uploads/gifts/teddy_bear_gift.svg',
                'animation_url'  => 'uploads/gifts/teddy_bear_gift.svg',
                'file_url'       => 'uploads/gifts/teddy_bear_gift.svg',
                'animation_type' => 'bounce_3d',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Cute',
                'description'    => 'Cuddly animated teddy bear holding a glowing red heart.',
                'sort_order'     => 4,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Birthday Cake',
                'coins'          => 1314,
                'coin_price'     => 1314,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/birthday_cake.svg',
                'icon_url'       => 'uploads/gifts/birthday_cake.svg',
                'animation_url'  => 'uploads/gifts/birthday_cake.svg',
                'file_url'       => 'uploads/gifts/birthday_cake.svg',
                'animation_type' => 'confetti_cake',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Celebration',
                'description'    => '3-tier luxury candle celebration cake with confetti shower.',
                'sort_order'     => 5,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Champagne Pop',
                'coins'          => 1999,
                'coin_price'     => 1999,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/champagne_gift.svg',
                'icon_url'       => 'uploads/gifts/champagne_gift.svg',
                'animation_url'  => 'uploads/gifts/champagne_gift.svg',
                'file_url'       => 'uploads/gifts/champagne_gift.svg',
                'animation_type' => 'fullscreen_champagne',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Cheers',
                'description'    => 'Sparkling golden champagne bottle pop with effervescent bubbles.',
                'sort_order'     => 6,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],

            // ==========================================
            // 💎 2. LUXURY GIFTS (Supercars, Yachts, Jets)
            // ==========================================
            [
                'name'           => 'Sports Bike Ninja',
                'coins'          => 3333,
                'coin_price'     => 3333,
                'category'       => 'luxury',
                'image'          => 'uploads/gifts/sports_bike_gift.svg',
                'icon_url'       => 'uploads/gifts/sports_bike_gift.svg',
                'animation_url'  => 'uploads/gifts/sports_bike_gift.svg',
                'file_url'       => 'uploads/gifts/sports_bike_gift.svg',
                'animation_type' => 'speed_drive',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Speed',
                'description'    => 'Supercharged sports bike zooming across the live room with exhaust flames.',
                'sort_order'     => 7,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Luxury Supercar',
                'coins'          => 5550,
                'coin_price'     => 5550,
                'category'       => 'luxury',
                'image'          => 'uploads/gifts/sports_car_gift.svg',
                'icon_url'       => 'uploads/gifts/sports_car_gift.svg',
                'animation_url'  => 'uploads/gifts/sports_car_gift.svg',
                'file_url'       => 'uploads/gifts/sports_car_gift.svg',
                'animation_type' => 'drive_in_drift',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Supercar',
                'description'    => 'Sleek golden-winged supercar sliding into the live stream with tire smoke.',
                'sort_order'     => 8,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Diamond Solitaire Ring',
                'coins'          => 9999,
                'coin_price'     => 9999,
                'category'       => 'luxury',
                'image'          => 'uploads/gifts/diamond_ring_gift.svg',
                'icon_url'       => 'uploads/gifts/diamond_ring_gift.svg',
                'animation_url'  => 'uploads/gifts/diamond_ring_gift.svg',
                'file_url'       => 'uploads/gifts/diamond_ring_gift.svg',
                'animation_type' => 'shine_burst',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Luxury',
                'description'    => '24K Diamond Solitaire ring gleaming with prism light reflections.',
                'sort_order'     => 9,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'VIP Helicopter',
                'coins'          => 15000,
                'coin_price'     => 15000,
                'category'       => 'luxury',
                'image'          => 'uploads/gifts/helicopter_gift.svg',
                'icon_url'       => 'uploads/gifts/helicopter_gift.svg',
                'animation_url'  => 'uploads/gifts/helicopter_gift.svg',
                'file_url'       => 'uploads/gifts/helicopter_gift.svg',
                'animation_type' => 'flying_helicopter',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'VIP Arrival',
                'description'    => 'Golden VIP helicopter landing with spotlight illumination.',
                'sort_order'     => 10,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Supersonic Private Jet',
                'coins'          => 25000,
                'coin_price'     => 25000,
                'category'       => 'luxury',
                'image'          => 'uploads/gifts/private_jet_gift.svg',
                'icon_url'       => 'uploads/gifts/private_jet_gift.svg',
                'animation_url'  => 'uploads/gifts/private_jet_gift.svg',
                'file_url'       => 'uploads/gifts/private_jet_gift.svg',
                'animation_type' => 'flying_jet_trail',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Top Tier',
                'description'    => 'Luxury supersonic private jet flying across the screen leaving smoke trail.',
                'sort_order'     => 11,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Mega Luxury Yacht',
                'coins'          => 50000,
                'coin_price'     => 50000,
                'category'       => 'luxury',
                'image'          => 'uploads/gifts/luxury_yacht_gift.svg',
                'icon_url'       => 'uploads/gifts/luxury_yacht_gift.svg',
                'animation_url'  => 'uploads/gifts/luxury_yacht_gift.svg',
                'file_url'       => 'uploads/gifts/luxury_yacht_gift.svg',
                'animation_type' => 'ocean_cruise',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Ultra Luxury',
                'description'    => 'Triple-deck ocean cruiser yacht sailing over luminous waves.',
                'sort_order'     => 12,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Diamond Crystal Palace',
                'coins'          => 100000,
                'coin_price'     => 100000,
                'category'       => 'luxury',
                'image'          => 'uploads/gifts/diamond_castle_gift.svg',
                'icon_url'       => 'uploads/gifts/diamond_castle_gift.svg',
                'animation_url'  => 'uploads/gifts/diamond_castle_gift.svg',
                'file_url'       => 'uploads/gifts/diamond_castle_gift.svg',
                'animation_type' => 'fullscreen_castle_aurora',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'SVIP Palace',
                'description'    => 'Majestic fairytale diamond castle rising with fireworks and aurora.',
                'sort_order'     => 13,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],

            // ==========================================
            // 💖 3. ROMANTIC GIFTS (Love, Couples, Romance)
            // ==========================================
            [
                'name'           => 'Love Mailbox',
                'coins'          => 520,
                'coin_price'     => 520,
                'category'       => 'romantic',
                'image'          => 'uploads/gifts/love_mailbox.svg',
                'icon_url'       => 'uploads/gifts/love_mailbox.svg',
                'animation_url'  => 'uploads/gifts/love_mailbox.svg',
                'file_url'       => 'uploads/gifts/love_mailbox.svg',
                'animation_type' => 'flying_envelope',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Romantic',
                'description'    => 'Vintage love letter delivered with flying envelopes and love birds.',
                'sort_order'     => 14,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Candlelight Dinner',
                'coins'          => 2000,
                'coin_price'     => 2000,
                'category'       => 'romantic',
                'image'          => 'uploads/gifts/candlelight_dinner.svg',
                'icon_url'       => 'uploads/gifts/candlelight_dinner.svg',
                'animation_url'  => 'uploads/gifts/candlelight_dinner.svg',
                'file_url'       => 'uploads/gifts/candlelight_dinner.svg',
                'animation_type' => 'romantic_glow',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Sweet',
                'description'    => 'Romantic 5-star candlelight dinner table for two.',
                'sort_order'     => 15,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Sunset Couple Walk',
                'coins'          => 5200,
                'coin_price'     => 5200,
                'category'       => 'romantic',
                'image'          => 'uploads/gifts/sunset_couple.svg',
                'icon_url'       => 'uploads/gifts/sunset_couple.svg',
                'animation_url'  => 'uploads/gifts/sunset_couple.svg',
                'file_url'       => 'uploads/gifts/sunset_couple.svg',
                'animation_type' => 'cinematic_sunset',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Eternal Love',
                'description'    => 'Golden hour romantic couple walking along glowing ocean beach.',
                'sort_order'     => 16,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Midnight Lovers Moon',
                'coins'          => 9999,
                'coin_price'     => 9999,
                'category'       => 'romantic',
                'image'          => 'uploads/gifts/midnight_lovers.svg',
                'icon_url'       => 'uploads/gifts/midnight_lovers.svg',
                'animation_url'  => 'uploads/gifts/midnight_lovers.svg',
                'file_url'       => 'uploads/gifts/midnight_lovers.svg',
                'animation_type' => 'celestial_moon',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Full Moon',
                'description'    => 'Lovers embracing on a crescent moon among shooting stars.',
                'sort_order'     => 17,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Vintage Romance Carriage',
                'coins'          => 13140,
                'coin_price'     => 13140,
                'category'       => 'romantic',
                'image'          => 'uploads/gifts/vintage_romance.svg',
                'icon_url'       => 'uploads/gifts/vintage_romance.svg',
                'animation_url'  => 'uploads/gifts/vintage_romance.svg',
                'file_url'       => 'uploads/gifts/vintage_romance.svg',
                'animation_type' => 'royal_carriage',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Forever 1314',
                'description'    => 'Royal white horse carriage surrounded by floating rose petals.',
                'sort_order'     => 18,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],

            // ==========================================
            // ⚡ 4. 2D/3D ANIMATION EFFECTS (Dragons, Phoenix, Rockets)
            // ==========================================
            [
                'name'           => 'Space Rocket Launch',
                'coins'          => 7777,
                'coin_price'     => 7777,
                'category'       => 'effects',
                'image'          => 'uploads/gifts/space_rocket_gift.svg',
                'icon_url'       => 'uploads/gifts/space_rocket_gift.svg',
                'animation_url'  => 'uploads/gifts/space_rocket_gift.svg',
                'file_url'       => 'uploads/gifts/space_rocket_gift.svg',
                'animation_type' => 'rocket_blastoff',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Rocket',
                'description'    => 'High-thrust space rocket taking off with flame trail and smoke.',
                'sort_order'     => 19,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Magic Genie Lamp',
                'coins'          => 15000,
                'coin_price'     => 15000,
                'category'       => 'effects',
                'image'          => 'uploads/gifts/genie_lamp.svg',
                'icon_url'       => 'uploads/gifts/genie_lamp.svg',
                'animation_url'  => 'uploads/gifts/genie_lamp.svg',
                'file_url'       => 'uploads/gifts/genie_lamp.svg',
                'animation_type' => 'mystic_smoke',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Wish',
                'description'    => 'Golden mystical magic lamp revealing a powerful wish smoke effect.',
                'sort_order'     => 20,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Flaming Fire Dragon',
                'coins'          => 20000,
                'coin_price'     => 20000,
                'category'       => 'effects',
                'image'          => 'uploads/gifts/dragon_gift.svg',
                'icon_url'       => 'uploads/gifts/dragon_gift.svg',
                'animation_url'  => 'uploads/gifts/dragon_gift.svg',
                'file_url'       => 'uploads/gifts/dragon_gift.svg',
                'animation_type' => 'dragon_roar_fire',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Dragon 3D',
                'description'    => 'Mythic oriental dragon soaring across live stream breathing golden flames.',
                'sort_order'     => 21,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Flying Phoenix Rebirth',
                'coins'          => 30000,
                'coin_price'     => 30000,
                'category'       => 'effects',
                'image'          => 'uploads/gifts/phoenix_gift.svg',
                'icon_url'       => 'uploads/gifts/phoenix_gift.svg',
                'animation_url'  => 'uploads/gifts/phoenix_gift.svg',
                'file_url'       => 'uploads/gifts/phoenix_gift.svg',
                'animation_type' => 'phoenix_flight',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Phoenix 3D',
                'description'    => 'Legendary immortal fire phoenix spreading radiant golden wings.',
                'sort_order'     => 22,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Galaxy Warp Portal',
                'coins'          => 45000,
                'coin_price'     => 45000,
                'category'       => 'effects',
                'image'          => 'uploads/gifts/galaxy_portal.svg',
                'icon_url'       => 'uploads/gifts/galaxy_portal.svg',
                'animation_url'  => 'uploads/gifts/galaxy_portal.svg',
                'file_url'       => 'uploads/gifts/galaxy_portal.svg',
                'animation_type' => 'cosmic_portal_warp',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Cosmic 3D',
                'description'    => 'Interstellar black hole and galaxy portal bending reality with starlight.',
                'sort_order'     => 23,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Interstellar Battleship',
                'coins'          => 75000,
                'coin_price'     => 75000,
                'category'       => 'effects',
                'image'          => 'uploads/gifts/space_battleship.svg',
                'icon_url'       => 'uploads/gifts/space_battleship.svg',
                'animation_url'  => 'uploads/gifts/space_battleship.svg',
                'file_url'       => 'uploads/gifts/space_battleship.svg',
                'animation_type' => 'battleship_laser',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Galactic 3D',
                'description'    => 'Massive cosmic dreadnought starship firing laser celebration beams.',
                'sort_order'     => 24,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],

            // ==========================================
            // 👑 5. VIP EXCLUSIVE GIFTS
            // ==========================================
            [
                'name'           => 'Fairy Princess Tiara',
                'coins'          => 1999,
                'coin_price'     => 1999,
                'category'       => 'vip',
                'image'          => 'uploads/gifts/fairy_crown.svg',
                'icon_url'       => 'uploads/gifts/fairy_crown.svg',
                'animation_url'  => 'uploads/gifts/fairy_crown.svg',
                'file_url'       => 'uploads/gifts/fairy_crown.svg',
                'animation_type' => 'sparkle_tiara',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Princess',
                'description'    => 'Sparkling enchanted emerald and amethyst fairy tiara.',
                'sort_order'     => 25,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Royal Sovereign Crown',
                'coins'          => 17700,
                'coin_price'     => 17700,
                'category'       => 'vip',
                'image'          => 'uploads/gifts/royal_crown_gift.svg',
                'icon_url'       => 'uploads/gifts/royal_crown_gift.svg',
                'animation_url'  => 'uploads/gifts/royal_crown_gift.svg',
                'file_url'       => 'uploads/gifts/royal_crown_gift.svg',
                'animation_type' => 'crown_descend_gold',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Royal VIP',
                'description'    => 'Imperial 24K gold crown with ruby jewels crowning the host.',
                'sort_order'     => 26,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Mythic Treasure Chest',
                'coins'          => 35000,
                'coin_price'     => 35000,
                'category'       => 'vip',
                'image'          => 'uploads/gifts/treasure_chest.svg',
                'icon_url'       => 'uploads/gifts/treasure_chest.svg',
                'animation_url'  => 'uploads/gifts/treasure_chest.svg',
                'file_url'       => 'uploads/gifts/treasure_chest.svg',
                'animation_type' => 'chest_burst_gems',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Jackpot',
                'description'    => 'Ancient sunken pirate treasure chest bursting open with diamonds and gold coins.',
                'sort_order'     => 27,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Golden Cobra Serpent',
                'coins'          => 7500,
                'coin_price'     => 7500,
                'category'       => 'effects',
                'image'          => 'uploads/gifts/golden_cobra_snake.svg',
                'icon_url'       => 'uploads/gifts/golden_cobra_snake.svg',
                'animation_url'  => 'uploads/gifts/golden_cobra_snake.svg',
                'file_url'       => 'uploads/gifts/golden_cobra_snake.svg',
                'animation_type' => 'snake_strike_emerald',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Mythic',
                'description'    => 'Majestic emerald and gold coiled king cobra rising with glowing neon eyes.',
                'sort_order'     => 28,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
            [
                'name'           => 'Playful Monkey King',
                'coins'          => 2200,
                'coin_price'     => 2200,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/playful_monkey_king.svg',
                'icon_url'       => 'uploads/gifts/playful_monkey_king.svg',
                'animation_url'  => 'uploads/gifts/playful_monkey_king.svg',
                'file_url'       => 'uploads/gifts/playful_monkey_king.svg',
                'animation_type' => 'monkey_flip_bananas',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Funny',
                'description'    => 'Acrobatic monkey king doing backflips and tossing golden bananas.',
                'sort_order'     => 29,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Lucky Gold Mouse',
                'coins'          => 888,
                'coin_price'     => 888,
                'category'       => 'popular',
                'image'          => 'uploads/gifts/lucky_gold_mouse.svg',
                'icon_url'       => 'uploads/gifts/lucky_gold_mouse.svg',
                'animation_url'  => 'uploads/gifts/lucky_gold_mouse.svg',
                'file_url'       => 'uploads/gifts/lucky_gold_mouse.svg',
                'animation_type' => 'mouse_coin_rush',
                'format'         => 'svg',
                'display_type'   => 'overlay',
                'badge'          => 'Lucky 888',
                'description'    => 'Cute golden lucky mouse bringing a chest of prosperity and wealth coins.',
                'sort_order'     => 30,
                'is_active'      => true,
                'is_broadcast'   => false,
            ],
            [
                'name'           => 'Padma River Boat Cruise',
                'coins'          => 6200,
                'coin_price'     => 6200,
                'category'       => 'desi',
                'image'          => 'uploads/gifts/padma_river_sunset.svg',
                'icon_url'       => 'uploads/gifts/padma_river_sunset.svg',
                'animation_url'  => 'uploads/gifts/padma_river_sunset.svg',
                'file_url'       => 'uploads/gifts/padma_river_sunset.svg',
                'animation_type' => 'river_boat_sailing',
                'format'         => 'svg',
                'display_type'   => 'fullscreen',
                'badge'          => 'Scenic Desi',
                'description'    => 'Serene traditional Bengali wooden boat sailing across golden sunset river waters.',
                'sort_order'     => 31,
                'is_active'      => true,
                'is_broadcast'   => true,
            ],
        ];

        foreach ($defaultGifts as $giftData) {
            static::updateOrCreate(
                ['name' => $giftData['name']],
                $giftData
            );
        }
    }

    /**
     * Relationship: User Gifts received.
     */
    public function userGifts()
    {
        return $this->hasMany(UserGift::class);
    }

    /**
     * Relationship: Live Gift Transactions.
     */
    public function giftTransactions()
    {
        return $this->hasMany(GiftTransaction::class, 'gift_id');
    }
}
