<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpendLessCard extends Model
{
    use HasFactory;

    protected $table = 'spend_less_cards';

    protected $fillable = [
        'card_type',
        'name',
        'category_name',
        'badge_text',
        'price_bdt',
        'original_price_bdt',
        'price_coins',
        'duration_days',
        'instant_reward_coins',
        'instant_reward_text',
        'daily_checkin_total_coins',
        'daily_checkin_text',
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
        'original_price_bdt'        => 'decimal:2',
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
        'discount_percent',
        'formatted_price_bdt',
        'formatted_original_price_bdt',
    ];

    /**
     * Computed discount percentage.
     */
    public function getDiscountPercentAttribute(): ?int
    {
        $orig = (float) ($this->original_price_bdt ?? 0);
        $curr = (float) ($this->price_bdt ?? 0);
        if ($orig > $curr && $orig > 0) {
            return (int) round((($orig - $curr) / $orig) * 100);
        }
        return null;
    }

    /**
     * Formatted current price in BDT (e.g. "৳ 300").
     */
    public function getFormattedPriceBdtAttribute(): string
    {
        return '৳ ' . number_format($this->price_bdt, 0);
    }

    /**
     * Formatted original / strikethrough price in BDT (e.g. "৳ 750.00").
     */
    public function getFormattedOriginalPriceBdtAttribute(): ?string
    {
        if ($this->original_price_bdt && (float) $this->original_price_bdt > 0) {
            return '৳ ' . number_format($this->original_price_bdt, 2);
        }
        return null;
    }

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

    public function subscriptions()
    {
        return $this->hasMany(UserSpendLessSubscription::class, 'spend_less_card_id');
    }

    /**
     * Seed default cards matching the 5 mobile screenshots.
     */
    public static function seedDefaultCards(bool $forceUpdate = false): void
    {
        if (static::count() > 0 && !$forceUpdate) {
            return;
        }

        // 1. New User Weekly Card (Screenshot 5)
        static::updateOrCreate(
            ['card_type' => 'new_user_weekly'],
            [
                'name'                      => 'New User Weekly Card',
                'category_name'             => 'New User Weekly Card',
                'badge_text'                => '60% OFF',
                'original_price_bdt'        => 750.00,
                'price_bdt'                 => 300.00,
                'price_coins'               => 8100,
                'duration_days'             => 7,
                'instant_reward_coins'      => 8100,
                'instant_reward_text'       => 'Gems in total 8100',
                'daily_checkin_total_coins' => 2020,
                'daily_checkin_text'        => 'Gems in total 2020',
                'total_return_coins'        => 10120,
                'card_color'                => '#EC4899',
                'banner_tag'                => 'New User Weekly Card: 10,120 Gems + New Star 3D Avatar Frame!',
                'daily_schedule'            => [
                    ['day' => 1, 'coins' => 8100, 'extra' => 'NEW STAR Avatar Frame'],
                    ['day' => 2, 'coins' => 300,  'extra' => null],
                    ['day' => 3, 'coins' => 210,  'extra' => null],
                    ['day' => 4, 'coins' => 500,  'extra' => null],
                    ['day' => 5, 'coins' => 300,  'extra' => null],
                    ['day' => 6, 'coins' => 210,  'extra' => null],
                    ['day' => 7, 'coins' => 500,  'extra' => 'New Star Title Badge'],
                ],
                'extra_rewards'             => [
                    ['title' => '7 Days NEW STAR Frame', 'tag' => '7days', 'validity' => '7days', 'icon' => 'frame_diamond', 'image' => 'assets/images/vip/new_star_frame.png'],
                    ['title' => '7 Days Newbie Glow',   'tag' => '7days', 'validity' => '7days', 'icon' => 'chat_bubble',   'image' => null],
                    ['title' => 'Bonus Lucky Cards x7', 'tag' => 'x7',    'validity' => 'x7',    'icon' => 'lucky_card',    'image' => null],
                ],
                'description'               => 'New User Weekly Card: 8,100 Instant Gems + 2,020 Daily Check-in Gems + 7D NEW STAR Frame',
                'sort_order'                => 1,
                'is_active'                 => true,
            ]
        );

        // 2. Super Monthly Card (Screenshot 2)
        static::updateOrCreate(
            ['card_type' => 'super_monthly'],
            [
                'name'                      => 'Super Monthly Card',
                'category_name'             => 'Super Monthly Card',
                'badge_text'                => '50% OFF',
                'original_price_bdt'        => 2400.00,
                'price_bdt'                 => 1200.00,
                'price_coins'               => 32940,
                'duration_days'             => 30,
                'instant_reward_coins'      => 32940,
                'instant_reward_text'       => 'Gems in total 32940',
                'daily_checkin_total_coins' => 26330,
                'daily_checkin_text'        => 'Gems in total 26330',
                'total_return_coins'        => 59270,
                'card_color'                => '#7C4DFF',
                'banner_tag'                => 'Super Monthly Card: 59,270 Gems + Outfits & Rewards!',
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
                    ['title' => '30 Days Gold Frame',     'tag' => '30days', 'validity' => '30days', 'icon' => 'frame_gold',   'image' => null],
                    ['title' => '30 Days Luxury Bubble',  'tag' => '30days', 'validity' => '30days', 'icon' => 'chat_bubble',  'image' => null],
                    ['title' => '30 Days Privilege Badge', 'tag' => '30days', 'validity' => '30days', 'icon' => 'badge_svip',   'image' => null],
                    ['title' => 'Bonus Lucky Cards x15',  'tag' => 'x15',    'validity' => 'x15',    'icon' => 'lucky_card',  'image' => null],
                ],
                'description'               => 'Super Monthly Card: 32,940 Instant Gems + 26,330 Daily Check-in Gems + Outfits',
                'sort_order'                => 2,
                'is_active'                 => true,
            ]
        );

        // 3. Luxury Monthly Card (Screenshot 3)
        static::updateOrCreate(
            ['card_type' => 'luxury_monthly'],
            [
                'name'                      => 'Luxury Monthly Card',
                'category_name'             => 'Luxury Monthly Card',
                'badge_text'                => '50% OFF',
                'original_price_bdt'        => 4800.00,
                'price_bdt'                 => 2400.00,
                'price_coins'               => 66600,
                'duration_days'             => 30,
                'instant_reward_coins'      => 66600,
                'instant_reward_text'       => 'Gems in total 66600',
                'daily_checkin_total_coins' => 87110,
                'daily_checkin_text'        => 'Gems in total 87110',
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
                    ['day' => 10, 'coins' => 2953,  'extra' => null],
                    ['day' => 11, 'coins' => 2953,  'extra' => null],
                    ['day' => 12, 'coins' => 2953,  'extra' => null],
                    ['day' => 13, 'coins' => 2953,  'extra' => null],
                    ['day' => 14, 'coins' => 2953,  'extra' => 'Super Card x2'],
                    ['day' => 15, 'coins' => 2953,  'extra' => null],
                    ['day' => 16, 'coins' => 2953,  'extra' => null],
                    ['day' => 17, 'coins' => 2953,  'extra' => null],
                    ['day' => 18, 'coins' => 2953,  'extra' => null],
                    ['day' => 19, 'coins' => 2953,  'extra' => null],
                    ['day' => 20, 'coins' => 2953,  'extra' => null],
                    ['day' => 21, 'coins' => 2953,  'extra' => null],
                    ['day' => 22, 'coins' => 2953,  'extra' => null],
                    ['day' => 23, 'coins' => 2953,  'extra' => null],
                    ['day' => 24, 'coins' => 2953,  'extra' => null],
                    ['day' => 25, 'coins' => 2953,  'extra' => null],
                    ['day' => 26, 'coins' => 2953,  'extra' => null],
                    ['day' => 27, 'coins' => 2953,  'extra' => null],
                    ['day' => 28, 'coins' => 2953,  'extra' => null],
                    ['day' => 29, 'coins' => 2953,  'extra' => null],
                    ['day' => 30, 'coins' => 2953,  'extra' => 'Luxury SVIP Crown'],
                ],
                'extra_rewards'             => [
                    ['title' => '30 Days Diamond Frame', 'tag' => '30days', 'validity' => '30days', 'icon' => 'frame_diamond', 'image' => null],
                    ['title' => '30 Days Chat Bubble',   'tag' => '30days', 'validity' => '30days', 'icon' => 'chat_bubble',   'image' => null],
                    ['title' => '30 Days VIP Title',     'tag' => '30days', 'validity' => '30days', 'icon' => 'svip_crown',    'image' => null],
                    ['title' => 'Bonus Lucky Cards x30', 'tag' => 'x30',    'validity' => 'x30',    'icon' => 'lucky_card',    'image' => null],
                ],
                'description'               => 'Luxury Monthly Card: 66,600 Instant Gems + 87,110 Daily Check-in Gems + Outfits & Rewards',
                'sort_order'                => 3,
                'is_active'                 => true,
            ]
        );

        // 4. Super Weekly Card (Screenshot 4)
        static::updateOrCreate(
            ['card_type' => 'super_weekly'],
            [
                'name'                      => 'Super Weekly Card',
                'category_name'             => 'Super Weekly Card',
                'badge_text'                => '30% OFF',
                'original_price_bdt'        => 642.86,
                'price_bdt'                 => 450.00,
                'price_coins'               => 12150,
                'duration_days'             => 7,
                'instant_reward_coins'      => 12150,
                'instant_reward_text'       => 'Gems in total 12150',
                'daily_checkin_total_coins' => 2540,
                'daily_checkin_text'        => 'Gems in total 2540',
                'total_return_coins'        => 14690,
                'card_color'                => '#FF4081',
                'banner_tag'                => 'Super Weekly Card: 14,690 Gems + Outfits!',
                'daily_schedule'            => [
                    ['day' => 1, 'coins' => 12150, 'extra' => '7D Emerald Frame'],
                    ['day' => 2, 'coins' => 240,   'extra' => null],
                    ['day' => 3, 'coins' => 400,   'extra' => null],
                    ['day' => 4, 'coins' => 500,   'extra' => null],
                    ['day' => 5, 'coins' => 600,   'extra' => null],
                    ['day' => 6, 'coins' => 300,   'extra' => null],
                    ['day' => 7, 'coins' => 500,   'extra' => 'Weekly Card Badge'],
                ],
                'extra_rewards'             => [
                    ['title' => '7 Days Emerald Frame',  'tag' => '7days', 'validity' => '7days', 'icon' => 'frame_avatar', 'image' => null],
                    ['title' => '7 Days Chat Bubble',    'tag' => '7days', 'validity' => '7days', 'icon' => 'chat_bubble', 'image' => null],
                    ['title' => '7 Days VIP Badge',      'tag' => '7days', 'validity' => '7days', 'icon' => 'badge_svip',  'image' => null],
                    ['title' => 'Bonus Lucky Cards x7',  'tag' => 'x7',    'validity' => 'x7',    'icon' => 'lucky_card',  'image' => null],
                ],
                'description'               => 'Super Weekly Card: 12,150 Instant Gems + 2,540 Daily Check-in Gems + Outfits',
                'sort_order'                => 4,
                'is_active'                 => true,
            ]
        );
    }
}
