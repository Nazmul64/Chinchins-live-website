<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BagItem extends Model
{
    use HasFactory;

    protected $table = 'bag_items';

    protected $fillable = [
        'name',
        'category',
        'code',
        'price_coins',
        'price_bdt',
        'duration_days',
        'badge',
        'discount_percent',
        'coupon_value',
        'icon_url',
        'image_url',
        'preview_url',
        'animation_url',
        'format',
        'effect_type',
        'description',
        'is_active',
        'is_giftable',
        'sort_order',
        'attributes',
    ];

    protected $casts = [
        'price_coins'      => 'integer',
        'price_bdt'        => 'decimal:2',
        'duration_days'    => 'integer',
        'discount_percent' => 'integer',
        'coupon_value'     => 'integer',
        'is_active'        => 'boolean',
        'is_giftable'      => 'boolean',
        'sort_order'       => 'integer',
        'attributes'       => 'array',
    ];

    protected $appends = [
        'icon_full_url',
        'image_full_url',
        'preview_full_url',
        'animation_full_url',
        'png_url',
        'svg_url',
        'formatted_price',
        'duration_text',
        'category_name',
    ];

    const CACHE_KEY_ACTIVE = 'chinchins_bag_items_active_v1';
    protected static ?\Illuminate\Support\Collection $_staticCachedItems = null;

    /**
     * Clear active cache on save/delete.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });
        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function clearCache(): void
    {
        static::$_staticCachedItems = null;
        Cache::forget(static::CACHE_KEY_ACTIVE);
    }

    /**
     * Categories dictionary matching Flutter App My Bag tabs.
     */
    public static function categories(): array
    {
        return [
            'coupon'          => 'Coupon',
            'avatar_frame'    => 'Avatar Frame',
            'chat_style'      => 'Chat Style',
            'profile_card'    => 'Profile Card',
            'entrance_bubble' => 'Entrance Bubble',
            'big_entrance'    => 'Big Entrance',
        ];
    }

    /**
     * Category Icons dictionary.
     */
    public static function categoryIcons(): array
    {
        return [
            'coupon'          => 'fa-solid fa-ticket',
            'avatar_frame'    => 'fa-solid fa-circle-notch',
            'chat_style'      => 'fa-solid fa-comment-dots',
            'profile_card'    => 'fa-solid fa-id-card-clip',
            'entrance_bubble' => 'fa-solid fa-coins',
            'big_entrance'    => 'fa-solid fa-car-side',
        ];
    }

    public function getCategoryNameAttribute(): string
    {
        return static::categories()[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getIconFullUrlAttribute(): ?string
    {
        $src = $this->icon_url ?: $this->image_url;
        if (empty($src)) return null;
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) return $src;
        return url(ltrim($src, '/'));
    }

    public function getImageFullUrlAttribute(): ?string
    {
        $src = $this->image_url ?: $this->icon_url;
        if (empty($src)) return null;
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) return $src;
        return url(ltrim($src, '/'));
    }

    public function getPngUrlAttribute(): ?string
    {
        $src = $this->image_url ?: $this->icon_url ?: $this->preview_url;
        if (empty($src)) return null;
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return preg_replace('/\.svg(\?.*)?$/i', '.png$1', $src);
        }
        $pngPath = preg_replace('/\.svg$/i', '.png', ltrim($src, '/'));
        return url($pngPath);
    }

    public function getSvgUrlAttribute(): ?string
    {
        $src = $this->image_url ?: $this->icon_url ?: $this->preview_url;
        if (empty($src)) return null;
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }
        return url(ltrim($src, '/'));
    }

    public function getPreviewFullUrlAttribute(): ?string
    {
        $src = $this->preview_url ?: $this->image_url ?: $this->icon_url;
        if (empty($src)) return null;
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) return $src;
        return url(ltrim($src, '/'));
    }

    public function getAnimationFullUrlAttribute(): ?string
    {
        $src = $this->animation_url ?: $this->preview_url ?: $this->image_url;
        if (empty($src)) return null;
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) return $src;
        return url(ltrim($src, '/'));
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_coins > 0) {
            return number_format($this->price_coins) . ' Gems';
        }
        if ($this->price_bdt > 0) {
            return '৳' . number_format($this->price_bdt, 2);
        }
        return 'Free';
    }

    public function getDurationTextAttribute(): string
    {
        if ($this->duration_days <= 0) {
            return 'Permanent / One-time';
        }
        return $this->duration_days . ' Days';
    }

    /**
     * User bag items ownership relation.
     */
    public function userBagItems()
    {
        return $this->hasMany(UserBagItem::class, 'bag_item_id');
    }

    /**
     * Seed initial items for all 6 My Bag categories if empty.
     */
    public static function seedDefaultItems(): void
    {
        if (static::count() > 0) {
            return;
        }

        $items = [
            // 1. Coupons
            [
                'name'             => '50% Off Recharge Coupon',
                'category'         => 'coupon',
                'code'             => 'coupon_sale_50',
                'price_coins'      => 0,
                'price_bdt'        => 0.00,
                'duration_days'    => 7,
                'badge'            => 'SALE 50%',
                'discount_percent' => 50,
                'coupon_value'     => 500,
                'icon_url'         => 'uploads/my_bag/coupon_sale_yellow.svg',
                'image_url'        => 'uploads/my_bag/coupon_sale_yellow.svg',
                'preview_url'      => 'uploads/my_bag/coupon_sale_yellow.svg',
                'format'           => 'svg',
                'effect_type'      => 'discount_recharge',
                'description'      => 'Gives 50% extra gems or 50% discount on next coin package recharge.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 1,
            ],
            [
                'name'             => 'VIP 500 Gems Voucher',
                'category'         => 'coupon',
                'code'             => 'coupon_vip_500',
                'price_coins'      => 500,
                'price_bdt'        => 50.00,
                'duration_days'    => 15,
                'badge'            => 'VIP CASHBACK',
                'discount_percent' => 30,
                'coupon_value'     => 500,
                'icon_url'         => 'uploads/my_bag/coupon_vip_discount.svg',
                'image_url'        => 'uploads/my_bag/coupon_vip_discount.svg',
                'preview_url'      => 'uploads/my_bag/coupon_vip_discount.svg',
                'format'           => 'svg',
                'effect_type'      => 'instant_cashback',
                'description'      => 'Redeem instantly in wallet for 500 bonus gems balance.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 2,
            ],

            // 2. Avatar Frames
            [
                'name'             => 'Royal Amethyst Frame',
                'category'         => 'avatar_frame',
                'code'             => 'frame_royal_amethyst',
                'price_coins'      => 1500,
                'price_bdt'        => 120.00,
                'duration_days'    => 30,
                'badge'            => 'POPULAR',
                'icon_url'         => 'uploads/my_bag/frame_royal_amethyst.svg',
                'image_url'        => 'uploads/my_bag/frame_royal_amethyst.svg',
                'preview_url'      => 'uploads/my_bag/frame_royal_amethyst.svg',
                'format'           => 'svg',
                'effect_type'      => 'frame_pulse_glow',
                'description'      => 'Enchanted violet and 24K gold rotating avatar frame with celestial aura.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 3,
            ],
            [
                'name'             => 'Fire Blaze Dragon Frame',
                'category'         => 'avatar_frame',
                'code'             => 'frame_fire_blaze',
                'price_coins'      => 2500,
                'price_bdt'        => 200.00,
                'duration_days'    => 30,
                'badge'            => 'HOT',
                'icon_url'         => 'uploads/my_bag/frame_fire_blaze.svg',
                'image_url'        => 'uploads/my_bag/frame_fire_blaze.svg',
                'preview_url'      => 'uploads/my_bag/frame_fire_blaze.svg',
                'format'           => 'svg',
                'effect_type'      => 'frame_flame_burn',
                'description'      => 'Blazing flaming dragon avatar frame for top hosts and elite callers.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 4,
            ],

            // 3. Chat Styles
            [
                'name'             => 'Neon Pink Sweet Bubble',
                'category'         => 'chat_style',
                'code'             => 'chat_bubble_neon_pink',
                'price_coins'      => 800,
                'price_bdt'        => 60.00,
                'duration_days'    => 15,
                'badge'            => 'SWEET',
                'icon_url'         => 'uploads/my_bag/chat_bubble_neon_pink.svg',
                'image_url'        => 'uploads/my_bag/chat_bubble_neon_pink.svg',
                'preview_url'      => 'uploads/my_bag/chat_bubble_neon_pink.svg',
                'format'           => 'svg',
                'effect_type'      => 'chat_bubble_pink',
                'description'      => 'Custom vibrant pink message bubble with Hi badge in 1-on-1 and live chats.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 5,
            ],
            [
                'name'             => 'Golden Luxury VIP Bubble',
                'category'         => 'chat_style',
                'code'             => 'chat_bubble_golden_luxury',
                'price_coins'      => 1200,
                'price_bdt'        => 99.00,
                'duration_days'    => 30,
                'badge'            => 'VIP LUXURY',
                'icon_url'         => 'uploads/my_bag/chat_bubble_golden_luxury.svg',
                'image_url'        => 'uploads/my_bag/chat_bubble_golden_luxury.svg',
                'preview_url'      => 'uploads/my_bag/chat_bubble_golden_luxury.svg',
                'format'           => 'svg',
                'effect_type'      => 'chat_bubble_gold',
                'description'      => 'Imperial 24K gold luxury chat bubble with glowing VIP badge.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 6,
            ],

            // 4. Profile Cards
            [
                'name'             => 'Aurora Galaxy Nebula Card',
                'category'         => 'profile_card',
                'code'             => 'profile_card_aurora_galaxy',
                'price_coins'      => 2000,
                'price_bdt'        => 150.00,
                'duration_days'    => 30,
                'badge'            => 'EXCLUSIVE',
                'icon_url'         => 'uploads/my_bag/profile_card_aurora_galaxy.svg',
                'image_url'        => 'uploads/my_bag/profile_card_aurora_galaxy.svg',
                'preview_url'      => 'uploads/my_bag/profile_card_aurora_galaxy.svg',
                'format'           => 'svg',
                'effect_type'      => 'profile_hologram',
                'description'      => 'Futuristic galaxy nebula theme background card on user profile view.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 7,
            ],
            [
                'name'             => 'Crimson Dragon Imperial Card',
                'category'         => 'profile_card',
                'code'             => 'profile_card_crimson_dragon',
                'price_coins'      => 3000,
                'price_bdt'        => 220.00,
                'duration_days'    => 30,
                'badge'            => 'LEGENDARY',
                'icon_url'         => 'uploads/my_bag/profile_card_crimson_dragon.svg',
                'image_url'        => 'uploads/my_bag/profile_card_crimson_dragon.svg',
                'preview_url'      => 'uploads/my_bag/profile_card_crimson_dragon.svg',
                'format'           => 'svg',
                'effect_type'      => 'profile_dragon',
                'description'      => 'Crimson red imperial dragon badge card for top supporters.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 8,
            ],

            // 5. Entrance Bubbles
            [
                'name'             => 'Royal Gold Crown Bubble',
                'category'         => 'entrance_bubble',
                'code'             => 'entrance_bubble_gold_crown',
                'price_coins'      => 1800,
                'price_bdt'        => 140.00,
                'duration_days'    => 30,
                'badge'            => 'ROYAL',
                'icon_url'         => 'uploads/my_bag/entrance_bubble_gold_crown.svg',
                'image_url'        => 'uploads/my_bag/entrance_bubble_gold_crown.svg',
                'preview_url'      => 'uploads/my_bag/entrance_bubble_gold_crown.svg',
                'format'           => 'svg',
                'effect_type'      => 'room_entry_capsule',
                'description'      => 'Gold coin capsule announcement whenever you enter any live stream or video room.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 9,
            ],
            [
                'name'             => 'Diamond VIP Entrance Bubble',
                'category'         => 'entrance_bubble',
                'code'             => 'entrance_bubble_diamond_vip',
                'price_coins'      => 3500,
                'price_bdt'        => 280.00,
                'duration_days'    => 30,
                'badge'            => 'DIAMOND',
                'icon_url'         => 'uploads/my_bag/entrance_bubble_diamond_vip.svg',
                'image_url'        => 'uploads/my_bag/entrance_bubble_diamond_vip.svg',
                'preview_url'      => 'uploads/my_bag/entrance_bubble_diamond_vip.svg',
                'format'           => 'svg',
                'effect_type'      => 'room_entry_diamond',
                'description'      => 'Diamond star glow broadcast announcement when joining live stream.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 10,
            ],

            // 6. Big Entrances
            [
                'name'             => 'Bugatti Supercar Ride Entrance',
                'category'         => 'big_entrance',
                'code'             => 'big_entrance_sports_car',
                'price_coins'      => 5000,
                'price_bdt'        => 400.00,
                'duration_days'    => 30,
                'badge'            => 'SUPER RIDE',
                'icon_url'         => 'uploads/my_bag/big_entrance_sports_car.svg',
                'image_url'        => 'uploads/my_bag/big_entrance_sports_car.svg',
                'preview_url'      => 'uploads/my_bag/big_entrance_sports_car.svg',
                'format'           => 'svg',
                'effect_type'      => 'fullscreen_car_drive',
                'description'      => 'Luxury sports car driving across screen with neon light trail on room entrance.',
                'is_active'        => true,
                'is_giftable'      => true,
                'sort_order'       => 11,
            ],
        ];

        foreach ($items as $item) {
            static::updateOrCreate(['code' => $item['code']], $item);
        }

        static::clearCache();
    }
}
