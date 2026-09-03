<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipPrivilegeCard extends Model
{
    use HasFactory;

    protected $table = 'vip_privilege_cards';

    protected $fillable = [
        'card_type',
        'name',
        'badge_text',
        'price_bdt',
        'price_coins',
        'duration_days',
        'instant_reward_coins',
        'daily_checkin_total_coins',
        'total_return_coins',
        'daily_schedule',
        'extra_rewards',
        'description',
        'card_color',
        'banner_tag',
        'icon_url',
        'animation_url',
        'bg_image_url',
        'format',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_bdt'                 => 'decimal:2',
        'price_coins'               => 'integer',
        'duration_days'             => 'integer',
        'instant_reward_coins'      => 'integer',
        'daily_checkin_total_coins' => 'integer',
        'total_return_coins'        => 'integer',
        'daily_schedule'            => 'array',
        'extra_rewards'             => 'array',
        'is_active'                 => 'boolean',
        'sort_order'                => 'integer',
    ];

    protected $appends = [
        'icon_full_url',
        'animation_full_url',
        'bg_image_full_url',
    ];

    /**
     * Full URL for Card Icon
     */
    public function getIconFullUrlAttribute(): ?string
    {
        if (empty($this->icon_url)) {
            return asset('assets/images/vip/vip_card_badge.png');
        }
        if (str_starts_with($this->icon_url, 'http://') || str_starts_with($this->icon_url, 'https://')) {
            return $this->icon_url;
        }
        return asset(ltrim($this->icon_url, '/'));
    }

    /**
     * Full URL for Animation (Lottie JSON / SVGA / WebP / GIF)
     */
    public function getAnimationFullUrlAttribute(): ?string
    {
        if (empty($this->animation_url)) {
            return null;
        }
        if (str_starts_with($this->animation_url, 'http://') || str_starts_with($this->animation_url, 'https://')) {
            return $this->animation_url;
        }
        return asset(ltrim($this->animation_url, '/'));
    }

    /**
     * Full URL for Background Image
     */
    public function getBgImageFullUrlAttribute(): ?string
    {
        if (empty($this->bg_image_url)) {
            return null;
        }
        if (str_starts_with($this->bg_image_url, 'http://') || str_starts_with($this->bg_image_url, 'https://')) {
            return $this->bg_image_url;
        }
        return asset(ltrim($this->bg_image_url, '/'));
    }

    /**
     * Subscriptions associated with this card.
     */
    public function subscriptions()
    {
        return $this->hasMany(UserVipCardSubscription::class, 'vip_card_id');
    }

    /**
     * Seed default initial cards if table is empty.
     */
    public static function seedDefaultCards(): void
    {
        if (static::count() > 0) {
            return;
        }

        // 1. New User Weekly Card
        static::create([
            'card_type'                 => 'new_user',
            'name'                      => 'New User Weekly Card',
            'badge_text'                => 'HOT',
            'price_bdt'                 => 300.00,
            'price_coins'               => 8100,
            'duration_days'             => 7,
            'instant_reward_coins'      => 8100,
            'daily_checkin_total_coins' => 2020,
            'total_return_coins'        => 10120,
            'card_color'                => '#FF4081',
            'banner_tag'                => 'Spend Less, Get More Gems! Update to New User Weekly Card',
            'daily_schedule'            => [
                ['day' => 1, 'coins' => 8100, 'extra' => 'Card x1'],
                ['day' => 2, 'coins' => 300,  'extra' => null],
                ['day' => 3, 'coins' => 210,  'extra' => null],
                ['day' => 4, 'coins' => 500,  'extra' => null],
                ['day' => 5, 'coins' => 350,  'extra' => null],
                ['day' => 6, 'coins' => 300,  'extra' => null],
                ['day' => 7, 'coins' => 360,  'extra' => 'Exclusive Badge'],
            ],
            'extra_rewards'             => [
                ['title' => 'Exclusive Avatar Frame', 'tag' => 'Free Outfits', 'icon' => 'frame_avatar'],
                ['title' => 'Weekly Card Badge',      'tag' => 'SVIP Icon',    'icon' => 'badge_svip'],
                ['title' => 'Free Lucky Card x1',     'tag' => 'Free Card',    'icon' => 'lucky_card'],
            ],
            'description'               => 'Normal Recharge = 8,100 Gems. Weekly Card = 10,120 Gems + Outfits + Free Cards!',
            'sort_order'                => 1,
            'is_active'                 => true,
        ]);

        // 2. Super Monthly Card
        static::create([
            'card_type'                 => 'super_monthly',
            'name'                      => 'Super Monthly Card',
            'badge_text'                => 'BEST VALUE',
            'price_bdt'                 => 1200.00,
            'price_coins'               => 32940,
            'duration_days'             => 30,
            'instant_reward_coins'      => 32940,
            'daily_checkin_total_coins' => 26330,
            'total_return_coins'        => 59270,
            'card_color'                => '#7C4DFF',
            'banner_tag'                => 'Super Monthly Card: 59,270 Gems + Outfits + Free Cards!',
            'daily_schedule'            => [
                ['day' => 1, 'coins' => 32940, 'extra' => 'Gold Frame'],
                ['day' => 2, 'coins' => 1790,  'extra' => null],
                ['day' => 3, 'coins' => 1210,  'extra' => null],
                ['day' => 4, 'coins' => 1790,  'extra' => null],
                ['day' => 5, 'coins' => 1210,  'extra' => null],
                ['day' => 6, 'coins' => 1790,  'extra' => null],
                ['day' => 7, 'coins' => 1790,  'extra' => null],
                ['day' => 8, 'coins' => 656,   'extra' => null],
                ['day' => 9, 'coins' => 656,   'extra' => null],
                ['day' => 10, 'coins' => 656,  'extra' => null],
                ['day' => 11, 'coins' => 656,  'extra' => null],
                ['day' => 12, 'coins' => 656,  'extra' => null],
                ['day' => 13, 'coins' => 656,  'extra' => null],
                ['day' => 14, 'coins' => 656,  'extra' => 'Bonus Card'],
                ['day' => 15, 'coins' => 656,  'extra' => null],
                ['day' => 16, 'coins' => 656,  'extra' => null],
                ['day' => 17, 'coins' => 656,  'extra' => null],
                ['day' => 18, 'coins' => 656,  'extra' => null],
                ['day' => 19, 'coins' => 656,  'extra' => null],
                ['day' => 20, 'coins' => 656,  'extra' => null],
                ['day' => 21, 'coins' => 656,  'extra' => null],
                ['day' => 22, 'coins' => 656,  'extra' => null],
                ['day' => 23, 'coins' => 656,  'extra' => null],
                ['day' => 24, 'coins' => 656,  'extra' => null],
                ['day' => 25, 'coins' => 656,  'extra' => null],
                ['day' => 26, 'coins' => 656,  'extra' => null],
                ['day' => 27, 'coins' => 656,  'extra' => null],
                ['day' => 28, 'coins' => 656,  'extra' => null],
                ['day' => 29, 'coins' => 656,  'extra' => null],
                ['day' => 30, 'coins' => 656,  'extra' => 'SVIP 30D'],
            ],
            'extra_rewards'             => [
                ['title' => 'Super VIP Gold Frame',   'tag' => 'Gold Frame',      'icon' => 'frame_gold'],
                ['title' => 'Luxury Chat Bubble',     'tag' => 'Special Outfit',  'icon' => 'chat_bubble'],
                ['title' => 'Privilege Entry Banner', 'tag' => 'Entry Animation', 'icon' => 'entry_anim'],
            ],
            'description'               => 'Normal Recharge = 32,940 Gems. Monthly Card = 59,270 Gems + Outfits + Free Cards!',
            'sort_order'                => 2,
            'is_active'                 => true,
        ]);

        // 3. Luxury Monthly Card
        static::create([
            'card_type'                 => 'luxury_monthly',
            'name'                      => 'Luxury Monthly Card',
            'badge_text'                => '50% OFF',
            'price_bdt'                 => 2400.00,
            'price_coins'               => 66600,
            'duration_days'             => 30,
            'instant_reward_coins'      => 66600,
            'daily_checkin_total_coins' => 87110,
            'total_return_coins'        => 153710,
            'card_color'                => '#2979FF',
            'banner_tag'                => 'Luxury Monthly Card: 153,710 Gems + Outfits + Free Cards!',
            'daily_schedule'            => [
                ['day' => 1, 'coins' => 66600, 'extra' => 'Diamond Frame'],
                ['day' => 2, 'coins' => 3500,  'extra' => null],
                ['day' => 3, 'coins' => 1790,  'extra' => null],
                ['day' => 4, 'coins' => 3500,  'extra' => null],
                ['day' => 5, 'coins' => 1790,  'extra' => null],
                ['day' => 6, 'coins' => 3500,  'extra' => null],
                ['day' => 7, 'coins' => 3500,  'extra' => null],
                ['day' => 8, 'coins' => 2953,  'extra' => null],
                ['day' => 9, 'coins' => 2953,  'extra' => null],
                ['day' => 10, 'coins' => 2953, 'extra' => null],
                ['day' => 11, 'coins' => 2953, 'extra' => null],
                ['day' => 12, 'coins' => 2953, 'extra' => null],
                ['day' => 13, 'coins' => 2953, 'extra' => null],
                ['day' => 14, 'coins' => 2953, 'extra' => 'Super Card x2'],
                ['day' => 15, 'coins' => 2953, 'extra' => null],
                ['day' => 16, 'coins' => 2953, 'extra' => null],
                ['day' => 17, 'coins' => 2953, 'extra' => null],
                ['day' => 18, 'coins' => 2953, 'extra' => null],
                ['day' => 19, 'coins' => 2953, 'extra' => null],
                ['day' => 20, 'coins' => 2953, 'extra' => null],
                ['day' => 21, 'coins' => 2953, 'extra' => null],
                ['day' => 22, 'coins' => 2953, 'extra' => null],
                ['day' => 23, 'coins' => 2953, 'extra' => null],
                ['day' => 24, 'coins' => 2953, 'extra' => null],
                ['day' => 25, 'coins' => 2953, 'extra' => null],
                ['day' => 26, 'coins' => 2953, 'extra' => null],
                ['day' => 27, 'coins' => 2953, 'extra' => null],
                ['day' => 28, 'coins' => 2953, 'extra' => null],
                ['day' => 29, 'coins' => 2953, 'extra' => null],
                ['day' => 30, 'coins' => 2953, 'extra' => 'Luxury SVIP Crown'],
            ],
            'extra_rewards'             => [
                ['title' => 'Diamond Halo Frame',        'tag' => 'Luxury Outfit', 'icon' => 'frame_diamond'],
                ['title' => 'SVIP Crown Badge & Title',  'tag' => 'SVIP Status',   'icon' => 'svip_crown'],
                ['title' => 'Global Room Entry Effect',  'tag' => 'Super Entry',   'icon' => 'global_entry'],
                ['title' => 'Free Lucky Cards x5',       'tag' => 'Free Cards',    'icon' => 'lucky_cards_5'],
            ],
            'description'               => 'Normal Recharge = 66,600 Gems. Luxury Card = 153,710 Gems + Outfits + Free Cards!',
            'sort_order'                => 3,
            'is_active'                 => true,
        ]);

        // 4. Super Weekly Card
        static::create([
            'card_type'                 => 'super_weekly',
            'name'                      => 'Super Weekly Card',
            'badge_text'                => 'POPULAR',
            'price_bdt'                 => 600.00,
            'price_coins'               => 16200,
            'duration_days'             => 7,
            'instant_reward_coins'      => 16200,
            'daily_checkin_total_coins' => 5000,
            'total_return_coins'        => 21200,
            'card_color'                => '#00E676',
            'banner_tag'                => 'Super Weekly Card: 21,200 Gems + Outfits!',
            'daily_schedule'            => [
                ['day' => 1, 'coins' => 16200, 'extra' => 'Green Frame'],
                ['day' => 2, 'coins' => 800,   'extra' => null],
                ['day' => 3, 'coins' => 700,   'extra' => null],
                ['day' => 4, 'coins' => 900,   'extra' => null],
                ['day' => 5, 'coins' => 800,   'extra' => null],
                ['day' => 6, 'coins' => 800,   'extra' => null],
                ['day' => 7, 'coins' => 1000,  'extra' => 'Weekly Card x1'],
            ],
            'extra_rewards'             => [
                ['title' => 'Neon Green Frame',     'tag' => 'Neon Outfit', 'icon' => 'frame_green'],
                ['title' => 'VIP Weekly Icon',      'tag' => 'VIP Badge',   'icon' => 'badge_green'],
                ['title' => 'Free Lucky Card x2',   'tag' => 'Free Cards',  'icon' => 'lucky_card_2'],
            ],
            'description'               => 'Normal Recharge = 16,200 Gems. Super Weekly = 21,200 Gems + Outfits!',
            'sort_order'                => 4,
            'is_active'                 => true,
        ]);
    }
}
