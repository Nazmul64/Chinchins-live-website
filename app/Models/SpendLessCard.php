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
     * Seed default cards (disabled - cards are created manually via Admin Panel).
     */
    public static function seedDefaultCards(bool $forceUpdate = false): void
    {
        // No-op: Spend less cards are managed manually via Admin Panel
    }
}
